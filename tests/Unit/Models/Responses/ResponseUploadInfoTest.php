<?php

use Yannelli\LaravelPlaud\Models\Responses\ResponseUploadInfo;

describe('ResponseUploadInfo', function () {
    it('can be instantiated with properties', function () {
        $response = new ResponseUploadInfo(
            status: 200,
            msg: 'success'
        );

        expect($response->status)->toBe(200)
            ->and($response->msg)->toBe('success');
    });

    it('can be created from array', function () {
        $data = [
            'status' => 200,
            'msg' => 'Upload info recorded',
        ];

        $response = ResponseUploadInfo::fromArray($data);

        expect($response->status)->toBe(200)
            ->and($response->msg)->toBe('Upload info recorded');
    });

    it('handles missing keys with default values', function () {
        $response = ResponseUploadInfo::fromArray([]);

        expect($response->status)->toBe(0)
            ->and($response->msg)->toBe('');
    });

    it('converts to array correctly', function () {
        $response = new ResponseUploadInfo(
            status: 200,
            msg: 'ok'
        );

        $array = $response->toArray();

        expect($array)->toBe([
            'status' => 200,
            'msg' => 'ok',
        ]);
    });

    it('roundtrips from array to array correctly', function () {
        $originalData = [
            'status' => 200,
            'msg' => 'success',
        ];

        $response = ResponseUploadInfo::fromArray($originalData);
        $resultArray = $response->toArray();

        expect($resultArray)->toBe($originalData);
    });

    it('handles error status', function () {
        $data = [
            'status' => 400,
            'msg' => 'Invalid request',
        ];

        $response = ResponseUploadInfo::fromArray($data);

        expect($response->status)->toBe(400)
            ->and($response->msg)->toBe('Invalid request');
    });
});
