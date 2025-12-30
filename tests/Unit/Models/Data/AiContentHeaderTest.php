<?php

use Yannelli\LaravelPlaud\Models\AiContentHeader;
use Yannelli\LaravelPlaud\Models\RecommendQuestion;

describe('AiContentHeader', function () {
    it('can be instantiated with default values', function () {
        $header = new AiContentHeader();

        expect($header->headline)->toBe('')
            ->and($header->keywords)->toBe([])
            ->and($header->industryCategory)->toBe('')
            ->and($header->recommendQuestions)->toBe([]);
    });

    it('can be instantiated with all properties', function () {
        $questions = [
            new RecommendQuestion(status: 200, msg: 'Question 1'),
        ];

        $header = new AiContentHeader(
            headline: 'Meeting Summary',
            keywords: ['meeting', 'summary', 'action-items'],
            industryCategory: 'Technology',
            recommendQuestions: $questions
        );

        expect($header->headline)->toBe('Meeting Summary')
            ->and($header->keywords)->toBe(['meeting', 'summary', 'action-items'])
            ->and($header->industryCategory)->toBe('Technology')
            ->and($header->recommendQuestions)->toHaveCount(1);
    });

    it('can be created from array', function () {
        $data = [
            'headline' => 'Project Discussion',
            'keywords' => ['project', 'planning'],
            'industry_category' => 'Business',
            'recommend_questions' => [
                [
                    'status' => 200,
                    'msg' => 'What are the next steps?',
                    'data_file_total' => 0,
                ],
                [
                    'status' => 200,
                    'msg' => 'Who is responsible?',
                    'data_file_total' => 0,
                ],
            ],
        ];

        $header = AiContentHeader::fromArray($data);

        expect($header->headline)->toBe('Project Discussion')
            ->and($header->keywords)->toBe(['project', 'planning'])
            ->and($header->industryCategory)->toBe('Business')
            ->and($header->recommendQuestions)->toHaveCount(2)
            ->and($header->recommendQuestions[0])->toBeInstanceOf(RecommendQuestion::class)
            ->and($header->recommendQuestions[0]->msg)->toBe('What are the next steps?');
    });

    it('handles missing keys with default values', function () {
        $header = AiContentHeader::fromArray([]);

        expect($header->headline)->toBe('')
            ->and($header->keywords)->toBe([])
            ->and($header->industryCategory)->toBe('')
            ->and($header->recommendQuestions)->toBe([]);
    });

    it('handles missing recommend_questions key', function () {
        $data = [
            'headline' => 'Test',
            'keywords' => ['test'],
        ];

        $header = AiContentHeader::fromArray($data);

        expect($header->recommendQuestions)->toBe([]);
    });

    it('converts to array correctly', function () {
        $header = new AiContentHeader(
            headline: 'Array Test',
            keywords: ['array', 'test'],
            industryCategory: 'Testing'
        );

        $array = $header->toArray();

        expect($array['headline'])->toBe('Array Test')
            ->and($array['keywords'])->toBe(['array', 'test'])
            ->and($array['industry_category'])->toBe('Testing')
            ->and($array['recommend_questions'])->toBe([]);
    });

    it('converts nested recommend_questions to array', function () {
        $questions = [
            new RecommendQuestion(status: 200, msg: 'First question'),
            new RecommendQuestion(status: 200, msg: 'Second question'),
        ];

        $header = new AiContentHeader(
            headline: 'Nested Test',
            recommendQuestions: $questions
        );

        $array = $header->toArray();

        expect($array['recommend_questions'])->toHaveCount(2)
            ->and($array['recommend_questions'][0])->toBeArray()
            ->and($array['recommend_questions'][0]['msg'])->toBe('First question');
    });
});
