<?php

require_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/config.php';

$config = include(__DIR__ . '/config.php');
$secretKey = $config['secretKey'];

function generateToken($userId)
{
    global $secretKey;

    $tokenPayload = [
        'user_id' => $userId,
        'exp' => time() + 3600,
    ];

    return Firebase\JWT\JWT::encode($tokenPayload, $secretKey, 'HS256');
}
