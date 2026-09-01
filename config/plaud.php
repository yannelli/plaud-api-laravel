<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Plaud API Access Token
    |--------------------------------------------------------------------------
    |
    | Bearer token used for API calls. This may be a long-lived user token (UT)
    | or a short-lived workspace token (WT, ~24h). Prefer storing a UT in
    | PLAUD_USER_TOKEN and minting a WT with Plaud::useWorkspace($id).
    |
    | Tokens copied from DevTools on /file/simple/web or /device/list are often
    | WTs and expire within a day. The longer-lived UT is in localStorage
    | `pld_tokenstr` / cookie `pld_ut`.
    |
    */

    'access_token' => env('PLAUD_ACCESS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Regional API host
    |--------------------------------------------------------------------------
    |
    | Default is the global discovery host. EU/APAC accounts live on
    | api-euc1.plaud.ai / api-apse1.plaud.ai. The client follows `-302`
    | region redirects and can also map a JWT `region` claim automatically.
    |
    | You may pass a full URL or a short name: us, eu, apse1, apac.
    |
    */

    'base_url' => env('PLAUD_BASE_URL', 'https://api.plaud.ai'),

    /*
    |--------------------------------------------------------------------------
    | User token (UT) and v3 refresh cookie
    |--------------------------------------------------------------------------
    |
    | Newer web sessions use httpOnly cookies `pld_ut` (user token) and
    | `pld_urt` (refresh, scoped to /auth/refresh-user-token) instead of a
    | body access_token. Set these when you captured a v3 session.
    |
    */

    'user_token' => env('PLAUD_USER_TOKEN'),
    'refresh_token' => env('PLAUD_REFRESH_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Optional workspace / device
    |--------------------------------------------------------------------------
    |
    | When PLAUD_WORKSPACE_ID is set together with a user token, the service
    | provider mints a workspace token at boot so data endpoints that require
    | a WT succeed.
    |
    */

    'workspace_id' => env('PLAUD_WORKSPACE_ID'),
    'device_id' => env('PLAUD_DEVICE_ID'),

];
