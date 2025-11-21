<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();

        if ($user->hasRole('user')) {
            return [
                'status' => 'required|in:pending,in_progress,completed,cancelled',
            ];
        }

        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'depends_on' => 'nullable|array',
            'depends_on.*' => 'exists:tasks,id',
        ];
    }
}
