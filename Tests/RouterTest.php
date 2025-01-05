<?php

namespace Tpf\Tests;

use Symfony\Component\HttpFoundation\Request;
use Tpf\Database\Repository;
use Tpf\Model\Session;
use Tpf\Model\User;
use Tpf\Model\Category;
use Tpf\Model\Comment;
use Tpf\Service\Auth\Auth;
use Tpf\Service\Router\Router;

class RouterTest extends BasicTest
{

    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    public function testHomeRoute()
    {
        global $TPF_REQUEST;
        $TPF_REQUEST = [];
        $request = Request::create('/');
        Router::route($request);
        self::assertEquals('HomeController', get_class($TPF_REQUEST['controller']['instance']));
        self::assertEquals('view', $TPF_REQUEST['controller']['method']);
    }

    public function testAdminRoute()
    {
        global $TPF_REQUEST;
        $TPF_REQUEST = [];
        $request = Request::create('/admin');
        Router::route($request);

        /* Should redirect to login page when there are no credentials
           or return 403 if current user is has a CLIENT role */
        self::assertFalse(isset($TPF_REQUEST['controller']));

        //self::assertEquals('AdminController', get_class($TPF_REQUEST['controller']['instance']));
        //self::assertEquals('view', $TPF_REQUEST['controller']['method']);
    }

    public function testGetUser()
    {
        global $TPF_REQUEST;
        $TPF_REQUEST = [];

        dbConnect();

        $request = Request::create('/getEntity?type=user&id=1');
        $response = Router::route($request);
        self::assertEquals('application/json', $response->headers->get('Content-Type'));

        /* Should give an error when we are not authorized */
        self::assertGreaterThanOrEqual(400, $response->getStatusCode());

        $accessToken = Utils::getAuthToken('admin', 'password');

        if ($accessToken != null) {

            $request = Request::create('/getEntity?type=user&id=1');
            $request->headers->add(['Authorization' => 'Bearer ' . $accessToken]);
            $TPF_REQUEST['session'] = Auth::authenticate($request);
            $response = Router::route($request);

            self::assertEquals(200, $response->getStatusCode());
            self::assertEquals('application/json', $response->headers->get('Content-Type'));

            $user = json_decode($response->getContent());
            self::assertEquals(1, $user->id);
            self::assertEquals('admin', $user->username);
            self::assertEquals(User::ROLE_ADMIN, $user->role);

            Utils::endSession(1, $accessToken);
        }
    }

    public function testGetPosts()
    {
        global $TPF_REQUEST, $dbal;
        $TPF_REQUEST = [];

        dbConnect();

        require_once PATH . '/src/Model/Entity.php';
        require_once PATH . '/src/Model/Blog/Post.php';

        $data = Utils::seedBlogPosts();

        $exception = null;

        try {

            $request = Request::create('/getEntity?type=blog_post&id=' . $data['posts'][0]->id);
            $response = Router::route($request);

            self::assertEquals(200, $response->getStatusCode());
            self::assertEquals('application/json', $response->headers->get('Content-Type'));

            $post = json_decode($response->getContent());
            self::assertEquals($data['posts'][0]->id, $post->id);
            self::assertEquals(1, $post->authorId);
            self::assertEquals('First blog entry', $post->name);
            self::assertFalse($post->isDeleted);

            $this->checkGetAllPosts($data);
            $this->checkFilterByCategory($data);
            $this->checkFilterByTags($data);

        } catch (\Exception $e) {
            $exception = $e;
        } finally {
            $this->cleanupPostData($data);
        }

        if ($exception != null) throw $exception;
    }

    private function checkGetAllPosts($data)
    {
        $request = Request::create('/getEntities?type=blog_post');
        $response = Router::route($request);
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('application/json', $response->headers->get('Content-Type'));

        $posts = json_decode($response->getContent());

        self::assertGreaterThanOrEqual(2, count($posts));
        self::assertGreaterThanOrEqual(2, $posts[0]->id);
        self::assertEquals($data['posts'][1]->id, $posts[0]->id);
    }

    private function checkFilterByCategory($data)
    {
        $request = Request::create('/getEntities?type=blog_post&category=' . $data['categories'][0]->id);
        $response = Router::route($request);
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('application/json', $response->headers->get('Content-Type'));

        $posts = json_decode($response->getContent());

        self::assertEquals(1, count($posts));
        self::assertEquals($data['posts'][0]->id, $posts[0]->id);
    }

    private function checkFilterByTags($data)
    {
        $request = Request::create('/getEntities?type=blog_post&tags=' . urlencode('["' . $data['posts'][1]->tags[0] . '"]'));
        $response = Router::route($request);
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('application/json', $response->headers->get('Content-Type'));

        $posts = json_decode($response->getContent());

        self::assertEquals(1, count($posts));
        self::assertEquals($data['posts'][1]->id, $posts[0]->id);
    }

    private function cleanupPostData($data)
    {
        global $dbal;

        $postIds = array_map(function($el) {
            return $el->id;
        }, $data['posts']);
        $categoryIds = array_map(function($el) {
            return $el->id;
        }, $data['categories']);
        $commentIds = array_map(function($el) {
            return $el->id;
        }, $data['comments']);
        (new Repository('\App\Model\Blog\Post'))->where(['`id` IN ('. implode(',', $postIds) .')'])->delete();
        (new Repository(Category::class))->where(['`id` IN ('. implode(',', $categoryIds) .')'])->delete();
        (new Repository(Comment::class))->where(['`id` IN ('. implode(',', $commentIds) .')'])->delete();

        $dbal->exec('ALTER TABLE `blog_post` AUTO_INCREMENT=0');
        $dbal->exec('ALTER TABLE `category` AUTO_INCREMENT=0');
        $dbal->exec('ALTER TABLE `comment` AUTO_INCREMENT=0');
    }

    public function testGetPostSchema()
    {
        global $TPF_REQUEST;
        $TPF_REQUEST = [];

        dbConnect();

        $accessToken = Utils::getAuthToken('admin', 'password');

        $request = Request::create('/getSchema?type=blog_post');
        $request->headers->add(['Authorization' => 'Bearer ' . $accessToken]);
        $TPF_REQUEST['session'] = Auth::authenticate($request);
        $response = Router::route($request);

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('application/json', $response->headers->get('Content-Type'));

        $schema = json_decode($response->getContent(), true);
        self::assertTrue(isset($schema['id']));
        self::assertEquals('text', $schema['name']);
        self::assertEquals('text', $schema['text']);
        self::assertEquals('bool', $schema['isActive']);
        self::assertEquals('bool', $schema['isDeleted']);

        Utils::endSession(1, $accessToken);
    }
}
