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
        public string $refreshToken = '',
        public string $tokenId = '',
        public string $versionTag = '',
    ) {}

    public static function fromArray(array $data): self
    {
        $inner = is_array($data['data'] ?? null) ? $data['data'] : [];
        $accessToken = $data['access_token'] ?? $inner['access_token'] ?? '';

        return new self(
            status: $data['status'] ?? 0,
            msg: $data['msg'] ?? '',
            accessToken: is_string($accessToken) ? $accessToken : '',
            tokenType: $data['token_type'] ?? $inner['token_type'] ?? '',
            loginCountPerHour: $data['login_count_per_hour'] ?? 0,
            loginTotalPerHour: $data['login_total_per_hour'] ?? 0,
            refreshToken: $data['refresh_token'] ?? $inner['refresh_token'] ?? '',
            tokenId: (string) ($data['token_id'] ?? $inner['token_id'] ?? ''),
            versionTag: (string) ($data['version_tag'] ?? $inner['version_tag'] ?? ''),
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
            'refresh_token' => $this->refreshToken,
            'token_id' => $this->tokenId,
            'version_tag' => $this->versionTag,
        ];
    }
}
