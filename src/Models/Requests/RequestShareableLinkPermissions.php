<?php

namespace Yannelli\LaravelPlaud\Models\Requests;

class RequestShareableLinkPermissions
{
    public function __construct(
        public ?int $isAudio = null,
        public ?int $isTrans = null,
        public ?int $isAiContent = null,
        public ?int $isMindmap = null,
    ) {}

    public function toArray(): array
    {
        return [
            'is_audio' => $this->isAudio,
            'is_trans' => $this->isTrans,
            'is_ai_content' => $this->isAiContent,
            'is_mindmap' => $this->isMindmap,
        ];
    }
}
