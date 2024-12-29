<?php

namespace Tpf\Tests;

use Symfony\Component\HttpFoundation\Request;
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

}
