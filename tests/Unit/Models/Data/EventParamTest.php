<?php

use Yannelli\LaravelPlaud\Models\EventParam;

describe('EventParam', function () {
    it('can be instantiated with all properties', function () {
        $param = new EventParam(
            action: 'export_audio',
            fileID: 'file-123',
            fileKey: 'key-456',
            from: 'web'
        );

        expect($param->action)->toBe('export_audio')
            ->and($param->fileID)->toBe('file-123')
            ->and($param->fileKey)->toBe('key-456')
            ->and($param->from)->toBe('web');
    });

    it('can be created from array', function () {
        $data = [
            'action' => 'export_transcription',
            'fileID' => 'abc-123',
            'fileKey' => 'abc-123',
            'from' => 'mobile',
        ];

        $param = EventParam::fromArray($data);

        expect($param->action)->toBe('export_transcription')
            ->and($param->fileID)->toBe('abc-123')
            ->and($param->fileKey)->toBe('abc-123')
            ->and($param->from)->toBe('mobile');
    });

    it('handles missing keys with default values', function () {
        $param = EventParam::fromArray([]);

        expect($param->action)->toBe('')
            ->and($param->fileID)->toBe('')
            ->and($param->fileKey)->toBe('')
            ->and($param->from)->toBe('');
    });

    it('converts to array correctly', function () {
        $param = new EventParam(
            action: 'download',
            fileID: 'dl-file',
            fileKey: 'dl-key',
            from: 'api'
        );

        $array = $param->toArray();

        expect($array)->toBe([
            'action' => 'download',
            'fileID' => 'dl-file',
            'fileKey' => 'dl-key',
            'from' => 'api',
        ]);
    });

    it('roundtrips from array to array correctly', function () {
        $originalData = [
            'action' => 'share',
            'fileID' => 'share-file',
            'fileKey' => 'share-key',
            'from' => 'desktop',
        ];

        $param = EventParam::fromArray($originalData);
        $resultArray = $param->toArray();

        expect($resultArray)->toBe($originalData);
    });
});
