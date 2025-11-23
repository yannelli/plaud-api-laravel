<?php

namespace Yannelli\LaravelPlaud\Facades;

use Illuminate\Support\Facades\Facade;
use Yannelli\LaravelPlaud\Models\Requests\RequestShareableLinkPermissions;
use Yannelli\LaravelPlaud\Models\Responses\ResponseAuth;
use Yannelli\LaravelPlaud\Models\Responses\ResponseFileTags;
use Yannelli\LaravelPlaud\Models\Responses\ResponseListRecordings;
use Yannelli\LaravelPlaud\Models\Responses\ResponseShareableLink;
use Yannelli\LaravelPlaud\Models\Responses\ResponseStatus;
use Yannelli\LaravelPlaud\Models\Responses\ResponseUser;
use Yannelli\LaravelPlaud\PlaudClient;

/**
 * @method static ResponseAuth authenticate(string $username, string $password)
 * @method static ResponseUser getMyUser()
 * @method static ResponseStatus getStatus()
 * @method static ResponseListRecordings getAllRecordings()
 * @method static ResponseListRecordings getRecordingsWithFilter(int $skip = 0, int $limit = 99999, int $isTrash = 2, string $sortBy = 'start_time', bool $isDesc = true)
 * @method static ResponseListRecordings getSpecificRecordings(array $recordingIds)
 * @method static ResponseFileTags getFileTags()
 * @method static ResponseShareableLink createShareableLink(string $recordingId, RequestShareableLinkPermissions $permissions)
 * @method static string downloadAudioFile(string $recordingId)
 * @method static string downloadTranscriptFile(string $recordingId, string $fileType)
 * @method static string downloadSummaryFile(string $recordingId, string $fileType)
 * @method static bool trashRecordings(array $recordingIds)
 * @method static bool untrashRecordings(array $recordingIds)
 * @method static bool permanentlyDeleteRecordings(array $recordingIds)
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
