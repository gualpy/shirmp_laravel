<?php

namespace App\Modules\Auth\Presentation\Requests;

use App\Modules\Auth\Domain\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class RegisterRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', Rule::enum(UserRole::class), Rule::notIn([UserRole::SUPER_ADMIN->value])],
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
