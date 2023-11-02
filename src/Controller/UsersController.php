<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class UsersController extends AbstractController{


  /**
   * @Route("/users/list", name="users_list")
   */

public function getAll(Request $request) {
    $response = new JsonResponse();
    $userName= $request->get('userName', 'Unknow');
    $response->setData([
        'success' => true,
        'data' => [
            [
                'id' => 1,
                'userName' => $userName
            ]
        ]
    ]);
    return $response;
}

}
