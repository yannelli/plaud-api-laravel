<?php

namespace Yannelli\LaravelPlaud\Models;

class TaskIdInfo
{
    public function __construct(
        public ?string $summaryId = null,
        public ?string $transId = null,
        public array $raw = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            summaryId: isset($data['summary_id']) ? (string) $data['summary_id'] : (isset($data['summaryId']) ? (string) $data['summaryId'] : null),
            transId: isset($data['trans_id']) ? (string) $data['trans_id'] : (isset($data['transId']) ? (string) $data['transId'] : null),
            raw: $data,
        );
    }

    public function toArray(): array
    {
        return $this->raw !== [] ? $this->raw : array_filter([
            'summary_id' => $this->summaryId,
            'trans_id' => $this->transId,
        ], fn ($value) => $value !== null);
    }
}
