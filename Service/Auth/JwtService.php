<?php

namespace Tpf\Service\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Tpf\Model\User;


class JwtService
{
    public static function createJwtToken(User $user): string
    {
        global $TPF_CONFIG;

        $now = time();
        $token = array(
            "iss" => $_SERVER['SERVER_NAME'],
            "aud" => $_SERVER['SERVER_NAME'],
            "iat" => $now,
            "nbf" => $now,
            "exp" => $now + $TPF_CONFIG['authentication_methods']['jwt']['lifetime'] ?? 3600 * 24,
            "data" => array(
                "id" => $user->id,
                "firstname" => $user->firstname,
                "lastname" => $user->lastname,
                "email" => $user->email
            )
        );

        return JWT::encode($token, $TPF_CONFIG['secret'], 'HS256');
    }

    public static function verifyJwtToken(string $token): ?object
    {
        global $TPF_CONFIG;

        try {
            $decoded = JWT::decode($token, new Key($TPF_CONFIG['secret'], 'HS256'));
            $decoded->data->expiresAt = $decoded->exp;
            return $decoded->data;
        } catch (\Exception $ex) {
            return null;
        }
    }

}