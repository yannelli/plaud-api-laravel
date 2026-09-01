<?php

namespace Yannelli\LaravelPlaud\Models;

class ExtraData
{
    public function __construct(
        public ?TranConfig $tranConfig = null,
        public ?AiContentFrom $aiContentFrom = null,
        public ?AiContentHeader $aiContentHeader = null,
        public ?TaskIdInfo $taskIdInfo = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $taskIdInfo = $data['task_id_info'] ?? $data['taskIdInfo'] ?? null;

        return new self(
            tranConfig: isset($data['tranConfig']) ? TranConfig::fromArray($data['tranConfig']) : null,
            aiContentFrom: isset($data['aiContentFrom']) ? AiContentFrom::fromArray($data['aiContentFrom']) : null,
            aiContentHeader: isset($data['aiContentHeader']) ? AiContentHeader::fromArray($data['aiContentHeader']) : null,
            taskIdInfo: is_array($taskIdInfo) ? TaskIdInfo::fromArray($taskIdInfo) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'tranConfig' => $this->tranConfig?->toArray(),
            'aiContentFrom' => $this->aiContentFrom?->toArray(),
            'aiContentHeader' => $this->aiContentHeader?->toArray(),
            'task_id_info' => $this->taskIdInfo?->toArray(),
        ];
    }
}
