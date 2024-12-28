<?php

namespace Tpf\Service\Auth\Providers;

use Symfony\Component\HttpFoundation\Request;
use Tpf\Model\User;
use Tpf\Service\Auth\AuthenticatorInterface;
use Tpf\Model\Session;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Tpf\Service\Auth\JwtService;


class JwtAuthenticator implements AuthenticatorInterface
{

    const NAME = 'jwt';

    public function authenticate(Request $request): ?Session
    {
        global $TPF_CONFIG;

        $token = '';

        $headerName = $TPF_CONFIG['authentication_methods'][self::NAME]['header'] ?? 'Authorization';
        if ($request->headers->has($headerName)) {
            $token = $request->headers->get($headerName);
        } else {
            $cookieName = $TPF_CONFIG['authentication_methods'][self::NAME]['cookie_name'] ?? $headerName;
            if ($request->cookies->has($cookieName)) {
                $token = $request->cookies->get($headerName);
            }
        }
        if ($data = JwtService::verifyJwtToken($token)) {
            /** @var User $user */
            $session = new Session;
            $session->userId = $data->id;
            $user = User::load($data->id);
            $session->user = $user;
            $session->secureSessionId = $token;
            $session->expiredAt = \DateTime::createFromFormat('U', ((string) $data->expiresAt));

            return $session;
        }

        return null;
    }
}