<?php

namespace Yannelli\LaravelPlaud\Models;

class DataProcessingTranssumm
{
    public function __construct(
        public array $filesTrans = [],
        public array $filesSumm = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            filesTrans: $data['files_trans'] ?? [],
            filesSumm: $data['files_summ'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'files_trans' => $this->filesTrans,
            'files_summ' => $this->filesSumm,
        ];
    }
}
