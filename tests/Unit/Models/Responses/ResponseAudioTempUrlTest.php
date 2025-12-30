<?php

use Yannelli\LaravelPlaud\Models\Responses\ResponseAudioTempUrl;

describe('ResponseAudioTempUrl', function () {
    it('can be instantiated with properties', function () {
        $response = new ResponseAudioTempUrl(
            status: 200,
            tempUrl: 'https://storage.plaud.ai/temp/audio.mp3',
            tempUrlOpus: 'https://storage.plaud.ai/temp/audio.opus'
        );

        expect($response->status)->toBe(200)
            ->and($response->tempUrl)->toBe('https://storage.plaud.ai/temp/audio.mp3')
            ->and($response->tempUrlOpus)->toBe('https://storage.plaud.ai/temp/audio.opus');
    });

    it('can be instantiated without opus url', function () {
        $response = new ResponseAudioTempUrl(
            status: 200,
            tempUrl: 'https://storage.plaud.ai/temp/audio.mp3'
        );

        expect($response->tempUrl)->toBe('https://storage.plaud.ai/temp/audio.mp3')
            ->and($response->tempUrlOpus)->toBeNull();
    });

    it('can be created from array', function () {
        $data = [
            'status' => 200,
            'temp_url' => 'https://cdn.plaud.ai/files/recording.mp3',
            'temp_url_opus' => 'https://cdn.plaud.ai/files/recording.opus',
        ];

        $response = ResponseAudioTempUrl::fromArray($data);

        expect($response->status)->toBe(200)
            ->and($response->tempUrl)->toBe('https://cdn.plaud.ai/files/recording.mp3')
            ->and($response->tempUrlOpus)->toBe('https://cdn.plaud.ai/files/recording.opus');
    });

    it('handles missing keys with default values', function () {
        $response = ResponseAudioTempUrl::fromArray([]);

        expect($response->status)->toBe(0)
            ->and($response->tempUrl)->toBe('')
            ->and($response->tempUrlOpus)->toBeNull();
    });

    it('converts to array correctly', function () {
        $response = new ResponseAudioTempUrl(
            status: 200,
            tempUrl: 'https://example.com/audio.mp3',
            tempUrlOpus: 'https://example.com/audio.opus'
        );

        $array = $response->toArray();

        expect($array)->toBe([
            'status' => 200,
            'temp_url' => 'https://example.com/audio.mp3',
            'temp_url_opus' => 'https://example.com/audio.opus',
        ]);
    });

    it('handles null opus url in array conversion', function () {
        $response = new ResponseAudioTempUrl(
            status: 200,
            tempUrl: 'https://example.com/audio.mp3'
        );

        $array = $response->toArray();

        expect($array['temp_url_opus'])->toBeNull();
    });
});
