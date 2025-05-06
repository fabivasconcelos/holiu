<?php
$env = parse_ini_file(__DIR__ . '/.env');

return [
    'db' => [
        'host' => $env['DB_HOST'],
        'name' => $env['DB_DATABASE'],
        'user' => $env['DB_USERNAME'],
        'pass' => $env['DB_PASSWORD'],
    ],
    'app_url' => $env['APP_URL'],
    'mercado_pago_token' => $env['MERCADO_PAGO_TOKEN'],
    'zapier_webhook' => $env['ZAPIER_WEBHOOK'],
    'mercado_pago_api_url' => $env['MERCADO_PAGO_API_URL'],
    'mercado_pago_preference_url' => $env['MERCADO_PAGO_PREFERENCE_URL'],
    'notification_url' => $env['NOTIFICATION_URL'],
    'holiu_member_area_url' => $env['HOLIU_MEMBER_AREA_URL'],
];