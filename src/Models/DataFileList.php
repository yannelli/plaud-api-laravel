<?php

namespace Yannelli\LaravelPlaud\Models;

class DataFileList
{
    /**
     * @param array<string> $keywords
     * @param array<string> $filetagIdList
     * @param array<TransContent> $transResult
     */
    public function __construct(
        public string $id,
        public string $filename,
        public array $keywords = [],
        public ?int $filesize = null,
        public ?string $filetype = null,
        public ?string $fullname = null,
        public ?string $fileMd5 = null,
        public ?bool $oriReady = null,
        public ?int $version = null,
        public ?int $versionMs = null,
        public ?int $editTime = null,
        public ?string $editFrom = null,
        public ?bool $isTrash = null,
        public ?int $startTime = null,
        public ?int $endTime = null,
        public ?int $duration = null,
        public ?int $timezone = null,
        public ?int $zonemins = null,
        public ?int $scene = null,
        public array $filetagIdList = [],
        public ?bool $isTrans = null,
        public ?bool $isSummary = null,
        public ?string $serialNumber = null,
        public ?int $sessionId = null,
        public ?int $channel = null,
        public ?string $oriFullname = null,
        public ?string $oriLocation = null,
        public array $transResult = [],
        public ?string $aiContent = null,
        public ?ExtraData $extraData = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $transResult = [];
        if (isset($data['trans_result']) && is_array($data['trans_result'])) {
            foreach ($data['trans_result'] as $trans) {
                $transResult[] = TransContent::fromArray($trans);
            }
        }

        return new self(
            id: $data['id'] ?? '',
            filename: $data['filename'] ?? '',
            keywords: $data['keywords'] ?? [],
            filesize: $data['filesize'] ?? null,
            filetype: $data['filetype'] ?? null,
            fullname: $data['fullname'] ?? null,
            fileMd5: $data['file_md5'] ?? null,
            oriReady: $data['ori_ready'] ?? null,
            version: $data['version'] ?? null,
            versionMs: $data['version_ms'] ?? null,
            editTime: $data['edit_time'] ?? null,
            editFrom: $data['edit_from'] ?? null,
            isTrash: $data['is_trash'] ?? null,
            startTime: $data['start_time'] ?? null,
            endTime: $data['end_time'] ?? null,
            duration: $data['duration'] ?? null,
            timezone: $data['timezone'] ?? null,
            zonemins: $data['zonemins'] ?? null,
            scene: $data['scene'] ?? null,
            filetagIdList: $data['filetag_id_list'] ?? [],
            isTrans: $data['is_trans'] ?? null,
            isSummary: $data['is_summary'] ?? null,
            serialNumber: $data['serial_number'] ?? null,
            sessionId: $data['session_id'] ?? null,
            channel: $data['channel'] ?? null,
            oriFullname: $data['ori_fullname'] ?? null,
            oriLocation: $data['ori_location'] ?? null,
            transResult: $transResult,
            aiContent: $data['ai_content'] ?? null,
            extraData: isset($data['extra_data']) ? ExtraData::fromArray($data['extra_data']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'keywords' => $this->keywords,
            'filesize' => $this->filesize,
            'filetype' => $this->filetype,
            'fullname' => $this->fullname,
            'file_md5' => $this->fileMd5,
            'ori_ready' => $this->oriReady,
            'version' => $this->version,
            'version_ms' => $this->versionMs,
            'edit_time' => $this->editTime,
            'edit_from' => $this->editFrom,
            'is_trash' => $this->isTrash,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'duration' => $this->duration,
            'timezone' => $this->timezone,
            'zonemins' => $this->zonemins,
            'scene' => $this->scene,
            'filetag_id_list' => $this->filetagIdList,
            'is_trans' => $this->isTrans,
            'is_summary' => $this->isSummary,
            'serial_number' => $this->serialNumber,
            'session_id' => $this->sessionId,
            'channel' => $this->channel,
            'ori_fullname' => $this->oriFullname,
            'ori_location' => $this->oriLocation,
            'trans_result' => array_map(fn($trans) => $trans->toArray(), $this->transResult),
            'ai_content' => $this->aiContent,
            'extra_data' => $this->extraData?->toArray(),
        ];
    }
}
