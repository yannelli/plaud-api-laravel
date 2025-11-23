<?php

namespace Yannelli\LaravelPlaud\Models;

class DataFiletagList
{
    public function __construct(
        public string $id,
        public string $name,
        public string $icon,
        public string $color,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            name: $data['name'] ?? '',
            icon: $data['icon'] ?? '',
            color: $data['color'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon' => $this->icon,
            'color' => $this->color,
        ];
    }
}
