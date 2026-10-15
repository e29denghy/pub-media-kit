<?php

namespace App\Http\Requests;

use App\Publishing\PlatformAdapterRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublishCombinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:500'],
            'platforms' => ['required', 'array', 'min:1'],
            'platforms.*' => ['string', Rule::in(app(PlatformAdapterRegistry::class)->keys())],
        ];
    }
}
