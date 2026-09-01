<?php

namespace Yannelli\LaravelPlaud\Models\Responses;

use Yannelli\LaravelPlaud\Models\Device;

class ResponseDevices
{
    /**
     * @param array<Device> $devices
     */
    public function __construct(
        public int $status,
        public string $msg = '',
        public array $devices = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $list = $data['data_device_list'] ?? $data['data'] ?? $data['devices'] ?? [];

        if (! is_array($list)) {
            $list = [];
        }

        if (isset($list['list']) && is_array($list['list'])) {
            $list = $list['list'];
        }

        $devices = [];
        foreach ($list as $item) {
            if (is_array($item)) {
                $devices[] = Device::fromArray($item);
            }
        }

        return new self(
            status: (int) ($data['status'] ?? 0),
            msg: (string) ($data['msg'] ?? ''),
            devices: $devices,
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'msg' => $this->msg,
            'data_device_list' => array_map(fn (Device $device) => $device->toArray(), $this->devices),
        ];
    }
}
