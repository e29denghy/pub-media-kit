<?php

namespace App\Http\Requests\Concerns;

use App\Http\Requests\StorePublishJobRequest;
use App\Publishing\PlatformAdapterRegistry;
use Illuminate\Validation\Rule;

trait NormalizesPublishingInput
{
    protected function prepareForValidation(): void
    {
        $tags = $this->input('tags', []);

        if (is_string($tags)) {
            $tags = collect(preg_split('/[,，]/u', $tags) ?: [])
                ->map(fn ($tag) => trim($tag))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        $combinationId = $this->input('publish_combination_id');

        $this->merge([
            'tags' => is_array($tags) ? array_values($tags) : [],
            'platforms' => $this->input('platforms', []) ?: [],
            'queue' => $this->boolean('queue'),
            'publish_combination_id' => $combinationId === '' || $combinationId === null
                ? null
                : $combinationId,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function platformRules(): array
    {
        return [
            'platforms' => ['nullable', 'array'],
            'platforms.*' => ['string', Rule::in(app(PlatformAdapterRegistry::class)->keys())],
            'publish_combination_id' => [
                'nullable',
                'integer',
                Rule::exists('publish_combinations', 'id')->where(
                    fn ($query) => $query->where('user_id', $this->user()->id),
                ),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $requiresPlatforms = $this instanceof StorePublishJobRequest || $this->boolean('queue');

            if (! $requiresPlatforms) {
                return;
            }

            if ($this->input('platforms') === [] && ! $this->input('publish_combination_id')) {
                $validator->errors()->add('platforms', '至少选择一个平台，或选择一个平台组合。');
            }
        });
    }
}
