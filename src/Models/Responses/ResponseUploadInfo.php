<?php

namespace Yannelli\LaravelPlaud\Models\Responses;

class ResponseUploadInfo
{
    public function __construct(
        public int $status,
        public string $msg,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'] ?? 0,
            msg: $data['msg'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'msg' => $this->msg,
        ];
    }
}
