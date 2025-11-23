<?php

namespace Yannelli\LaravelPlaud\Models;

class DataUser
{
    public function __construct(
        public string $id,
        public mixed $avatar = null,
        public string $nickname = '',
        public mixed $birthday = null,
        public mixed $gender = null,
        public string $country = '',
        public mixed $address = null,
        public string $email = '',
        public bool $emailVerified = false,
        public mixed $phone = null,
        public bool $phoneVerified = false,
        public int $membershipId = 0,
        public int $startTime = 0,
        public int $expireTime = 0,
        public int $resetTime = 0,
        public int $secondsLeft = 0,
        public int $secondsTotal = 0,
        public int $membershipIdTraffic = 0,
        public int $startTimeTraffic = 0,
        public int $expireTimeTraffic = 0,
        public int $secondsLeftTraffic = 0,
        public int $secondsTotalTraffic = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            avatar: $data['avatar'] ?? null,
            nickname: $data['nickname'] ?? '',
            birthday: $data['birthday'] ?? null,
            gender: $data['gender'] ?? null,
            country: $data['country'] ?? '',
            address: $data['address'] ?? null,
            email: $data['email'] ?? '',
            emailVerified: $data['email_verified'] ?? false,
            phone: $data['phone'] ?? null,
            phoneVerified: $data['phone_verified'] ?? false,
            membershipId: $data['membership_id'] ?? 0,
            startTime: $data['start_time'] ?? 0,
            expireTime: $data['expire_time'] ?? 0,
            resetTime: $data['reset_time'] ?? 0,
            secondsLeft: $data['seconds_left'] ?? 0,
            secondsTotal: $data['seconds_total'] ?? 0,
            membershipIdTraffic: $data['membership_id_traffic'] ?? 0,
            startTimeTraffic: $data['start_time_traffic'] ?? 0,
            expireTimeTraffic: $data['expire_time_traffic'] ?? 0,
            secondsLeftTraffic: $data['seconds_left_traffic'] ?? 0,
            secondsTotalTraffic: $data['seconds_total_traffic'] ?? 0,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'avatar' => $this->avatar,
            'nickname' => $this->nickname,
            'birthday' => $this->birthday,
            'gender' => $this->gender,
            'country' => $this->country,
            'address' => $this->address,
            'email' => $this->email,
            'email_verified' => $this->emailVerified,
            'phone' => $this->phone,
            'phone_verified' => $this->phoneVerified,
            'membership_id' => $this->membershipId,
            'start_time' => $this->startTime,
            'expire_time' => $this->expireTime,
            'reset_time' => $this->resetTime,
            'seconds_left' => $this->secondsLeft,
            'seconds_total' => $this->secondsTotal,
            'membership_id_traffic' => $this->membershipIdTraffic,
            'start_time_traffic' => $this->startTimeTraffic,
            'expire_time_traffic' => $this->expireTimeTraffic,
            'seconds_left_traffic' => $this->secondsLeftTraffic,
            'seconds_total_traffic' => $this->secondsTotalTraffic,
        ];
    }
}
