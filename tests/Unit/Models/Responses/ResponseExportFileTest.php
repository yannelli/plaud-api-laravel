<?php

use Yannelli\LaravelPlaud\Models\Responses\ResponseExportFile;

describe('ResponseExportFile', function () {
    it('can be instantiated with properties', function () {
        $response = new ResponseExportFile(
            status: 200,
            msg: 'success',
            data: 'https://storage.plaud.ai/exports/document.pdf'
        );

        expect($response->status)->toBe(200)
            ->and($response->msg)->toBe('success')
            ->and($response->data)->toBe('https://storage.plaud.ai/exports/document.pdf');
    });

    it('can be created from array', function () {
        $data = [
            'status' => 200,
            'msg' => 'Export complete',
            'data' => 'https://cdn.plaud.ai/exports/transcript.docx',
        ];

        $response = ResponseExportFile::fromArray($data);

        expect($response->status)->toBe(200)
            ->and($response->msg)->toBe('Export complete')
            ->and($response->data)->toBe('https://cdn.plaud.ai/exports/transcript.docx');
    });

    it('handles missing keys with default values', function () {
        $response = ResponseExportFile::fromArray([]);

        expect($response->status)->toBe(0)
            ->and($response->msg)->toBe('')
            ->and($response->data)->toBe('');
    });

    it('converts to array correctly', function () {
        $response = new ResponseExportFile(
            status: 200,
            msg: 'ok',
            data: 'https://example.com/file.pdf'
        );

        $array = $response->toArray();

        expect($array)->toBe([
            'status' => 200,
            'msg' => 'ok',
            'data' => 'https://example.com/file.pdf',
        ]);
    });

    it('roundtrips from array to array correctly', function () {
        $originalData = [
            'status' => 200,
            'msg' => 'success',
            'data' => 'https://storage.example.com/export.txt',
        ];

        $response = ResponseExportFile::fromArray($originalData);
        $resultArray = $response->toArray();

        expect($resultArray)->toBe($originalData);
    });

    it('handles empty data url', function () {
        $response = new ResponseExportFile(
            status: 400,
            msg: 'Export failed',
            data: ''
        );

        expect($response->data)->toBe('');
    });
});
