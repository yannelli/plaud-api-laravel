<?php

namespace Yannelli\LaravelPlaud;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Yannelli\LaravelPlaud\Exceptions\PlaudException;

/**
 * Low-level HTTP client for interacting with the Plaud API
 */
class PlaudClient
{
    protected const BASE_URL = 'https://api.plaud.ai';

    protected ?string $accessToken = null;

    /**
     * Create a new Plaud HTTP client instance
     */
    public function __construct(?string $accessToken = null)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * Set the access token for authenticated requests
     */
    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * Get the access token
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * Create a base HTTP client with common configuration
     */
    protected function http(): PendingRequest
    {
        $http = Http::baseUrl(self::BASE_URL)
            ->acceptJson()
            ->timeout(120);

        if ($this->accessToken) {
            $http->withToken($this->accessToken);
        }

        return $http;
    }

    /**
     * Authenticate with username and password
     */
    public function authenticate(string $username, string $password): array
    {
        $response = $this->http()
            ->asForm()
            ->post('/auth/access-token', [
                'username' => $username,
                'password' => $password,
                'client_id' => 'web',
            ]);

        if (!$response->successful()) {
            throw new PlaudException('Authentication failed: ' . $response->body(), $response->status());
        }

        $data = $response->json();

        if (isset($data['access_token'])) {
            $this->setAccessToken($data['access_token']);
        }

        return $data;
    }

    /**
     * Perform a GET request
     */
    public function get(string $endpoint): array
    {
        $response = $this->http()->get($endpoint);

        if (!$response->successful()) {
            throw new PlaudException("GET request failed for {$endpoint}: " . $response->body(), $response->status());
        }

        return $response->json() ?? [];
    }

    /**
     * Perform a POST request
     */
    public function post(string $endpoint, array|object $data): array
    {
        $response = $this->http()->post($endpoint, $data);

        if (!$response->successful()) {
            throw new PlaudException("POST request failed for {$endpoint}: " . $response->body(), $response->status());
        }

        return $response->json() ?? [];
    }

    /**
     * Perform a POST request without expecting a response body
     */
    public function postNoResponse(string $endpoint, array|object $data): bool
    {
        $response = $this->http()->post($endpoint, $data);

        return $response->successful();
    }

    /**
     * Perform a DELETE request with a body
     */
    public function deleteWithBody(string $endpoint, array|object $data): bool
    {
        $response = $this->http()->delete($endpoint, $data);

        return $response->successful();
    }

    /**
     * Download a file from a URL and return as base64
     */
    public function downloadFileAsBase64(string $url): string
    {
        $response = Http::timeout(300)->get($url);

        if (!$response->successful()) {
            throw new PlaudException("Failed to download file from {$url}: " . $response->body(), $response->status());
        }

        return base64_encode($response->body());
    }
}
