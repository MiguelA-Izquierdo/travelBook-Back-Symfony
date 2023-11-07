<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\User\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersController extends AbstractController
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

    /**
     * @Route("/users/list", name="users_list", methods={"GET"})
     */
    public function getAll(Request $request)
    {
        $users = $this->userRepository->findAll();

        $userData = [];
        foreach ($users as $user) {
            $userData[] = $this->serializeUser($user);
        }

        return $this->json(['success' => true, 'data' => $userData]);
    }

    /**
     * @Route("/users", name="create_user", methods={"POST"})
     */
    public function create(Request $request, UserPasswordHasherInterface $passwordHasher)
    {
        $requestData = json_decode($request->getContent(), true);
        $plainPassword = 'contrasena_secreta';

        $user = new User();
        $this->updateUserFromRequest($user, $requestData, $passwordHasher, $plainPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $userData = $this->serializeUser($user);

        return $this->json(['user' => $userData], Response::HTTP_CREATED);
    }

    /**
     * @Route("/users/{id}", name="delete_user", methods={"DELETE"})
     */
    public function delete(int $id)
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('El usuario no fue encontrado.');
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->json(['user' => $this->serializeUser($user)], Response::HTTP_OK);
    }

    /**
     * @Route("/users/{id}", name="update_user", methods={"PATCH"})
     */
    public function update(int $id, Request $request, UserPasswordHasherInterface $passwordHasher)
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->json(['message' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), true);
        $plainPassword = 'contrasena_secreta';

        $this->updateUserFromRequest($user, $requestData, $passwordHasher, $plainPassword);

        $this->entityManager->flush();

        return $this->json(['message' => 'Usuario actualizado'], Response::HTTP_OK);
    }


    /**
     * @Route("users/login", name="user_login", methods={"POST"})
     */
    public function login(Request $request, UserPasswordHasherInterface $passwordHasher)
{
    $requestData = json_decode($request->getContent(), true);
    $username = $requestData['userName'];
    $password = $requestData['password'];

    // Verifica las credenciales del usuario utilizando el UserPasswordHasherInterface
    $user = $this->userRepository->findOneBy(['userName' => $username]);
    if (!$user) {
        return $this->json(['message' => 'Credenciales incorrectas'], Response::HTTP_UNAUTHORIZED);
    }

    if ($passwordHasher->isPasswordValid($user, $password)) {
        // Las credenciales son correctas, genera un token JWT
       
        $token = $this->authService->createJWT($user);

        return $this->json(['token' => $token]);
    } else {
        // Las credenciales son incorrectas
        return $this->json(['message' => 'Credenciales incorrectas'], Response::HTTP_UNAUTHORIZED);
    }
    }


    private function updateUserFromRequest(User $user, array $requestData, UserPasswordHasherInterface $passwordHasher, string $plainPassword)
    {
        if (isset($requestData['userName'])) {
            $user->setUserName($requestData['userName']);
        }
        if (isset($requestData['email'])) {
            $user->setEmail($requestData['email']);
        }
        if (isset($requestData['firstName'])) {
            $user->setFirstName($requestData['firstName']);
        }
        if (isset($requestData['lastName'])) {
            $user->setLastName($requestData['lastName']);
        }

        if (isset($requestData['password'])) {
            $hashedPassword = $passwordHasher->hashPassword($user, $requestData['password']);
            $user->setPassword($hashedPassword);
        }
    }

    private function serializeUser(User $user)
    {
        return [
            'id' => $user->getId(),
            'userName' => $user->getUserName(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
        ];
    }
}
