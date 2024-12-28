<?php

namespace Tpf\Service\Auth\Providers;

use Symfony\Component\HttpFoundation\Request;
use Tpf\Model\User;
use Tpf\Model\Session;
use Tpf\Service\Auth\AuthenticatorInterface;
use Tpf\Database\Repository;


class CookieAuthenticator implements AuthenticatorInterface
{

    const NAME = 'cookie';

    public function authenticate(Request $request): ?Session
    {
        global $TPF_CONFIG;

        if (!isset($TPF_CONFIG['authentication_methods'][self::NAME])) {
            return null;
        }
        $session_id = $_COOKIE[$TPF_CONFIG['authentication_methods'][self::NAME]['cookie_name']] ?? null;
        if (!$session_id || empty(trim($session_id))) {
            return null;
        }
        return (new Repository(Session::class))->findOneBy(['type' => self::NAME, 'secure_session_id' => $session_id], true, 1);

//        if (!$session) return null;
//
//        /** @var User $user */
//        if ($user = User::load($session->userId)) {
//            $session->user = $user;
//            return $session;
//        }
//
//        return null;
    }
}