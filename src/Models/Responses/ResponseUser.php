<?php

namespace Yannelli\LaravelPlaud\Models\Responses;

use Yannelli\LaravelPlaud\Models\DataUser;
use Yannelli\LaravelPlaud\Models\DataState;

class ResponseUser
{
    public function __construct(
        public int $status,
        public ?DataUser $dataUser = null,
        public ?DataState $dataState = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'] ?? 0,
            dataUser: isset($data['data_user']) ? DataUser::fromArray($data['data_user']) : null,
            dataState: isset($data['data_state']) ? DataState::fromArray($data['data_state']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'data_user' => $this->dataUser?->toArray(),
            'data_state' => $this->dataState?->toArray(),
        ];
    }
}
