<?php

use Yannelli\LaravelPlaud\Models\RecommendQuestion;
use Yannelli\LaravelPlaud\Models\DataFileList;

describe('RecommendQuestion', function () {
    it('can be instantiated with default values', function () {
        $question = new RecommendQuestion();

        expect($question->status)->toBe(0)
            ->and($question->msg)->toBe('')
            ->and($question->dataFileTotal)->toBe(0)
            ->and($question->dataFileList)->toBe([]);
    });

    it('can be instantiated with all properties', function () {
        $files = [
            new DataFileList(id: 'file-1', filename: 'Related File 1'),
            new DataFileList(id: 'file-2', filename: 'Related File 2'),
        ];

        $question = new RecommendQuestion(
            status: 200,
            msg: 'What were the action items?',
            dataFileTotal: 2,
            dataFileList: $files
        );

        expect($question->status)->toBe(200)
            ->and($question->msg)->toBe('What were the action items?')
            ->and($question->dataFileTotal)->toBe(2)
            ->and($question->dataFileList)->toHaveCount(2);
    });

    it('can be created from array', function () {
        $data = [
            'status' => 200,
            'msg' => 'What is the timeline?',
            'data_file_total' => 1,
            'data_file_list' => [
                [
                    'id' => 'related-file',
                    'filename' => 'Timeline Document',
                    'filesize' => 1024,
                ],
            ],
        ];

        $question = RecommendQuestion::fromArray($data);

        expect($question->status)->toBe(200)
            ->and($question->msg)->toBe('What is the timeline?')
            ->and($question->dataFileTotal)->toBe(1)
            ->and($question->dataFileList)->toHaveCount(1)
            ->and($question->dataFileList[0])->toBeInstanceOf(DataFileList::class)
            ->and($question->dataFileList[0]->filename)->toBe('Timeline Document');
    });

    it('handles missing keys with default values', function () {
        $question = RecommendQuestion::fromArray([]);

        expect($question->status)->toBe(0)
            ->and($question->msg)->toBe('')
            ->and($question->dataFileTotal)->toBe(0)
            ->and($question->dataFileList)->toBe([]);
    });

    it('handles missing data_file_list key', function () {
        $data = [
            'status' => 200,
            'msg' => 'Test question',
            'data_file_total' => 0,
        ];

        $question = RecommendQuestion::fromArray($data);

        expect($question->dataFileList)->toBe([]);
    });

    it('converts to array correctly', function () {
        $question = new RecommendQuestion(
            status: 200,
            msg: 'Array test question',
            dataFileTotal: 0
        );

        $array = $question->toArray();

        expect($array['status'])->toBe(200)
            ->and($array['msg'])->toBe('Array test question')
            ->and($array['data_file_total'])->toBe(0)
            ->and($array['data_file_list'])->toBe([]);
    });

    it('converts nested data_file_list to array', function () {
        $files = [
            new DataFileList(id: 'nested-file', filename: 'Nested File'),
        ];

        $question = new RecommendQuestion(
            status: 200,
            msg: 'Nested test',
            dataFileTotal: 1,
            dataFileList: $files
        );

        $array = $question->toArray();

        expect($array['data_file_list'])->toHaveCount(1)
            ->and($array['data_file_list'][0])->toBeArray()
            ->and($array['data_file_list'][0]['id'])->toBe('nested-file');
    });
});
