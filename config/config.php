<?php
return [
    'app' => [
        'name' => 'Alzikrayat',
        'tagline' => 'Every photo is a memory worth keeping.',
        'debug' => (getenv('APP_DEBUG') ?: '1') === '1',
        'timezone' => getenv('APP_TIMEZONE') ?: 'Africa/Khartoum',
    ],

    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => (int) (getenv('DB_PORT') ?: 3306),
        'name' => getenv('DB_NAME') ?: 'alzikrayat',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],

    'upload' => [
        'directory' => dirname(__DIR__) . '/public/images/uploads',
        'publicPath' => 'images/uploads',
        'maxBytes' => 5 * 1024 * 1024,
        'allowedMime' => [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ],
    ],

    'auth' => [
        'lastLoginCookie' => 'alz_last_login',
        'lastLoginDays' => 7,
        'maxLoginAttempts' => 5,
        'lockoutSeconds' => 60,
    ],

    'gallery' => [
        'perPage' => 12,
        'styleCookie' => 'alz_gallery_style',
        'styles' => [
            'grid3' => ['label' => '3 Columns',   'icon' => 'bi-grid-3x3-gap'],
            'grid4' => ['label' => '4 Columns',   'icon' => 'bi-grid'],
            'list' => ['label' => 'List Cards',  'icon' => 'bi-view-list'],
            'masonry' => ['label' => 'Masonry',     'icon' => 'bi-bricks'],
            'slider' => ['label' => 'Full Slider', 'icon' => 'bi-collection-play'],
        ],
    ],
];
