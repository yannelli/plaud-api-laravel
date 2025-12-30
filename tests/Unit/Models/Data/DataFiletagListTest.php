<?php

use Yannelli\LaravelPlaud\Models\DataFiletagList;

describe('DataFiletagList', function () {
    it('can be instantiated with all properties', function () {
        $tag = new DataFiletagList(
            id: 'tag-123',
            name: 'Work',
            icon: 'briefcase',
            color: '#FF5733'
        );

        expect($tag->id)->toBe('tag-123')
            ->and($tag->name)->toBe('Work')
            ->and($tag->icon)->toBe('briefcase')
            ->and($tag->color)->toBe('#FF5733');
    });

    it('can be created from array', function () {
        $data = [
            'id' => 'tag-456',
            'name' => 'Personal',
            'icon' => 'home',
            'color' => '#33FF57',
        ];

        $tag = DataFiletagList::fromArray($data);

        expect($tag->id)->toBe('tag-456')
            ->and($tag->name)->toBe('Personal')
            ->and($tag->icon)->toBe('home')
            ->and($tag->color)->toBe('#33FF57');
    });

    it('handles missing keys with default values', function () {
        $tag = DataFiletagList::fromArray([]);

        expect($tag->id)->toBe('')
            ->and($tag->name)->toBe('')
            ->and($tag->icon)->toBe('')
            ->and($tag->color)->toBe('');
    });

    it('converts to array correctly', function () {
        $tag = new DataFiletagList(
            id: 'arr-tag',
            name: 'Important',
            icon: 'star',
            color: '#FFD700'
        );

        $array = $tag->toArray();

        expect($array)->toBe([
            'id' => 'arr-tag',
            'name' => 'Important',
            'icon' => 'star',
            'color' => '#FFD700',
        ]);
    });

    it('roundtrips from array to array correctly', function () {
        $originalData = [
            'id' => 'round-tag',
            'name' => 'Meetings',
            'icon' => 'calendar',
            'color' => '#0000FF',
        ];

        $tag = DataFiletagList::fromArray($originalData);
        $resultArray = $tag->toArray();

        expect($resultArray)->toBe($originalData);
    });
});
