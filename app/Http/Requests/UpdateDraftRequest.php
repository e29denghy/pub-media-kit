<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesPublishingInput;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDraftRequest extends FormRequest
{
    use NormalizesPublishingInput;

    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('update', $this->route('draft'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:100000'],
            'tags' => ['nullable', 'array', 'max:20'],
            'tags.*' => ['string', 'max:40'],
            'queue' => ['sometimes', 'boolean'],
            'media' => ['nullable', 'array', 'max:8'],
            'media.*' => ['file', 'image', 'max:5120'],
            'remove_media' => ['nullable', 'array'],
            'remove_media.*' => ['integer'],
            ...$this->platformRules(),
        ];
    }
}
