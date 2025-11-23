<?php

namespace Yannelli\LaravelPlaud\Models;

class TranConfig
{
    public function __construct(
        public string $llm = '',
        public string $type = '',
        public string $language = '',
        public string $typeType = '',
        public int $diarization = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            llm: $data['llm'] ?? '',
            type: $data['type'] ?? '',
            language: $data['language'] ?? '',
            typeType: $data['type_type'] ?? '',
            diarization: $data['diarization'] ?? 0,
        );
    }

    public function toArray(): array
    {
        return [
            'llm' => $this->llm,
            'type' => $this->type,
            'language' => $this->language,
            'type_type' => $this->typeType,
            'diarization' => $this->diarization,
        ];
    }
}
