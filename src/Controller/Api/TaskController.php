<?php

namespace App\Controller\Api;

header("Access-Control-Allow-Origin:*");
use App\Entity\Task;
use App\Form\Type\TaskFormType;
use App\Service\FirestoreService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\View as ViewAttribute;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormInterface;

class TaskController extends AbstractFOSRestController
{
    private $firestoreService;

    public function __construct(FirestoreService $firestoreService)
    {
        $this->firestoreService = $firestoreService;
    }

    #[Route(path: "api/tasks/list", name: "get_all_tasks", methods: ["GET"])]
    #[ViewAttribute(serializerGroups: ['task'], serializerEnableMaxDepthChecks: true)]
    public function getAll()
    {
        try {
            $tasks = $this->firestoreService->getAllTasks();
            return $tasks;
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => 'Error en la base de datos'], 500);
        }
    }

    #[Route(path: "api/tasks", name: "create_task", methods: ["POST"])]
    #[ViewAttribute(serializerGroups: ['task'], serializerEnableMaxDepthChecks: true)]
    public function create(Request $request)
    {
        $task = new Task();
        $form = $this->createForm(TaskFormType::class, $task);
        $form->handleRequest($request);
 
        if ($form->isSubmitted() && $form->isValid()) {
            $firestoreResponse = $this->firestoreService->createTask($task);
            $jsonData = json_encode($firestoreResponse);

            return new JsonResponse($jsonData, Response::HTTP_CREATED, [], true);
        } else {
            $errors = $this->getFormErrors($form);
            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route(path: "api/tasks/{id}", name: "delete_task", methods: ["DELETE"])]
    #[ViewAttribute(serializerGroups: ['task'], serializerEnableMaxDepthChecks: true)]
    public function delete(string $id)
    {
        $firestoreResponse = $this->firestoreService->deleteTask($id);
        
        if (isset($firestoreResponse['error'])) {
            return new JsonResponse($firestoreResponse, Response::HTTP_INTERNAL_SERVER_ERROR);
        } else {
            return new JsonResponse($firestoreResponse, Response::HTTP_OK);
        }
    }

    #[Route(path: "api/tasks/{id}", name: "update_task", methods: ["PATCH"])]
    #[ViewAttribute(serializerGroups: ['task'], serializerEnableMaxDepthChecks: true)]
    public function update(Request $request, string $id)
    {
        $existingTask = $this->firestoreService->getTaskById($id);

        if (!$existingTask) {
            return new JsonResponse(['error' => 'La tarea no existe.'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(TaskFormType::class);
        $form->submit($request->request->all(), false); // El segundo argumento 'false' permite la actualización parcial

        if ($form->isSubmitted() && $form->isValid()) {
            $firestoreResponse = $this->firestoreService->updateTask($id, $form->getData());
            $jsonData = json_encode($firestoreResponse);

            return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
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
