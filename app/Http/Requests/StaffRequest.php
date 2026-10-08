<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', $user
                ? Rule::unique('users', 'email')->ignore($user->id)
                : Rule::unique('users', 'email')],
            'role' => ['required', 'in:owner,staff'],
            'contact_number' => ['nullable', 'numeric'],
        ];

        if ($user) {
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
            $rules['is_active'] = ['nullable', 'boolean'];
        } else {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        return $rules;
    }
}
