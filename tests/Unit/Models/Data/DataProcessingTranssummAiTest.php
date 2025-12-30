<?php

use Yannelli\LaravelPlaud\Models\DataProcessingTranssummAi;

describe('DataProcessingTranssummAi', function () {
    it('can be instantiated with default values', function () {
        $processing = new DataProcessingTranssummAi();

        expect($processing->filesTrans)->toBe([])
            ->and($processing->filesSumm)->toBe([]);
    });

    it('can be instantiated with file lists', function () {
        $processing = new DataProcessingTranssummAi(
            filesTrans: ['ai-file-1', 'ai-file-2'],
            filesSumm: ['ai-file-3', 'ai-file-4', 'ai-file-5']
        );

        expect($processing->filesTrans)->toBe(['ai-file-1', 'ai-file-2'])
            ->and($processing->filesSumm)->toBe(['ai-file-3', 'ai-file-4', 'ai-file-5']);
    });

    it('can be created from array', function () {
        $data = [
            'files_trans' => ['ai-trans-1'],
            'files_summ' => ['ai-summ-1', 'ai-summ-2'],
        ];

        $processing = DataProcessingTranssummAi::fromArray($data);

        expect($processing->filesTrans)->toBe(['ai-trans-1'])
            ->and($processing->filesSumm)->toBe(['ai-summ-1', 'ai-summ-2']);
    });

    it('handles missing keys with default values', function () {
        $processing = DataProcessingTranssummAi::fromArray([]);

        expect($processing->filesTrans)->toBe([])
            ->and($processing->filesSumm)->toBe([]);
    });

    it('converts to array correctly', function () {
        $processing = new DataProcessingTranssummAi(
            filesTrans: ['arr-ai-trans'],
            filesSumm: ['arr-ai-summ']
        );

        $array = $processing->toArray();

        expect($array)->toBe([
            'files_trans' => ['arr-ai-trans'],
            'files_summ' => ['arr-ai-summ'],
        ]);
    });

    it('roundtrips from array to array correctly', function () {
        $originalData = [
            'files_trans' => ['round-ai-trans-1', 'round-ai-trans-2'],
            'files_summ' => ['round-ai-summ-1'],
        ];

        $processing = DataProcessingTranssummAi::fromArray($originalData);
        $resultArray = $processing->toArray();

        expect($resultArray)->toBe($originalData);
    });
});
