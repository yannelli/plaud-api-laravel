<?php

namespace Yannelli\LaravelPlaud\Models;

class TransContent
{
    public function __construct(
        public string $content,
        public string $speaker,
        public int $endTime,
        public int $startTime,
        public string $embeddingKey = '',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            content: $data['content'] ?? '',
            speaker: $data['speaker'] ?? '',
            endTime: $data['end_time'] ?? 0,
            startTime: $data['start_time'] ?? 0,
            embeddingKey: $data['embeddingKey'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'speaker' => $this->speaker,
            'end_time' => $this->endTime,
            'start_time' => $this->startTime,
            'embeddingKey' => $this->embeddingKey,
        ];
    }
}
