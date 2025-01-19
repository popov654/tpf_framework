<?php

namespace Tpf\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Tpf\Database\Query;
use Tpf\Model\Session;
use Tpf\Model\User;
use Tpf\Service\Auth\Auth;
use Tpf\Service\Auth\PasswordHasher;
use Tpf\Service\Router\Router;
use Tpf\Service\Repository\UsersRepositoryService;


class QueryTest extends BasicTest
{

    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    public function testSelectUpdateDelete()
    {
        global $dbal;
        dbConnect();

        $user = new User();
        $user->username = 'test';
        $user->password = 'test';
        $user->email = 'test@tpf';
        $user->save();

        $query = new Query(User::class);
        $query->whereEq(['username' => 'test']);
        self::assertGreaterThanOrEqual(1, $query->count());
        $result = $query->select();
        self::assertGreaterThanOrEqual(1, count($result));
        self::assertEquals('test', $result[0]['username']);
        $id = $result[0]['id'];


        $query->whereEq(['id' => $id]);

        $query->update(['username' => 'test2']);
        $result = $query->select();
        self::assertEquals(1, count($result));
        self::assertEquals('test2', $result[0]['username']);

        $query->delete();
        
        self::assertEquals(0, $query->count());
        $result = $query->select();
        self::assertEquals(0, count($result));

        $dbal->exec('ALTER TABLE `user` AUTO_INCREMENT=0');
    }

    public function testJoinQuery()
    {
        global $dbal;
        dbConnect();

        $data = Utils::seedBlogPosts();

        try {
            $query = new Query(\App\Model\Blog\Post::class);
            $query->join('user', 'author_id');

            $result = $query->select();

            self::assertGreaterThanOrEqual(2, count($result));
            self::assertEquals('Second blog entry', $result[0]['name']);

            $query->where(['`user`.`username` = \'test\'']);

            $result = $query->select();

            self::assertEquals(0, count($result));

        } finally {
            Utils::cleanupPostData($data);
        }
    }

    public function testChildWhere()
    {
        global $dbal;
        dbConnect();

        $data = Utils::seedBlogPosts();

        try {
            $query = new Query(\App\Model\Blog\Post::class);
            $query->whereEq(['author' => ['username' => 'admin']]);

            $result = $query->select();

            self::assertGreaterThanOrEqual(2, count($result));
            self::assertEquals('Second blog entry', $result[0]['name']);

            $query->where(['author' => ['username' => 'test']]);

            $result = $query->select();

            self::assertEquals(0, count($result));

        } finally {
            Utils::cleanupPostData($data);
        }
    }

    public function testQueryWithCounters()
    {
        global $dbal;
        dbConnect();

        $data = Utils::seedBlogPosts();

        try {
            $usersRepositoryService = new UsersRepositoryService();
            $result = $usersRepositoryService->getUsersWithCounters(['blog_post' => 'author_id'], ['id' => 1]);

            self::assertEquals(1, count($result));
            self::assertGreaterThanOrEqual(2, $result[0]['blog_post_count']);
        } finally {
            Utils::cleanupPostData($data);
        }
    }

}