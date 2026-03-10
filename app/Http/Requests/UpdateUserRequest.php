<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admins can update users
        return $this->user()?->role?->name === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->user),
            ],
            'role_id' => ['required', 'exists:roles,id'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The user name is required.',
            'name.max' => 'The user name must not exceed 255 characters.',
            'email.required' => 'An email address is required.',
            'email.email' => 'The email address must be valid.',
            'email.unique' => 'This email is already in use by another user.',
            'role_id.required' => 'A system role is required.',
            'role_id.exists' => 'The selected role does not exist.',
        ];
    }
}
