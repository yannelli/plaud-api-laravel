<?php

namespace Yannelli\LaravelPlaud\Models\Responses;

class ResponseOtp
{
    public function __construct(
        public int $status,
        public string $msg = '',
        public string $token = '',
        public ?string $redirectApiBase = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $redirect = $data['data']['domains']['api'] ?? null;

        return new self(
            status: (int) ($data['status'] ?? 0),
            msg: (string) ($data['msg'] ?? ''),
            token: (string) ($data['token'] ?? $data['data']['token'] ?? ''),
            redirectApiBase: is_string($redirect) ? rtrim($redirect, '/') : null,
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'msg' => $this->msg,
            'token' => $this->token,
            'redirect_api_base' => $this->redirectApiBase,
        ];
    }
}
