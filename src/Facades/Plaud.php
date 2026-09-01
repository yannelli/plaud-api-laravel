<?php

namespace Yannelli\LaravelPlaud\Facades;

use Illuminate\Support\Facades\Facade;
use Yannelli\LaravelPlaud\Models\DataFileList;
use Yannelli\LaravelPlaud\Models\DataFiletagList;
use Yannelli\LaravelPlaud\Models\Requests\RequestShareableLinkPermissions;
use Yannelli\LaravelPlaud\Models\Responses\ResponseAuth;
use Yannelli\LaravelPlaud\Models\Responses\ResponseAudioTempUrl;
use Yannelli\LaravelPlaud\Models\Responses\ResponseDevices;
use Yannelli\LaravelPlaud\Models\Responses\ResponseFileTags;
use Yannelli\LaravelPlaud\Models\Responses\ResponseListRecordings;
use Yannelli\LaravelPlaud\Models\Responses\ResponseOtp;
use Yannelli\LaravelPlaud\Models\Responses\ResponsePayload;
use Yannelli\LaravelPlaud\Models\Responses\ResponseShareableLink;
use Yannelli\LaravelPlaud\Models\Responses\ResponseSpeakers;
use Yannelli\LaravelPlaud\Models\Responses\ResponseStatus;
use Yannelli\LaravelPlaud\Models\Responses\ResponseUser;
use Yannelli\LaravelPlaud\Models\Responses\ResponseWorkspaces;
use Yannelli\LaravelPlaud\PlaudClient;

/**
 * @method static ResponseAuth authenticate(string $username, string $password)
 * @method static ResponseOtp sendOtpCode(string $email)
 * @method static ResponseAuth otpLogin(string $code, string $otpToken)
 * @method static ResponseAuth refreshUserToken()
 * @method static ResponseWorkspaces getWorkspaces()
 * @method static string useWorkspace(string $workspaceId)
 * @method static bool isUsingWorkspaceToken()
 * @method static ResponseUser getMyUser()
 * @method static ResponseStatus getStatus()
 * @method static ResponseListRecordings getAllRecordings()
 * @method static ResponseListRecordings getRecordingsWithFilter(int $skip = 0, int $limit = 99999, int $isTrash = 2, string $sortBy = 'start_time', bool $isDesc = true, ?string $filetagId = null)
 * @method static ResponseListRecordings getSpecificRecordings(array $recordingIds)
 * @method static DataFileList getRecording(string $recordingId)
 * @method static ResponsePayload updateRecording(string $recordingId, array $attributes)
 * @method static ResponsePayload renameRecording(string $recordingId, string $filename)
 * @method static ResponsePayload setRecordingTags(string $recordingId, array $tagIds)
 * @method static ResponseFileTags getFileTags()
 * @method static DataFiletagList createFileTag(string $name, string $color = '', string $icon = '')
 * @method static bool deleteFileTag(string $tagId)
 * @method static ResponseListRecordings getRecordingsByTag(string $tagId, int $skip = 0, int $limit = 99999)
 * @method static ResponseShareableLink createShareableLink(string $recordingId, RequestShareableLinkPermissions $permissions)
 * @method static ResponsePayload getPrivateShare(string $recordingId)
 * @method static ResponsePayload getPublicShare(string $recordingId)
 * @method static ResponseAudioTempUrl getAudioTempUrl(string $recordingId, bool $opus = false)
 * @method static string downloadAudioFile(string $recordingId)
 * @method static string downloadAudioDirect(string $recordingId)
 * @method static string downloadTranscriptFile(string $recordingId, string $fileType)
 * @method static string downloadSummaryFile(string $recordingId, string $fileType)
 * @method static ResponsePayload startAnalysis(string $recordingId, string $language = 'en', string $type = 'REASONING-NOTE')
 * @method static ResponsePayload getTranssumm(string $recordingId, string $language = 'en', string $summType = 'REASONING-NOTE', bool $reload = false)
 * @method static ResponsePayload getFileTaskStatus(string $recordingId)
 * @method static ResponsePayload getAiNotes(string $recordingId)
 * @method static ResponsePayload saveAnalysisResults(string $recordingId, array $analysisResult)
 * @method static ResponseSpeakers getSpeakers()
 * @method static array getSpeakersForRecording(string $recordingId)
 * @method static ResponsePayload renameSpeaker(string $recordingId, string $oldName, string $newName)
 * @method static ResponseDevices getDevices()
 * @method static DataFileList uploadRecording(string $filePath, ?string $name = null)
 * @method static bool trashRecordings(array $recordingIds)
 * @method static bool untrashRecordings(array $recordingIds)
 * @method static bool permanentlyDeleteRecordings(array $recordingIds)
 * @method static self setAccessToken(string $accessToken)
 * @method static self setBaseUrl(string $baseUrl)
 * @method static self setRegion(string $region)
 * @method static string getBaseUrl()
 * @method static PlaudClient getClient()
 * @method static string|null getAccessToken()
 *
 * @see \Yannelli\LaravelPlaud\PlaudService
 */
class Plaud extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'plaud';
    }
}
