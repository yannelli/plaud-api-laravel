<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Plaud API Access Token
    |--------------------------------------------------------------------------
    |
    | This is the access token for authenticating with the Plaud API.
    | You can obtain this token by authenticating with your username and password
    | using the PlaudService::authenticate() method, or set it here directly
    | if you already have a valid token.
    |
    | It's recommended to store this in your .env file or store this dynamically and set with Config::set()
    | PLAUD_ACCESS_TOKEN=your-access-token-here
    |
    */

    'access_token' => env('PLAUD_ACCESS_TOKEN'),

];
