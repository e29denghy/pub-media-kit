<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesPublishingInput;
use Illuminate\Foundation\Http\FormRequest;

class StorePublishJobRequest extends FormRequest
{
    use NormalizesPublishingInput;

    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('view', $this->route('draft'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->platformRules();
    }
}
