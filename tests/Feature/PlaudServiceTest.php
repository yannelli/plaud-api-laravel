<?php

use Illuminate\Support\Facades\Http;
use Yannelli\LaravelPlaud\PlaudService;
use Yannelli\LaravelPlaud\PlaudClient;
use Yannelli\LaravelPlaud\Exceptions\PlaudException;
use Yannelli\LaravelPlaud\Models\Responses\ResponseAuth;
use Yannelli\LaravelPlaud\Models\Responses\ResponseUser;
use Yannelli\LaravelPlaud\Models\Responses\ResponseStatus;
use Yannelli\LaravelPlaud\Models\Responses\ResponseListRecordings;
use Yannelli\LaravelPlaud\Models\Responses\ResponseFileTags;
use Yannelli\LaravelPlaud\Models\Responses\ResponseShareableLink;
use Yannelli\LaravelPlaud\Models\Requests\RequestShareableLinkPermissions;
use Yannelli\LaravelPlaud\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Http::preventStrayRequests();
});

describe('PlaudService', function () {

    describe('constructor', function () {
        it('creates its own client if none provided', function () {
            $service = new PlaudService();

            expect($service->getClient())->toBeInstanceOf(PlaudClient::class);
        });

        it('uses provided client', function () {
            $client = new PlaudClient('provided-token');
            $service = new PlaudService($client);

            expect($service->getClient())->toBe($client)
                ->and($service->getAccessToken())->toBe('provided-token');
        });
    });

    describe('getClient and getAccessToken', function () {
        it('returns the underlying client', function () {
            $client = new PlaudClient('test-token');
            $service = new PlaudService($client);

            expect($service->getClient())->toBeInstanceOf(PlaudClient::class)
                ->and($service->getAccessToken())->toBe('test-token');
        });
    });

    describe('authenticate', function () {
        it('authenticates and returns ResponseAuth', function () {
            Http::fake([
                'api.plaud.ai/auth/access-token' => Http::response([
                    'status' => 200,
                    'msg' => 'success',
                    'access_token' => 'new-token',
                    'token_type' => 'Bearer',
                    'login_count_per_hour' => 1,
                    'login_total_per_hour' => 10,
                ], 200),
            ]);

            $service = new PlaudService();
            $response = $service->authenticate('user@example.com', 'password');

            expect($response)->toBeInstanceOf(ResponseAuth::class)
                ->and($response->accessToken)->toBe('new-token')
                ->and($response->status)->toBe(200);
        });

        it('throws exception for empty username', function () {
            $service = new PlaudService();

            expect(fn() => $service->authenticate('', 'password'))
                ->toThrow(PlaudException::class, 'Username or password cannot be empty.');
        });

        it('throws exception for empty password', function () {
            $service = new PlaudService();

            expect(fn() => $service->authenticate('user@example.com', ''))
                ->toThrow(PlaudException::class, 'Username or password cannot be empty.');
        });

        it('throws exception when no access token returned', function () {
            Http::fake([
                'api.plaud.ai/auth/access-token' => Http::response([
                    'status' => 200,
                    'msg' => 'success',
                ], 200),
            ]);

            $service = new PlaudService();

            expect(fn() => $service->authenticate('user@example.com', 'password'))
                ->toThrow(PlaudException::class, 'Authentication failed.');
        });
    });

    describe('getMyUser', function () {
        it('returns ResponseUser', function () {
            Http::fake([
                'api.plaud.ai/user/me' => Http::response([
                    'status' => 200,
                    'data_user' => [
                        'id' => 'user-123',
                        'nickname' => 'Test User',
                        'email' => 'test@example.com',
                    ],
                    'data_state' => [
                        'is_bind' => 1,
                        'is_membership' => 1,
                        'autorenew_status_ios' => false,
                        'autorenew_status_android' => false,
                        'autorenew_status_web' => true,
                        'membership_flag' => 'pro',
                        'membership_type' => 'annual',
                    ],
                ], 200),
            ]);

            $service = new PlaudService(new PlaudClient('token'));
            $response = $service->getMyUser();

            expect($response)->toBeInstanceOf(ResponseUser::class)
                ->and($response->status)->toBe(200)
                ->and($response->dataUser->nickname)->toBe('Test User');
        });
    });

    describe('getStatus', function () {
        it('returns ResponseStatus', function () {
            Http::fake([
                'api.plaud.ai/ai/status' => Http::response([
                    'status' => 200,
                    'data_processing' => ['file-1'],
                    'data_processing_chatllm' => [],
                ], 200),
            ]);

            $service = new PlaudService(new PlaudClient('token'));
            $response = $service->getStatus();

            expect($response)->toBeInstanceOf(ResponseStatus::class)
                ->and($response->status)->toBe(200)
                ->and($response->dataProcessing)->toBe(['file-1']);
        });
    });

    describe('getAllRecordings', function () {
        it('returns ResponseListRecordings', function () {
            Http::fake([
                'api.plaud.ai/file/simple/web*' => Http::response([
                    'status' => 200,
                    'msg' => 'success',
                    'data_file_total' => 2,
                    'data_file_list' => [
                        ['id' => 'rec-1', 'filename' => 'Recording 1'],
                        ['id' => 'rec-2', 'filename' => 'Recording 2'],
                    ],
                ], 200),
            ]);

            $service = new PlaudService(new PlaudClient('token'));
            $response = $service->getAllRecordings();

            expect($response)->toBeInstanceOf(ResponseListRecordings::class)
                ->and($response->dataFileTotal)->toBe(2)
                ->and($response->dataFileList)->toHaveCount(2);
        });
    });

    describe('getRecordingsWithFilter', function () {
        it('returns filtered recordings', function () {
            Http::fake([
                'api.plaud.ai/file/simple/web*' => Http::response([
                    'status' => 200,
                    'msg' => 'success',
                    'data_file_total' => 1,
                    'data_file_list' => [
                        ['id' => 'rec-1', 'filename' => 'Filtered Recording'],
                    ],
                ], 200),
            ]);

            $service = new PlaudService(new PlaudClient('token'));
            $response = $service->getRecordingsWithFilter(
                skip: 0,
                limit: 10,
                isTrash: 0,
                sortBy: 'start_time',
                isDesc: false
            );

            expect($response)->toBeInstanceOf(ResponseListRecordings::class)
                ->and($response->dataFileTotal)->toBe(1);
        });
    });

    describe('getSpecificRecordings', function () {
        it('returns specific recordings by IDs', function () {
            Http::fake([
                'api.plaud.ai/file/list' => Http::response([
                    'status' => 200,
                    'msg' => 'success',
                    'data_file_total' => 2,
                    'data_file_list' => [
                        ['id' => 'rec-abc', 'filename' => 'Recording ABC'],
                        ['id' => 'rec-xyz', 'filename' => 'Recording XYZ'],
                    ],
                ], 200),
            ]);

            $service = new PlaudService(new PlaudClient('token'));
            $response = $service->getSpecificRecordings(['rec-abc', 'rec-xyz']);

            expect($response)->toBeInstanceOf(ResponseListRecordings::class)
                ->and($response->dataFileTotal)->toBe(2);
        });

        it('throws exception for empty recording IDs', function () {
            $service = new PlaudService();

            expect(fn() => $service->getSpecificRecordings([]))
                ->toThrow(PlaudException::class, 'Recording IDs cannot be empty.');
        });
    });

    describe('getFileTags', function () {
        it('returns ResponseFileTags', function () {
            Http::fake([
                'api.plaud.ai/filetag/' => Http::response([
                    'status' => 200,
                    'msg' => 'success',
                    'data_filetag_total' => 2,
                    'data_filetag_list' => [
                        ['id' => 'tag-1', 'name' => 'Work', 'icon' => 'briefcase', 'color' => '#FF0000'],
                        ['id' => 'tag-2', 'name' => 'Personal', 'icon' => 'home', 'color' => '#00FF00'],
                    ],
                ], 200),
            ]);

            $service = new PlaudService(new PlaudClient('token'));
            $response = $service->getFileTags();

            expect($response)->toBeInstanceOf(ResponseFileTags::class)
                ->and($response->dataFiletagTotal)->toBe(2)
                ->and($response->dataFiletagList)->toHaveCount(2);
        });
    });

    describe('createShareableLink', function () {
        it('creates shareable link', function () {
            Http::fake([
                'api.plaud.ai/file/share-url/rec-123' => Http::response([
                    'status' => 200,
                    'url' => 'https://share.plaud.ai/abc123',
                ], 200),
            ]);

            $permissions = new RequestShareableLinkPermissions(
                isAudio: 1,
                isTrans: 1,
                isAiContent: 0,
                isMindmap: 0
            );

            $service = new PlaudService(new PlaudClient('token'));
            $response = $service->createShareableLink('rec-123', $permissions);

            expect($response)->toBeInstanceOf(ResponseShareableLink::class)
                ->and($response->url)->toBe('https://share.plaud.ai/abc123');
        });

        it('throws exception for empty recording ID', function () {
            $service = new PlaudService();
            $permissions = new RequestShareableLinkPermissions();

            expect(fn() => $service->createShareableLink('', $permissions))
                ->toThrow(PlaudException::class, 'Recording ID cannot be empty.');
        });
    });

    describe('downloadAudioFile', function () {
        it('downloads audio file and returns base64', function () {
            $audioContent = 'fake-audio-binary-content';

            Http::fake([
                'api.plaud.ai/others/upload-info' => Http::response([
                    'status' => 200,
                    'msg' => 'success',
                ], 200),
                'api.plaud.ai/file/temp-url/rec-audio' => Http::response([
                    'status' => 200,
                    'temp_url' => 'https://storage.example.com/audio.mp3',
                ], 200),
                'https://storage.example.com/audio.mp3' => Http::response($audioContent, 200),
            ]);

            $service = new PlaudService(new PlaudClient('token'));
            $result = $service->downloadAudioFile('rec-audio');

            expect($result)->toBe(base64_encode($audioContent));
        });

        it('throws exception for empty recording ID', function () {
            $service = new PlaudService();

            expect(fn() => $service->downloadAudioFile(''))
                ->toThrow(PlaudException::class, 'Recording ID cannot be empty.');
        });

        it('throws exception when upload info fails', function () {
            Http::fake([
                'api.plaud.ai/others/upload-info' => Http::response([
                    'status' => 200,
                    'msg' => 'failed',
                ], 200),
            ]);

            $service = new PlaudService(new PlaudClient('token'));

            expect(fn() => $service->downloadAudioFile('rec-id'))
                ->toThrow(PlaudException::class, 'Upload Info failed.');
        });

        it('throws exception when no download URL', function () {
            Http::fake([
                'api.plaud.ai/others/upload-info' => Http::response([
                    'status' => 200,
                    'msg' => 'success',
                ], 200),
                'api.plaud.ai/file/temp-url/rec-id' => Http::response([
                    'status' => 200,
                    'temp_url' => '',
                ], 200),
            ]);

            $service = new PlaudService(new PlaudClient('token'));

            expect(fn() => $service->downloadAudioFile('rec-id'))
                ->toThrow(PlaudException::class, 'No download url found.');
        });
    });

    describe('downloadTranscriptFile', function () {
        it('throws exception for empty recording ID or file type', function () {
            $service = new PlaudService();

            expect(fn() => $service->downloadTranscriptFile('', 'PDF'))
                ->toThrow(PlaudException::class, 'Recording ID and File Type cannot be empty.');

            expect(fn() => $service->downloadTranscriptFile('rec-id', ''))
                ->toThrow(PlaudException::class, 'Recording ID and File Type cannot be empty.');
        });
    });

    describe('downloadSummaryFile', function () {
        it('throws exception for empty recording ID or file type', function () {
            $service = new PlaudService();

            expect(fn() => $service->downloadSummaryFile('', 'PDF'))
                ->toThrow(PlaudException::class, 'Recording ID and File Type cannot be empty.');

            expect(fn() => $service->downloadSummaryFile('rec-id', ''))
                ->toThrow(PlaudException::class, 'Recording ID and File Type cannot be empty.');
        });
    });

    describe('trashRecordings', function () {
        it('moves recordings to trash', function () {
            Http::fake([
                'api.plaud.ai/file/trash/' => Http::response(null, 200),
            ]);

            $service = new PlaudService(new PlaudClient('token'));
            $result = $service->trashRecordings(['rec-1', 'rec-2']);

            expect($result)->toBeTrue();
        });

        it('throws exception for empty recording IDs', function () {
            $service = new PlaudService();

            expect(fn() => $service->trashRecordings([]))
                ->toThrow(PlaudException::class, 'Recording IDs cannot be empty.');
        });
    });

    describe('untrashRecordings', function () {
        it('restores recordings from trash', function () {
            Http::fake([
                'api.plaud.ai/file/untrash/' => Http::response(null, 200),
            ]);

            $service = new PlaudService(new PlaudClient('token'));
            $result = $service->untrashRecordings(['rec-1']);

            expect($result)->toBeTrue();
        });

        it('throws exception for empty recording IDs', function () {
            $service = new PlaudService();

            expect(fn() => $service->untrashRecordings([]))
                ->toThrow(PlaudException::class, 'Recording IDs cannot be empty.');
        });
    });

    describe('permanentlyDeleteRecordings', function () {
        it('permanently deletes recordings', function () {
            Http::fake([
                'api.plaud.ai/file/' => Http::response(null, 200),
            ]);

            $service = new PlaudService(new PlaudClient('token'));
            $result = $service->permanentlyDeleteRecordings(['rec-1', 'rec-2']);

            expect($result)->toBeTrue();
        });

        it('throws exception for empty recording IDs', function () {
            $service = new PlaudService();

            expect(fn() => $service->permanentlyDeleteRecordings([]))
                ->toThrow(PlaudException::class, 'Recording IDs cannot be empty.');
        });
    });
});
