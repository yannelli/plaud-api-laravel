<?php

namespace Yannelli\LaravelPlaud\Models\Requests;

use Yannelli\LaravelPlaud\Models\Info;

class RequestUploadInfo
{
    public function __construct(
        public Info $info,
    ) {}

    public function toArray(): array
    {
        return [
            'info' => $this->info->toArray(),
        ];
    }
}
