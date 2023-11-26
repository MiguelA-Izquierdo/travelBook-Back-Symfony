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
    private $entityManager;
    private $taskRepository;
    private $firestoreService;

    public function __construct(EntityManagerInterface $entityManager, FirestoreService $firestoreService)
    {
        $this->entityManager = $entityManager;
        $this->taskRepository = $entityManager->getRepository(Task::class);
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
