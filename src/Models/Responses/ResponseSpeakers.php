<?php

namespace Yannelli\LaravelPlaud\Models\Responses;

use Yannelli\LaravelPlaud\Models\Speaker;

class ResponseSpeakers
{
    /**
     * @param array<Speaker> $speakers
     */
    public function __construct(
        public int $status,
        public string $msg = '',
        public array $speakers = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $list = $data['data_speaker_list'] ?? $data['data'] ?? $data['speakers'] ?? [];

        if (! is_array($list)) {
            $list = [];
        }

        $speakers = [];
        foreach ($list as $item) {
            if (is_array($item)) {
                $speakers[] = Speaker::fromArray($item);
            }
        }

        return new self(
            status: (int) ($data['status'] ?? 0),
            msg: (string) ($data['msg'] ?? ''),
            speakers: $speakers,
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'msg' => $this->msg,
            'data_speaker_list' => array_map(fn (Speaker $speaker) => $speaker->toArray(), $this->speakers),
        ];
    }
}
