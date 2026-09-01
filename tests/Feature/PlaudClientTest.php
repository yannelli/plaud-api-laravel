<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Yannelli\LaravelPlaud\PlaudClient;
use Yannelli\LaravelPlaud\Exceptions\PlaudException;
use Yannelli\LaravelPlaud\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Http::preventStrayRequests();
});

describe('PlaudClient', function () {

    describe('constructor and access token', function () {
        it('can be instantiated without access token', function () {
            $client = new PlaudClient();

            expect($client->getAccessToken())->toBeNull();
        });

        it('can be instantiated with access token', function () {
            $client = new PlaudClient('test-token-123');

            expect($client->getAccessToken())->toBe('test-token-123');
        });

        it('can set access token', function () {
            $client = new PlaudClient();
            $result = $client->setAccessToken('new-token');

            expect($client->getAccessToken())->toBe('new-token')
                ->and($result)->toBeInstanceOf(PlaudClient::class);
        });

        it('setAccessToken returns self for chaining', function () {
            $client = new PlaudClient();

            $result = $client->setAccessToken('token-1')
                ->setAccessToken('token-2');

            expect($result)->toBeInstanceOf(PlaudClient::class)
                ->and($client->getAccessToken())->toBe('token-2');
        });
    });

    describe('authenticate', function () {
        it('sends authentication request and returns data', function () {
            Http::fake([
                'api.plaud.ai/auth/access-token' => Http::response([
                    'status' => 200,
                    'access_token' => 'new-access-token',
                    'token_type' => 'Bearer',
                    'msg' => 'success',
                ], 200),
            ]);

            $client = new PlaudClient();
            $data = $client->authenticate('user@example.com', 'password123');

            expect($data['access_token'])->toBe('new-access-token')
                ->and($client->getAccessToken())->toBe('new-access-token');

            Http::assertSent(function (Request $request) {
                return $request->url() === 'https://api.plaud.ai/auth/access-token'
                    && $request['username'] === 'user@example.com'
                    && $request['password'] === 'password123'
                    && $request['client_id'] === 'web';
            });
        });

        it('throws exception on authentication failure', function () {
            Http::fake([
                'api.plaud.ai/auth/access-token' => Http::response([
                    'error' => 'Invalid credentials',
                ], 401),
            ]);

            $client = new PlaudClient();

            expect(fn() => $client->authenticate('user@example.com', 'wrong-password'))
                ->toThrow(PlaudException::class);
        });
    });

    describe('get', function () {
        it('sends GET request with authorization header', function () {
            Http::fake([
                'api.plaud.ai/user/me' => Http::response([
                    'status' => 200,
                    'data_user' => ['id' => 'user-123'],
                ], 200),
            ]);

            $client = new PlaudClient('my-token');
            $data = $client->get('/user/me');

            expect($data['status'])->toBe(200)
                ->and($data['data_user']['id'])->toBe('user-123');

            Http::assertSent(function (Request $request) {
                return $request->url() === 'https://api.plaud.ai/user/me'
                    && $request->hasHeader('Authorization', 'Bearer my-token');
            });
        });

        it('throws exception on GET failure', function () {
            Http::fake([
                'api.plaud.ai/test-endpoint' => Http::response([
                    'error' => 'Not found',
                ], 404),
            ]);

            $client = new PlaudClient('token');

            expect(fn() => $client->get('/test-endpoint'))
                ->toThrow(PlaudException::class);
        });

        it('returns empty array when response is null', function () {
            Http::fake([
                'api.plaud.ai/empty' => Http::response(null, 200),
            ]);

            $client = new PlaudClient('token');
            $data = $client->get('/empty');

            expect($data)->toBe([]);
        });
    });

    describe('post', function () {
        it('sends POST request with JSON body', function () {
            Http::fake([
                'api.plaud.ai/file/list' => Http::response([
                    'status' => 200,
                    'data_file_list' => [],
                ], 200),
            ]);

            $client = new PlaudClient('post-token');
            $data = $client->post('/file/list', ['file-1', 'file-2']);

            expect($data['status'])->toBe(200);

            Http::assertSent(function (Request $request) {
                return $request->url() === 'https://api.plaud.ai/file/list'
                    && $request->hasHeader('Authorization', 'Bearer post-token');
            });
        });

        it('throws exception on POST failure', function () {
            Http::fake([
                'api.plaud.ai/fail' => Http::response([
                    'error' => 'Bad request',
                ], 400),
            ]);

            $client = new PlaudClient('token');

            expect(fn() => $client->post('/fail', ['data']))
                ->toThrow(PlaudException::class);
        });
    });

    describe('postNoResponse', function () {
        it('returns true on successful POST', function () {
            Http::fake([
                'api.plaud.ai/file/trash/' => Http::response(null, 200),
            ]);

            $client = new PlaudClient('trash-token');
            $result = $client->postNoResponse('/file/trash/', ['file-id']);

            expect($result)->toBeTrue();
        });

        it('returns false on failed POST', function () {
            Http::fake([
                'api.plaud.ai/file/trash/' => Http::response(null, 500),
            ]);

            $client = new PlaudClient('token');
            $result = $client->postNoResponse('/file/trash/', ['file-id']);

            expect($result)->toBeFalse();
        });
    });

    describe('deleteWithBody', function () {
        it('returns true on successful DELETE', function () {
            Http::fake([
                'api.plaud.ai/file/' => Http::response(null, 200),
            ]);

            $client = new PlaudClient('delete-token');
            $result = $client->deleteWithBody('/file/', ['file-id']);

            expect($result)->toBeTrue();

            Http::assertSent(function (Request $request) {
                return $request->url() === 'https://api.plaud.ai/file/'
                    && $request->method() === 'DELETE'
                    && $request->hasHeader('Authorization', 'Bearer delete-token')
                    && $request->body() === json_encode(['file-id']);
            });
        });

        it('returns false on failed DELETE', function () {
            Http::fake([
                'api.plaud.ai/file/' => Http::response(null, 403),
            ]);

            $client = new PlaudClient('token');
            $result = $client->deleteWithBody('/file/', ['file-id']);

            expect($result)->toBeFalse();
        });
    });

    describe('downloadFileAsBase64', function () {
        it('downloads file and returns base64 encoded content', function () {
            $fileContent = 'This is test file content';

            Http::fake([
                'https://storage.example.com/file.mp3' => Http::response($fileContent, 200),
            ]);

            $client = new PlaudClient();
            $result = $client->downloadFileAsBase64('https://storage.example.com/file.mp3');

            expect($result)->toBe(base64_encode($fileContent));
        });

        it('throws exception on download failure', function () {
            Http::fake([
                'https://storage.example.com/missing.mp3' => Http::response(null, 404),
            ]);

            $client = new PlaudClient();

            expect(fn() => $client->downloadFileAsBase64('https://storage.example.com/missing.mp3'))
                ->toThrow(PlaudException::class);
        });

        it('handles binary content correctly', function () {
            $binaryContent = "\x00\x01\x02\x03\xFF\xFE\xFD";

            Http::fake([
                'https://storage.example.com/binary.bin' => Http::response($binaryContent, 200),
            ]);

            $client = new PlaudClient();
            $result = $client->downloadFileAsBase64('https://storage.example.com/binary.bin');

            expect(base64_decode($result))->toBe($binaryContent);
        });
    });

    describe('regions, retries, and headers', function () {
        it('resolves a short region name passed as the constructor base URL', function () {
            $client = new PlaudClient('token', 'eu');

            expect($client->getBaseUrl())->toBe('https://api-euc1.plaud.ai');
        });

        it('resolves a short region name on setBaseUrl', function () {
            $client = new PlaudClient('token');
            $client->setBaseUrl('apse1');

            expect($client->getBaseUrl())->toBe('https://api-apse1.plaud.ai');
        });

        it('sends a browser user-agent on data requests', function () {
            Http::fake([
                'api.plaud.ai/user/me' => Http::response(['status' => 0], 200),
            ]);

            $client = new PlaudClient('my-token');
            $client->get('/user/me');

            Http::assertSent(function (Request $request) {
                return str_contains($request->header('User-Agent')[0] ?? '', 'Mozilla/5.0');
            });
        });

        it('follows a -302 region mismatch to the regional host', function () {
            Http::fake(function (Request $request) {
                if (str_contains($request->url(), 'api.plaud.ai')) {
                    return Http::response([
                        'status' => -302,
                        'msg' => 'user region mismatch',
                        'data' => ['domains' => ['api' => 'https://api-euc1.plaud.ai']],
                    ], 200);
                }

                return Http::response([
                    'status' => 0,
                    'data_user' => ['id' => 'eu-user'],
                ], 200);
            });

            $client = new PlaudClient('my-token');
            $data = $client->get('/user/me');

            expect($data['data_user']['id'])->toBe('eu-user')
                ->and($client->getBaseUrl())->toBe('https://api-euc1.plaud.ai');
        });

        it('routes to the EU host from a JWT region claim', function () {
            $token = rtrim(strtr(base64_encode(json_encode(['alg' => 'none'])), '+/', '-_'), '=')
                .'.'.rtrim(strtr(base64_encode(json_encode(['region' => 'aws:eu-central-1'])), '+/', '-_'), '=')
                .'.sig';

            $client = new PlaudClient($token);

            expect($client->getBaseUrl())->toBe('https://api-euc1.plaud.ai');
        });

        it('retries a 429 and then succeeds', function () {
            Http::fake([
                'api.plaud.ai/user/me' => Http::sequence()
                    ->push(['error' => 'slow down'], 429)
                    ->push(['status' => 0, 'data_user' => ['id' => 'ok']], 200),
            ]);

            $client = new PlaudClient('token');
            $data = $client->get('/user/me');

            expect($data['data_user']['id'])->toBe('ok');
        });

        it('sends otp-send-code as JSON and stores the handle', function () {
            Http::fake([
                'api.plaud.ai/auth/otp-send-code' => Http::response([
                    'status' => 0,
                    'token' => 'otp-handle',
                    'msg' => 'ok',
                ], 200),
            ]);

            $client = new PlaudClient();
            $result = $client->sendOtpCode('user@example.com');

            expect($result['token'])->toBe('otp-handle');

            Http::assertSent(function (Request $request) {
                return str_ends_with($request->url(), '/auth/otp-send-code')
                    && $request['username'] === 'user@example.com';
            });
        });

        it('completes otp login and stores the user token', function () {
            Http::fake([
                'api.plaud.ai/auth/otp-login' => Http::response([
                    'status' => 0,
                    'access_token' => 'user-jwt',
                    'token_type' => 'Bearer',
                ], 200),
            ]);

            $client = new PlaudClient();
            $data = $client->otpLogin('123456', 'otp-handle');

            expect($data['access_token'])->toBe('user-jwt')
                ->and($client->getAccessToken())->toBe('user-jwt')
                ->and($client->getUserToken())->toBe('user-jwt');
        });

        it('mints a workspace token from a user token', function () {
            Http::fake([
                'api.plaud.ai/user-app/auth/workspace/token/ws-1' => Http::response([
                    'status' => 0,
                    'access_token' => 'workspace-jwt',
                ], 200),
            ]);

            $client = new PlaudClient();
            $client->setUserToken('long-lived-ut');
            $wt = $client->mintWorkspaceToken('ws-1');

            expect($wt)->toBe('workspace-jwt')
                ->and($client->getAccessToken())->toBe('workspace-jwt')
                ->and($client->getUserToken())->toBe('long-lived-ut');
        });

        it('throws when a leftover negative API status is not a region redirect', function () {
            Http::fake([
                'api.plaud.ai/user/me' => Http::response([
                    'status' => -1,
                    'msg' => 'token expired',
                ], 200),
            ]);

            $client = new PlaudClient('token');

            expect(fn () => $client->get('/user/me'))
                ->toThrow(PlaudException::class, 'token expired');
        });

        it('patches JSON to an endpoint', function () {
            Http::fake([
                'api.plaud.ai/file/rec-1' => Http::response(['status' => 0], 200),
            ]);

            $client = new PlaudClient('token');
            $data = $client->patch('/file/rec-1', ['filename' => 'New']);

            expect($data['status'])->toBe(0);
        });
    });
});

