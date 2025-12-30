<?php

use Yannelli\LaravelPlaud\Models\Requests\RequestExportFile;
use Yannelli\LaravelPlaud\Models\TransContent;

describe('RequestExportFile', function () {
    it('can be instantiated with required values', function () {
        $request = new RequestExportFile(
            fileId: 'file-123',
            promptType: 'trans',
            toFormat: 'PDF',
            title: 'Meeting Notes',
            createTime: '2024-01-15 10:30:00'
        );

        expect($request->fileId)->toBe('file-123')
            ->and($request->promptType)->toBe('trans')
            ->and($request->toFormat)->toBe('PDF')
            ->and($request->title)->toBe('Meeting Notes')
            ->and($request->createTime)->toBe('2024-01-15 10:30:00')
            ->and($request->withSpeaker)->toBeNull()
            ->and($request->withTimestamp)->toBeNull()
            ->and($request->transContent)->toBe([]);
    });

    it('can be instantiated with all values', function () {
        $transContent = [
            new TransContent(
                content: 'Hello world',
                speaker: 'Speaker 1',
                endTime: 5000,
                startTime: 0
            ),
        ];

        $request = new RequestExportFile(
            fileId: 'file-456',
            promptType: 'trans',
            toFormat: 'DOCX',
            title: 'Interview',
            createTime: '2024-02-20 14:00:00',
            withSpeaker: 1,
            withTimestamp: 1,
            transContent: $transContent
        );

        expect($request->withSpeaker)->toBe(1)
            ->and($request->withTimestamp)->toBe(1)
            ->and($request->transContent)->toHaveCount(1);
    });

    it('converts to array correctly without trans content', function () {
        $request = new RequestExportFile(
            fileId: 'file-789',
            promptType: 'trans',
            toFormat: 'TXT',
            title: 'Notes',
            createTime: '2024-03-01 09:00:00',
            withSpeaker: 0,
            withTimestamp: 1
        );

        $array = $request->toArray();

        expect($array)->toBe([
            'file_id' => 'file-789',
            'prompt_type' => 'trans',
            'to_format' => 'TXT',
            'title' => 'Notes',
            'create_time' => '2024-03-01 09:00:00',
            'with_speaker' => 0,
            'with_timestamp' => 1,
            'trans_content' => [],
        ]);
    });

    it('converts to array correctly with trans content', function () {
        $transContent = [
            new TransContent(
                content: 'First segment',
                speaker: 'Alice',
                endTime: 3000,
                startTime: 0,
                embeddingKey: 'key-1'
            ),
            new TransContent(
                content: 'Second segment',
                speaker: 'Bob',
                endTime: 6000,
                startTime: 3000,
                embeddingKey: 'key-2'
            ),
        ];

        $request = new RequestExportFile(
            fileId: 'file-abc',
            promptType: 'trans',
            toFormat: 'SRT',
            title: 'Conversation',
            createTime: '2024-04-15 16:30:00',
            withSpeaker: 1,
            withTimestamp: 1,
            transContent: $transContent
        );

        $array = $request->toArray();

        expect($array['trans_content'])->toHaveCount(2)
            ->and($array['trans_content'][0])->toBe([
                'content' => 'First segment',
                'speaker' => 'Alice',
                'end_time' => 3000,
                'start_time' => 0,
                'embeddingKey' => 'key-1',
            ])
            ->and($array['trans_content'][1]['speaker'])->toBe('Bob');
    });

    it('handles empty trans content array', function () {
        $request = new RequestExportFile(
            fileId: 'empty-file',
            promptType: 'trans',
            toFormat: 'PDF',
            title: 'Empty',
            createTime: '2024-05-01 00:00:00',
            transContent: []
        );

        $array = $request->toArray();

        expect($array['trans_content'])->toBe([]);
    });
});
