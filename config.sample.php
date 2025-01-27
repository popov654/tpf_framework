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
        'realms' => [
            'Blog' => ['item' => 'post', 'category' => 'topic'],
            'Forum' => ['item' => 'post', 'category' => 'thread'],
            'Shop' => ['item' => 'item', 'category' => 'category'],
            'support' => ['item' => 'ticket', 'category' => null]
        ],
    ];

?>