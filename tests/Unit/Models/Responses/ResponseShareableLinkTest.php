<?php

use Yannelli\LaravelPlaud\Models\Responses\ResponseShareableLink;

describe('ResponseShareableLink', function () {
    it('can be instantiated with properties', function () {
        $response = new ResponseShareableLink(
            status: 200,
            url: 'https://share.plaud.ai/abc123'
        );

        expect($response->status)->toBe(200)
            ->and($response->url)->toBe('https://share.plaud.ai/abc123');
    });

    it('can be created from array', function () {
        $data = [
            'status' => 200,
            'url' => 'https://share.plaud.ai/xyz789',
        ];

        $response = ResponseShareableLink::fromArray($data);

        expect($response->status)->toBe(200)
            ->and($response->url)->toBe('https://share.plaud.ai/xyz789');
    });

    it('handles missing keys with default values', function () {
        $response = ResponseShareableLink::fromArray([]);

        expect($response->status)->toBe(0)
            ->and($response->url)->toBe('');
    });

    it('converts to array correctly', function () {
        $response = new ResponseShareableLink(
            status: 200,
            url: 'https://share.plaud.ai/test123'
        );

        $array = $response->toArray();

        expect($array)->toBe([
            'status' => 200,
            'url' => 'https://share.plaud.ai/test123',
        ]);
    });

    it('roundtrips from array to array correctly', function () {
        $originalData = [
            'status' => 200,
            'url' => 'https://share.plaud.ai/roundtrip',
        ];

        $response = ResponseShareableLink::fromArray($originalData);
        $resultArray = $response->toArray();

        expect($resultArray)->toBe($originalData);
    });
});
