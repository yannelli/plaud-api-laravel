<?php

namespace Yannelli\LaravelPlaud\Models\Responses;

class ResponseExportFile
{
    public function __construct(
        public int $status,
        public string $msg,
        public string $data,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'] ?? 0,
            msg: $data['msg'] ?? '',
            data: $data['data'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'msg' => $this->msg,
            'data' => $this->data,
        ];
    }
}
