<?php

use Yannelli\LaravelPlaud\Models\TransContent;

describe('TransContent', function () {
    it('can be instantiated with all properties', function () {
        $trans = new TransContent(
            content: 'Hello, this is a test.',
            speaker: 'Speaker 1',
            endTime: 5000,
            startTime: 0,
            embeddingKey: 'emb-key-123'
        );

        expect($trans->content)->toBe('Hello, this is a test.')
            ->and($trans->speaker)->toBe('Speaker 1')
            ->and($trans->endTime)->toBe(5000)
            ->and($trans->startTime)->toBe(0)
            ->and($trans->embeddingKey)->toBe('emb-key-123');
    });

    it('has default empty embedding key', function () {
        $trans = new TransContent(
            content: 'Content',
            speaker: 'Speaker',
            endTime: 1000,
            startTime: 0
        );

        expect($trans->embeddingKey)->toBe('');
    });

    it('can be created from array', function () {
        $data = [
            'content' => 'This is the transcribed content.',
            'speaker' => 'Alice',
            'end_time' => 10000,
            'start_time' => 5000,
            'embeddingKey' => 'key-abc',
        ];

        $trans = TransContent::fromArray($data);

        expect($trans->content)->toBe('This is the transcribed content.')
            ->and($trans->speaker)->toBe('Alice')
            ->and($trans->endTime)->toBe(10000)
            ->and($trans->startTime)->toBe(5000)
            ->and($trans->embeddingKey)->toBe('key-abc');
    });

    it('handles missing keys with default values', function () {
        $trans = TransContent::fromArray([]);

        expect($trans->content)->toBe('')
            ->and($trans->speaker)->toBe('')
            ->and($trans->endTime)->toBe(0)
            ->and($trans->startTime)->toBe(0)
            ->and($trans->embeddingKey)->toBe('');
    });

    it('converts to array correctly', function () {
        $trans = new TransContent(
            content: 'Array test content',
            speaker: 'Bob',
            endTime: 3000,
            startTime: 1000,
            embeddingKey: 'emb-xyz'
        );

        $array = $trans->toArray();

        expect($array)->toBe([
            'content' => 'Array test content',
            'speaker' => 'Bob',
            'end_time' => 3000,
            'start_time' => 1000,
            'embeddingKey' => 'emb-xyz',
        ]);
    });

    it('roundtrips from array to array correctly', function () {
        $originalData = [
            'content' => 'Roundtrip test',
            'speaker' => 'Charlie',
            'end_time' => 8000,
            'start_time' => 4000,
            'embeddingKey' => 'round-key',
        ];

        $trans = TransContent::fromArray($originalData);
        $resultArray = $trans->toArray();

        expect($resultArray)->toBe($originalData);
    });
});
