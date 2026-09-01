<?php

namespace Yannelli\LaravelPlaud;

use Carbon\Carbon;
use Yannelli\LaravelPlaud\Exceptions\PlaudException;
use Yannelli\LaravelPlaud\Models\DataFileList;
use Yannelli\LaravelPlaud\Models\DataFiletagList;
use Yannelli\LaravelPlaud\Models\EventParam;
use Yannelli\LaravelPlaud\Models\Info;
use Yannelli\LaravelPlaud\Models\Requests\RequestExportFile;
use Yannelli\LaravelPlaud\Models\Requests\RequestExportSummary;
use Yannelli\LaravelPlaud\Models\Requests\RequestShareableLinkPermissions;
use Yannelli\LaravelPlaud\Models\Requests\RequestUploadInfo;
use Yannelli\LaravelPlaud\Models\Responses\ResponseAuth;
use Yannelli\LaravelPlaud\Models\Responses\ResponseAudioTempUrl;
use Yannelli\LaravelPlaud\Models\Responses\ResponseDevices;
use Yannelli\LaravelPlaud\Models\Responses\ResponseExportFile;
use Yannelli\LaravelPlaud\Models\Responses\ResponseFileTags;
use Yannelli\LaravelPlaud\Models\Responses\ResponseListRecordings;
use Yannelli\LaravelPlaud\Models\Responses\ResponseOtp;
use Yannelli\LaravelPlaud\Models\Responses\ResponsePayload;
use Yannelli\LaravelPlaud\Models\Responses\ResponseShareableLink;
use Yannelli\LaravelPlaud\Models\Responses\ResponseSpeakers;
use Yannelli\LaravelPlaud\Models\Responses\ResponseStatus;
use Yannelli\LaravelPlaud\Models\Responses\ResponseUploadInfo;
use Yannelli\LaravelPlaud\Models\Responses\ResponseUser;
use Yannelli\LaravelPlaud\Models\Responses\ResponseWorkspaces;
use Yannelli\LaravelPlaud\Support\Jwt;

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
     * Set the Bearer token used for subsequent API calls
     */
    public function setAccessToken(string $accessToken): self
    {
        $this->client->setAccessToken($accessToken);

        return $this;
    }

    public function setBaseUrl(string $baseUrl): self
    {
        $this->client->setBaseUrl($baseUrl);

        return $this;
    }

    public function setRegion(string $region): self
    {
        $this->client->setRegion($region);

        return $this;
    }

    public function getBaseUrl(): string
    {
        return $this->client->getBaseUrl();
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

        $hasSession = $response->accessToken !== ''
            || $this->client->getAccessToken()
            || $this->client->getUserCookie();

        if (! $hasSession) {
            throw new PlaudException('Authentication failed.');
        }

        return $response;
    }

    /**
     * Start email OTP login. Returns the otp token needed by otpLogin().
     */
    public function sendOtpCode(string $email): ResponseOtp
    {
        if (empty($email)) {
            throw new PlaudException('Email cannot be empty.');
        }

        $result = $this->client->sendOtpCode($email);

        return ResponseOtp::fromArray($result['raw'] + [
            'token' => $result['token'],
            'data' => ['domains' => ['api' => $result['api_base']]],
        ]);
    }

    /**
     * Complete email OTP login and store the resulting user token.
     */
    public function otpLogin(string $code, string $otpToken): ResponseAuth
    {
        if (empty($code) || empty($otpToken)) {
            throw new PlaudException('OTP code and token cannot be empty.');
        }

        $data = $this->client->otpLogin($code, $otpToken);

        return ResponseAuth::fromArray($data);
    }

    /**
     * Refresh a v3 cookie session.
     */
    public function refreshUserToken(): ResponseAuth
    {
        return ResponseAuth::fromArray($this->client->refreshUserToken());
    }

    /**
     * List workspaces for the current user token.
     */
    public function getWorkspaces(): ResponseWorkspaces
    {
        $data = $this->client->get('/team-app/workspaces/list');

        return ResponseWorkspaces::fromArray($data);
    }

    /**
     * Mint a short-lived workspace token and use it for subsequent data calls.
     */
    public function useWorkspace(string $workspaceId): string
    {
        if (empty($workspaceId)) {
            throw new PlaudException('Workspace ID cannot be empty.');
        }

        return $this->client->mintWorkspaceToken($workspaceId);
    }

    /**
     * True when the current Bearer token is a short-lived workspace token.
     */
    public function isUsingWorkspaceToken(): bool
    {
        $token = $this->client->getAccessToken();

        return $token !== null && Jwt::isWorkspaceToken($token);
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
        bool $isDesc = true,
        ?string $filetagId = null,
    ): ResponseListRecordings {
        $query = [
            'skip' => $skip,
            'limit' => $limit,
            'is_trash' => $isTrash,
            'sort_by' => $sortBy,
            'is_desc' => $isDesc ? 'true' : 'false',
        ];

        if ($filetagId !== null && $filetagId !== '') {
            $query['filetag_id'] = $filetagId;
        }

        $data = $this->client->get('/file/simple/web', $query);

        return ResponseListRecordings::fromArray($data);
    }

    /**
     * Get specific recordings by their IDs
     *
     * @param  array<string>  $recordingIds
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
     * Get a single recording's detail payload (GET /file/detail/{id}).
     */
    public function getRecording(string $recordingId): DataFileList
    {
        if (empty($recordingId)) {
            throw new PlaudException('Recording ID cannot be empty.');
        }

        try {
            $data = $this->client->get('/file/detail/'.$recordingId);
            $payload = is_array($data['data'] ?? null) ? $data['data'] : $data;

            if (is_array($payload) && (($payload['id'] ?? $payload['file_id'] ?? '') !== '')) {
                return DataFileList::fromArray($payload);
            }
        } catch (PlaudException) {
            // Older accounts still answer POST /file/list and not GET /file/detail/{id}.
        }

        return $this->requireRecording($recordingId);
    }

    /**
     * Patch recording metadata (filename, tags, keywords, extra_data, ...).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function updateRecording(string $recordingId, array $attributes): ResponsePayload
    {
        if (empty($recordingId)) {
            throw new PlaudException('Recording ID cannot be empty.');
        }

        if ($attributes === []) {
            throw new PlaudException('Update attributes cannot be empty.');
        }

        return ResponsePayload::fromArray(
            $this->client->patch("/file/{$recordingId}", $attributes)
        );
    }

    /**
     * Rename a recording via PATCH /file/{id}.
     */
    public function renameRecording(string $recordingId, string $filename): ResponsePayload
    {
        if ($filename === '') {
            throw new PlaudException('Filename cannot be empty.');
        }

        return $this->updateRecording($recordingId, ['filename' => $filename]);
    }

    /**
     * Replace the folder/tag IDs on a recording.
     *
     * @param  array<int, string>  $tagIds
     */
    public function setRecordingTags(string $recordingId, array $tagIds): ResponsePayload
    {
        return $this->updateRecording($recordingId, [
            'filetag_id_list' => array_values($tagIds),
        ]);
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
     * Create a file tag (folder). POST /filetag/ {name}.
     */
    public function createFileTag(string $name, string $color = '', string $icon = ''): DataFiletagList
    {
        if ($name === '') {
            throw new PlaudException('Tag name cannot be empty.');
        }

        $body = ['name' => $name];
        if ($color !== '') {
            $body['color'] = $color;
        }
        if ($icon !== '') {
            $body['icon'] = $icon;
        }

        $data = $this->client->post('/filetag/', $body);
        $payload = is_array($data['data'] ?? null) ? $data['data'] : $data;

        if (isset($data['data_filetag_list']) && is_array($data['data_filetag_list']) && $data['data_filetag_list'] !== []) {
            $last = $data['data_filetag_list'][array_key_last($data['data_filetag_list'])];
            if (is_array($last)) {
                $payload = $last;
            }
        }

        return DataFiletagList::fromArray(is_array($payload) ? $payload : ['name' => $name]);
    }

    /**
     * Delete a file tag (folder). DELETE /filetag/{id}.
     */
    public function deleteFileTag(string $tagId): bool
    {
        if ($tagId === '') {
            throw new PlaudException('Tag ID cannot be empty.');
        }

        return $this->client->delete('/filetag/'.$tagId);
    }

    /**
     * List recordings that belong to a tag (server-side filetag_id filter).
     */
    public function getRecordingsByTag(string $tagId, int $skip = 0, int $limit = 99999): ResponseListRecordings
    {
        if ($tagId === '') {
            throw new PlaudException('Tag ID cannot be empty.');
        }

        return $this->getRecordingsWithFilter(
            skip: $skip,
            limit: $limit,
            isTrash: 0,
            filetagId: $tagId,
        );
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
     * Get an existing private share payload.
     */
    public function getPrivateShare(string $recordingId): ResponsePayload
    {
        if (empty($recordingId)) {
            throw new PlaudException('Recording ID cannot be empty.');
        }

        return ResponsePayload::fromArray(
            $this->client->post('/share/private/get', ['file_id' => $recordingId])
        );
    }

    /**
     * Get an existing public share payload.
     */
    public function getPublicShare(string $recordingId): ResponsePayload
    {
        if (empty($recordingId)) {
            throw new PlaudException('Recording ID cannot be empty.');
        }

        return ResponsePayload::fromArray(
            $this->client->post('/share/public/get', ['file_id' => $recordingId])
        );
    }

    /**
     * Get a temporary audio download URL without pulling the bytes.
     */
    public function getAudioTempUrl(string $recordingId, bool $opus = false): ResponseAudioTempUrl
    {
        if (empty($recordingId)) {
            throw new PlaudException('Recording ID cannot be empty.');
        }

        $query = $opus ? ['is_opus' => 'true'] : [];
        $data = $this->client->get("/file/temp-url/{$recordingId}", $query);

        return ResponseAudioTempUrl::fromArray($data);
    }

    /**
     * Download an audio file (MP3) as base64 string
     */
    public function downloadAudioFile(string $recordingId): string
    {
        if (empty($recordingId)) {
            throw new PlaudException('Recording ID cannot be empty.');
        }

        $summaryId = $this->summaryIdFromRecording($recordingId);

        $uploadInfo = new RequestUploadInfo(
            info: new Info(
                eventCat: 'share',
                eventParam: new EventParam(
                    action: 'export_audio',
                    fileKey: $recordingId,
                    fileID: $recordingId,
                    from: 'web',
                    summaryId: $summaryId,
                )
            )
        );

        $uploadInfoData = $this->client->post('/others/upload-info', $uploadInfo->toArray());
        $uploadInfoResponse = ResponseUploadInfo::fromArray($uploadInfoData);

        if ($uploadInfoResponse->msg !== 'success') {
            throw new PlaudException('Upload Info failed.');
        }

        $tempUrlResponse = $this->getAudioTempUrl($recordingId);

        if (empty($tempUrlResponse->tempUrl)) {
            throw new PlaudException('No download url found.');
        }

        return $this->client->downloadFileAsBase64($tempUrlResponse->tempUrl);
    }

    /**
     * Download audio via GET /file/download/{id} (newer web-app path).
     */
    public function downloadAudioDirect(string $recordingId): string
    {
        if (empty($recordingId)) {
            throw new PlaudException('Recording ID cannot be empty.');
        }

        return $this->client->downloadAuthenticatedAsBase64("/file/download/{$recordingId}");
    }

    /**
     * Download a transcript file as base64 string
     */
    public function downloadTranscriptFile(string $recordingId, string $fileType): string
    {
        if (empty($recordingId) || empty($fileType)) {
            throw new PlaudException('Recording ID and File Type cannot be empty.');
        }

        $file = $this->requireRecording($recordingId);
        $summaryId = $this->summaryIdFromFile($file);

        $uploadInfo = new RequestUploadInfo(
            info: new Info(
                eventCat: 'share',
                eventParam: new EventParam(
                    action: 'export_transcription',
                    fileKey: $recordingId,
                    fileID: $recordingId,
                    from: 'web',
                    summaryId: $summaryId,
                )
            )
        );

        $uploadInfoData = $this->client->post('/others/upload-info', $uploadInfo->toArray());
        $uploadInfoResponse = ResponseUploadInfo::fromArray($uploadInfoData);

        if ($uploadInfoResponse->msg !== 'success') {
            throw new PlaudException('Upload Info failed.');
        }

        [$hasSpeaker, $hasTimestamp] = $this->speakerTimestampFlags($file);

        $exportFile = new RequestExportFile(
            fileId: $recordingId,
            promptType: 'trans',
            toFormat: $fileType,
            title: $file->filename,
            createTime: $this->convertTimestampToDateTime($file->startTime ?? 0),
            withSpeaker: $hasSpeaker,
            withTimestamp: $hasTimestamp,
            transContent: $file->transResult,
            summaryId: $summaryId,
        );

        $exportData = $this->client->post('/file/document/export', $exportFile->toArray());
        $exportResponse = ResponseExportFile::fromArray($exportData);

        if (empty($exportResponse->data)) {
            throw new PlaudException('No download url found.');
        }

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

        $file = $this->requireRecording($recordingId);
        $summaryId = $this->summaryIdFromFile($file);
        [$hasSpeaker, $hasTimestamp] = $this->speakerTimestampFlags($file);

        $exportFile = new RequestExportSummary(
            fileId: $recordingId,
            promptType: 'summary',
            toFormat: $fileType,
            title: $file->filename,
            createTime: $this->convertTimestampToDateTime($file->startTime ?? 0),
            withSpeaker: $hasSpeaker,
            withTimestamp: $hasTimestamp,
            summaryContent: $file->aiContent ?? '',
            summaryId: $summaryId,
        );

        $exportData = $this->client->post('/file/document/export', $exportFile->toArray());
        $exportResponse = ResponseExportFile::fromArray($exportData);

        if (empty($exportResponse->data)) {
            throw new PlaudException('No download url found.');
        }

        return $this->client->downloadFileAsBase64($exportResponse->data);
    }

    /**
     * Start cloud transcription / analysis for a recording.
     */
    public function startAnalysis(string $recordingId, string $language = 'en', string $type = 'REASONING-NOTE'): ResponsePayload
    {
        if (empty($recordingId)) {
            throw new PlaudException('Recording ID cannot be empty.');
        }

        $data = $this->client->patch("/file/{$recordingId}", [
            'extra_data' => [
                'tranConfig' => [
                    'language' => $language,
                    'type_type' => 'system',
                    'type' => $type,
                    'diarization' => 1,
                    'llm' => 'auto',
                ],
            ],
        ]);

        return ResponsePayload::fromArray($data);
    }

    /**
     * Poll POST /ai/transsumm/{id} for analysis results.
     */
    public function getTranssumm(
        string $recordingId,
        string $language = 'en',
        string $summType = 'REASONING-NOTE',
        bool $reload = false,
    ): ResponsePayload {
        if (empty($recordingId)) {
            throw new PlaudException('Recording ID cannot be empty.');
        }

        $data = $this->client->post("/ai/transsumm/{$recordingId}", [
            'is_reload' => $reload ? 1 : 0,
            'summ_type' => $summType,
            'summ_type_type' => 'system',
            'info' => json_encode([
                'language' => $language,
                'diarization' => 1,
                'llm' => 'auto',
            ]),
            'support_mul_summ' => true,
        ]);

        return ResponsePayload::fromArray($data);
    }

    /**
     * GET /ai/file-task-status?file_id=
     */
    public function getFileTaskStatus(string $recordingId): ResponsePayload
    {
        if (empty($recordingId)) {
            throw new PlaudException('Recording ID cannot be empty.');
        }

        return ResponsePayload::fromArray(
            $this->client->get('/ai/file-task-status', ['file_id' => $recordingId])
        );
    }

    /**
     * GET /ai/query_note?file_id=
     */
    public function getAiNotes(string $recordingId): ResponsePayload
    {
        if (empty($recordingId)) {
            throw new PlaudException('Recording ID cannot be empty.');
        }

        return ResponsePayload::fromArray(
            $this->client->get('/ai/query_note', ['file_id' => $recordingId])
        );
    }

    /**
     * Persist analysis results back onto the recording (PATCH /file/{id}).
     *
     * @param  array<string, mixed>  $analysisResult
     */
    public function saveAnalysisResults(string $recordingId, array $analysisResult): ResponsePayload
    {
        if (empty($recordingId)) {
            throw new PlaudException('Recording ID cannot be empty.');
        }

        $rawAi = $analysisResult['data_result_summ'] ?? '';
        $aiContent = is_string($rawAi) ? $rawAi : json_encode($rawAi);
        $aiContentHeader = [];

        if (is_string($rawAi) && str_starts_with(trim($rawAi), '{')) {
            $parsed = json_decode($rawAi, true);
            if (is_array($parsed)) {
                if (isset($parsed['markdown']) && is_string($parsed['markdown'])) {
                    $aiContent = $parsed['markdown'];
                } elseif (isset($parsed['content']['markdown']) && is_string($parsed['content']['markdown'])) {
                    $aiContent = $parsed['content']['markdown'];
                } elseif (isset($parsed['summary']) && is_string($parsed['summary'])) {
                    $aiContent = $parsed['summary'];
                }
                $aiContentHeader = is_array($parsed['header'] ?? null) ? $parsed['header'] : [];
            }
        }

        $data = $this->client->patch("/file/{$recordingId}", [
            'trans_result' => $analysisResult['data_result'] ?? [],
            'ai_content' => $aiContent,
            'outline_result' => $analysisResult['outline_result'] ?? [],
            'support_mul_summ' => true,
            'extra_data' => [
                'task_id_info' => $analysisResult['task_id_info'] ?? [],
                'aiContentHeader' => $aiContentHeader,
            ],
        ]);

        return ResponsePayload::fromArray($data);
    }

    /**
     * GET /speaker/list
     */
    public function getSpeakers(): ResponseSpeakers
    {
        return ResponseSpeakers::fromArray($this->client->get('/speaker/list'));
    }

    /**
     * Unique speaker names in a recording's transcript, with segment counts.
     *
     * @return array<int, array{name: string, segments_count: int}>
     */
    public function getSpeakersForRecording(string $recordingId): array
    {
        $file = $this->requireRecording($recordingId);
        $stats = [];

        foreach ($file->transResult as $segment) {
            $name = trim($segment->speaker);
            if ($name === '') {
                continue;
            }
            $stats[$name] = ($stats[$name] ?? 0) + 1;
        }

        arsort($stats);

        $result = [];
        foreach ($stats as $name => $count) {
            $result[] = ['name' => $name, 'segments_count' => $count];
        }

        return $result;
    }

    /**
     * Rename a speaker label inside a recording transcript and PATCH it back.
     */
    public function renameSpeaker(string $recordingId, string $oldName, string $newName): ResponsePayload
    {
        if (empty($recordingId) || $oldName === '' || $newName === '') {
            throw new PlaudException('Recording ID, old name, and new name cannot be empty.');
        }

        $file = $this->requireRecording($recordingId);
        $renamed = 0;
        $segments = [];

        foreach ($file->transResult as $segment) {
            $payload = $segment->toArray();
            if (trim($segment->speaker) === $oldName) {
                $payload['speaker'] = $newName;
                $renamed++;
            }
            $segments[] = $payload;
        }

        if ($renamed === 0) {
            throw new PlaudException("Speaker '{$oldName}' not found in recording {$recordingId}.");
        }

        return ResponsePayload::fromArray(
            $this->client->patch("/file/{$recordingId}", ['trans_result' => $segments])
        );
    }

    /**
     * GET /device/list
     */
    public function getDevices(): ResponseDevices
    {
        return ResponseDevices::fromArray($this->client->get('/device/list'));
    }

    /**
     * Upload a local audio file to Plaud (presign → PUT → merge → confirm).
     */
    public function uploadRecording(string $filePath, ?string $name = null): DataFileList
    {
        if ($filePath === '' || ! is_file($filePath)) {
            throw new PlaudException("Audio file not found: {$filePath}");
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $fileType = in_array($ext, ['asr', 'opus'], true) ? 'OPUS' : 'MP3';
        $fileSize = filesize($filePath);

        if ($fileSize === false) {
            throw new PlaudException("Unable to stat audio file: {$filePath}");
        }

        $upload = $this->client->post('/file/get_upload_presigned_url', [
            'filesize' => $fileSize,
            'file_type' => $fileType,
        ]);

        $uploadData = is_array($upload['data'] ?? null) ? $upload['data'] : $upload;
        $partUrls = $uploadData['part_urls'] ?? [];
        if (is_string($partUrls) && $partUrls !== '') {
            $partUrls = [$partUrls];
        }
        if ((! is_array($partUrls) || $partUrls === []) && isset($uploadData['url']) && is_string($uploadData['url'])) {
            $partUrls = [$uploadData['url']];
        }

        $uploadId = $uploadData['upload_id'] ?? null;
        $objectName = $uploadData['object_name'] ?? null;

        if (! is_array($partUrls) || $partUrls === [] || ! is_string($uploadId) || ! is_string($objectName)) {
            throw new PlaudException('Upload presign response was missing part_urls/upload_id/object_name.');
        }

        $parts = $this->putUploadParts($filePath, $partUrls);

        $this->client->post('/file/merge_multipart', [
            'upload_id' => $uploadId,
            'object_name' => $objectName,
            'parts' => $parts,
        ]);

        $timestampMs = (int) floor(microtime(true) * 1000);
        $confirmed = $this->client->post('/file/confirm_upload', [
            'upload_id' => $uploadId,
            'object_name' => $objectName,
            'scene' => 101,
            'is_tmp' => 0,
            'support_mul_summ' => true,
            'file_type' => $fileType,
            'filename' => $name ?: ('Meeting '.date('d.m.Y')),
            'start_time' => $timestampMs,
            'session_id' => (int) floor($timestampMs / 1000),
            'serial_number' => bin2hex(random_bytes(16)),
        ]);

        $payload = is_array($confirmed['data'] ?? null) ? $confirmed['data'] : $confirmed;

        return DataFileList::fromArray($payload);
    }

    /**
     * Move recordings to trash
     *
     * @param  array<string>  $recordingIds
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
     * @param  array<string>  $recordingIds
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
     * @param  array<string>  $recordingIds
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

    protected function requireRecording(string $recordingId): DataFileList
    {
        $recordingData = $this->client->post('/file/list', [$recordingId]);
        $recording = ResponseListRecordings::fromArray($recordingData);

        if ($recording->dataFileTotal !== 1 && count($recording->dataFileList) !== 1) {
            throw new PlaudException("Unable to locate recording with provided ID {$recordingId}.");
        }

        return $recording->dataFileList[0];
    }

    protected function summaryIdFromRecording(string $recordingId): ?string
    {
        try {
            return $this->summaryIdFromFile($this->requireRecording($recordingId));
        } catch (\Throwable) {
            return null;
        }
    }

    protected function summaryIdFromFile(DataFileList $file): ?string
    {
        $fromTask = $file->extraData?->taskIdInfo?->summaryId;
        if (is_string($fromTask) && $fromTask !== '') {
            return $fromTask;
        }

        return null;
    }

    /**
     * @return array{0: int, 1: int}
     */
    protected function speakerTimestampFlags(DataFileList $file): array
    {
        $hasSpeaker = 0;
        $hasTimestamp = 0;

        foreach ($file->transResult as $trans) {
            if (! empty($trans->speaker)) {
                $hasSpeaker = 1;
            }
            if ($trans->startTime >= 0) {
                $hasTimestamp = 1;
            }
        }

        return [$hasSpeaker, $hasTimestamp];
    }

    /**
     * Upload each presigned part and return the merge payload.
     *
     * @param  array<int, mixed>  $partUrls
     * @return array<int, array{Etag: string, PartNumber: int}>
     */
    protected function putUploadParts(string $filePath, array $partUrls): array
    {
        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            throw new PlaudException("Unable to read audio file: {$filePath}");
        }

        try {
            $fileSize = filesize($filePath);
            if ($fileSize === false) {
                throw new PlaudException("Unable to stat audio file: {$filePath}");
            }

            $urls = array_values(array_filter($partUrls, fn ($url) => is_string($url) && $url !== ''));
            if ($urls === []) {
                throw new PlaudException('Upload presign response contained no part URLs.');
            }

            $parts = [];
            $remaining = (int) $fileSize;
            $count = count($urls);

            foreach ($urls as $index => $url) {
                $left = $count - $index;
                $chunkSize = $left === 1 ? $remaining : (int) ceil($remaining / $left);
                $chunk = $chunkSize > 0 ? fread($handle, $chunkSize) : '';

                if ($chunk === false) {
                    throw new PlaudException("Unable to read audio file chunk from {$filePath}");
                }

                $put = $this->client->putRaw($url, $chunk, [
                    'Content-Type' => 'application/octet-stream',
                ]);

                $parts[] = [
                    'Etag' => trim((string) $put->header('ETag'), '"'),
                    'PartNumber' => $index + 1,
                ];
                $remaining -= strlen($chunk);
            }

            return $parts;
        } finally {
            fclose($handle);
        }
    }
}
