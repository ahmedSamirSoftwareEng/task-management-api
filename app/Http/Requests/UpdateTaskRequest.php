<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\TaskStatus;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();

        if ($user->isRegularUser()) {
            return [
                'status' => [
                    'nullable',
                    Rule::in(array_column(TaskStatus::cases(), 'value')),
                ],
            ];
        }

        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => [
                'nullable',
                Rule::in(array_column(TaskStatus::cases(), 'value')),
            ],
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'depends_on' => 'nullable|array',
            'depends_on.*' => 'exists:tasks,id',
        ];
    }
}
