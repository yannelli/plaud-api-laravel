<?php

namespace Yannelli\LaravelPlaud\Models;

class EventParam
{
    public function __construct(
        public string $action,
        public string $fileID,
        public string $fileKey,
        public string $from,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            action: $data['action'] ?? '',
            fileID: $data['fileID'] ?? '',
            fileKey: $data['fileKey'] ?? '',
            from: $data['from'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'action' => $this->action,
            'fileID' => $this->fileID,
            'fileKey' => $this->fileKey,
            'from' => $this->from,
        ];
    }
}
