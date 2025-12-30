<?php

use Yannelli\LaravelPlaud\Models\Responses\ResponseFileTags;
use Yannelli\LaravelPlaud\Models\DataFiletagList;

describe('ResponseFileTags', function () {
    it('can be instantiated with default values', function () {
        $response = new ResponseFileTags(
            status: 200,
            msg: 'success',
            dataFiletagTotal: 0
        );

        expect($response->status)->toBe(200)
            ->and($response->msg)->toBe('success')
            ->and($response->dataFiletagTotal)->toBe(0)
            ->and($response->dataFiletagList)->toBe([]);
    });

    it('can be instantiated with file tags', function () {
        $tags = [
            new DataFiletagList(id: 'tag-1', name: 'Work', icon: 'briefcase', color: '#FF0000'),
            new DataFiletagList(id: 'tag-2', name: 'Personal', icon: 'home', color: '#00FF00'),
        ];

        $response = new ResponseFileTags(
            status: 200,
            msg: 'success',
            dataFiletagTotal: 2,
            dataFiletagList: $tags
        );

        expect($response->dataFiletagTotal)->toBe(2)
            ->and($response->dataFiletagList)->toHaveCount(2)
            ->and($response->dataFiletagList[0]->name)->toBe('Work')
            ->and($response->dataFiletagList[1]->color)->toBe('#00FF00');
    });

    it('can be created from array', function () {
        $data = [
            'status' => 200,
            'msg' => 'ok',
            'data_filetag_total' => 3,
            'data_filetag_list' => [
                ['id' => 'folder-1', 'name' => 'Meetings', 'icon' => 'calendar', 'color' => '#0000FF'],
                ['id' => 'folder-2', 'name' => 'Interviews', 'icon' => 'people', 'color' => '#FFFF00'],
                ['id' => 'folder-3', 'name' => 'Notes', 'icon' => 'note', 'color' => '#FF00FF'],
            ],
        ];

        $response = ResponseFileTags::fromArray($data);

        expect($response->status)->toBe(200)
            ->and($response->dataFiletagTotal)->toBe(3)
            ->and($response->dataFiletagList)->toHaveCount(3)
            ->and($response->dataFiletagList[0])->toBeInstanceOf(DataFiletagList::class)
            ->and($response->dataFiletagList[0]->name)->toBe('Meetings')
            ->and($response->dataFiletagList[2]->icon)->toBe('note');
    });

    it('handles empty file tag list', function () {
        $data = [
            'status' => 200,
            'msg' => 'No tags found',
            'data_filetag_total' => 0,
            'data_filetag_list' => [],
        ];

        $response = ResponseFileTags::fromArray($data);

        expect($response->dataFiletagTotal)->toBe(0)
            ->and($response->dataFiletagList)->toBe([]);
    });

    it('handles missing file tag list key', function () {
        $data = [
            'status' => 200,
            'msg' => 'success',
            'data_filetag_total' => 0,
        ];

        $response = ResponseFileTags::fromArray($data);

        expect($response->dataFiletagList)->toBe([]);
    });

    it('converts to array correctly', function () {
        $tags = [
            new DataFiletagList(id: 'tag-abc', name: 'Important', icon: 'star', color: '#FFD700'),
        ];

        $response = new ResponseFileTags(
            status: 200,
            msg: 'success',
            dataFiletagTotal: 1,
            dataFiletagList: $tags
        );

        $array = $response->toArray();

        expect($array['status'])->toBe(200)
            ->and($array['data_filetag_total'])->toBe(1)
            ->and($array['data_filetag_list'])->toHaveCount(1)
            ->and($array['data_filetag_list'][0])->toBe([
                'id' => 'tag-abc',
                'name' => 'Important',
                'icon' => 'star',
                'color' => '#FFD700',
            ]);
    });
});
