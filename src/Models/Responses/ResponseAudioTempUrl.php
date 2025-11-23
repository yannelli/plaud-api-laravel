<?php

namespace Yannelli\LaravelPlaud\Models\Responses;

class ResponseAudioTempUrl
{
    public function __construct(
        public int $status,
        public string $tempUrl,
        public mixed $tempUrlOpus = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'] ?? 0,
            tempUrl: $data['temp_url'] ?? '',
            tempUrlOpus: $data['temp_url_opus'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'temp_url' => $this->tempUrl,
            'temp_url_opus' => $this->tempUrlOpus,
        ];
    }
}
