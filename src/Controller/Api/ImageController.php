<?php

namespace App\Controller\Api;

use App\Entity\Image;
use App\Form\Type\ImageFormType;
use App\Service\MediaFileService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends AbstractFOSRestController
{
    #[Route(path: "/api/images/{filename}", name: "show_image", methods: ["GET"])]
    public function show($filename, MediaFileService $fileUploader): Response
    {
        $imagePath = $fileUploader->getTargetDirectory() . '/' . $filename;

        $content = file_get_contents($imagePath);

        $response = new Response($content);
        $response->headers->set('Content-Type', 'image/jpeg');  // Ajusta el tipo MIME según tu necesidad

        return $response;
    }

    #[Route(path: "/api/images", name: "upload_photo", methods: ["POST"])]
    public function uploadPhoto(Request $request, MediaFileService $fileUploader): JsonResponse
    {
        $image = new Image();
        $form = $this->createForm(ImageFormType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $brochureFile = $form->get('brochure')->getData();

            if ($brochureFile) {
                $brochureFileName = $fileUploader->upload($brochureFile);

                return $this->json([
                    'message' => 'File uploaded successfully',
                    'filename' => $brochureFileName
                ], Response::HTTP_OK);
            }
        } else {
            $errors = $this->getFormErrors($form);
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }
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
