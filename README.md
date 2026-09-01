[![Tests](https://github.com/yannelli/plaud-api-laravel/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/yannelli/plaud-api-laravel/actions/workflows/tests.yml)

# Laravel Plaud API Package

An **unofficial** Laravel package for the Plaud API. This package provides a clean, Laravel-idiomatic interface for managing recordings, transcriptions, and summaries from the Plaud platform.

Plaud does not publish a general-purpose public API for consumer accounts. This library talks to the same `api.plaud.ai` (and regional) hosts the web app uses. Those endpoints change without notice.

## Features

- Email/password login, email OTP login, and pasted bearer tokens
- User token (UT) vs workspace token (WT) helpers, including workspace minting
- Regional API hosts (US/global, EU, APAC) with automatic `-302` redirects
- List, filter, upload, and manage recordings
- Download audio, transcripts, and summaries
- Start / poll / save cloud transcription (`/ai/transsumm`, `PATCH /file/{id}`)
- Speakers, devices, workspaces, file tags, and share links
- Laravel HTTP client integration with browser-like headers and 429 retries
- Facade support, deferred service provider, type-safe models
- PHP 8.2–8.5 and Laravel 12 / 13

## Requirements

- PHP 8.2 or higher (Laravel 13 requires PHP 8.3+)
- Laravel 12.x or 13.x
- Guzzle HTTP client 7.9+ or 8.x

## Installation

Install the package via Composer:

```bash
composer require yannelli/laravel-plaud
```

### Publish Configuration (Optional)

You can publish the configuration file if you want to customize it:

```bash
php artisan vendor:publish --tag=plaud-config
```

This will create a `config/plaud.php` file in your Laravel application.

### Environment Configuration

```env
# Bearer used for API calls. Prefer a long-lived user token (see Auth below).
PLAUD_ACCESS_TOKEN=your-access-token-here

# Optional: regional host or short name (us, eu, apse1, apac)
# PLAUD_BASE_URL=https://api.plaud.ai

# Optional: long-lived user token (UT) and v3 refresh cookie (pld_urt)
# PLAUD_USER_TOKEN=
# PLAUD_REFRESH_TOKEN=

# Optional: mint a workspace token (WT) at boot from the user token
# PLAUD_WORKSPACE_ID=
# PLAUD_DEVICE_ID=
```

## Usage

### Authentication

Plaud currently issues **two** kinds of JWT, and newer web sessions may not put a token in the JSON body at all.

| Credential | Where it shows up | Lifetime | Use |
|------------|-------------------|----------|-----|
| User token (UT) | `localStorage.pld_tokenstr`, cookie `pld_ut`, OTP/password login | days to ~months | Identity, minting WTs. **Store this.** |
| Workspace token (WT) | `Authorization` on `/file/simple/web` and `/device/list` | ~24h | Data endpoints. Claims include `ut_ref` / `wid`. **Do not paste this as your long-lived secret** — it cannot mint a replacement. |
| Refresh cookie `pld_urt` | Set-Cookie on v3 login | ~30 days | `POST /auth/refresh-user-token` only |

`Plaud::isUsingWorkspaceToken()` is true when the current Bearer has WT claims.

#### Email + password

Still works for accounts that have a Plaud password (SSO-only accounts must set one first, or use OTP / a pasted UT).

```php
use Yannelli\LaravelPlaud\Facades\Plaud;

$authResponse = Plaud::authenticate('your-username', 'your-password');
$accessToken = $authResponse->accessToken;
```

Some v3 logins return an empty `access_token` and put the session in `pld_ut` / `pld_urt` cookies. The client captures those cookies and will refresh on 401 when `PLAUD_REFRESH_TOKEN` (or a captured `pld_urt`) is present.

Password login is **not** ECIES-encrypted in this package. If Plaud starts rejecting plaintext `/auth/access-token` for your account, use OTP or paste a UT from the web app.

#### Email OTP (recommended for SSO)

```php
$otp = Plaud::sendOtpCode('you@example.com');
// Plaud emails a one-time code. Keep $otp->token.
$auth = Plaud::otpLogin($codeFromEmail, $otp->token);
```

OTP send follows regional `-302` redirects (`data.domains.api`).

#### Pasted token from web.plaud.ai

1. Sign in at [web.plaud.ai](https://web.plaud.ai)
2. Copy `pld_tokenstr` from localStorage (UT), **not** the Bearer on `/file/simple/web` (usually a WT)
3. Set `PLAUD_ACCESS_TOKEN` / `PLAUD_USER_TOKEN`

Then mint a WT if data calls require one:

```php
$workspaces = Plaud::getWorkspaces();
Plaud::useWorkspace($workspaces->workspaces[0]->id);
```

### Regional hosts

Accounts are sharded. The client defaults to `https://api.plaud.ai`, maps a JWT `region` claim when the host is still the discovery URL, and retries once when the body is `status: -302` with `data.domains.api`.

```php
Plaud::setRegion('eu');          // https://api-euc1.plaud.ai
Plaud::setRegion('apse1');       // https://api-apse1.plaud.ai
Plaud::setBaseUrl('https://api-euc1.plaud.ai');
```

Known hosts include `api.plaud.ai` (global/US), `api-euc1.plaud.ai` (EU), and `api-apse1.plaud.ai` (APAC), plus other `api-*.plaud.ai` names derived from AWS region claims.

### Using Dependency Injection

You can also use dependency injection instead of the facade:

```php
use Yannelli\LaravelPlaud\PlaudService;

class RecordingController extends Controller
{
    public function __construct(
        protected PlaudService $plaud
    ) {}

    public function index()
    {
        $recordings = $this->plaud->getAllRecordings();
        return view('recordings.index', compact('recordings'));
    }
}
```

### Get User Information

```php
use Yannelli\LaravelPlaud\Facades\Plaud;

$user = Plaud::getMyUser();

echo $user->dataUser->email;
echo $user->dataUser->nickname;
```

### Get System Status

```php
$status = Plaud::getStatus();

// Check processing status
if (!empty($status->dataProcessingTranssummAi->filesTrans)) {
    echo "Files are being transcribed...";
}
```

### Retrieve Recordings

#### Get All Recordings

```php
$recordings = Plaud::getAllRecordings();

foreach ($recordings->dataFileList as $recording) {
    echo $recording->filename;
    echo $recording->duration;
    echo $recording->startTime;
}
```

#### Get Recordings with Filters

```php
$recordings = Plaud::getRecordingsWithFilter(
    skip: 0,
    limit: 50,
    isTrash: 0,  // 0 = not in trash, 1 = in trash, 2 = all
    sortBy: 'start_time',
    isDesc: true,
    filetagId: 'optional-folder-id',
);

$one = Plaud::getRecording('recording-id'); // GET /file/detail/{id}
```

#### Get Specific Recordings by ID

```php
$recordingIds = ['recording-id-1', 'recording-id-2'];
$recordings = Plaud::getSpecificRecordings($recordingIds);
```

### File Tags (Folders)

```php
$tags = Plaud::getFileTags();

foreach ($tags->dataFiletagList as $tag) {
    echo $tag->name;
    echo $tag->color;
}

$folder = Plaud::createFileTag('Meetings', color: '#4F46E5');
Plaud::setRecordingTags('recording-id', [$folder->id]);
$inFolder = Plaud::getRecordingsByTag($folder->id);
Plaud::deleteFileTag($folder->id);
```

### Create Shareable Links

```php
use Yannelli\LaravelPlaud\Models\Requests\RequestShareableLinkPermissions;

$permissions = new RequestShareableLinkPermissions(
    isAudio: 1,      // Allow audio sharing
    isTrans: 1,      // Allow transcript sharing
    isAiContent: 1,  // Allow AI content sharing
    isMindmap: 0     // Disable mindmap sharing
);

$shareableLink = Plaud::createShareableLink('recording-id', $permissions);

echo $shareableLink->url; // The shareable URL
```

### Download Files

#### Download Audio File

```php
use Yannelli\LaravelPlaud\Facades\Plaud;
use Illuminate\Support\Facades\Storage;

$recordingId = 'your-recording-id';
$base64Audio = Plaud::downloadAudioFile($recordingId);

// Decode and save to storage
$audioData = base64_decode($base64Audio);
Storage::put('recordings/audio.mp3', $audioData);
```

#### Download Transcript

```php
use Yannelli\LaravelPlaud\Constants\FileTypes;

$recordingId = 'your-recording-id';

// Download as PDF
$base64Transcript = Plaud::downloadTranscriptFile($recordingId, FileTypes::PDF);

// Or download as TXT, DOCX, SRT, or Markdown
$base64Transcript = Plaud::downloadTranscriptFile($recordingId, FileTypes::TXT);

// Save to storage
$transcriptData = base64_decode($base64Transcript);
Storage::put('recordings/transcript.pdf', $transcriptData);
```

#### Download Summary

```php
use Yannelli\LaravelPlaud\Constants\FileTypes;

$recordingId = 'your-recording-id';
$base64Summary = Plaud::downloadSummaryFile($recordingId, FileTypes::DOCX);

$summaryData = base64_decode($base64Summary);
Storage::put('recordings/summary.docx', $summaryData);
```

### Manage Recordings

#### Move to Trash

```php
$recordingIds = ['recording-id-1', 'recording-id-2'];
$success = Plaud::trashRecordings($recordingIds);

if ($success) {
    echo "Recordings moved to trash successfully";
}
```

#### Restore from Trash

```php
$recordingIds = ['recording-id-1', 'recording-id-2'];
$success = Plaud::untrashRecordings($recordingIds);
```

#### Permanently Delete

```php
$recordingIds = ['recording-id-1', 'recording-id-2'];
$success = Plaud::permanentlyDeleteRecordings($recordingIds);

if ($success) {
    echo "Recordings permanently deleted";
}
```

#### Upload an audio file

```php
$uploaded = Plaud::uploadRecording('/path/to/meeting.mp3', 'Standup');
Plaud::renameRecording($uploaded->id, 'Standup (edited)');
```

### Transcription and AI

```php
Plaud::startAnalysis($id, language: 'en');
$status = Plaud::getTranssumm($id, language: 'en');
$notes = Plaud::getAiNotes($id);
$task = Plaud::getFileTaskStatus($id);

// After analysis completes, persist results (required by the web-app flow)
Plaud::saveAnalysisResults($id, $status->raw);
```

### Speakers, devices, workspaces

```php
$speakers = Plaud::getSpeakers();
$inFile = Plaud::getSpeakersForRecording($id);
Plaud::renameSpeaker($id, 'Speaker 1', 'Alice');

$devices = Plaud::getDevices();
$workspaces = Plaud::getWorkspaces();
```

## Available File Types

The package provides a `FileTypes` constant class with the following supported formats:

```php
use Yannelli\LaravelPlaud\Constants\FileTypes;

FileTypes::MP3      // Audio format
FileTypes::WAV      // Audio format
FileTypes::TXT      // Plain text
FileTypes::PDF      // PDF document
FileTypes::DOCX     // Microsoft Word
FileTypes::SRT      // Subtitle format
FileTypes::MARKDOWN // Markdown format
```

## Error Handling

The package throws `PlaudException` for API errors:

```php
use Yannelli\LaravelPlaud\Facades\Plaud;
use Yannelli\LaravelPlaud\Exceptions\PlaudException;

try {
    $recordings = Plaud::getAllRecordings();
} catch (PlaudException $e) {
    // Handle API errors
    logger()->error('Plaud API Error: ' . $e->getMessage());

    // Get HTTP status code if available
    $statusCode = $e->getCode();
}
```

## Advanced Usage

### Using the Low-Level Client

If you need more control, you can use the `PlaudClient` directly:

```php
use Yannelli\LaravelPlaud\PlaudClient;

$client = new PlaudClient();
$client->authenticate('username', 'password');

// Make custom API requests
$response = $client->get('/custom/endpoint');
$response = $client->post('/custom/endpoint', ['data' => 'value']);
```

### Accessing the Client from the Service

```php
use Yannelli\LaravelPlaud\Facades\Plaud;

$client = Plaud::getClient();
$accessToken = Plaud::getAccessToken();
```

## Available Methods

### PlaudService Methods

| Method | Description |
|--------|-------------|
| `authenticate($username, $password)` | Email + password login (`POST /auth/access-token`) |
| `sendOtpCode($email)` | Email a one-time code (`POST /auth/otp-send-code`) |
| `otpLogin($code, $otpToken)` | Finish OTP login (`POST /auth/otp-login`) |
| `refreshUserToken()` | Refresh a v3 cookie session |
| `getWorkspaces()` | List workspaces (`GET /team-app/workspaces/list`) |
| `useWorkspace($id)` | Mint a WT (`POST /user-app/auth/workspace/token/{id}`) |
| `isUsingWorkspaceToken()` | Whether the current Bearer is a WT |
| `getMyUser()` | Current user (`GET /user/me`) |
| `getStatus()` | Processing status (`GET /ai/status`) |
| `getAllRecordings()` | List recordings (`GET /file/simple/web`) |
| `getRecordingsWithFilter(...)` | List with skip/limit/trash/sort/tag filters |
| `getSpecificRecordings($ids)` | Batch detail (`POST /file/list` with a JSON array of IDs) |
| `getRecording($id)` | Single detail (`GET /file/detail/{id}`, falls back to `POST /file/list`) |
| `updateRecording($id, $attributes)` | Patch metadata (`PATCH /file/{id}`) |
| `renameRecording($id, $filename)` | Rename a recording |
| `setRecordingTags($id, $tagIds)` | Replace `filetag_id_list` on a recording |
| `getFileTags()` | Folders (`GET /filetag/`) |
| `createFileTag($name, $color, $icon)` | Create a folder (`POST /filetag/`) |
| `deleteFileTag($id)` | Delete a folder (`DELETE /filetag/{id}`) |
| `getRecordingsByTag($id)` | List recordings in a folder |
| `createShareableLink($id, $permissions)` | Create share URL |
| `getPrivateShare($id)` / `getPublicShare($id)` | Existing share payloads |
| `getAudioTempUrl($id)` | Presigned audio URL (`GET /file/temp-url/{id}`) |
| `downloadAudioFile($id)` | Audio as base64 via temp URL |
| `downloadAudioDirect($id)` | Audio as base64 via `GET /file/download/{id}` |
| `downloadTranscriptFile($id, $type)` | Export transcript as base64 |
| `downloadSummaryFile($id, $type)` | Export summary as base64 |
| `startAnalysis($id, $language)` | `PATCH /file/{id}` with `tranConfig` |
| `getTranssumm($id, ...)` | `POST /ai/transsumm/{id}` |
| `getFileTaskStatus($id)` | `GET /ai/file-task-status` |
| `getAiNotes($id)` | `GET /ai/query_note` |
| `saveAnalysisResults($id, $result)` | Persist transsumm output |
| `getSpeakers()` | `GET /speaker/list` |
| `getSpeakersForRecording($id)` | Unique names in a transcript |
| `renameSpeaker($id, $old, $new)` | Patch `trans_result` speaker labels |
| `getDevices()` | `GET /device/list` |
| `uploadRecording($path, $name)` | Presign → S3 PUT (all parts) → merge → confirm |
| `trashRecordings($ids)` | Move recordings to trash |
| `untrashRecordings($ids)` | Restore recordings from trash |
| `permanentlyDeleteRecordings($ids)` | Permanently delete recordings |
| `setAccessToken` / `setRegion` / `setBaseUrl` | Client configuration |

## Unofficial API coverage

Cross-checked in September 2026 against recent unofficial clients (including [arbuzmell/plaud-api](https://github.com/arbuzmell/plaud-api), [sergivalverde/plaud-toolkit](https://github.com/sergivalverde/plaud-toolkit), [giovi321/plaud-unofficial-api](https://github.com/giovi321/plaud-unofficial-api), [jaisonerick/plaud-cli](https://github.com/jaisonerick/plaud-cli), [leonardsellem/n8n-nodes-plaud-unofficial](https://github.com/leonardsellem/n8n-nodes-plaud-unofficial), [leonardsellem/plaud-sync-for-obsidian](https://github.com/leonardsellem/plaud-sync-for-obsidian), and [riffado/riffado](https://github.com/riffado/riffado)).

**Implemented here**

- Auth: `/auth/access-token`, `/auth/otp-send-code`, `/auth/otp-login`, `/auth/refresh-user-token`
- Workspaces: `/team-app/workspaces/list`, `/user-app/auth/workspace/token/{id}`
- Files: `/file/simple/web`, `/file/list`, `/file/detail/{id}`, `PATCH /file/{id}`, `/file/temp-url/{id}`, `/file/download/{id}`, trash/untrash/delete, document export, share URL
- Upload: `/file/get_upload_presigned_url`, `/file/merge_multipart`, `/file/confirm_upload` (every presigned part is PUT, not only the first)
- Tags: `GET/POST /filetag/`, `DELETE /filetag/{id}`, assign tags with `PATCH /file/{id}` `filetag_id_list`
- AI: `/ai/status`, `/ai/transsumm/{id}`, `/ai/file-task-status`, `/ai/query_note`
- Other: `/user/me`, `/speaker/list`, `/device/list`, `/share/private/get`, `/share/public/get`
- Client behaviour: regional `-302` follow, JWT `region` routing, browser `User-Agent`, 429 retry, v3 `pld_ut`/`pld_urt` cookies, leftover negative API `status` treated as an error

**Still different from some other clients (not implemented)**

- ECIES-encrypted password login (`GET /config/security` + secp256k1 seal used by [plaud-cli](https://github.com/jaisonerick/plaud-cli))
- WebSocket device streams
- `/speaker/sync` (present as a constant in arbuzmell/plaud-api; request body is not documented in recent clients)
- Plaud's **official** MCP/CLI at [docs.plaud.ai](https://docs.plaud.ai) (`platform.plaud.ai/developer/api`) — a separate product from this unofficial web API

Live traffic from a Plaud web session is still the best way to confirm an account's exact host and token scheme. This refresh did **not** replay a live web.plaud.ai session (no account credentials were available). Web-app access can be used to validate anything that still 401s or 403s.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Security

If you discover any security-related issues, please email the package maintainer instead of using the issue tracker.

## Credits

- Original .NET library: [JamesStuder/Plaud_API](https://github.com/JamesStuder/Plaud_API)
- Laravel package maintainer: [Ryan Yannelli](https://ryanyannelli.com)
- Recent unofficial clients used to map endpoint drift (2026): [arbuzmell/plaud-api](https://github.com/arbuzmell/plaud-api), [sergivalverde/plaud-toolkit](https://github.com/sergivalverde/plaud-toolkit), [giovi321/plaud-unofficial-api](https://github.com/giovi321/plaud-unofficial-api), [jaisonerick/plaud-cli](https://github.com/jaisonerick/plaud-cli), [leonardsellem/n8n-nodes-plaud-unofficial](https://github.com/leonardsellem/n8n-nodes-plaud-unofficial), [leonardsellem/plaud-sync-for-obsidian](https://github.com/leonardsellem/plaud-sync-for-obsidian), [riffado/riffado](https://github.com/riffado/riffado)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Disclaimer

This is an **unofficial** package and is not affiliated with, maintained, or endorsed by Plaud. Use at your own risk.
