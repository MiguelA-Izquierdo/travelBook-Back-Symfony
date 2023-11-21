<?php

namespace App\Controller\Api;

use App\Service\MediaFileService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImageController
{
    #[Route(path: "api/uploads/{filename}", name: "show_image", methods: ["GET"])]
    public function show($filename, MediaFileService $fileUploader)
    {
        $imagePath = $fileUploader->getTargetDirectory() . '/' . $filename;

        $response = new Response();
        $response->headers->set('Content-Type', 'image/jpeg');  // Ajusta el tipo MIME según tu necesidad
        $response->setContent(file_get_contents($imagePath));

        return $response;
    }

}
