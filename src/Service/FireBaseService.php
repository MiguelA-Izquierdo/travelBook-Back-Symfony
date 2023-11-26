<?php
namespace App\Service;

use App\Entity\Task;
use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Firestore\FirestoreClient;

class FirestoreService
{
    private $db;

    public function __construct(string $serviceAccountFilePath)
    {
        $this->db = new FirestoreClient([
            'keyFilePath' => '../Keys\todo-638a2-165026564644.json',
            'projectId' => 'todo-638a2',
        ]);
    }

    public function getAllTasks()
    {
     try {
        $tasksCollection = $this->db->collection('ToDo');

        $query = $tasksCollection->documents();

        $tasks = [];

        foreach ($query as $document) {
            if ($document->exists()) {
                $taskId = $document->id();          
                $taskData = $document->data();
                $taskData['id'] = $taskId;

                $tasks[] = $taskData;
            }
        }

        return $tasks;
        } catch (GoogleException $e) {

            $errorResponse = [
                'error' => true,
                'message' => 'Error al obtener tareas de Firestore: ' . $e->getMessage(),
                'code' => $e->getCode(),
            ];

            return json_encode($errorResponse);
        }
    }

    public function getTaskById(string $taskId): array
    {
        try {
            $tasksCollection = $this->db->collection('ToDo');

            // Obtener el documento con el ID proporcionado
            $taskDocument = $tasksCollection->document($taskId)->snapshot();

            // Verificar si el documento existe
            if ($taskDocument->exists()) {
                // Obtener los datos del documento y agregar el ID
                $taskData = $taskDocument->data();
                $taskData['id'] = $taskDocument->id();

                return $taskData;
            } else {
                // El documento no existe, devolver un JSON indicando que no existe
                return [
                    'error' => true,
                    'message' => 'No existe una tarea con el ID proporcionado.',
                ];
            }
        } catch (GoogleException $e) {
            // Manejar excepciones de Firestore, por ejemplo, loggear o lanzar una excepción personalizada
            return [
                'error' => true,
                'message' => 'Error al obtener tarea de Firestore: ' . $e->getMessage(),
            ];
        }
    }

    public function createTask(Task $task)
    {
        try {
            $tasksCollection = $this->db->collection('ToDo');

            $newTask = [
                'owner' => $task->getOwner(),
                'title' => $task->getTitle(),
                'isCompleted' => $task->getIsCompleted(),
            ];

            $tasksCollection->add($newTask);

            return $newTask;
        } catch (GoogleException $e) {
            $errorResponse = [
                'error' => true,
                'message' => 'Error al crear tarea en Firestore: ' . $e->getMessage(),
                'code' => $e->getCode(),
            ];

            return json_encode($errorResponse);
        }
    }

    public function updateTask(string $id, Task $task)
    {
        try {
            $tasksCollection = $this->db->collection('ToDo');
            $existingTask = $tasksCollection->document($id)->snapshot();

            if ($existingTask->exists()) {
                $documentRef = $existingTask->reference();

                $documentRef->set([
                    'owner' => $task->getOwner(),
                    'title' => $task->getTitle(),
                    'isCompleted' => $task->getIsCompleted(),
                ]);

                $updatedTask = $documentRef->snapshot()->data();

                return $updatedTask;
            } else {
                return [
                    'error' => true,
                    'message' => 'La tarea no existe en Firestore.',
                ];
            }
        } catch (GoogleException $e) {
            return [
                'error' => true,
                'message' => 'Error al actualizar tarea en Firestore: ' . $e->getMessage(),
                'code' => $e->getCode(),
            ];
        }
    }

    public function deleteTask(string $id)
    {
        try {
            $tasksCollection = $this->db->collection('ToDo');

            $existingTask = $tasksCollection->document($id)->snapshot();

            if ($existingTask->exists()) {
                $tasksCollection->document($id)->delete();

                return [
                    'message' => 'La tarea se eliminó correctamente.',
                ];
            } else {
                return [
                    'error' => true,
                    'message' => 'La tarea no existe en Firestore.',
                ];
            }
        } catch (GoogleException $e) {
            $errorResponse = [
                'error' => true,
                'message' => 'Error al eliminar tarea en Firestore: ' . $e->getMessage(),
                'code' => $e->getCode(),
            ];

            return $errorResponse;
        }
    }

    public function getFirestoreClient(): FirestoreClient
    {
        return $this->db;
    }
}
