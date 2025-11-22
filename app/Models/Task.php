<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\TaskStatus;

class Task extends Model
{
    protected $fillable = ['title', 'description', 'status', 'assigned_to', 'due_date'];

    protected $casts = [
        'status' => TaskStatus::class,
        'due_date' => 'date',
    ];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    public function dependencies()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_id');
    }

    public function dependents()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'depends_on_id', 'task_id');
    }
}
