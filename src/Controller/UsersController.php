<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Entity\User as EntityUser;

class UsersController extends AbstractController
{
    /**
     * @Route("/users/list", name="users_list")
     */
   public function getAll(Request $request, EntityManagerInterface $entityManager) {
    $userRepository = $entityManager->getRepository(User::class);
    $users = $userRepository->findAll();

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

    $response = new JsonResponse([
        'success' => true,
        'data' => $userData
    ]);

    return $response;
}

    /**
     * @Route("/users", name="create_user", methods={"POST"})
     */
    public function create(Request $request, EntityManagerInterface $entityManager) {
        $requestData = json_decode($request->getContent(), true);

        // Crear una nueva instancia de la entidad User
        $user = new User();
        $user->setUserName($requestData['userName']);
        $user->setPassword($requestData['password']);
        $user->setEmail($requestData['email']);
        $user->setFirstName($requestData['firstName']);
        $user->setLastName($requestData['lastName']);

        $entityManager->persist($user);
        $entityManager->flush();

        // Crear un objeto con los datos del usuario
        $userData = (object) [
            'id' => $user->getId(),
            'userName' => $user->getUserName(),
            'password' => $user->getPassword(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
        ];

        // Crear la respuesta JSON
        $response = new JsonResponse(['user' => $userData], Response::HTTP_CREATED);

        // Devolver la respuesta
        return $response;
    }
}
