<?php

namespace Tpf\Tests;

use Symfony\Component\HttpFoundation\Request;
use Tpf\Database\Repository;
use Tpf\Model\AbstractEntity;
use Tpf\Model\Category;
use Tpf\Model\Comment;
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
        $categories = self::seedCategories();

        $time = new \DateTime();
        $posts = [
            [
                'name' => 'First blog entry',
                'text' => 'Content 1',
                'image' => 'website-3483020_640.png',
                'categories' => [$categories[0]->id],
                'authorId' => 1,
                'isActive' => 1,
                'isDeleted' => 0,
                'createdAt' => $time,
                'modifiedAt' => $time
            ],
            [
                'name' => 'Second blog entry',
                'text' => 'Content 2',
                'image' => 'website-3374825_1920.jpg',
                'categories' => [],
                'tags' => ['tag'],
                'authorId' => 1,
                'isActive' => 1,
                'isDeleted' => 0,
                'createdAt' => $time,
                'modifiedAt' => $time
            ]
        ];
        require_once PATH . '/src/Model/Entity.php';
        require_once PATH . '/src/Model/Blog/Post.php';

        $result = [];

        foreach ($posts as $postData) {
            $post = new \App\Model\Blog\Post();
            $result[] = $post;
            AbstractEntity::fillFromArray($post, $postData);
            $post->save();
        }

        $comments = self::seedComments($posts);

        $result = ['posts' => $result, 'categories' => $categories, 'comments' => $comments];

        return $result;
    }

    private static function seedCategories()
    {
        $time = new \Datetime();
        $categories = [
            [
                'type' => 'blog_post',
                'name' => 'Test category',
                'parent' => 0,
                'isActive' => 1,
                'createdAt' => $time,
                'modifiedAt' => $time
            ],
            [
                'type' => 'blog_post',
                'name' => 'Nested category 1',
                'parent' => 0,
                'isActive' => 1,
                'createdAt' => $time,
                'modifiedAt' => $time
            ],
            [
                'type' => 'blog_post',
                'name' => 'Nested category 2',
                'parent' => 0,
                'isActive' => 1,
                'createdAt' => $time,
                'modifiedAt' => $time
            ]
        ];

        $parentId = 0;

        $result = [];

        foreach ($categories as $categoryData) {
            $category = new Category();
            $result[] = $category;
            AbstractEntity::fillFromArray($category, $categoryData);
            $category->parent = $parentId;
            $category->save();
            if ($parentId == 0) {
                $parentId = $category->id;
            }
        }

        return $result;
    }

    private static function seedComments(array $posts)
    {
        $time = new \Datetime();
        $comments = [
            [
                'type' => 'blog_post',
                'entityId' => $posts[0]->id,
                'text' => 'Comment 1',
                'authorId' => 1,
                'createdAt' => $time,
                'modifiedAt' => $time
            ],
            [
                'type' => 'blog_post',
                'entityId' => $posts[0]->id,
                'text' => 'Comment 2',
                'authorId' => 1,
                'createdAt' => $time,
                'modifiedAt' => $time
            ]
        ];

        $result = [];

        foreach ($comments as $commentData) {
            $comment = new Comment();
            $result[] = $comment;
            AbstractEntity::fillFromArray($comment, $commentData);
            $comment->save();
        }

        return $result;
    }
}