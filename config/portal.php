<?php

return [
    'api_base_url' => rtrim(env('PORTAL_API_BASE_URL', env('APP_URL', 'http://localhost')), '/'),
];
