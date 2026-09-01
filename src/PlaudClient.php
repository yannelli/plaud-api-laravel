<?php

namespace Yannelli\LaravelPlaud;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Yannelli\LaravelPlaud\Constants\PlaudRegions;
use Yannelli\LaravelPlaud\Exceptions\PlaudException;
use Yannelli\LaravelPlaud\Support\Jwt;

/**
 * Low-level HTTP client for interacting with the Plaud API
 */
class PlaudClient
{
    protected string $baseUrl;

    protected ?string $accessToken = null;

    protected ?string $userToken = null;

    protected ?string $userCookie = null;

    protected ?string $refreshCookie = null;

    protected ?string $deviceId = null;

    protected int $timeout;

    /**
     * Create a new Plaud HTTP client instance
     */
    public function __construct(
        ?string $accessToken = null,
        ?string $baseUrl = null,
        ?string $userToken = null,
        ?string $refreshToken = null,
        ?string $deviceId = null,
        int $timeout = 120,
    ) {
        $this->timeout = $timeout;
        $this->deviceId = $deviceId;
        $this->baseUrl = $baseUrl ? PlaudRegions::resolve($baseUrl) : PlaudRegions::GLOBAL;

        if ($userToken) {
            $this->setUserToken($userToken);
        }

        if ($refreshToken) {
            $this->refreshCookie = $refreshToken;
        }

        if ($accessToken) {
            $this->setAccessToken($accessToken);
        }
    }

    /**
     * Set the access token for authenticated requests
     */
    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        $this->applyRegionFromToken($accessToken);

        if ($this->userToken === null && ! Jwt::isWorkspaceToken($accessToken)) {
            $this->userToken = $accessToken;
        }

        return $this;
    }

    /**
     * Get the access token (Bearer used on API calls; may be a workspace token)
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * Long-lived user token (UT). Required to mint workspace tokens.
     */
    public function setUserToken(string $userToken): self
    {
        $this->userToken = $userToken;
        $this->userCookie = $userToken;
        $this->applyRegionFromToken($userToken);

        if ($this->accessToken === null) {
            $this->accessToken = $userToken;
        }

        return $this;
    }

    public function getUserToken(): ?string
    {
        return $this->userToken;
    }

    public function setRefreshCookie(string $refreshCookie): self
    {
        $this->refreshCookie = $refreshCookie;

        return $this;
    }

    public function getUserCookie(): ?string
    {
        return $this->userCookie;
    }

    public function getRefreshCookie(): ?string
    {
        return $this->refreshCookie;
    }

    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = PlaudRegions::resolve($baseUrl);

        return $this;
    }

    public function setRegion(string $region): self
    {
        $this->baseUrl = PlaudRegions::resolve($region);

        return $this;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setDeviceId(string $deviceId): self
    {
        $this->deviceId = $deviceId;

        return $this;
    }

    /**
     * Create a base HTTP client with common configuration
     */
    protected function http(bool $forAuth = false): PendingRequest
    {
        $headers = $forAuth ? $this->authHeaders() : $this->dataHeaders();

        $http = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->withHeaders($headers);

        if (! $forAuth) {
            $http = $http->acceptJson();
        }

        $bearer = $forAuth ? null : $this->accessToken;
        if ($bearer) {
            $http = $http->withToken($bearer);
        }

        $cookies = array_filter([
            'pld_ut' => $this->userCookie,
            'pld_urt' => $this->refreshCookie,
        ]);

        if ($cookies !== []) {
            $http = $http->withCookies($cookies, $this->cookieDomain());
        }

        return $http;
    }

    /**
     * @return array<string, string>
     */
    protected function authHeaders(): array
    {
        return [
            'Accept' => 'application/json, text/plain, */*',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function dataHeaders(): array
    {
        $headers = [
            'User-Agent' => PlaudRegions::USER_AGENT,
            'Origin' => 'https://web.plaud.ai',
            'Referer' => 'https://web.plaud.ai/',
            'app-platform' => 'web',
            'edit-from' => 'web',
        ];

        if ($this->deviceId) {
            $headers['x-device-id'] = $this->deviceId;
        }

        return $headers;
    }

    protected function cookieDomain(): string
    {
        return parse_url($this->baseUrl, PHP_URL_HOST) ?: 'api.plaud.ai';
    }

    /**
     * Authenticate with username and password
     */
    public function authenticate(string $username, string $password): array
    {
        $payload = [
            'username' => $username,
            'password' => $password,
            'client_id' => 'web',
        ];

        $data = $this->sendAuthForm('/auth/access-token', $payload);

        $token = $this->extractAccessToken($data);

        if ($token !== '') {
            $this->setAccessToken($token);
        }

        return $data;
    }

    /**
     * Request an emailed one-time login code.
     *
     * @return array{token: string, api_base: string, raw: array}
     */
    public function sendOtpCode(string $email): array
    {
        $data = $this->sendAuthJson('/auth/otp-send-code', [
            'username' => $email,
        ]);

        $token = (string) ($data['token'] ?? $data['data']['token'] ?? '');

        if ($token === '') {
            throw new PlaudException($data['msg'] ?? 'Failed to send verification code.');
        }

        return [
            'token' => $token,
            'api_base' => $this->baseUrl,
            'raw' => $data,
        ];
    }

    /**
     * Complete OTP login and store the resulting user token.
     */
    public function otpLogin(string $code, string $otpToken): array
    {
        $data = $this->sendAuthJson('/auth/otp-login', [
            'code' => $code,
            'token' => $otpToken,
        ]);

        $token = $this->extractAccessToken($data);

        if ($token === '') {
            throw new PlaudException($data['msg'] ?? 'Invalid verification code.');
        }

        $this->setUserToken($token);
        $this->setAccessToken($token);

        return $data;
    }

    /**
     * Refresh a v3 cookie session via POST /auth/refresh-user-token.
     */
    public function refreshUserToken(): array
    {
        $response = $this->http(true)->post('/auth/refresh-user-token');

        $this->applyCookiesFromResponse($response);

        if (! $response->successful()) {
            throw new PlaudException('Token refresh failed: '.$response->body(), $response->status());
        }

        $data = $response->json() ?? [];
        $this->followRegionRedirectFromBody($data);

        $token = $this->extractAccessToken($data);

        if ($token !== '') {
            $this->setAccessToken($token);
            $this->setUserToken($token);
        } elseif ($this->userCookie) {
            $this->setUserToken($this->userCookie);
        }

        return is_array($data) ? $data : [];
    }

    /**
     * Mint a short-lived workspace token (WT) from the stored user token (UT).
     */
    public function mintWorkspaceToken(string $workspaceId): string
    {
        $userToken = $this->userToken ?: $this->accessToken;

        if (! $userToken) {
            throw new PlaudException('A user token is required to mint a workspace token.');
        }

        if (Jwt::isWorkspaceToken($userToken)) {
            throw new PlaudException('Cannot mint a workspace token from another workspace token. Store the user token (localStorage pld_tokenstr / pld_ut), not a token copied from /file/simple/web.');
        }

        $http = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->withHeaders($this->dataHeaders())
            ->acceptJson()
            ->withToken($userToken)
            ->withBody('{}', 'application/json');

        $response = $http->post("/user-app/auth/workspace/token/{$workspaceId}");
        $data = $this->decodeOrThrow($response, "POST /user-app/auth/workspace/token/{$workspaceId}");
        $this->followRegionRedirectFromBody($data);

        if ($this->isRegionMismatch($data)) {
            $response = Http::baseUrl($this->baseUrl)
                ->timeout($this->timeout)
                ->withHeaders($this->dataHeaders())
                ->acceptJson()
                ->withToken($userToken)
                ->withBody('{}', 'application/json')
                ->post("/user-app/auth/workspace/token/{$workspaceId}");
            $data = $this->decodeOrThrow($response, "POST /user-app/auth/workspace/token/{$workspaceId}");
        }

        $token = $this->extractAccessToken($data);

        if ($token === '') {
            throw new PlaudException('Workspace token mint returned no access_token.');
        }

        $this->setAccessToken($token);

        return $token;
    }

    /**
     * Perform a GET request
     */
    public function get(string $endpoint, array $query = []): array
    {
        $response = $this->send('GET', $endpoint, $query);

        return $this->decodeOrThrow($response, "GET {$endpoint}");
    }

    /**
     * Perform a POST request
     */
    public function post(string $endpoint, array|object $data): array
    {
        $response = $this->send('POST', $endpoint, $data);

        return $this->decodeOrThrow($response, "POST {$endpoint}");
    }

    /**
     * Perform a PATCH request
     */
    public function patch(string $endpoint, array|object $data): array
    {
        $response = $this->send('PATCH', $endpoint, $data);

        return $this->decodeOrThrow($response, "PATCH {$endpoint}");
    }

    /**
     * Perform a PUT request to an absolute URL (used for S3 uploads)
     */
    public function putRaw(string $url, mixed $body, array $headers = []): Response
    {
        $request = Http::timeout(300)->withHeaders($headers);

        if (is_string($body) || is_resource($body)) {
            $request = $request->withBody($body, $headers['Content-Type'] ?? 'application/octet-stream');
        }

        $response = is_array($body)
            ? $request->put($url, $body)
            : $request->put($url);

        if (! $response->successful()) {
            throw new PlaudException("PUT request failed for {$url}: ".$response->body(), $response->status());
        }

        return $response;
    }

    /**
     * Perform a POST request without expecting a response body
     */
    public function postNoResponse(string $endpoint, array|object $data): bool
    {
        $response = $this->send('POST', $endpoint, $data);

        return $response->successful();
    }

    /**
     * Perform a DELETE request, optionally with a JSON body
     */
    public function delete(string $endpoint, array|object|null $data = null): bool
    {
        $response = $this->send('DELETE', $endpoint, $data);

        return $response->successful();
    }

    /**
     * Perform a DELETE request with a body
     */
    public function deleteWithBody(string $endpoint, array|object $data): bool
    {
        return $this->delete($endpoint, $data);
    }

    /**
     * Download a file from a URL and return as base64
     */
    public function downloadFileAsBase64(string $url): string
    {
        $response = Http::timeout(300)
            ->withHeaders(['User-Agent' => PlaudRegions::USER_AGENT])
            ->get($url);

        if (! $response->successful()) {
            throw new PlaudException("Failed to download file from {$url}: ".$response->body(), $response->status());
        }

        return base64_encode($response->body());
    }

    /**
     * Download an authenticated Plaud path (e.g. /file/download/{id}) as base64
     */
    public function downloadAuthenticatedAsBase64(string $endpoint): string
    {
        $response = $this->send('GET', $endpoint);

        if (! $response->successful()) {
            throw new PlaudException("Failed to download {$endpoint}: ".$response->body(), $response->status());
        }

        return base64_encode($response->body());
    }

    /**
     * @param  array<string, mixed>|object|null  $data
     */
    protected function send(string $method, string $endpoint, array|object|null $data = null, int $attempts = 0, bool $retriedAuth = false): Response
    {
        $http = $this->http();
        $method = strtoupper($method);

        $response = match ($method) {
            'GET' => $http->get($endpoint, is_array($data) ? $data : []),
            'POST' => $http->post($endpoint, $data ?? []),
            'PATCH' => $http->patch($endpoint, $data ?? []),
            'PUT' => $http->put($endpoint, $data ?? []),
            'DELETE' => $this->sendDelete($http, $endpoint, $data),
            default => throw new PlaudException("Unsupported HTTP method {$method}."),
        };

        $this->applyCookiesFromResponse($response);

        if (in_array($response->status(), [429, 500, 502, 503], true) && $attempts < 2) {
            $retryAfter = $response->header('Retry-After');
            if (is_numeric($retryAfter) && (int) $retryAfter > 0) {
                sleep(min((int) $retryAfter, 2));
            }

            return $this->send($method, $endpoint, $data, $attempts + 1, $retriedAuth);
        }

        $json = $response->json();

        if (is_array($json) && $this->followRegionRedirectFromBody($json) && $attempts < 3) {
            return $this->send($method, $endpoint, $data, $attempts + 1, $retriedAuth);
        }

        if ($response->status() === 401 && ! $retriedAuth && $this->refreshCookie) {
            try {
                $this->refreshUserToken();
            } catch (PlaudException) {
                return $response;
            }

            return $this->send($method, $endpoint, $data, $attempts, true);
        }

        return $response;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function sendAuthForm(string $endpoint, array $payload, int $redirects = 0): array
    {
        $response = $this->http(true)->asForm()->post($endpoint, $payload);

        $this->applyCookiesFromResponse($response);

        if (! $response->successful()) {
            throw new PlaudException('Authentication failed: '.$response->body(), $response->status());
        }

        $data = $response->json() ?? [];

        if ($this->followRegionRedirectFromBody($data) && $redirects < 3) {
            return $this->sendAuthForm($endpoint, $payload, $redirects + 1);
        }

        return is_array($data) ? $data : [];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function sendAuthJson(string $endpoint, array $payload, int $redirects = 0): array
    {
        $response = $this->http(true)
            ->withHeaders(['User-Agent' => PlaudRegions::USER_AGENT])
            ->asJson()
            ->post($endpoint, $payload);

        $this->applyCookiesFromResponse($response);

        if (! $response->successful()) {
            throw new PlaudException("Auth request failed for {$endpoint}: ".$response->body(), $response->status());
        }

        $data = $response->json() ?? [];

        if ($this->followRegionRedirectFromBody($data) && $redirects < 3) {
            return $this->sendAuthJson($endpoint, $payload, $redirects + 1);
        }

        return is_array($data) ? $data : [];
    }

    /**
     * @param  array<string, mixed>|object|null  $data
     */
    protected function sendDelete(PendingRequest $http, string $endpoint, array|object|null $data): Response
    {
        if ($data === null || $data === []) {
            return $http->delete($endpoint);
        }

        $payload = is_object($data) && method_exists($data, 'toArray')
            ? $data->toArray()
            : $data;

        return $http->withBody(
            is_string($payload) ? $payload : (string) json_encode($payload),
            'application/json'
        )->delete($endpoint);
    }

    /**
     * @return array<string, mixed>
     */
    protected function decodeOrThrow(Response $response, string $context): array
    {
        if (! $response->successful()) {
            throw new PlaudException("{$context} failed: ".$response->body(), $response->status());
        }

        $data = $response->json() ?? [];

        if (is_array($data) && $this->isNegativeApiStatus($data)) {
            $message = trim((string) ($data['msg'] ?? ''));

            throw new PlaudException(
                $message !== '' ? $message : "{$context} failed with API status {$data['status']}.",
                (int) $data['status']
            );
        }

        return is_array($data) ? $data : [];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function isNegativeApiStatus(array $data): bool
    {
        if ($this->isRegionMismatch($data)) {
            return true;
        }

        $status = $data['status'] ?? null;

        return is_numeric($status) && (int) $status < 0;
    }

    protected function applyCookiesFromResponse(Response $response): void
    {
        try {
            $jar = $response->cookies();
        } catch (\Throwable) {
            return;
        }

        foreach ($jar as $cookie) {
            $name = $cookie->getName();
            $value = $cookie->getValue();

            if ($value === '' || strtolower((string) $value) === 'deleted') {
                continue;
            }

            if ($name === 'pld_ut') {
                $this->userCookie = $value;
                $this->userToken = $this->userToken ?: $value;
            }

            if ($name === 'pld_urt') {
                $this->refreshCookie = $value;
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function followRegionRedirectFromBody(array $data): bool
    {
        if (! $this->isRegionMismatch($data)) {
            return false;
        }

        $target = $this->redirectBaseFromBody($data);

        if ($target === null || $target === $this->baseUrl) {
            return false;
        }

        $this->baseUrl = $target;

        return true;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function isRegionMismatch(array $data): bool
    {
        $status = $data['status'] ?? null;
        $msg = strtolower(trim((string) ($data['msg'] ?? '')));

        return $status === -302 || $msg === 'user region mismatch';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function redirectBaseFromBody(array $data): ?string
    {
        $domain = $data['data']['domains']['api']
            ?? $data['data']['domains']['host']
            ?? $data['data']['domain']
            ?? null;

        if (! is_string($domain) || $domain === '') {
            return null;
        }

        if (! PlaudRegions::isPlaudHost($domain)) {
            return null;
        }

        if (! str_starts_with($domain, 'http')) {
            $domain = 'https://'.$domain;
        }

        return rtrim($domain, '/');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function extractAccessToken(array $data): string
    {
        $inner = is_array($data['data'] ?? null) ? $data['data'] : [];

        foreach ([$data, $inner] as $container) {
            $token = $container['access_token'] ?? null;
            if (is_string($token) && trim($token) !== '') {
                return trim($token);
            }
        }

        return '';
    }

    protected function applyRegionFromToken(string $token): void
    {
        if ($this->baseUrl !== PlaudRegions::GLOBAL) {
            return;
        }

        $mapped = PlaudRegions::fromJwtRegion(Jwt::region($token));

        if ($mapped && $mapped !== $this->baseUrl) {
            $this->baseUrl = $mapped;
        }
    }
}
