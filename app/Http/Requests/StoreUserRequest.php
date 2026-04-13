<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'nullable|string|min:8',
            'role'      => 'nullable|string|exists:roles,name',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'El nombre es requerido.',
            'email.required' => 'El correo es requerido.',
            'email.unique'   => 'Este correo ya está registrado.',
            'email.email'    => 'El correo no es válido.',
        ];
    }
}