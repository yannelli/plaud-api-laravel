<?php

use Yannelli\LaravelPlaud\Models\Requests\RequestUploadInfo;
use Yannelli\LaravelPlaud\Models\Info;
use Yannelli\LaravelPlaud\Models\EventParam;

describe('RequestUploadInfo', function () {
    it('can be instantiated with Info object', function () {
        $eventParam = new EventParam(
            action: 'export_audio',
            fileID: 'file-123',
            fileKey: 'file-123',
            from: 'web'
        );

        $info = new Info(
            eventCat: 'share',
            eventParam: $eventParam
        );

        $request = new RequestUploadInfo(info: $info);

        expect($request->info)->toBeInstanceOf(Info::class)
            ->and($request->info->eventCat)->toBe('share')
            ->and($request->info->eventParam->action)->toBe('export_audio');
    });

    it('converts to array correctly', function () {
        $eventParam = new EventParam(
            action: 'export_transcription',
            fileID: 'file-456',
            fileKey: 'file-456',
            from: 'web'
        );

        $info = new Info(
            eventCat: 'share',
            eventParam: $eventParam
        );

        $request = new RequestUploadInfo(info: $info);
        $array = $request->toArray();

        expect($array)->toBe([
            'info' => [
                'event_cat' => 'share',
                'event_param' => [
                    'action' => 'export_transcription',
                    'fileID' => 'file-456',
                    'fileKey' => 'file-456',
                    'from' => 'web',
                ],
            ],
        ]);
    });

    it('properly nests the info structure', function () {
        $eventParam = new EventParam(
            action: 'download',
            fileID: 'abc-123',
            fileKey: 'abc-123',
            from: 'mobile'
        );

        $info = new Info(
            eventCat: 'download',
            eventParam: $eventParam
        );

        $request = new RequestUploadInfo(info: $info);
        $array = $request->toArray();

        expect($array)->toHaveKey('info')
            ->and($array['info'])->toHaveKey('event_cat')
            ->and($array['info'])->toHaveKey('event_param')
            ->and($array['info']['event_param'])->toHaveKey('action')
            ->and($array['info']['event_param'])->toHaveKey('fileID')
            ->and($array['info']['event_param'])->toHaveKey('fileKey')
            ->and($array['info']['event_param'])->toHaveKey('from');
    });
});
