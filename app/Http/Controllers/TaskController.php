<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}


    public function index(GetTaskRequest $request)
    {
        $filters = $request->validated();
        $user = auth()->user();
        $tasks = $this->taskService->listTasks($filters, $user);
        $tasks = $this->taskRepository->filterTasks($filters);

        return TaskResource::collection($tasks);
    }
    public function show($id)
    {
        $user = auth()->user();

        try {
            $task = $this->taskService->getTaskForUser($id, $user);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'You are not authorized to view this task.',
            ], 403);
        }

        if (!$task) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        return new TaskResource($task);
    }

    public function store(StoreTaskRequest $request)
    {
        $task = $this->taskService->createTask($request->validated());
        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $updatedTask = $this->taskService->updateTask($task, $request->validated(), $request->user());

        return new TaskResource($updatedTask);
    }


    public function destroy(Task $task): JsonResponse
    {
        $this->taskService->deleteTask($task);

        return response()->json([
            'message' => 'Task deleted successfully',
        ], 200);
    }
}
