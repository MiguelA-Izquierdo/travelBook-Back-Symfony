<?php
namespace App\Service;

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
                $tasks[] = $document->data();
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

    

    public function getFirestoreClient(): FirestoreClient
    {
        return $this->db;
    }
}
