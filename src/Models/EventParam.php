<?php

namespace Yannelli\LaravelPlaud\Models;

class EventParam
{
    public function __construct(
        public string $action,
        public string $fileID,
        public string $fileKey,
        public string $from,
        public ?string $summaryId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            action: $data['action'] ?? '',
            fileID: $data['fileID'] ?? '',
            fileKey: $data['fileKey'] ?? '',
            from: $data['from'] ?? '',
            summaryId: $data['summaryId'] ?? $data['summary_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        $payload = [
            'action' => $this->action,
            'fileID' => $this->fileID,
            'fileKey' => $this->fileKey,
            'from' => $this->from,
        ];

        if ($this->summaryId !== null && $this->summaryId !== '') {
            $payload['summaryId'] = $this->summaryId;
        }

        return $payload;
    }
}
