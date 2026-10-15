<?php

namespace App\Publishing;

final class FillStep
{
    public function __construct(
        public readonly int $order,
        public readonly string $action,
        public readonly string $field,
        public readonly ?string $value,
        public readonly string $note,
        public readonly string $noteEn,
    ) {}

    /**
     * @return array{order: int, action: string, field: string, value: ?string, note: string, note_en: string}
     */
    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'action' => $this->action,
            'field' => $this->field,
            'value' => $this->value,
            'note' => $this->note,
            'note_en' => $this->noteEn,
        ];
    }
}
