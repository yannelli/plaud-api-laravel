<?php

use Yannelli\LaravelPlaud\Models\Responses\ResponseListRecordings;
use Yannelli\LaravelPlaud\Models\DataFileList;

describe('ResponseListRecordings', function () {
    it('can be instantiated with default values', function () {
        $response = new ResponseListRecordings(
            status: 200,
            msg: 'success',
            dataFileTotal: 0
        );

        expect($response->status)->toBe(200)
            ->and($response->msg)->toBe('success')
            ->and($response->dataFileTotal)->toBe(0)
            ->and($response->dataFileList)->toBe([]);
    });

    it('can be instantiated with file list', function () {
        $files = [
            new DataFileList(id: 'file-1', filename: 'Meeting 1'),
            new DataFileList(id: 'file-2', filename: 'Meeting 2'),
        ];

        $response = new ResponseListRecordings(
            status: 200,
            msg: 'success',
            dataFileTotal: 2,
            dataFileList: $files
        );

        expect($response->dataFileTotal)->toBe(2)
            ->and($response->dataFileList)->toHaveCount(2)
            ->and($response->dataFileList[0]->filename)->toBe('Meeting 1')
            ->and($response->dataFileList[1]->filename)->toBe('Meeting 2');
    });

    it('can be created from array', function () {
        $data = [
            'status' => 200,
            'msg' => 'ok',
            'data_file_total' => 2,
            'data_file_list' => [
                [
                    'id' => 'rec-123',
                    'filename' => 'Recording 1',
                    'filesize' => 1024000,
                    'duration' => 3600,
                    'is_trans' => true,
                    'is_summary' => true,
                ],
                [
                    'id' => 'rec-456',
                    'filename' => 'Recording 2',
                    'filesize' => 2048000,
                    'duration' => 7200,
                    'is_trans' => false,
                    'is_summary' => false,
                ],
            ],
        ];

        $response = ResponseListRecordings::fromArray($data);

        expect($response->status)->toBe(200)
            ->and($response->msg)->toBe('ok')
            ->and($response->dataFileTotal)->toBe(2)
            ->and($response->dataFileList)->toHaveCount(2)
            ->and($response->dataFileList[0])->toBeInstanceOf(DataFileList::class)
            ->and($response->dataFileList[0]->id)->toBe('rec-123')
            ->and($response->dataFileList[0]->isTrans)->toBeTrue()
            ->and($response->dataFileList[1]->id)->toBe('rec-456')
            ->and($response->dataFileList[1]->isTrans)->toBeFalse();
    });

    it('handles empty file list', function () {
        $data = [
            'status' => 200,
            'msg' => 'No recordings found',
            'data_file_total' => 0,
            'data_file_list' => [],
        ];

        $response = ResponseListRecordings::fromArray($data);

        expect($response->dataFileTotal)->toBe(0)
            ->and($response->dataFileList)->toBe([]);
    });

    it('handles missing file list key', function () {
        $data = [
            'status' => 200,
            'msg' => 'success',
            'data_file_total' => 0,
        ];

        $response = ResponseListRecordings::fromArray($data);

        expect($response->dataFileList)->toBe([]);
    });

    it('converts to array correctly', function () {
        $files = [
            new DataFileList(
                id: 'file-abc',
                filename: 'Test Recording',
                filesize: 512000,
                duration: 1800
            ),
        ];

        $response = new ResponseListRecordings(
            status: 200,
            msg: 'success',
            dataFileTotal: 1,
            dataFileList: $files
        );

        $array = $response->toArray();

        expect($array['status'])->toBe(200)
            ->and($array['msg'])->toBe('success')
            ->and($array['data_file_total'])->toBe(1)
            ->and($array['data_file_list'])->toHaveCount(1)
            ->and($array['data_file_list'][0]['id'])->toBe('file-abc')
            ->and($array['data_file_list'][0]['filename'])->toBe('Test Recording');
    });

    it('handles nullable status and total', function () {
        $response = new ResponseListRecordings(
            status: null,
            msg: '',
            dataFileTotal: null
        );

        expect($response->status)->toBeNull()
            ->and($response->dataFileTotal)->toBeNull();
    });
});
