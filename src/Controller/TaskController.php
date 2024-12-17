<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Utils\ValidationErrorFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskController extends AbstractController {
    private TaskRepository $taskRepository;

    public function __construct(TaskRepository $taskRepository) {
        $this->taskRepository = $taskRepository;
    }

    //1. Task erstellen
    #[Route('/tasks', name: 'create_task', methods: ['POST'])]
    public function createTask(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = $request->getContent();

        try {
            //Deserialize & validate task data
            $task = $serializer->deserialize($data, Task::class, 'json');
            $errors = $validator->validate($task);
            if (count($errors) > 0) {
                return $this->json([
                    'error' => 'Validation failed',
                    'details' => ValidationErrorFormatter::format($errors),
                ], 400);
            }

            $this->taskRepository->save($task);
            return $this->json($task, 201);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Invalid data',
                'details' => $e->getMessage()
            ], 400);
        }
    }


    //2. Alle Tasks abrufen
    #[Route('/tasks', name: 'get_tasks', methods: ['GET'])]
    public function getTasks(Request $request): JsonResponse {
        //Get optional parameters
        $status = $request->query->get('status');
        $page = $request->query->get('page');

        //Validate parameters if set
        if ($status && !in_array($status, ['pending', 'in_progress', 'completed'], true)) {
            return $this->json(['error' => 'Invalid status value.'], 404);
        }
        if ($page && $page < 1) {
            return $this->json(['error' => 'Invalid pagination values.'], 404);
        }

        //Return paginated tasks if set
        if ($page) {
            $limit = $request->query->get('limit') ?? 10;
            $result = $this->taskRepository->findTasksByParameters($status, $page, $limit);

            return $this->json([
                'data' => $result['data'],
                'pagination' => [
                    'total' => $result['total'],
                    'current_page' => $result['current_page'],
                    'limit' => $result['limit'],
                    'total_pages' => ceil($result['total'] / $result['limit'])
                ]
            ]);
        }

        //Return tasks without pagination
        $tasks = $this->taskRepository->findTasksByParameters($status);
        return $this->json($tasks);
    }


    //3. Einzelnen Task abrufen
    #[Route('/tasks/{id}', name: 'get_task', methods: ['GET'])]
    public function getTask(int $id): JsonResponse {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            return $this->json(['error' => 'Task not found'], 404);
        }

        return $this->json($task);
    }

    //4. Task aktualisieren
    #[Route('/tasks/{id}', name: 'update_task', methods: ['PUT'])]
    public function updateTask(
        int $id,
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            return $this->json(['error' => 'Task not found'], 404);
        }

        //Add data to existing task
        $serializer->deserialize(
            $request->getContent(),
            Task::class,
            'json',
            ['object_to_populate' => $task]
        );

        //Validate task data
        $errors = $validator->validate($task);
        if (count($errors) > 0) {
            return $this->json([
                'error' => 'Validation failed',
                'details' => ValidationErrorFormatter::format($errors),
            ], 400);
        }

        $this->taskRepository->save($task);
        return $this->json($task);
    }

    //5. Task löschen
    #[Route('/tasks/{id}', name: 'delete_task', methods: ['DELETE'])]
    public function deleteTask(int $id): JsonResponse {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            return $this->json(['error' => 'Task not found'], 404);
        }

        $this->taskRepository->remove($task);
        return $this->json(['message' => 'Task deleted successfully']);
    }
}
