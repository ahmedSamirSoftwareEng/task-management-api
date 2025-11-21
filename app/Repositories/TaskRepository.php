<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function all(): Collection
    {
        return Task::with(['dependencies', 'dependents'])->get();
    }

    public function find(int $id): ?Task
    {
        return Task::with(['dependencies', 'dependents'])->find($id);
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task->fresh();
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    public function filterTasks(array $filters): Collection
    {
        $query = Task::with(['dependencies', 'dependents']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (isset($filters['due_date_from'])) {
            $query->where('due_date', '>=', $filters['due_date_from']);
        }

        if (isset($filters['due_date_to'])) {
            $query->where('due_date', '<=', $filters['due_date_to']);
        }

        return $query->get();
    }
}
