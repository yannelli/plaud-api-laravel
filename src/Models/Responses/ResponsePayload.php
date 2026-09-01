<?php

namespace Yannelli\LaravelPlaud\Models\Responses;

/**
 * Generic Plaud envelope used by less-structured endpoints
 * (AI notes, task status, transsumm, share links).
 */
class ResponsePayload
{
    public function __construct(
        public int $status,
        public string $msg = '',
        public mixed $data = null,
        public array $raw = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $payload = $data['data'] ?? $data['payload'] ?? null;

        return new self(
            status: (int) ($data['status'] ?? 0),
            msg: (string) ($data['msg'] ?? ''),
            data: $payload ?? $data,
            raw: $data,
        );
    }

    public function toArray(): array
    {
        return $this->raw !== [] ? $this->raw : [
            'status' => $this->status,
            'msg' => $this->msg,
            'data' => $this->data,
        ];
    }
}
