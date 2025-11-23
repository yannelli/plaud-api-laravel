<?php

namespace Yannelli\LaravelPlaud\Models;

class Info
{
    public function __construct(
        public string $eventCat,
        public EventParam $eventParam,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            eventCat: $data['event_cat'] ?? '',
            eventParam: isset($data['event_param']) ? EventParam::fromArray($data['event_param']) : new EventParam('', '', '', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'event_cat' => $this->eventCat,
            'event_param' => $this->eventParam->toArray(),
        ];
    }
}
