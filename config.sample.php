<?php
    $TPF_CONFIG = [
        'secret' => 'KFSO2fKMyHao+Ll9RPGOrqvGXVvulds0rkvJv3ysAzojDCUg',
        'default_realm' => 'shop',
        'authentication_method' => 'password',
        'password_encryption' => 'PASSWORD_BCRYPT',
        'password_encryption_strength' => '11',
        'db' => [
            'host' => 'localhost',
            'database' => 'tpf',
            'user' => 'tpf',
            'password' => 'changeme'
        ],
        'mail' => [
            'server' => 'smtp.example.com',
            'port' => 465,
            'username' =>  'user',
            'password' => 'password',
            'secure' => true,
        ],
        'images' => [
            'image' => [
                'width' => 1200,
                'height' => 800
            ],
            'avatar' => [
                'full_width' => 180,
                'full_height' => 280,
                'width' => 80,
                'height' => 80
            ]
        ],
        'realms' => [
            'blog' => ['item' => 'post', 'category' => 'topic'],
            'forum' => ['item' => 'post', 'category' => 'thread'],
            'shop' => ['item' => 'item', 'category' => 'category'],
            'support' => ['item' => 'ticket', 'category' => null]
        ],
        'upload_dir' => '/media/'
    ];

?>