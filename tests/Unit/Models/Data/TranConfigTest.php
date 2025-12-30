<?php

use Yannelli\LaravelPlaud\Models\TranConfig;

describe('TranConfig', function () {
    it('can be instantiated with default values', function () {
        $config = new TranConfig();

        expect($config->llm)->toBe('')
            ->and($config->type)->toBe('')
            ->and($config->language)->toBe('')
            ->and($config->typeType)->toBe('')
            ->and($config->diarization)->toBe(0);
    });

    it('can be instantiated with all properties', function () {
        $config = new TranConfig(
            llm: 'gpt-4',
            type: 'transcription',
            language: 'en',
            typeType: 'meeting',
            diarization: 1
        );

        expect($config->llm)->toBe('gpt-4')
            ->and($config->type)->toBe('transcription')
            ->and($config->language)->toBe('en')
            ->and($config->typeType)->toBe('meeting')
            ->and($config->diarization)->toBe(1);
    });

    it('can be created from array', function () {
        $data = [
            'llm' => 'claude',
            'type' => 'summary',
            'language' => 'fr',
            'type_type' => 'interview',
            'diarization' => 2,
        ];

        $config = TranConfig::fromArray($data);

        expect($config->llm)->toBe('claude')
            ->and($config->type)->toBe('summary')
            ->and($config->language)->toBe('fr')
            ->and($config->typeType)->toBe('interview')
            ->and($config->diarization)->toBe(2);
    });

    it('handles missing keys with default values', function () {
        $config = TranConfig::fromArray([]);

        expect($config->llm)->toBe('')
            ->and($config->type)->toBe('')
            ->and($config->language)->toBe('')
            ->and($config->typeType)->toBe('')
            ->and($config->diarization)->toBe(0);
    });

    it('converts to array correctly', function () {
        $config = new TranConfig(
            llm: 'whisper',
            type: 'transcribe',
            language: 'es',
            typeType: 'call',
            diarization: 3
        );

        $array = $config->toArray();

        expect($array)->toBe([
            'llm' => 'whisper',
            'type' => 'transcribe',
            'language' => 'es',
            'type_type' => 'call',
            'diarization' => 3,
        ]);
    });

    it('roundtrips from array to array correctly', function () {
        $originalData = [
            'llm' => 'roundtrip-llm',
            'type' => 'roundtrip-type',
            'language' => 'ja',
            'type_type' => 'roundtrip-type-type',
            'diarization' => 5,
        ];

        $config = TranConfig::fromArray($originalData);
        $resultArray = $config->toArray();

        expect($resultArray)->toBe($originalData);
    });
});
