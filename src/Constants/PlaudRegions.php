<?php

namespace Yannelli\LaravelPlaud\Constants;

/**
 * Known Plaud API hosts.
 *
 * Plaud shards accounts across regional APIs. `api.plaud.ai` is the
 * discovery host; a token's JWT `region` claim or a `-302` envelope
 * (`data.domains.api`) points at the account's real host.
 */
class PlaudRegions
{
    public const GLOBAL = 'https://api.plaud.ai';
    public const EU = 'https://api-euc1.plaud.ai';
    public const APSE1 = 'https://api-apse1.plaud.ai';

    /**
     * Browser-like UA. Plaud has been observed rejecting default client
     * user-agents (e.g. Guzzle, Node fetch) with HTTP 403.
     */
    public const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';

    /**
     * @var array<string, string>
     */
    public const HOSTS = [
        'global' => self::GLOBAL,
        'us' => self::GLOBAL,
        'eu' => self::EU,
        'euc1' => self::EU,
        'apac' => self::APSE1,
        'apse1' => self::APSE1,
        'aws:us-west-2' => self::GLOBAL,
        'aws:us-west-1' => 'https://api-usw1.plaud.ai',
        'aws:us-east-1' => 'https://api-use1.plaud.ai',
        'aws:us-east-2' => 'https://api-use2.plaud.ai',
        'aws:eu-central-1' => self::EU,
        'aws:eu-west-1' => 'https://api-euw1.plaud.ai',
        'aws:ap-southeast-1' => self::APSE1,
        'aws:ap-southeast-2' => 'https://api-apse2.plaud.ai',
        'aws:ap-northeast-1' => 'https://api-apne1.plaud.ai',
        'aws:ap-south-1' => 'https://api-aps1.plaud.ai',
    ];

    public static function resolve(string $regionOrUrl): string
    {
        $value = trim($regionOrUrl);

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return rtrim($value, '/');
        }

        $key = strtolower($value);

        if (isset(self::HOSTS[$key])) {
            return self::HOSTS[$key];
        }

        if (isset(self::HOSTS[$value])) {
            return self::HOSTS[$value];
        }

        return self::GLOBAL;
    }

    public static function fromJwtRegion(?string $region): ?string
    {
        if ($region === null || $region === '') {
            return null;
        }

        if (isset(self::HOSTS[$region])) {
            return self::HOSTS[$region];
        }

        $lower = strtolower($region);

        if (str_contains($lower, 'eu-') || $lower === 'eu') {
            return self::EU;
        }

        if (str_contains($lower, 'ap-') || str_contains($lower, 'apse') || $lower === 'apac') {
            return self::APSE1;
        }

        if (isset(self::HOSTS[$lower])) {
            return self::HOSTS[$lower];
        }

        return null;
    }

    public static function isPlaudHost(string $hostOrUrl): bool
    {
        $host = strtolower(trim($hostOrUrl));

        foreach (['https://', 'http://'] as $prefix) {
            if (str_starts_with($host, $prefix)) {
                $host = substr($host, strlen($prefix));
                break;
            }
        }

        $host = explode('/', $host, 2)[0];

        return $host === 'plaud.ai' || str_ends_with($host, '.plaud.ai');
    }
}
