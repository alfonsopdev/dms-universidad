<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->route('user')->id;
        return [
            'name'      => 'sometimes|string|max:255',
            'email'     => "sometimes|email|unique:users,email,{$userId}",
            'password'  => 'nullable|string|min:8',
            'role'      => 'nullable|string|exists:roles,name',
            'is_active' => 'nullable|boolean',
        ];
    }
}