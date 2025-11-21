<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Support\Collection;

interface TaskRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?Task;

    public function create(array $data): Task;

    public function update(Task $task, array $data): Task;

    public function delete(Task $task): bool;

    public function filterTasks(array $filters): Collection;
}
