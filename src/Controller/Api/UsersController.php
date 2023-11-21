<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Entity\UserFormType;
use App\Service\User\AuthService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\View as ViewAttribute;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;


class UsersController extends AbstractFOSRestController
{
    private $entityManager;
    private $userRepository;
    private $authService;

    public function __construct(EntityManagerInterface $entityManager, AuthService $authService)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $entityManager->getRepository(User::class);
        $this->authService = $authService;
    }

    #[Route(path: "api/users/list", name: "get_all_users", methods: ["GET"])]
    #[ViewAttribute(serializerGroups: ['user'], serializerEnableMaxDepthChecks: true)]
    public function getAll()
    {
        try {
            return $this->userRepository->findAll();
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => 'Error en la base de datos'], 500);
        }
    }

    #[Route(path: "api/users", name: "create_user", methods: ["POST"])]
    #[ViewAttribute(serializerGroups: ['user'], serializerEnableMaxDepthChecks: true)]
    public function create(Request $request, UserPasswordHasherInterface $passwordHasher, SerializerInterface $serializer)
    {
        $requestData = json_decode($request->getContent(), true);

        $user = new User();
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword($user, $requestData['password']);
            $user->setPassword($hashedPassword);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $serializedUser = $serializer->serialize($user, 'json', ['groups' => 'user']);
            return new JsonResponse($serializedUser, Response::HTTP_CREATED, [], true);
        } else {
        $errors = $this->getFormErrors($form);
        return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route(path: "api/users/{id}", name: "delete_user", methods: ["DELETE"])]
    #[ViewAttribute(serializerGroups: ['user'], serializerEnableMaxDepthChecks: true)]
    public function delete(int $id)
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->json(['error' => 'El usuario no fue encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->json(['message' => 'El usuario ha sido eliminado con éxito.'], Response::HTTP_OK);
    }

    #[Route(path: "api/users/{id}", name: "update_user", methods: ["PATCH"])]
    #[ViewAttribute(serializerGroups: ['user'], serializerEnableMaxDepthChecks: true)]
    public function update(int $id, Request $request, UserPasswordHasherInterface $passwordHasher)
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
          return $this->json(['message' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }

      $form = $this->createForm(UserFormType::class, $user);

      $form->submit(json_decode($request->getContent(), true), false); 

      if ($form->isValid()) {
          $password = $form->get('password')->getData();
          if ($password) {
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
          }

          $this->entityManager->flush();

          return $this->json(['message' => 'Usuario actualizado'], Response::HTTP_OK);
      }

      return $this->json(['errors' => $this->getFormErrors($form)], Response::HTTP_BAD_REQUEST);
    }

    #[Route(path: "api/users/login", name: "user_login", methods: ["POST"])]
    #[ViewAttribute(serializerGroups: ['user'], serializerEnableMaxDepthChecks: true)]
    public function login(Request $request, UserPasswordHasherInterface $passwordHasher)
    {
        $requestData = json_decode($request->getContent(), true);
        $username = $requestData['userName'];
        $password = $requestData['password'];

        $user = $this->userRepository->findOneBy(['userName' => $username]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return $this->json(['message' => 'Credenciales incorrectas'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $this->authService->createJWT($user);

        return $this->json(['token' => $token]);
    }

    private function getFormErrors(FormInterface $form): array
    {
      $errors = [];
      foreach ($form->getErrors(true, true) as $error) {
        $fieldName = $error->getOrigin()->getName();
        $errors[$fieldName] = $error->getMessage();
      }

      return $errors;
    }
}
