<?php

namespace Yannelli\LaravelPlaud\Models;

class ExtraData
{
    public function __construct(
        public ?TranConfig $tranConfig = null,
        public ?AiContentFrom $aiContentFrom = null,
        public ?AiContentHeader $aiContentHeader = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tranConfig: isset($data['tranConfig']) ? TranConfig::fromArray($data['tranConfig']) : null,
            aiContentFrom: isset($data['aiContentFrom']) ? AiContentFrom::fromArray($data['aiContentFrom']) : null,
            aiContentHeader: isset($data['aiContentHeader']) ? AiContentHeader::fromArray($data['aiContentHeader']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'tranConfig' => $this->tranConfig?->toArray(),
            'aiContentFrom' => $this->aiContentFrom?->toArray(),
            'aiContentHeader' => $this->aiContentHeader?->toArray(),
        ];
    }
}
