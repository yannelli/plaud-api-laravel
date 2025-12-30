<?php

use Yannelli\LaravelPlaud\Models\Responses\ResponseStatus;
use Yannelli\LaravelPlaud\Models\DataProcessingTranssumm;
use Yannelli\LaravelPlaud\Models\DataProcessingTranssummAi;

describe('ResponseStatus', function () {
    it('can be instantiated with default values', function () {
        $response = new ResponseStatus(status: 200);

        expect($response->status)->toBe(200)
            ->and($response->dataProcessing)->toBe([])
            ->and($response->dataProcessingChatllm)->toBe([])
            ->and($response->dataProcessingTranssumm)->toBeNull()
            ->and($response->dataProcessingAi)->toBe([])
            ->and($response->dataProcessingChatllmAi)->toBe([])
            ->and($response->dataProcessingTranssummAi)->toBeNull()
            ->and($response->dataProcessingEdit)->toBe([]);
    });

    it('can be instantiated with all properties', function () {
        $transsumm = new DataProcessingTranssumm(
            filesTrans: ['file-1', 'file-2'],
            filesSumm: ['file-3']
        );

        $transsummAi = new DataProcessingTranssummAi(
            filesTrans: ['file-4'],
            filesSumm: ['file-5', 'file-6']
        );

        $response = new ResponseStatus(
            status: 200,
            dataProcessing: ['process-1'],
            dataProcessingChatllm: ['chat-1'],
            dataProcessingTranssumm: $transsumm,
            dataProcessingAi: ['ai-1'],
            dataProcessingChatllmAi: ['chatai-1'],
            dataProcessingTranssummAi: $transsummAi,
            dataProcessingEdit: ['edit-1']
        );

        expect($response->dataProcessing)->toBe(['process-1'])
            ->and($response->dataProcessingTranssumm->filesTrans)->toBe(['file-1', 'file-2'])
            ->and($response->dataProcessingTranssummAi->filesSumm)->toBe(['file-5', 'file-6']);
    });

    it('can be created from array', function () {
        $data = [
            'status' => 200,
            'data_processing' => ['proc-1', 'proc-2'],
            'data_processing_chatllm' => ['llm-1'],
            'data_processing_transsumm' => [
                'files_trans' => ['trans-file-1'],
                'files_summ' => ['summ-file-1'],
            ],
            'data_processing_ai' => ['ai-proc-1'],
            'data_processing_chatllm_ai' => ['llm-ai-1'],
            'data_processing_transsumm_ai' => [
                'files_trans' => ['trans-ai-1'],
                'files_summ' => ['summ-ai-1'],
            ],
            'data_processing_edit' => ['edit-1'],
        ];

        $response = ResponseStatus::fromArray($data);

        expect($response->status)->toBe(200)
            ->and($response->dataProcessing)->toBe(['proc-1', 'proc-2'])
            ->and($response->dataProcessingTranssumm)->toBeInstanceOf(DataProcessingTranssumm::class)
            ->and($response->dataProcessingTranssumm->filesTrans)->toBe(['trans-file-1'])
            ->and($response->dataProcessingTranssummAi)->toBeInstanceOf(DataProcessingTranssummAi::class)
            ->and($response->dataProcessingTranssummAi->filesSumm)->toBe(['summ-ai-1']);
    });

    it('handles missing transsumm objects', function () {
        $data = [
            'status' => 200,
            'data_processing' => [],
        ];

        $response = ResponseStatus::fromArray($data);

        expect($response->dataProcessingTranssumm)->toBeNull()
            ->and($response->dataProcessingTranssummAi)->toBeNull();
    });

    it('converts to array correctly', function () {
        $transsumm = new DataProcessingTranssumm(
            filesTrans: ['file-a'],
            filesSumm: ['file-b']
        );

        $response = new ResponseStatus(
            status: 200,
            dataProcessing: ['p1'],
            dataProcessingTranssumm: $transsumm
        );

        $array = $response->toArray();

        expect($array['status'])->toBe(200)
            ->and($array['data_processing'])->toBe(['p1'])
            ->and($array['data_processing_transsumm'])->toBe([
                'files_trans' => ['file-a'],
                'files_summ' => ['file-b'],
            ])
            ->and($array['data_processing_transsumm_ai'])->toBeNull();
    });
});
