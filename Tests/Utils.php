<?php

namespace Tpf\Tests;

use Symfony\Component\HttpFoundation\Request;
use Tpf\Database\Repository;
use Tpf\Model\Session;
use Tpf\Model\User;
use Tpf\Service\Router\Router;

class Utils
{
    public static function getAuthToken(string $login, string $password): ?string
    {
        global $TPF_CONFIG, $TPF_REQUEST;
        $payload = json_encode(['login' => $login, 'password' => $password, 'type' => 'oauth']);
        $request = Request::create($TPF_CONFIG['auth_url'] ?? '/login', 'POST', [], [], [], [], $payload);
        Router::route($request);

        $user = (new Repository(User::class))->findOneBy(['username' => $login]);
        $session = (new Repository(Session::class))->findOneBy(['user_id' => $user->id]);

        return $session ? $session->secureSessionId : null;
    }

    public static function endSession(int $userId, string $token)
    {
        global $TPF_CONFIG, $TPF_REQUEST;
        $session = (new Repository(Session::class))->findOneBy(['user_id' => $userId, 'secure_session_id' => $token]);
        if ($session) {
            $session->delete();
            /** @var PDO $dbal */
            $dbal->exec('ALTER TABLE `'. $TPF_CONFIG['db']['database'] .'`.`session` AUTO_INCREMENT=0;');
            unset($TPF_REQUEST['session']);
        }
    }
}