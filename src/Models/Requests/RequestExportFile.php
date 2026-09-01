<?php

namespace Yannelli\LaravelPlaud\Models\Requests;

use Yannelli\LaravelPlaud\Models\TransContent;

class RequestExportFile
{
    /**
     * @param array<TransContent> $transContent
     */
    public function __construct(
        public string $fileId,
        public string $promptType,
        public string $toFormat,
        public string $title,
        public string $createTime,
        public ?int $withSpeaker = null,
        public ?int $withTimestamp = null,
        public array $transContent = [],
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
            'trans_content' => array_map(fn($content) => $content->toArray(), $this->transContent),
        ];

        if ($this->summaryId !== null && $this->summaryId !== '') {
            $payload['summary_id'] = $this->summaryId;
        }

        return $payload;
    }
}
