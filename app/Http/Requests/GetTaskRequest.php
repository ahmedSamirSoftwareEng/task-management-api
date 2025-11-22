<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\TaskStatus;

class GetTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'nullable',
                Rule::in(array_column(TaskStatus::cases(), 'value')),
            ],
            'assigned_to' => 'nullable|exists:users,id',
            'due_date_from' => 'nullable|date',
            'due_date_to' => 'nullable|date|after_or_equal:due_date_from',
        ];
    }
}
