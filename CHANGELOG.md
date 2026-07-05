# Changelog

All notable changes to `laravel-plaud` will be documented in this file.

## Unreleased

### Added
- Support for Laravel 13.x (alongside the existing Laravel 12.x support)
- CI test matrix across PHP 8.3/8.4 and Laravel 12/13

### Changed
- Widened `illuminate/support` and `illuminate/http` constraints to `^12.0|^13.0`
- Widened dev dependencies: `orchestra/testbench` to `^10.0|^11.0`, `pestphp/pest` and `pestphp/pest-plugin-laravel` to `^3.0|^4.0`

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
