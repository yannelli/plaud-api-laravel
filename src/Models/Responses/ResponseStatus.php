<?php

namespace Yannelli\LaravelPlaud\Models\Responses;

use Yannelli\LaravelPlaud\Models\DataProcessingTranssumm;
use Yannelli\LaravelPlaud\Models\DataProcessingTranssummAi;

class ResponseStatus
{
    public function __construct(
        public int $status,
        public array $dataProcessing = [],
        public array $dataProcessingChatllm = [],
        public ?DataProcessingTranssumm $dataProcessingTranssumm = null,
        public array $dataProcessingAi = [],
        public array $dataProcessingChatllmAi = [],
        public ?DataProcessingTranssummAi $dataProcessingTranssummAi = null,
        public array $dataProcessingEdit = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'] ?? 0,
            dataProcessing: $data['data_processing'] ?? [],
            dataProcessingChatllm: $data['data_processing_chatllm'] ?? [],
            dataProcessingTranssumm: isset($data['data_processing_transsumm'])
                ? DataProcessingTranssumm::fromArray($data['data_processing_transsumm'])
                : null,
            dataProcessingAi: $data['data_processing_ai'] ?? [],
            dataProcessingChatllmAi: $data['data_processing_chatllm_ai'] ?? [],
            dataProcessingTranssummAi: isset($data['data_processing_transsumm_ai'])
                ? DataProcessingTranssummAi::fromArray($data['data_processing_transsumm_ai'])
                : null,
            dataProcessingEdit: $data['data_processing_edit'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'data_processing' => $this->dataProcessing,
            'data_processing_chatllm' => $this->dataProcessingChatllm,
            'data_processing_transsumm' => $this->dataProcessingTranssumm?->toArray(),
            'data_processing_ai' => $this->dataProcessingAi,
            'data_processing_chatllm_ai' => $this->dataProcessingChatllmAi,
            'data_processing_transsumm_ai' => $this->dataProcessingTranssummAi?->toArray(),
            'data_processing_edit' => $this->dataProcessingEdit,
        ];
    }
}
