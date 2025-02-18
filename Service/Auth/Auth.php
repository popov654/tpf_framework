<?php

namespace Tpf\Service\Auth;

use Symfony\Component\HttpFoundation\Request;
use Tpf\Database\ValidationException;
use Tpf\Model\Session;


class Auth
{
    public static function authenticate(Request $request): ?Session
    {
        global $TPF_CONFIG;

        $methods = $TPF_CONFIG['authentication_methods'] ?? [
            'cookie' => [
                'cookie_name' => 'ssid',
                'lifetime' => 3600 * 24 * 30
            ]
        ];

        foreach ($methods as $name => $method) {
            $className = $method['class'] ?? ucfirst($name) . 'Authenticator';
            /** @var AuthenticatorInterface $authenticator */
            $authenticator = new (__NAMESPACE__ . '\\Providers\\' . $className);
            $session = $authenticator->authenticate($request);
            if ($session) {
                $session->user->lastLoginAt = new \Datetime();
                try {
                    $session->user->validate = false;
                    $session->user->save();
                    $session->user->validate = true;
                } catch (ValidationException $ignore) {}
                return $session;
            }
        }

        return null;
    }
}
