<?php

use Yannelli\LaravelPlaud\Models\Info;
use Yannelli\LaravelPlaud\Models\EventParam;

describe('Info', function () {
    it('can be instantiated with all properties', function () {
        $eventParam = new EventParam(
            action: 'export',
            fileID: 'file-1',
            fileKey: 'key-1',
            from: 'web'
        );

        $info = new Info(
            eventCat: 'share',
            eventParam: $eventParam
        );

        expect($info->eventCat)->toBe('share')
            ->and($info->eventParam)->toBeInstanceOf(EventParam::class)
            ->and($info->eventParam->action)->toBe('export');
    });

    it('can be created from array', function () {
        $data = [
            'event_cat' => 'download',
            'event_param' => [
                'action' => 'download_audio',
                'fileID' => 'audio-file',
                'fileKey' => 'audio-key',
                'from' => 'mobile',
            ],
        ];

        $info = Info::fromArray($data);

        expect($info->eventCat)->toBe('download')
            ->and($info->eventParam)->toBeInstanceOf(EventParam::class)
            ->and($info->eventParam->action)->toBe('download_audio')
            ->and($info->eventParam->from)->toBe('mobile');
    });

    it('creates default EventParam when missing', function () {
        $data = [
            'event_cat' => 'test',
        ];

        $info = Info::fromArray($data);

        expect($info->eventCat)->toBe('test')
            ->and($info->eventParam)->toBeInstanceOf(EventParam::class)
            ->and($info->eventParam->action)->toBe('')
            ->and($info->eventParam->fileID)->toBe('');
    });

    it('handles completely empty array', function () {
        $info = Info::fromArray([]);

        expect($info->eventCat)->toBe('')
            ->and($info->eventParam)->toBeInstanceOf(EventParam::class);
    });

    it('converts to array correctly', function () {
        $eventParam = new EventParam(
            action: 'upload',
            fileID: 'up-file',
            fileKey: 'up-key',
            from: 'api'
        );

        $info = new Info(
            eventCat: 'upload',
            eventParam: $eventParam
        );

        $array = $info->toArray();

        expect($array)->toBe([
            'event_cat' => 'upload',
            'event_param' => [
                'action' => 'upload',
                'fileID' => 'up-file',
                'fileKey' => 'up-key',
                'from' => 'api',
            ],
        ]);
    });

    it('roundtrips from array to array correctly', function () {
        $originalData = [
            'event_cat' => 'roundtrip',
            'event_param' => [
                'action' => 'test',
                'fileID' => 'test-file',
                'fileKey' => 'test-key',
                'from' => 'test',
            ],
        ];

        $info = Info::fromArray($originalData);
        $resultArray = $info->toArray();

        expect($resultArray)->toBe($originalData);
    });
});
