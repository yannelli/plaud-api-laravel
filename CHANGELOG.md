# Changelog

All notable changes to `laravel-plaud` will be documented in this file.

## Unreleased

### Added
- PHP 8.2 and 8.5 support alongside 8.3/8.4 (Laravel 13 still requires PHP 8.3+)
- CI matrix: PHP 8.2–8.5 × Laravel 12/13 (PHP 8.2 + Laravel 13 excluded)
- Regional API hosts with JWT `region` routing and `-302` `data.domains.api` follow
- Email OTP login (`/auth/otp-send-code`, `/auth/otp-login`)
- v3 cookie session capture (`pld_ut` / `pld_urt`) and `/auth/refresh-user-token`
- Workspace list + workspace-token mint (UT vs WT)
- File detail (`GET /file/detail/{id}`), direct audio download, temp URL helper
- Cloud analysis: start (`PATCH /file/{id}`), poll (`POST /ai/transsumm/{id}`), notes, task status, save results
- Speakers list / per-recording stats / rename
- Devices list, private/public share payloads
- Recording upload (presign → PUT every part → merge → confirm)
- File tag create/delete and assigning tags on a recording
- Filter recordings by `filetag_id`
- Browser-like User-Agent / Origin headers and 429 retries
- Config keys: `base_url`, `user_token`, `refresh_token`, `workspace_id`, `device_id`

### Fixed
- Export requests now send `summary_id` from `extra_data.task_id_info` (required by newer Plaud export)
- `trans_result` objects that are not a list of segments no longer blow up model hydration
- `ai_content` objects are JSON-encoded instead of violating the string property
- File detail aliases `file_id` / `file_name` map onto `DataFileList`
- Empty `access_token` nested under `data` is read; v3 cookie sessions are not treated as a hard login failure when cookies are present
- Audio temp-url parsers accept `url` / nested `data.url` in addition to `temp_url`
- `setBaseUrl()` resolves short region names (`eu`, `apse1`, …) the same way the constructor does
- Leftover negative JSON `status` values (other than a followed `-302`) are treated as API errors
- Permanent delete sends a JSON array body on `DELETE /file/`

### Changed
- PHP constraint `^8.3` → `^8.2`
- Guzzle constraint `^7.8` → `^7.9|^8.0`
- Data requests send web-app headers; login uses a minimal header set so `/auth/access-token` does not return an empty token

## v0.1.0 - 2024-11-22

### Added
- Initial release
- Full Plaud API support for Laravel 12
- Authentication with username/password
- Retrieve and filter recordings
- Download audio files, transcripts, and summaries
- Create shareable links with custom permissions
- Manage recordings (trash, restore, permanently delete)
- Get user information and system status
- File tag management
- Laravel HTTP client integration
- Facade support for easy access
- Comprehensive type-safe models
- Error handling with PlaudException
