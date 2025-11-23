<?php

namespace Yannelli\LaravelPlaud\Models\Responses;

class ResponseShareableLink
{
    public function __construct(
        public int $status,
        public string $url,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'] ?? 0,
            url: $data['url'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'url' => $this->url,
        ];
    }
}
