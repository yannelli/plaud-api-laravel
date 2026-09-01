<?php

namespace Yannelli\LaravelPlaud\Models;

class Device
{
    public function __construct(
        public string $id,
        public string $name = '',
        public ?string $serialNumber = null,
        public array $raw = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? $data['device_id'] ?? ''),
            name: (string) ($data['name'] ?? $data['device_name'] ?? ''),
            serialNumber: isset($data['serial_number']) ? (string) $data['serial_number'] : null,
            raw: $data,
        );
    }

    public function toArray(): array
    {
        return $this->raw !== [] ? $this->raw : array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'serial_number' => $this->serialNumber,
        ], fn ($value) => $value !== null && $value !== '');
    }
}
