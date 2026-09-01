<?php

namespace Yannelli\LaravelPlaud\Models\Requests;

class RequestExportSummary
{
    public function __construct(
        public string $fileId,
        public string $promptType,
        public string $toFormat,
        public string $title,
        public string $createTime,
        public ?int $withSpeaker = null,
        public ?int $withTimestamp = null,
        public string $summaryContent = '',
        public ?string $summaryId = null,
    ) {}

    public function toArray(): array
    {
        $payload = [
            'file_id' => $this->fileId,
            'prompt_type' => $this->promptType,
            'to_format' => $this->toFormat,
            'title' => $this->title,
            'create_time' => $this->createTime,
            'with_speaker' => $this->withSpeaker,
            'with_timestamp' => $this->withTimestamp,
            'summary_content' => $this->summaryContent,
        ];

        if ($this->summaryId !== null && $this->summaryId !== '') {
            $payload['summary_id'] = $this->summaryId;
        }

        return $payload;
    }
}
