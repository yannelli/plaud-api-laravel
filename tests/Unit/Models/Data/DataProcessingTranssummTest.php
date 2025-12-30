<?php

use Yannelli\LaravelPlaud\Models\DataProcessingTranssumm;

describe('DataProcessingTranssumm', function () {
    it('can be instantiated with default values', function () {
        $processing = new DataProcessingTranssumm();

        expect($processing->filesTrans)->toBe([])
            ->and($processing->filesSumm)->toBe([]);
    });

    it('can be instantiated with file lists', function () {
        $processing = new DataProcessingTranssumm(
            filesTrans: ['file-1', 'file-2', 'file-3'],
            filesSumm: ['file-4', 'file-5']
        );

        expect($processing->filesTrans)->toBe(['file-1', 'file-2', 'file-3'])
            ->and($processing->filesSumm)->toBe(['file-4', 'file-5']);
    });

    it('can be created from array', function () {
        $data = [
            'files_trans' => ['trans-1', 'trans-2'],
            'files_summ' => ['summ-1'],
        ];

        $processing = DataProcessingTranssumm::fromArray($data);

        expect($processing->filesTrans)->toBe(['trans-1', 'trans-2'])
            ->and($processing->filesSumm)->toBe(['summ-1']);
    });

    it('handles missing keys with default values', function () {
        $processing = DataProcessingTranssumm::fromArray([]);

        expect($processing->filesTrans)->toBe([])
            ->and($processing->filesSumm)->toBe([]);
    });

    it('converts to array correctly', function () {
        $processing = new DataProcessingTranssumm(
            filesTrans: ['arr-trans-1'],
            filesSumm: ['arr-summ-1', 'arr-summ-2']
        );

        $array = $processing->toArray();

        expect($array)->toBe([
            'files_trans' => ['arr-trans-1'],
            'files_summ' => ['arr-summ-1', 'arr-summ-2'],
        ]);
    });

    it('roundtrips from array to array correctly', function () {
        $originalData = [
            'files_trans' => ['round-trans-1', 'round-trans-2'],
            'files_summ' => ['round-summ-1'],
        ];

        $processing = DataProcessingTranssumm::fromArray($originalData);
        $resultArray = $processing->toArray();

        expect($resultArray)->toBe($originalData);
    });
});
