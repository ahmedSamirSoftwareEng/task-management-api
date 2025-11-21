<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Repositories\TaskRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {}

    /**
     * Display a listing of tasks with filters
     */
    public function index(GetTaskRequest $request)
    {
        $filters = $request->validated();
        $user = auth()->user();
        if ($user->isRegularUser()) {
            $filters['assigned_to'] = $user->id;
        }
        $tasks = $this->taskRepository->filterTasks($filters);

        return TaskResource::collection($tasks);
    }
    public function show($id)
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $user = auth()->user();

        if ($user->hasRole('user') && $task->assigned_to !== $user->id) {
            return response()->json(['message' => 'You are not authorized to view this task.'], 403);
        }

        return new TaskResource($task);
    }

    public function store(StoreTaskRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $dependsOn = $data['depends_on'] ?? [];
            unset($data['depends_on']);

            $task = $this->taskRepository->create($data);

            if (!empty($dependsOn)) {
                if (in_array($task->id, $dependsOn)) {
                    throw ValidationException::withMessages([
                        'depends_on' => ['A task cannot depend on itself.']
                    ]);
                }

                $task->dependencies()->attach($dependsOn);
            }

            DB::commit();

            return (new TaskResource($task->load(['dependencies', 'dependents'])))
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $data = $request->validated();
        $user = $request->user();
        if ($user->isRegularUser() && !$user->ownsTask($task)) {
            return response()->json([
                'message' => 'You are not authorized to update this task.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            if ($user->isRegularUser()) {
                $data = ['status' => $data['status']];

                if ($data['status'] === 'completed') {
                    $incompleteDeps = $task->dependencies()
                        ->where('status', '!=', 'completed')
                        ->exists();

                    if ($incompleteDeps) {
                        throw ValidationException::withMessages([
                            'status' => ['Cannot complete task until all dependencies are completed.']
                        ]);
                    }
                }
            }

            $dependsOn = $data['depends_on'] ?? null;
            unset($data['depends_on']);


            $updatedTask = $this->taskRepository->update($task, $data);


            if ($user->isManager() && $dependsOn !== null) {

                if (in_array($task->id, $dependsOn)) {
                    throw ValidationException::withMessages([
                        'depends_on' => ['A task cannot depend on itself.']
                    ]);
                }


                foreach ($dependsOn as $depId) {
                    if ($this->hasCircularDependency($task->id, $depId)) {
                        throw ValidationException::withMessages([
                            'depends_on' => ["Task {$depId} indirectly depends on Task {$task->id}. Circular dependency detected."]
                        ]);
                    }
                }

                $updatedTask->dependencies()->sync($dependsOn);
            }

            DB::commit();

            return new TaskResource($updatedTask->load(['dependencies', 'dependents']));
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function destroy(Task $task): JsonResponse
    {
        if ($task->dependents()->exists()) {
            return response()->json([
                'message' => 'Cannot delete task because other tasks depend on it.'
            ], 422);
        }

        $this->taskRepository->delete($task);

        return response()->json([
            'message' => 'Task deleted successfully'
        ], 200);
    }
    private function hasCircularDependency(int $targetTaskId, int $currentId): bool
    {
        $task = Task::with('dependencies')->find($currentId);

        if (!$task) {
            return false;
        }

        foreach ($task->dependencies as $dependency) {
            if ($dependency->id === $targetTaskId) {
                return true;
            }

            if ($this->hasCircularDependency($targetTaskId, $dependency->id)) {
                return true;
            }
        }

        return false;
    }
}
