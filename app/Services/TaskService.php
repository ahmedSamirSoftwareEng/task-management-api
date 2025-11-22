<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Enums\TaskStatus;

class TaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository
    ) {}

    public function listTasks(array $filters, User $user): Collection
    {
        if ($user->isRegularUser()) {
            $filters['assigned_to'] = $user->id;
        }

        return $this->taskRepository->filterTasks($filters);
    }
    public function getTaskForUser(int $id, User $user): ?Task
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            return null;
        }

        if ($user->hasRole('user') && $task->assigned_to !== $user->id) {
            throw ValidationException::withMessages([
                'authorization' => ['You are not authorized to view this task.'],
            ]);
        }

        return $task;
    }

    public function createTask(array $data): Task
    {
        return DB::transaction(function () use ($data) {
            $dependsOn = $data['depends_on'] ?? [];
            unset($data['depends_on']);

            $task = $this->taskRepository->create($data);

            if (!empty($dependsOn)) {
                if (in_array($task->id, $dependsOn)) {
                    throw ValidationException::withMessages([
                        'depends_on' => ['A task cannot depend on itself.'],
                    ]);
                }

                $task->dependencies()->attach($dependsOn);
            }

            return $task->load(['dependencies', 'dependents']);
        });
    }

    public function updateTask(Task $task, array $data, User $user): Task
    {
        return DB::transaction(function () use ($task, $data, $user) {

            if ($user->isRegularUser() && !$user->ownsTask($task)) {
                throw ValidationException::withMessages([
                    'authorization' => ['You are not authorized to update this task.'],
                ]);
            }

            if ($user->isRegularUser()) {
                $data = ['status' => $data['status']];

                if (($data['status'] ?? null) === TaskStatus::Completed->value) {
                    $incompleteDeps = $task->dependencies()
                        ->where('status', '!=', TaskStatus::Completed->value)
                        ->exists();

                    if ($incompleteDeps) {
                        throw ValidationException::withMessages([
                            'status' => ['Cannot complete task until all dependencies are completed.'],
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
                        'depends_on' => ['A task cannot depend on itself.'],
                    ]);
                }

                foreach ($dependsOn as $depId) {
                    if ($this->createsCycle($task->id, $depId)) {
                        throw ValidationException::withMessages([
                            'depends_on' => ["Task {$depId} indirectly depends on Task {$task->id}. Circular dependency detected."],
                        ]);
                    }
                }

                $updatedTask->dependencies()->sync($dependsOn);
            }

            return $updatedTask->load(['dependencies', 'dependents']);
        });
    }


    public function deleteTask(Task $task): void
    {
        if ($task->dependents()->exists()) {
            throw ValidationException::withMessages([
                'task' => ['Cannot delete task because other tasks depend on it.'],
            ]);
        }

        $this->taskRepository->delete($task);
    }


    private function createsCycle(int $targetTaskId, int $currentId): bool
    {
        $task = $this->taskRepository->find($currentId);

        if (!$task) {
            return false;
        }

        foreach ($task->dependencies as $dependency) {
            if ($dependency->id === $targetTaskId) {
                return true;
            }

            if ($this->createsCycle($targetTaskId, $dependency->id)) {
                return true;
            }
        }

        return false;
    }
}
