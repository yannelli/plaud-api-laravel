<?php

namespace Yannelli\LaravelPlaud\Models;

class RecommendQuestion
{
    /**
     * @param array<DataFileList> $dataFileList
     */
    public function __construct(
        public int $status = 0,
        public string $msg = '',
        public int $dataFileTotal = 0,
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
            status: $data['status'] ?? 0,
            msg: $data['msg'] ?? '',
            dataFileTotal: $data['data_file_total'] ?? 0,
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
