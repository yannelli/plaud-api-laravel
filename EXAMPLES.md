# Laravel Plaud API - Usage Examples

This file contains practical examples of using the Laravel Plaud package.

## Table of Contents

1. [Basic Setup](#basic-setup)
2. [Authentication](#authentication)
3. [Managing Recordings](#managing-recordings)
4. [Downloading Files](#downloading-files)
5. [Sharing Recordings](#sharing-recordings)
6. [Advanced Examples](#advanced-examples)

## Basic Setup

### Using the Facade

```php
use Yannelli\LaravelPlaud\Facades\Plaud;

// The simplest way to use the package
$recordings = Plaud::getAllRecordings();
```

### Using Dependency Injection

```php
use Yannelli\LaravelPlaud\PlaudService;

class RecordingService
{
    public function __construct(
        protected PlaudService $plaud
    ) {}

    public function getRecordings()
    {
        return $this->plaud->getAllRecordings();
    }
}
```

## Authentication

### Authenticate and Store Token

```php
use Yannelli\LaravelPlaud\Facades\Plaud;
use Illuminate\Support\Facades\Cache;

public function login(Request $request)
{
    $response = Plaud::authenticate(
        $request->input('username'),
        $request->input('password')
    );

    // Store token in cache for later use
    Cache::put('plaud_token', $response->accessToken, now()->addDays(30));

    return response()->json([
        'token' => $response->accessToken,
        'token_type' => $response->tokenType,
    ]);
}
```

### Using a Stored Token

```php
use Yannelli\LaravelPlaud\PlaudClient;
use Yannelli\LaravelPlaud\PlaudService;
use Illuminate\Support\Facades\Cache;

public function getService()
{
    $token = Cache::get('plaud_token');

    $client = new PlaudClient($token);
    $service = new PlaudService($client);

    return $service;
}
```

## Managing Recordings

### List All Recordings with Details

```php
use Yannelli\LaravelPlaud\Facades\Plaud;

public function listRecordings()
{
    $response = Plaud::getAllRecordings();

    $recordings = collect($response->dataFileList)->map(function ($recording) {
        return [
            'id' => $recording->id,
            'filename' => $recording->filename,
            'duration' => $recording->duration,
            'start_time' => \Carbon\Carbon::createFromTimestamp($recording->startTime / 1000),
            'is_transcribed' => $recording->isTrans,
            'has_summary' => $recording->isSummary,
            'is_trash' => $recording->isTrash,
        ];
    });

    return $recordings;
}
```

### Filter Recordings by Date

```php
use Yannelli\LaravelPlaud\Facades\Plaud;

public function getRecentRecordings()
{
    // Get recordings from the last 7 days
    $recordings = Plaud::getRecordingsWithFilter(
        skip: 0,
        limit: 100,
        isTrash: 0, // Exclude trash
        sortBy: 'start_time',
        isDesc: true
    );

    $sevenDaysAgo = now()->subDays(7)->timestamp * 1000;

    $recent = collect($recordings->dataFileList)
        ->filter(fn($r) => $r->startTime >= $sevenDaysAgo);

    return $recent;
}
```

### Bulk Delete Old Recordings

```php
use Yannelli\LaravelPlaud\Facades\Plaud;

public function deleteOldRecordings()
{
    $recordings = Plaud::getAllRecordings();

    $oneYearAgo = now()->subYear()->timestamp * 1000;

    $oldRecordingIds = collect($recordings->dataFileList)
        ->filter(fn($r) => $r->startTime < $oneYearAgo)
        ->pluck('id')
        ->toArray();

    if (!empty($oldRecordingIds)) {
        // Move to trash first
        Plaud::trashRecordings($oldRecordingIds);

        // Then permanently delete
        Plaud::permanentlyDeleteRecordings($oldRecordingIds);

        return count($oldRecordingIds);
    }

    return 0;
}
```

## Downloading Files

### Download and Save Audio File

```php
use Yannelli\LaravelPlaud\Facades\Plaud;
use Illuminate\Support\Facades\Storage;

public function downloadAndSaveAudio(string $recordingId)
{
    // Get recording details first
    $recording = Plaud::getSpecificRecordings([$recordingId]);
    $filename = $recording->dataFileList[0]->filename ?? 'recording';

    // Download audio as base64
    $base64Audio = Plaud::downloadAudioFile($recordingId);

    // Decode and save
    $audioData = base64_decode($base64Audio);
    $path = "recordings/{$recordingId}/{$filename}.mp3";

    Storage::disk('local')->put($path, $audioData);

    return Storage::disk('local')->url($path);
}
```

### Download Multiple Format Transcripts

```php
use Yannelli\LaravelPlaud\Facades\Plaud;
use Yannelli\LaravelPlaud\Constants\FileTypes;
use Illuminate\Support\Facades\Storage;

public function downloadTranscriptAllFormats(string $recordingId)
{
    $formats = [
        FileTypes::PDF,
        FileTypes::DOCX,
        FileTypes::TXT,
        FileTypes::SRT,
        FileTypes::MARKDOWN,
    ];

    $downloads = [];

    foreach ($formats as $format) {
        try {
            $base64Content = Plaud::downloadTranscriptFile($recordingId, $format);
            $content = base64_decode($base64Content);

            $extension = strtolower($format);
            $path = "transcripts/{$recordingId}/transcript.{$extension}";

            Storage::put($path, $content);
            $downloads[$format] = Storage::url($path);

        } catch (\Exception $e) {
            $downloads[$format] = null;
        }
    }

    return $downloads;
}
```

### Stream Audio File to Browser

```php
use Yannelli\LaravelPlaud\Facades\Plaud;

public function streamAudio(string $recordingId)
{
    $base64Audio = Plaud::downloadAudioFile($recordingId);
    $audioData = base64_decode($base64Audio);

    return response($audioData)
        ->header('Content-Type', 'audio/mpeg')
        ->header('Content-Disposition', 'inline; filename="recording.mp3"');
}
```

## Sharing Recordings

### Create Public Share Link

```php
use Yannelli\LaravelPlaud\Facades\Plaud;
use Yannelli\LaravelPlaud\Models\Requests\RequestShareableLinkPermissions;

public function shareRecording(string $recordingId)
{
    $permissions = new RequestShareableLinkPermissions(
        isAudio: 1,      // Share audio
        isTrans: 1,      // Share transcript
        isAiContent: 1,  // Share AI summary
        isMindmap: 0     // Don't share mindmap
    );

    $response = Plaud::createShareableLink($recordingId, $permissions);

    return [
        'url' => $response->url,
        'permissions' => $permissions->toArray(),
    ];
}
```

### Create Audio-Only Share Link

```php
use Yannelli\LaravelPlaud\Facades\Plaud;
use Yannelli\LaravelPlaud\Models\Requests\RequestShareableLinkPermissions;

public function shareAudioOnly(string $recordingId)
{
    $permissions = new RequestShareableLinkPermissions(
        isAudio: 1,      // Only audio
        isTrans: 0,
        isAiContent: 0,
        isMindmap: 0
    );

    return Plaud::createShareableLink($recordingId, $permissions);
}
```

## Advanced Examples

### Create a Recording Dashboard

```php
use Yannelli\LaravelPlaud\Facades\Plaud;

public function getDashboardStats()
{
    $user = Plaud::getMyUser();
    $recordings = Plaud::getAllRecordings();
    $tags = Plaud::getFileTags();

    $stats = [
        'user' => [
            'email' => $user->dataUser->email,
            'nickname' => $user->dataUser->nickname,
            'seconds_left' => $user->dataUser->secondsLeft,
            'seconds_total' => $user->dataUser->secondsTotal,
        ],
        'recordings' => [
            'total' => $recordings->dataFileTotal,
            'transcribed' => collect($recordings->dataFileList)
                ->where('isTrans', true)->count(),
            'summarized' => collect($recordings->dataFileList)
                ->where('isSummary', true)->count(),
            'in_trash' => collect($recordings->dataFileList)
                ->where('isTrash', true)->count(),
        ],
        'tags' => $tags->dataFiletagTotal,
        'storage' => [
            'used_mb' => collect($recordings->dataFileList)
                ->sum('filesize') / 1024 / 1024,
        ],
    ];

    return $stats;
}
```

### Batch Process Recordings

```php
use Yannelli\LaravelPlaud\Facades\Plaud;
use Illuminate\Support\Facades\Storage;

public function batchDownloadRecordings(array $recordingIds)
{
    $results = [];

    foreach ($recordingIds as $recordingId) {
        try {
            // Get recording info
            $recording = Plaud::getSpecificRecordings([$recordingId]);
            $info = $recording->dataFileList[0];

            // Download audio
            $audioBase64 = Plaud::downloadAudioFile($recordingId);
            Storage::put("batch/{$recordingId}/audio.mp3", base64_decode($audioBase64));

            // Download transcript if available
            if ($info->isTrans) {
                $transcriptBase64 = Plaud::downloadTranscriptFile($recordingId, 'PDF');
                Storage::put("batch/{$recordingId}/transcript.pdf", base64_decode($transcriptBase64));
            }

            // Download summary if available
            if ($info->isSummary) {
                $summaryBase64 = Plaud::downloadSummaryFile($recordingId, 'DOCX');
                Storage::put("batch/{$recordingId}/summary.docx", base64_decode($summaryBase64));
            }

            $results[$recordingId] = 'success';

        } catch (\Exception $e) {
            $results[$recordingId] = 'failed: ' . $e->getMessage();
        }
    }

    return $results;
}
```

### Search Recordings by Keywords

```php
use Yannelli\LaravelPlaud\Facades\Plaud;

public function searchRecordings(string $keyword)
{
    $recordings = Plaud::getAllRecordings();

    $results = collect($recordings->dataFileList)
        ->filter(function ($recording) use ($keyword) {
            // Search in filename
            if (stripos($recording->filename, $keyword) !== false) {
                return true;
            }

            // Search in keywords
            if (in_array($keyword, $recording->keywords ?? [])) {
                return true;
            }

            // Search in AI content
            if (stripos($recording->aiContent ?? '', $keyword) !== false) {
                return true;
            }

            return false;
        });

    return $results->values();
}
```

### Export Recording with Metadata

```php
use Yannelli\LaravelPlaud\Facades\Plaud;
use Illuminate\Support\Facades\Storage;

public function exportRecordingWithMetadata(string $recordingId)
{
    $recording = Plaud::getSpecificRecordings([$recordingId]);
    $data = $recording->dataFileList[0];

    // Create metadata JSON
    $metadata = [
        'id' => $data->id,
        'filename' => $data->filename,
        'duration' => $data->duration,
        'start_time' => \Carbon\Carbon::createFromTimestampMs($data->startTime)->toIso8601String(),
        'end_time' => \Carbon\Carbon::createFromTimestampMs($data->endTime)->toIso8601String(),
        'keywords' => $data->keywords,
        'is_transcribed' => $data->isTrans,
        'is_summarized' => $data->isSummary,
        'transcript' => array_map(fn($t) => [
            'speaker' => $t->speaker,
            'content' => $t->content,
            'start_time' => $t->startTime,
            'end_time' => $t->endTime,
        ], $data->transResult),
        'ai_summary' => $data->aiContent,
    ];

    // Save metadata
    Storage::put("exports/{$recordingId}/metadata.json", json_encode($metadata, JSON_PRETTY_PRINT));

    // Download files
    $audioBase64 = Plaud::downloadAudioFile($recordingId);
    Storage::put("exports/{$recordingId}/audio.mp3", base64_decode($audioBase64));

    return [
        'success' => true,
        'path' => "exports/{$recordingId}",
        'metadata' => $metadata,
    ];
}
```

## Error Handling

### Comprehensive Error Handling

```php
use Yannelli\LaravelPlaud\Facades\Plaud;
use Yannelli\LaravelPlaud\Exceptions\PlaudException;

public function safeDownload(string $recordingId)
{
    try {
        $audio = Plaud::downloadAudioFile($recordingId);

        return [
            'success' => true,
            'data' => $audio,
        ];

    } catch (PlaudException $e) {
        logger()->error('Plaud API Error', [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'recording_id' => $recordingId,
        ]);

        return [
            'success' => false,
            'error' => $e->getMessage(),
            'http_code' => $e->getCode(),
        ];
    }
}
```
