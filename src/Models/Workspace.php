<?php

namespace Yannelli\LaravelPlaud\Models;

class Workspace
{
    public function __construct(
        public string $id,
        public string $name = '',
        public array $raw = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? $data['wid'] ?? $data['workspace_id'] ?? ''),
            name: (string) ($data['name'] ?? $data['workspace_name'] ?? $data['title'] ?? ''),
            raw: $data,
        );
    }

    public function toArray(): array
    {
        return $this->raw !== [] ? $this->raw : [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
