<?php

use Yannelli\LaravelPlaud\Models\Requests\RequestExportSummary;

describe('RequestExportSummary', function () {
    it('can be instantiated with required values', function () {
        $request = new RequestExportSummary(
            fileId: 'file-123',
            promptType: 'summary',
            toFormat: 'PDF',
            title: 'Meeting Summary',
            createTime: '2024-01-15 10:30:00'
        );

        expect($request->fileId)->toBe('file-123')
            ->and($request->promptType)->toBe('summary')
            ->and($request->toFormat)->toBe('PDF')
            ->and($request->title)->toBe('Meeting Summary')
            ->and($request->createTime)->toBe('2024-01-15 10:30:00')
            ->and($request->withSpeaker)->toBeNull()
            ->and($request->withTimestamp)->toBeNull()
            ->and($request->summaryContent)->toBe('');
    });

    it('can be instantiated with all values', function () {
        $request = new RequestExportSummary(
            fileId: 'file-456',
            promptType: 'summary',
            toFormat: 'DOCX',
            title: 'Interview Summary',
            createTime: '2024-02-20 14:00:00',
            withSpeaker: 1,
            withTimestamp: 1,
            summaryContent: '## Key Points\n- Point 1\n- Point 2'
        );

        expect($request->withSpeaker)->toBe(1)
            ->and($request->withTimestamp)->toBe(1)
            ->and($request->summaryContent)->toBe('## Key Points\n- Point 1\n- Point 2');
    });

    it('converts to array correctly', function () {
        $request = new RequestExportSummary(
            fileId: 'file-789',
            promptType: 'summary',
            toFormat: 'TXT',
            title: 'Notes Summary',
            createTime: '2024-03-01 09:00:00',
            withSpeaker: 0,
            withTimestamp: 1,
            summaryContent: 'This is the summary content'
        );

        $array = $request->toArray();

        expect($array)->toBe([
            'file_id' => 'file-789',
            'prompt_type' => 'summary',
            'to_format' => 'TXT',
            'title' => 'Notes Summary',
            'create_time' => '2024-03-01 09:00:00',
            'with_speaker' => 0,
            'with_timestamp' => 1,
            'summary_content' => 'This is the summary content',
        ]);
    });

    it('handles empty summary content', function () {
        $request = new RequestExportSummary(
            fileId: 'empty-file',
            promptType: 'summary',
            toFormat: 'PDF',
            title: 'Empty Summary',
            createTime: '2024-05-01 00:00:00',
            summaryContent: ''
        );

        $array = $request->toArray();

        expect($array['summary_content'])->toBe('');
    });

    it('preserves markdown formatting in summary content', function () {
        $markdown = "# Summary\n\n## Action Items\n\n- [ ] Task 1\n- [ ] Task 2\n\n## Notes\n\nSome notes here.";

        $request = new RequestExportSummary(
            fileId: 'md-file',
            promptType: 'summary',
            toFormat: 'markdown',
            title: 'Markdown Summary',
            createTime: '2024-06-01 12:00:00',
            summaryContent: $markdown
        );

        expect($request->summaryContent)->toBe($markdown)
            ->and($request->toArray()['summary_content'])->toBe($markdown);
    });
});
