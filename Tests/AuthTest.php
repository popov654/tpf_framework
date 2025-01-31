<?php

namespace Tpf\Tests;

use AppKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Tpf\Database\Repository;
use Tpf\Model\Session;
use Tpf\Service\Auth\Auth;
use Tpf\Service\Router\Router;

class AuthTest extends BasicTest
{

    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    public function testGetLoginForm()
    {
        global $TPF_REQUEST;
        $TPF_REQUEST = [];
        $request = Request::create('/login');
        $response = Router::route($request);
        self::assertFalse(isset($TPF_REQUEST['controller']));
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsStringIgnoringCase('<form', $response->getContent());
    }

    public function testIncorrectAuthCredentials()
    {
        global $TPF_REQUEST;
        $TPF_REQUEST = [];
        dbConnect();
        $request = Request::create('/login', 'POST', ['login' => 'admin', 'password' => 'fake']);
        $response = Router::route($request);
        self::assertFalse(isset($TPF_REQUEST['controller']));
        self::assertSame(403, $response->getStatusCode());
    }

    public function testCorrectAuthCredentials()
    {
        global $TPF_REQUEST;
        $TPF_REQUEST = [];
        dbConnect();
        $request = Request::create('/login', 'POST', ['login' => 'admin', 'password' => 'password']);
        $response = Router::route($request);
        self::assertTrue($response instanceof RedirectResponse);
        self::assertSame('/', $response->headers->get('Location'));
    }

    public function testLogout()
    {
        global $TPF_CONFIG, $TPF_REQUEST, $dbal;
        $TPF_REQUEST = [];
        dbConnect();
        $session = (new Repository(Session::class))->findOneBy(['user_id' => 1]);
        $request = Request::create('/logout', 'GET', ['hash' => substr($session->secureSessionId, -8)]);
        //$request->cookies->add(['ssid' => $session->secureSessionId . '; Expires=' . date(DATE_RFC7231, time() + 3600 * 24)]);
        $_COOKIE[$TPF_CONFIG['authentication_methods']['cookie']['cookie_name']] = $session->secureSessionId;

        $response = AppKernel::process($request);

        /** @var \PDO $dbal */
        $dbal->exec('ALTER TABLE `'. $TPF_CONFIG['db']['database'] .'`.`session` AUTO_INCREMENT=0;');

        self::assertTrue($response instanceof RedirectResponse);
        self::assertSame('/', $response->headers->get('Location'));
    }
}
