<?php

use Yannelli\LaravelPlaud\Models\ExtraData;
use Yannelli\LaravelPlaud\Models\TranConfig;
use Yannelli\LaravelPlaud\Models\AiContentFrom;
use Yannelli\LaravelPlaud\Models\AiContentHeader;

describe('ExtraData', function () {
    it('can be instantiated with default null values', function () {
        $extra = new ExtraData();

        expect($extra->tranConfig)->toBeNull()
            ->and($extra->aiContentFrom)->toBeNull()
            ->and($extra->aiContentHeader)->toBeNull();
    });

    it('can be instantiated with all properties', function () {
        $tranConfig = new TranConfig(
            llm: 'gpt-4',
            type: 'transcription',
            language: 'en'
        );

        $aiContentFrom = new AiContentFrom(
            info: 'Meeting info',
            notes: 'Some notes'
        );

        $aiContentHeader = new AiContentHeader(
            headline: 'Summary Headline',
            keywords: ['meeting', 'summary']
        );

        $extra = new ExtraData(
            tranConfig: $tranConfig,
            aiContentFrom: $aiContentFrom,
            aiContentHeader: $aiContentHeader
        );

        expect($extra->tranConfig)->toBeInstanceOf(TranConfig::class)
            ->and($extra->tranConfig->llm)->toBe('gpt-4')
            ->and($extra->aiContentFrom)->toBeInstanceOf(AiContentFrom::class)
            ->and($extra->aiContentFrom->info)->toBe('Meeting info')
            ->and($extra->aiContentHeader)->toBeInstanceOf(AiContentHeader::class)
            ->and($extra->aiContentHeader->headline)->toBe('Summary Headline');
    });

    it('can be created from array', function () {
        $data = [
            'tranConfig' => [
                'llm' => 'claude',
                'type' => 'summary',
                'language' => 'fr',
                'type_type' => 'meeting',
                'diarization' => 1,
            ],
            'aiContentFrom' => [
                'info' => 'Content info',
                'notes' => 'Content notes',
                'location' => 'Office',
                'attendees' => 'Alice, Bob',
            ],
            'aiContentHeader' => [
                'headline' => 'Test Headline',
                'keywords' => ['test', 'demo'],
                'industry_category' => 'Technology',
            ],
        ];

        $extra = ExtraData::fromArray($data);

        expect($extra->tranConfig)->toBeInstanceOf(TranConfig::class)
            ->and($extra->tranConfig->llm)->toBe('claude')
            ->and($extra->tranConfig->diarization)->toBe(1)
            ->and($extra->aiContentFrom)->toBeInstanceOf(AiContentFrom::class)
            ->and($extra->aiContentFrom->location)->toBe('Office')
            ->and($extra->aiContentHeader)->toBeInstanceOf(AiContentHeader::class)
            ->and($extra->aiContentHeader->industryCategory)->toBe('Technology');
    });

    it('handles missing nested objects', function () {
        $extra = ExtraData::fromArray([]);

        expect($extra->tranConfig)->toBeNull()
            ->and($extra->aiContentFrom)->toBeNull()
            ->and($extra->aiContentHeader)->toBeNull();
    });

    it('converts to array correctly with all data', function () {
        $tranConfig = new TranConfig(llm: 'gpt-3.5', language: 'de');
        $aiContentFrom = new AiContentFrom(info: 'Info');
        $aiContentHeader = new AiContentHeader(headline: 'Header');

        $extra = new ExtraData(
            tranConfig: $tranConfig,
            aiContentFrom: $aiContentFrom,
            aiContentHeader: $aiContentHeader
        );

        $array = $extra->toArray();

        expect($array)->toHaveKey('tranConfig')
            ->and($array)->toHaveKey('aiContentFrom')
            ->and($array)->toHaveKey('aiContentHeader')
            ->and($array['tranConfig']['llm'])->toBe('gpt-3.5')
            ->and($array['aiContentFrom']['info'])->toBe('Info')
            ->and($array['aiContentHeader']['headline'])->toBe('Header');
    });

    it('converts to array correctly with null values', function () {
        $extra = new ExtraData();
        $array = $extra->toArray();

        expect($array)->toBe([
            'tranConfig' => null,
            'aiContentFrom' => null,
            'aiContentHeader' => null,
        ]);
    });
});
