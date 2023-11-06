<?php

namespace App\Controller;

use App\Entity\User;
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

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $entityManager->getRepository(User::class);
    }

    /**
     * @Route("/users/list", name="users_list", methods={"GET"})
     */
    public function getAll(Request $request)
    {
        $users = $this->userRepository->findAll();

        $userData = [];
        foreach ($users as $user) {
            $userData[] = [
                'id' => $user->getId(),
                'userName' => $user->getUserName(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'data' => $userData
        ]);
    }

    /**
     * @Route("/users", name="create_user", methods={"POST"})
     */
    public function create(Request $request, UserPasswordHasherInterface $passwordHasher)
    {
        $requestData = json_decode($request->getContent(), true);
        $plainPassword = 'contrasena_secreta';

        $user = new User();
        $user->setUserName($requestData['userName']);
        $user->setPassword($requestData['password']);
        $user->setEmail($requestData['email']);
        $user->setFirstName($requestData['firstName']);
        $user->setLastName($requestData['lastName']);

        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $userData = [
            'id' => $user->getId(),
            'userName' => $user->getUserName(),
            'password' => $user->getPassword(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
        ];

        return new JsonResponse(['user' => $userData], Response::HTTP_CREATED);
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

        return new JsonResponse(['user' => $user], Response::HTTP_CREATED);
    }
}
