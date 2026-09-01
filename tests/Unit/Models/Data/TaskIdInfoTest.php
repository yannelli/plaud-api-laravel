<?php

use Yannelli\LaravelPlaud\Models\TaskIdInfo;
use Yannelli\LaravelPlaud\Models\ExtraData;

describe('TaskIdInfo', function () {
    it('reads summary_id from snake and camel case', function () {
        $info = TaskIdInfo::fromArray(['summary_id' => 'sum-1', 'trans_id' => 'tr-1']);

        expect($info->summaryId)->toBe('sum-1')
            ->and($info->transId)->toBe('tr-1');
    });
});

describe('ExtraData task_id_info', function () {
    it('hydrates TaskIdInfo from extra_data', function () {
        $extra = ExtraData::fromArray([
            'task_id_info' => ['summary_id' => 'sum-9'],
        ]);

        expect($extra->taskIdInfo)->toBeInstanceOf(TaskIdInfo::class)
            ->and($extra->taskIdInfo->summaryId)->toBe('sum-9');
    });
});
