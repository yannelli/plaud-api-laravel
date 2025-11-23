<?php

namespace Yannelli\LaravelPlaud\Models;

class AiContentFrom
{
    public function __construct(
        public string $info = '',
        public string $notes = '',
        public string $location = '',
        public string $attendees = '',
        public string $dateTime = '',
        public string $conclusion = '',
        public string $insertMore = '',
        public string $arrangements = '',
        public string $aiSuggestions = '',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            info: $data['info'] ?? '',
            notes: $data['notes'] ?? '',
            location: $data['location'] ?? '',
            attendees: $data['attendees'] ?? '',
            dateTime: $data['date_time'] ?? '',
            conclusion: $data['conclusion'] ?? '',
            insertMore: $data['insert_more'] ?? '',
            arrangements: $data['arrangements'] ?? '',
            aiSuggestions: $data['ai_suggestions'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'info' => $this->info,
            'notes' => $this->notes,
            'location' => $this->location,
            'attendees' => $this->attendees,
            'date_time' => $this->dateTime,
            'conclusion' => $this->conclusion,
            'insert_more' => $this->insertMore,
            'arrangements' => $this->arrangements,
            'ai_suggestions' => $this->aiSuggestions,
        ];
    }
}
