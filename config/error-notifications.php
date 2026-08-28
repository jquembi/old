<?php

return [
    'enabled' => env('ERROR_NOTIFICATIONS_ENABLED', false),
    'to' => env('ERROR_NOTIFICATION_EMAIL', env('MAIL_FROM_ADDRESS')),
    'throttle_minutes' => (int) env('ERROR_NOTIFICATION_THROTTLE_MINUTES', 10),
];
