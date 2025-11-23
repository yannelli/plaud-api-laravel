<?php

namespace Yannelli\LaravelPlaud\Models;

class NextDatum
{
    public function __construct(
        public string $membershipType,
        public int $membershipMonths,
        public int $membershipSeconds,
        public int $periodCurr,
        public int $periodLeft,
        public int $secondsLeft,
        public int $secondsTotal,
        public int $autorenewStatus,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            membershipType: $data['membership_type'] ?? '',
            membershipMonths: $data['membership_months'] ?? 0,
            membershipSeconds: $data['membership_seconds'] ?? 0,
            periodCurr: $data['period_curr'] ?? 0,
            periodLeft: $data['period_left'] ?? 0,
            secondsLeft: $data['seconds_left'] ?? 0,
            secondsTotal: $data['seconds_total'] ?? 0,
            autorenewStatus: $data['autorenew_status'] ?? 0,
        );
    }

    public function toArray(): array
    {
        return [
            'membership_type' => $this->membershipType,
            'membership_months' => $this->membershipMonths,
            'membership_seconds' => $this->membershipSeconds,
            'period_curr' => $this->periodCurr,
            'period_left' => $this->periodLeft,
            'seconds_left' => $this->secondsLeft,
            'seconds_total' => $this->secondsTotal,
            'autorenew_status' => $this->autorenewStatus,
        ];
    }
}
