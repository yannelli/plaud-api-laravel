<?php

use Yannelli\LaravelPlaud\Models\AiContentFrom;

describe('AiContentFrom', function () {
    it('can be instantiated with default values', function () {
        $content = new AiContentFrom();

        expect($content->info)->toBe('')
            ->and($content->notes)->toBe('')
            ->and($content->location)->toBe('')
            ->and($content->attendees)->toBe('')
            ->and($content->dateTime)->toBe('')
            ->and($content->conclusion)->toBe('')
            ->and($content->insertMore)->toBe('')
            ->and($content->arrangements)->toBe('')
            ->and($content->aiSuggestions)->toBe('');
    });

    it('can be instantiated with all properties', function () {
        $content = new AiContentFrom(
            info: 'Meeting about project',
            notes: 'Key discussion points',
            location: 'Conference Room A',
            attendees: 'Alice, Bob, Carol',
            dateTime: '2024-01-15 14:00:00',
            conclusion: 'Agreed on timeline',
            insertMore: 'Additional notes',
            arrangements: 'Follow-up meeting scheduled',
            aiSuggestions: 'Consider adding more resources'
        );

        expect($content->info)->toBe('Meeting about project')
            ->and($content->notes)->toBe('Key discussion points')
            ->and($content->location)->toBe('Conference Room A')
            ->and($content->attendees)->toBe('Alice, Bob, Carol')
            ->and($content->dateTime)->toBe('2024-01-15 14:00:00')
            ->and($content->conclusion)->toBe('Agreed on timeline')
            ->and($content->insertMore)->toBe('Additional notes')
            ->and($content->arrangements)->toBe('Follow-up meeting scheduled')
            ->and($content->aiSuggestions)->toBe('Consider adding more resources');
    });

    it('can be created from array', function () {
        $data = [
            'info' => 'Interview session',
            'notes' => 'Candidate responses',
            'location' => 'Virtual',
            'attendees' => 'HR Team',
            'date_time' => '2024-02-20 10:00:00',
            'conclusion' => 'Proceed to next round',
            'insert_more' => 'References needed',
            'arrangements' => 'Technical interview next',
            'ai_suggestions' => 'Verify technical skills',
        ];

        $content = AiContentFrom::fromArray($data);

        expect($content->info)->toBe('Interview session')
            ->and($content->notes)->toBe('Candidate responses')
            ->and($content->location)->toBe('Virtual')
            ->and($content->dateTime)->toBe('2024-02-20 10:00:00')
            ->and($content->conclusion)->toBe('Proceed to next round')
            ->and($content->aiSuggestions)->toBe('Verify technical skills');
    });

    it('handles missing keys with default values', function () {
        $content = AiContentFrom::fromArray([]);

        expect($content->info)->toBe('')
            ->and($content->notes)->toBe('')
            ->and($content->location)->toBe('')
            ->and($content->attendees)->toBe('')
            ->and($content->dateTime)->toBe('')
            ->and($content->conclusion)->toBe('')
            ->and($content->insertMore)->toBe('')
            ->and($content->arrangements)->toBe('')
            ->and($content->aiSuggestions)->toBe('');
    });

    it('converts to array correctly', function () {
        $content = new AiContentFrom(
            info: 'Array test info',
            notes: 'Array test notes',
            location: 'Office',
            attendees: 'Team',
            dateTime: '2024-03-01 09:00:00',
            conclusion: 'Done',
            insertMore: 'More',
            arrangements: 'Next steps',
            aiSuggestions: 'Suggestions'
        );

        $array = $content->toArray();

        expect($array)->toBe([
            'info' => 'Array test info',
            'notes' => 'Array test notes',
            'location' => 'Office',
            'attendees' => 'Team',
            'date_time' => '2024-03-01 09:00:00',
            'conclusion' => 'Done',
            'insert_more' => 'More',
            'arrangements' => 'Next steps',
            'ai_suggestions' => 'Suggestions',
        ]);
    });

    it('roundtrips from array to array correctly', function () {
        $originalData = [
            'info' => 'Roundtrip info',
            'notes' => 'Roundtrip notes',
            'location' => 'Roundtrip location',
            'attendees' => 'Roundtrip attendees',
            'date_time' => '2024-04-15 16:30:00',
            'conclusion' => 'Roundtrip conclusion',
            'insert_more' => 'Roundtrip more',
            'arrangements' => 'Roundtrip arrangements',
            'ai_suggestions' => 'Roundtrip suggestions',
        ];

        $content = AiContentFrom::fromArray($originalData);
        $resultArray = $content->toArray();

        expect($resultArray)->toBe($originalData);
    });
});
