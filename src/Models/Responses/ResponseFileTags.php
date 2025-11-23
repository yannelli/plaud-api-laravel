<?php

namespace Yannelli\LaravelPlaud\Models\Responses;

use Yannelli\LaravelPlaud\Models\DataFiletagList;

class ResponseFileTags
{
    /**
     * @param array<DataFiletagList> $dataFiletagList
     */
    public function __construct(
        public int $status,
        public string $msg,
        public int $dataFiletagTotal,
        public array $dataFiletagList = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $dataFiletagList = [];
        if (isset($data['data_filetag_list']) && is_array($data['data_filetag_list'])) {
            foreach ($data['data_filetag_list'] as $tag) {
                $dataFiletagList[] = DataFiletagList::fromArray($tag);
            }
        }

        return new self(
            status: $data['status'] ?? 0,
            msg: $data['msg'] ?? '',
            dataFiletagTotal: $data['data_filetag_total'] ?? 0,
            dataFiletagList: $dataFiletagList,
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'msg' => $this->msg,
            'data_filetag_total' => $this->dataFiletagTotal,
            'data_filetag_list' => array_map(fn($tag) => $tag->toArray(), $this->dataFiletagList),
        ];
    }
}
