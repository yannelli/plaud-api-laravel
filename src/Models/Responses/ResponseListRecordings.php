<?php

namespace Yannelli\LaravelPlaud\Models\Responses;

use Yannelli\LaravelPlaud\Models\DataFileList;

class ResponseListRecordings
{
    /**
     * @param array<DataFileList> $dataFileList
     */
    public function __construct(
        public ?int $status,
        public string $msg,
        public ?int $dataFileTotal,
        public array $dataFileList = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $dataFileList = [];
        if (isset($data['data_file_list']) && is_array($data['data_file_list'])) {
            foreach ($data['data_file_list'] as $file) {
                $dataFileList[] = DataFileList::fromArray($file);
            }
        }

        return new self(
            status: $data['status'] ?? null,
            msg: $data['msg'] ?? '',
            dataFileTotal: $data['data_file_total'] ?? null,
            dataFileList: $dataFileList,
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'msg' => $this->msg,
            'data_file_total' => $this->dataFileTotal,
            'data_file_list' => array_map(fn($file) => $file->toArray(), $this->dataFileList),
        ];
    }
}
