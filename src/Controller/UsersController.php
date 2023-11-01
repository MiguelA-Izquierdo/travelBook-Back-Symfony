<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
class UsersController extends AbstractController{


  /**
     * @Route("/users/list", name="users_list")
     */

public function getAll() {
    $response = new JsonResponse();
    // $response->setContent('<div>Lista de usuarios</div>');
    $response->setData([
        'success' => true,
        'data' => [
            [
                'id' => 1,
                'userName' => 'SrIzquierdo'
            ]
        ]
    ]);
    return $response;
}

}
