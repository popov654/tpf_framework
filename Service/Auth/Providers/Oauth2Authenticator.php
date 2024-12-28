<?php

namespace Tpf\Service\Auth\Providers;

use Symfony\Component\HttpFoundation\Request;
use Tpf\Database\Repository;
use Tpf\Service\Auth\AuthenticatorInterface;
use Tpf\Model\Session;


class Oauth2Authenticator implements AuthenticatorInterface
{

    const NAME = 'oauth2';

    public function authenticate(Request $request): ?Session
    {
        global $TPF_CONFIG;

        $headerName = $TPF_CONFIG['authentication_methods'][self::NAME]['header'] ?? 'Authorization';
        if (!$request->headers->has($headerName)) {
            return null;
        }
        $header = str_replace($TPF_CONFIG['authentication_methods'][self::NAME]['prefix'], '', $request->headers->get($headerName));

        return (new Repository(Session::class))->findOneBy(['type' => self::NAME, 'secure_session_id' => $header], true, 1);
    }
}