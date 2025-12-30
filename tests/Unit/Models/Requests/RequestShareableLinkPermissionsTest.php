<?php

use Yannelli\LaravelPlaud\Models\Requests\RequestShareableLinkPermissions;

describe('RequestShareableLinkPermissions', function () {
    it('can be instantiated with default values', function () {
        $permissions = new RequestShareableLinkPermissions();

        expect($permissions->isAudio)->toBeNull()
            ->and($permissions->isTrans)->toBeNull()
            ->and($permissions->isAiContent)->toBeNull()
            ->and($permissions->isMindmap)->toBeNull();
    });

    it('can be instantiated with specific values', function () {
        $permissions = new RequestShareableLinkPermissions(
            isAudio: 1,
            isTrans: 1,
            isAiContent: 0,
            isMindmap: 1
        );

        expect($permissions->isAudio)->toBe(1)
            ->and($permissions->isTrans)->toBe(1)
            ->and($permissions->isAiContent)->toBe(0)
            ->and($permissions->isMindmap)->toBe(1);
    });

    it('converts to array correctly', function () {
        $permissions = new RequestShareableLinkPermissions(
            isAudio: 1,
            isTrans: 0,
            isAiContent: 1,
            isMindmap: 0
        );

        $array = $permissions->toArray();

        expect($array)->toBe([
            'is_audio' => 1,
            'is_trans' => 0,
            'is_ai_content' => 1,
            'is_mindmap' => 0,
        ]);
    });

    it('converts null values to array correctly', function () {
        $permissions = new RequestShareableLinkPermissions();

        $array = $permissions->toArray();

        expect($array)->toBe([
            'is_audio' => null,
            'is_trans' => null,
            'is_ai_content' => null,
            'is_mindmap' => null,
        ]);
    });

    it('allows partial permission setting', function () {
        $permissions = new RequestShareableLinkPermissions(
            isAudio: 1
        );

        expect($permissions->isAudio)->toBe(1)
            ->and($permissions->isTrans)->toBeNull();

        $array = $permissions->toArray();

        expect($array['is_audio'])->toBe(1)
            ->and($array['is_trans'])->toBeNull();
    });
});
