<?php

namespace Tpf\Service\Auth;

use Tpf\Model\User;

class PasswordHasher
{
    const DEFAULT_STRENGTH = 11;

    public static function hashPassword(User $user): void
    {
        global $TPF_CONFIG;
        if (!isset($TPF_CONFIG)) {
            $TPF_CONFIG = [];
        }
        $hashedPassword = password_hash($user->password, eval('return ' . ($TPF_CONFIG['security']['password_encryption'] ?? 'PASSWORD_BCRYPT') . ';'),
            ['cost' => $TPF_CONFIG['security']['password_encryption_strength'] ?? self::DEFAULT_STRENGTH]);
        $user->password = $hashedPassword;
    }

    public static function verifyPassword(User $user, string $password): bool
    {
        return password_verify($password, $user->password);
    }
}