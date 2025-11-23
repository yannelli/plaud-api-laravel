<?php

namespace Yannelli\LaravelPlaud\Models;

class AiContentHeader
{
    /**
     * @param array<string> $keywords
     * @param array<RecommendQuestion> $recommendQuestions
     */
    public function __construct(
        public string $headline = '',
        public array $keywords = [],
        public string $industryCategory = '',
        public array $recommendQuestions = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $recommendQuestions = [];
        if (isset($data['recommend_questions']) && is_array($data['recommend_questions'])) {
            foreach ($data['recommend_questions'] as $question) {
                $recommendQuestions[] = RecommendQuestion::fromArray($question);
            }
        }

        return new self(
            headline: $data['headline'] ?? '',
            keywords: $data['keywords'] ?? [],
            industryCategory: $data['industry_category'] ?? '',
            recommendQuestions: $recommendQuestions,
        );
    }

    public function toArray(): array
    {
        return [
            'headline' => $this->headline,
            'keywords' => $this->keywords,
            'industry_category' => $this->industryCategory,
            'recommend_questions' => array_map(fn($q) => $q->toArray(), $this->recommendQuestions),
        ];
    }
}
