<?php

namespace Yannelli\LaravelPlaud\Models;

class DataState
{
    /**
     * @param array<NextDatum> $nextData
     */
    public function __construct(
        public int $isBind,
        public int $isMembership,
        public bool $autorenewStatusIos,
        public bool $autorenewStatusAndroid,
        public bool $autorenewStatusWeb,
        public string $membershipFlag,
        public string $membershipType,
        public array $nextData = [],
        public int $isFreeTrialNow = 0,
        public int $isFreeTrialHistory = 0,
        public bool $isInner = false,
        public bool $isOuter = false,
        public bool $isGray = false,
        public bool $isVirtual = false,
        public int $createdAt = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        $nextData = [];
        if (isset($data['next_data']) && is_array($data['next_data'])) {
            foreach ($data['next_data'] as $datum) {
                $nextData[] = NextDatum::fromArray($datum);
            }
        }

        return new self(
            isBind: $data['is_bind'] ?? 0,
            isMembership: $data['is_membership'] ?? 0,
            autorenewStatusIos: $data['autorenew_status_ios'] ?? false,
            autorenewStatusAndroid: $data['autorenew_status_android'] ?? false,
            autorenewStatusWeb: $data['autorenew_status_web'] ?? false,
            membershipFlag: $data['membership_flag'] ?? '',
            membershipType: $data['membership_type'] ?? '',
            nextData: $nextData,
            isFreeTrialNow: $data['is_free_trial_now'] ?? 0,
            isFreeTrialHistory: $data['is_free_trial_history'] ?? 0,
            isInner: $data['is_inner'] ?? false,
            isOuter: $data['is_outer'] ?? false,
            isGray: $data['is_gray'] ?? false,
            isVirtual: $data['is_virtual'] ?? false,
            createdAt: $data['created_at'] ?? 0,
        );
    }

    public function toArray(): array
    {
        return [
            'is_bind' => $this->isBind,
            'is_membership' => $this->isMembership,
            'autorenew_status_ios' => $this->autorenewStatusIos,
            'autorenew_status_android' => $this->autorenewStatusAndroid,
            'autorenew_status_web' => $this->autorenewStatusWeb,
            'membership_flag' => $this->membershipFlag,
            'membership_type' => $this->membershipType,
            'next_data' => array_map(fn($datum) => $datum->toArray(), $this->nextData),
            'is_free_trial_now' => $this->isFreeTrialNow,
            'is_free_trial_history' => $this->isFreeTrialHistory,
            'is_inner' => $this->isInner,
            'is_outer' => $this->isOuter,
            'is_gray' => $this->isGray,
            'is_virtual' => $this->isVirtual,
            'created_at' => $this->createdAt,
        ];
    }
}
