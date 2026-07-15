<?php

return [
    'base_uri' => env('SSO_BASE_URI', 'https://dev1.gorontaloprov.go.id'),
    'token_path' => env('SSO_TOKEN_PATH', '/oauth/token'),
    'client_id' => env('SSO_CLIENT_ID', 'a14a37f8-f58b-4026-840f-fef1df670599'),
    'client_secret' => env('SSO_CLIENT_SECRET', "ndWDwWxBakDYD7umNmy33c5yHiuUgPv6jwVn4n1J"),
    'profile_path' => env('SSO_PROFILE_PATH', '/api/v2/me'),
    'base_uri_code' => env('SSO_BASE_URI_CODE', env('SSO_BASE_URI', 'https://sso.gorontaloprov.go.id')),
    'client_id_code' => env('SSO_CLIENT_ID_CODE', env('SSO_CLIENT_ID', 'a1556946-1782-4e43-b1f4-1fde847f1153')),
    'client_secret_code' => env('SSO_CLIENT_SECRET_CODE', env('SSO_CLIENT_SECRET', "CEND863nvwb7otFbsVNFWNqeVWzerjW6QrqKvflg")),
    'redirect_uri' => env('SSO_REDIRECT_URI', 'http://localhost:8001/callback'),
];

