<?php

namespace Yannelli\LaravelPlaud\Models\Responses;

class ResponseAuth
{
    public function __construct(
        public int $status,
        public string $msg,
        public string $accessToken,
        public string $tokenType,
        public int $loginCountPerHour,
        public int $loginTotalPerHour,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'] ?? 0,
            msg: $data['msg'] ?? '',
            accessToken: $data['access_token'] ?? '',
            tokenType: $data['token_type'] ?? '',
            loginCountPerHour: $data['login_count_per_hour'] ?? 0,
            loginTotalPerHour: $data['login_total_per_hour'] ?? 0,
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'msg' => $this->msg,
            'access_token' => $this->accessToken,
            'token_type' => $this->tokenType,
            'login_count_per_hour' => $this->loginCountPerHour,
            'login_total_per_hour' => $this->loginTotalPerHour,
        ];
    }
}
