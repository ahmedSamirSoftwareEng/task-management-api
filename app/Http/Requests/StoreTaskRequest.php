<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\TaskStatus;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
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
