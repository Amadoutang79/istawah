<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('assignMembers', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'user_id'      => ['required', 'exists:users,id'],
            'project_role' => ['required', Rule::in(['OWNER', 'MANAGER', 'MEMBER'])],
        ];
    }
}