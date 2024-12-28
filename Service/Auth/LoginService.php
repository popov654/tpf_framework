<?php

namespace Tpf\Service\Auth;

use Symfony\Component\HttpFoundation\Request;
use Tpf\Database\Repository;
use Tpf\Model\Session;
use Tpf\Model\User;

class LoginService
{
    public static function login(Request $request, string $method = null): ?Session
    {
        global $TPF_CONFIG;

        if ($request->get('login') && $request->get('password')) {
            $user = self::findUser($request->get('login') ?? $request->get('email'));
            if ($user) {
                if (PasswordHasher::verifyPassword($user, $request->get('password'))) {
                    $session = self::createSession($user, 'cookie');
                    setcookie($TPF_CONFIG['authentication_methods']['cookie']['cookie_name'] ?? 'ssid', $session->secureSessionId, $session->expiredAt->getTimestamp());
                    return $session;
                }
            }

            return null;
        } else {
            try {
                $data = json_decode(file_get_contents("php://input"));
            } catch (\Exception $e) {
                return null;
            }
            if (!$data) {
                return null;
            }
            $user = self::findUser($data->login ?? $data->email);
            if ($user) {
                if (PasswordHasher::verifyPassword($user, $data->password)) {
                    if (!$data->type) {
                        $data->type = $method;
                    }
                    if ($data->type != 'jwt') {
                        $session = self::createSession($user, 'oauth2');
                        header('Access-Token: ' . $session->secureSessionId);
                        header('Access-Token-Type: oauth');
                    } else {
                        $token = JwtService::createJwtToken($user);
                        header('Access-Token: ' . $token);
                        header('Access-Token-Type: jwt');
                        $session = new Session();
                        $session->type = 'jwt';
                        $session->userId = $user->id;
                        $session->user = $user;
                        $session->secureSessionId = $token;
                    }
                    return $session ?? null;
                }
            }
        }

        return null;
    }

    private static function findUser(?string $loginOrEmail): ?User
    {
        if (!$loginOrEmail || empty(trim($loginOrEmail))) {
            return null;
        }
        $userRepository = new Repository(User::class);
        $user = $userRepository->findOneBy(['username' => $loginOrEmail]);
        if (!$user) {
            $userRepository->findOneBy(['email' => $loginOrEmail]);
        }

        return $user;
    }

    public static function logout(Request $request): void
    {
        global $TPF_REQUEST;

        $ssid = $TPF_REQUEST['session']->secureSessionId;

        if (isset($TPF_REQUEST['session']) && $request->query->get('hash') == substr($ssid, -8)) {
            $TPF_REQUEST['session']->delete();
            unset($TPF_REQUEST['session']);
        }
    }

    private static function createSession(User $user, string $type)
    {
        $now = time();
        $session = new Session();
        $session->type = $method ?? $type;
        $session->userId = $user->id;
        $session->user = $user;
        $session->expiredAt = \DateTime::createFromFormat('U', (string) ($now + ($TPF_CONFIG['authentication_methods'][$type]['lifetime'] ?? 3600 * 24 * 30)));
        try {
            $session->secureSessionId = bin2hex(random_bytes(24));
        } catch (\Exception $ex) {
            $session->secureSessionId = md5(uniqid());
        }
        $session->save();

        return $session;
    }


}