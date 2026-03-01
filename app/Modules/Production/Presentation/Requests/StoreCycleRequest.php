<?php

namespace App\Modules\Production\Presentation\Requests;

use App\Modules\Production\Domain\Enums\CycleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreCycleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'started_at' => ['required', 'date'],
            'status' => ['nullable', Rule::enum(CycleStatus::class)],
            'ended_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
