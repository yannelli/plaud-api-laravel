<?php

use Yannelli\LaravelPlaud\Models\DataFileList;
use Yannelli\LaravelPlaud\Models\TransContent;
use Yannelli\LaravelPlaud\Models\ExtraData;

describe('DataFileList', function () {
    it('can be instantiated with minimal properties', function () {
        $file = new DataFileList(
            id: 'file-123',
            filename: 'Meeting Notes'
        );

        expect($file->id)->toBe('file-123')
            ->and($file->filename)->toBe('Meeting Notes')
            ->and($file->keywords)->toBe([])
            ->and($file->transResult)->toBe([])
            ->and($file->extraData)->toBeNull();
    });

    it('can be instantiated with all properties', function () {
        $transResult = [
            new TransContent(
                content: 'Hello',
                speaker: 'Alice',
                endTime: 1000,
                startTime: 0
            ),
        ];

        $file = new DataFileList(
            id: 'file-456',
            filename: 'Interview',
            keywords: ['interview', 'hr'],
            filesize: 1024000,
            filetype: 'audio/mp3',
            fullname: 'interview.mp3',
            oriReady: true,
            version: 1,
            isTrash: false,
            startTime: 1704067200000,
            endTime: 1704070800000,
            duration: 3600000,
            isTrans: true,
            isSummary: true,
            transResult: $transResult,
            aiContent: 'AI generated summary here'
        );

        expect($file->filename)->toBe('Interview')
            ->and($file->keywords)->toBe(['interview', 'hr'])
            ->and($file->filesize)->toBe(1024000)
            ->and($file->isTrans)->toBeTrue()
            ->and($file->isSummary)->toBeTrue()
            ->and($file->transResult)->toHaveCount(1)
            ->and($file->aiContent)->toBe('AI generated summary here');
    });

    it('can be created from array', function () {
        $data = [
            'id' => 'rec-789',
            'filename' => 'Conference Call',
            'keywords' => ['meeting', 'important'],
            'filesize' => 2048000,
            'filetype' => 'audio/wav',
            'is_trash' => false,
            'start_time' => 1704067200000,
            'end_time' => 1704074400000,
            'duration' => 7200000,
            'is_trans' => true,
            'is_summary' => false,
            'trans_result' => [
                [
                    'content' => 'First segment',
                    'speaker' => 'Bob',
                    'end_time' => 5000,
                    'start_time' => 0,
                    'embeddingKey' => 'key-1',
                ],
                [
                    'content' => 'Second segment',
                    'speaker' => 'Carol',
                    'end_time' => 10000,
                    'start_time' => 5000,
                    'embeddingKey' => 'key-2',
                ],
            ],
            'ai_content' => 'Summary of the call',
        ];

        $file = DataFileList::fromArray($data);

        expect($file->id)->toBe('rec-789')
            ->and($file->filename)->toBe('Conference Call')
            ->and($file->keywords)->toBe(['meeting', 'important'])
            ->and($file->duration)->toBe(7200000)
            ->and($file->isTrans)->toBeTrue()
            ->and($file->isSummary)->toBeFalse()
            ->and($file->transResult)->toHaveCount(2)
            ->and($file->transResult[0])->toBeInstanceOf(TransContent::class)
            ->and($file->transResult[0]->speaker)->toBe('Bob')
            ->and($file->transResult[1]->speaker)->toBe('Carol')
            ->and($file->aiContent)->toBe('Summary of the call');
    });

    it('handles extra data', function () {
        $data = [
            'id' => 'extra-file',
            'filename' => 'With Extra',
            'extra_data' => [
                'tranConfig' => [
                    'llm' => 'gpt-4',
                    'language' => 'en',
                ],
            ],
        ];

        $file = DataFileList::fromArray($data);

        expect($file->extraData)->toBeInstanceOf(ExtraData::class)
            ->and($file->extraData->tranConfig)->not->toBeNull()
            ->and($file->extraData->tranConfig->llm)->toBe('gpt-4');
    });

    it('handles missing keys with default values', function () {
        $file = DataFileList::fromArray([]);

        expect($file->id)->toBe('')
            ->and($file->filename)->toBe('')
            ->and($file->keywords)->toBe([])
            ->and($file->filesize)->toBeNull()
            ->and($file->transResult)->toBe([])
            ->and($file->extraData)->toBeNull();
    });

    it('converts to array correctly', function () {
        $file = new DataFileList(
            id: 'arr-file',
            filename: 'Array Test',
            keywords: ['test'],
            filesize: 512000,
            isTrans: true,
            duration: 1800000
        );

        $array = $file->toArray();

        expect($array['id'])->toBe('arr-file')
            ->and($array['filename'])->toBe('Array Test')
            ->and($array['keywords'])->toBe(['test'])
            ->and($array['filesize'])->toBe(512000)
            ->and($array['is_trans'])->toBeTrue()
            ->and($array['duration'])->toBe(1800000)
            ->and($array['trans_result'])->toBe([])
            ->and($array['extra_data'])->toBeNull();
    });

    it('converts nested trans_result to array', function () {
        $transResult = [
            new TransContent(
                content: 'Nested content',
                speaker: 'Dave',
                endTime: 2000,
                startTime: 1000
            ),
        ];

        $file = new DataFileList(
            id: 'nested-file',
            filename: 'Nested Test',
            transResult: $transResult
        );

        $array = $file->toArray();

        expect($array['trans_result'])->toHaveCount(1)
            ->and($array['trans_result'][0])->toBeArray()
            ->and($array['trans_result'][0]['content'])->toBe('Nested content');
    });
});
