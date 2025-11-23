<?php

namespace Yannelli\LaravelPlaud;

use Carbon\Carbon;
use Yannelli\LaravelPlaud\Exceptions\PlaudException;
use Yannelli\LaravelPlaud\Models\EventParam;
use Yannelli\LaravelPlaud\Models\Info;
use Yannelli\LaravelPlaud\Models\Requests\RequestExportFile;
use Yannelli\LaravelPlaud\Models\Requests\RequestExportSummary;
use Yannelli\LaravelPlaud\Models\Requests\RequestShareableLinkPermissions;
use Yannelli\LaravelPlaud\Models\Requests\RequestUploadInfo;
use Yannelli\LaravelPlaud\Models\Responses\ResponseAuth;
use Yannelli\LaravelPlaud\Models\Responses\ResponseAudioTempUrl;
use Yannelli\LaravelPlaud\Models\Responses\ResponseExportFile;
use Yannelli\LaravelPlaud\Models\Responses\ResponseFileTags;
use Yannelli\LaravelPlaud\Models\Responses\ResponseListRecordings;
use Yannelli\LaravelPlaud\Models\Responses\ResponseShareableLink;
use Yannelli\LaravelPlaud\Models\Responses\ResponseStatus;
use Yannelli\LaravelPlaud\Models\Responses\ResponseUploadInfo;
use Yannelli\LaravelPlaud\Models\Responses\ResponseUser;

/**
 * High-level service for interacting with the Plaud API
 */
class PlaudService
{
    protected PlaudClient $client;

    /**
     * Create a new Plaud service instance
     */
    public function __construct(?PlaudClient $client = null)
    {
        $this->client = $client ?? new PlaudClient();
    }

    /**
     * Get the underlying HTTP client
     */
    public function getClient(): PlaudClient
    {
        return $this->client;
    }

    /**
     * Get the current access token
     */
    public function getAccessToken(): ?string
    {
        return $this->client->getAccessToken();
    }

    /**
     * Authenticate with the Plaud API using username and password
     */
    public function authenticate(string $username, string $password): ResponseAuth
    {
        if (empty($username) || empty($password)) {
            throw new PlaudException('Username or password cannot be empty.');
        }

        $data = $this->client->authenticate($username, $password);

        $response = ResponseAuth::fromArray($data);

        if (empty($response->accessToken)) {
            throw new PlaudException('Authentication failed.');
        }

        return $response;
    }

    /**
     * Get the current user's profile information
     */
    public function getMyUser(): ResponseUser
    {
        $data = $this->client->get('/user/me');
        return ResponseUser::fromArray($data);
    }

    /**
     * Get the current status of the API and system
     */
    public function getStatus(): ResponseStatus
    {
        $data = $this->client->get('/ai/status');
        return ResponseStatus::fromArray($data);
    }

    /**
     * Get all recordings without filters
     */
    public function getAllRecordings(): ResponseListRecordings
    {
        $endpoint = '/file/simple/web?skip=0&limit=99999&is_trash=2&sort_by=start_time&is_desc=true';
        $data = $this->client->get($endpoint);
        return ResponseListRecordings::fromArray($data);
    }

    /**
     * Get recordings with filters applied
     */
    public function getRecordingsWithFilter(
        int $skip = 0,
        int $limit = 99999,
        int $isTrash = 2,
        string $sortBy = 'start_time',
        bool $isDesc = true
    ): ResponseListRecordings {
        $endpoint = sprintf(
            '/file/simple/web?skip=%d&limit=%d&is_trash=%d&sort_by=%s&is_desc=%s',
            $skip,
            $limit,
            $isTrash,
            $sortBy,
            $isDesc ? 'true' : 'false'
        );

        $data = $this->client->get($endpoint);
        return ResponseListRecordings::fromArray($data);
    }

    /**
     * Get specific recordings by their IDs
     *
     * @param array<string> $recordingIds
     */
    public function getSpecificRecordings(array $recordingIds): ResponseListRecordings
    {
        if (empty($recordingIds)) {
            throw new PlaudException('Recording IDs cannot be empty.');
        }

        $data = $this->client->post('/file/list', $recordingIds);
        return ResponseListRecordings::fromArray($data);
    }

    /**
     * Get all file tags (folders)
     */
    public function getFileTags(): ResponseFileTags
    {
        $data = $this->client->get('/filetag/');
        return ResponseFileTags::fromArray($data);
    }

    /**
     * Create a shareable link for a recording
     */
    public function createShareableLink(
        string $recordingId,
        RequestShareableLinkPermissions $permissions
    ): ResponseShareableLink {
        if (empty($recordingId)) {
            throw new PlaudException('Recording ID cannot be empty.');
        }

        $data = $this->client->post("/file/share-url/{$recordingId}", $permissions->toArray());
        return ResponseShareableLink::fromArray($data);
    }

    /**
     * Download an audio file (MP3) as base64 string
     */
    public function downloadAudioFile(string $recordingId): string
    {
        if (empty($recordingId)) {
            throw new PlaudException('Recording ID cannot be empty.');
        }

        // Send upload info request
        $uploadInfo = new RequestUploadInfo(
            info: new Info(
                eventCat: 'share',
                eventParam: new EventParam(
                    action: 'export_audio',
                    fileKey: $recordingId,
                    fileID: $recordingId,
                    from: 'web'
                )
            )
        );

        $uploadInfoData = $this->client->post('/others/upload-info', $uploadInfo->toArray());
        $uploadInfoResponse = ResponseUploadInfo::fromArray($uploadInfoData);

        if ($uploadInfoResponse->msg !== 'success') {
            throw new PlaudException('Upload Info failed.');
        }

        // Get temporary download URL
        $tempUrlData = $this->client->get("/file/temp-url/{$recordingId}");
        $tempUrlResponse = ResponseAudioTempUrl::fromArray($tempUrlData);

        if (empty($tempUrlResponse->tempUrl)) {
            throw new PlaudException('No download url found.');
        }

        // Download and encode file
        return $this->client->downloadFileAsBase64($tempUrlResponse->tempUrl);
    }

    /**
     * Download a transcript file as base64 string
     */
    public function downloadTranscriptFile(string $recordingId, string $fileType): string
    {
        if (empty($recordingId) || empty($fileType)) {
            throw new PlaudException('Recording ID and File Type cannot be empty.');
        }

        // Send upload info request
        $uploadInfo = new RequestUploadInfo(
            info: new Info(
                eventCat: 'share',
                eventParam: new EventParam(
                    action: 'export_transcription',
                    fileKey: $recordingId,
                    fileID: $recordingId,
                    from: 'web'
                )
            )
        );

        $uploadInfoData = $this->client->post('/others/upload-info', $uploadInfo->toArray());
        $uploadInfoResponse = ResponseUploadInfo::fromArray($uploadInfoData);

        if ($uploadInfoResponse->msg !== 'success') {
            throw new PlaudException('Upload Info failed.');
        }

        // Get recording details
        $recordingData = $this->client->post('/file/list', [$recordingId]);
        $recording = ResponseListRecordings::fromArray($recordingData);

        if ($recording->dataFileTotal !== 1) {
            throw new PlaudException("Unable to locate recording with provided ID {$recordingId}.");
        }

        $file = $recording->dataFileList[0];

        // Check if recording has speakers and timestamps
        $hasSpeaker = 0;
        $hasTimestamp = 0;

        foreach ($file->transResult as $trans) {
            if (!empty($trans->speaker)) {
                $hasSpeaker = 1;
            }
            if ($trans->startTime >= 0) {
                $hasTimestamp = 1;
            }
        }

        // Create export request
        $exportFile = new RequestExportFile(
            fileId: $recordingId,
            promptType: 'trans',
            toFormat: $fileType,
            title: $file->filename,
            createTime: $this->convertTimestampToDateTime($file->startTime ?? 0),
            withSpeaker: $hasSpeaker,
            withTimestamp: $hasTimestamp,
            transContent: $file->transResult
        );

        $exportData = $this->client->post('/file/document/export', $exportFile->toArray());
        $exportResponse = ResponseExportFile::fromArray($exportData);

        if (empty($exportResponse->data)) {
            throw new PlaudException('No download url found.');
        }

        // Download and encode file
        return $this->client->downloadFileAsBase64($exportResponse->data);
    }

    /**
     * Download a summary file as base64 string
     */
    public function downloadSummaryFile(string $recordingId, string $fileType): string
    {
        if (empty($recordingId) || empty($fileType)) {
            throw new PlaudException('Recording ID and File Type cannot be empty.');
        }

        // Get recording details
        $recordingData = $this->client->post('/file/list', [$recordingId]);
        $recording = ResponseListRecordings::fromArray($recordingData);

        if ($recording->dataFileTotal !== 1) {
            throw new PlaudException("Unable to locate recording with provided ID {$recordingId}.");
        }

        $file = $recording->dataFileList[0];

        // Check if recording has speakers and timestamps
        $hasSpeaker = 0;
        $hasTimestamp = 0;

        foreach ($file->transResult as $trans) {
            if (!empty($trans->speaker)) {
                $hasSpeaker = 1;
            }
            if ($trans->startTime >= 0) {
                $hasTimestamp = 1;
            }
        }

        // Create export request
        $exportFile = new RequestExportSummary(
            fileId: $recordingId,
            promptType: 'summary',
            toFormat: $fileType,
            title: $file->filename,
            createTime: $this->convertTimestampToDateTime($file->startTime ?? 0),
            withSpeaker: $hasSpeaker,
            withTimestamp: $hasTimestamp,
            summaryContent: $file->aiContent ?? ''
        );

        $exportData = $this->client->post('/file/document/export', $exportFile->toArray());
        $exportResponse = ResponseExportFile::fromArray($exportData);

        if (empty($exportResponse->data)) {
            throw new PlaudException('No download url found.');
        }

        // Download and encode file
        return $this->client->downloadFileAsBase64($exportResponse->data);
    }

    /**
     * Move recordings to trash
     *
     * @param array<string> $recordingIds
     */
    public function trashRecordings(array $recordingIds): bool
    {
        if (empty($recordingIds)) {
            throw new PlaudException('Recording IDs cannot be empty.');
        }

        return $this->client->postNoResponse('/file/trash/', $recordingIds);
    }

    /**
     * Restore recordings from trash
     *
     * @param array<string> $recordingIds
     */
    public function untrashRecordings(array $recordingIds): bool
    {
        if (empty($recordingIds)) {
            throw new PlaudException('Recording IDs cannot be empty.');
        }

        return $this->client->postNoResponse('/file/untrash/', $recordingIds);
    }

    /**
     * Permanently delete recordings from trash
     *
     * @param array<string> $recordingIds
     */
    public function permanentlyDeleteRecordings(array $recordingIds): bool
    {
        if (empty($recordingIds)) {
            throw new PlaudException('Recording IDs cannot be empty.');
        }

        return $this->client->deleteWithBody('/file/', $recordingIds);
    }

    /**
     * Convert Unix timestamp (milliseconds) to formatted datetime string
     */
    protected function convertTimestampToDateTime(int $timestamp): string
    {
        return Carbon::createFromTimestampMs($timestamp)->format('Y-m-d H:i:s');
    }
}
