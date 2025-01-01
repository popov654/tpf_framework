<?php

namespace Tpf\Tests;

use Symfony\Component\HttpFoundation\Request;
use Tpf\Database\AbstractEntity;
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
        global $TPF_CONFIG, $TPF_REQUEST, $dbal;
        $session = (new Repository(Session::class))->findOneBy(['user_id' => $userId, 'secure_session_id' => $token]);
        if ($session) {
            $session->delete();
            /** @var \PDO $dbal */
            $dbal->exec('ALTER TABLE `'. $TPF_CONFIG['db']['database'] .'`.`session` AUTO_INCREMENT=0;');
            unset($TPF_REQUEST['session']);
        }
    }

    public static function seedBlogPosts()
    {
        $time = (new \DateTime())->format('Y-m-d H:i:s');
        $posts = [
            [
                'name' => 'First blog entry',
                'text' => 'Content 1',
                'image' => 'website-3483020_640.png',
                'author_id' => 1,
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => $time,
                'modified_at' => $time
            ],
            [
                'name' => 'Second blog entry',
                'text' => 'Content 2',
                'image' => 'website-3374825_1920.jpg',
                'author_id' => 1,
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => $time,
                'modified_at' => $time
            ]
        ];
        require_once PATH . '/src/Model/Entity.php';
        require_once PATH . '/src/Model/Blog/Post.php';
        foreach ($posts as $postData) {
            $post = new App\Model\Blog\Post();
            AbstractEntity::fillFromArray($post, $postData);
            $post->save();
        }
    }
}