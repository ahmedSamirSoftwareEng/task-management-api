<?php

namespace Database\Seeders;

use App\Enums\TaskStatusEnum;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {

        $userId1 = User::where('email', 'user1@example.com')->value('id');
        $userId2 = User::where('email', 'user2@example.com')->value('id');


        $task1 = Task::create([
            'title'       => 'Gather task API requirements',
            'description' => 'Review specs and list all endpoints, roles, and validations.',
            'assigned_to' => $userId1,
            'due_date'    => Carbon::now()->addDays(2),
            'status'      => 'completed',
        ]);

        $task2 = Task::create([
            'title'       => 'Create ERD for task system',
            'description' => 'Design tables for users, tasks, dependencies, and RBAC.',
            'assigned_to' => $userId1,
            'due_date'    => Carbon::now()->addDays(3),
            'status'      => 'cancelled',
        ]);

        $task3 = Task::create([
            'title'       => 'Implement task CRUD API',
            'description' => 'Build endpoints for creating, updating, listing, and viewing tasks.',
            'assigned_to' => $userId1,
            'due_date'    => Carbon::now()->addDays(4),
            'status'      => 'pending',
        ]);

        $task4 = Task::create([
            'title'       => 'Configure role-based access control',
            'description' => 'Integrate Spatie Permission and apply policies to endpoints.',
            'assigned_to' => $userId1,
            'due_date'    => Carbon::now()->addDays(5),
            'status'      => 'pending',
        ]);

        $task5 = Task::create([
            'title'       => 'Prepare Postman collection & docs',
            'description' => 'Document all API endpoints and export collection for submission.',
            'assigned_to' => $userId2,
            'due_date'    => Carbon::now()->addDays(6),
            'status'      => 'in_progress',
        ]);


        $task3->dependencies()->attach([$task1->id, $task2->id]);

        $task4->dependencies()->attach($task3->id);

        $task5->dependencies()->attach([$task3->id, $task4->id]);
    }
}
