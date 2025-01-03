<?php

namespace Tpf\Service\Router;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tpf\Model\User;
use Tpf\Service\Logger;


class Router
{
    public static function route(Request $request): Response
    {
        global $TPF_CONFIG, $TPF_REQUEST;
        $realm = $TPF_CONFIG['default_realm'] ?? 'blog';

        require_once __DIR__ . "/../../Core/controllers.php";

        if ($request->getPathInfo() == '/') {
            return self::__routeHome($request);
        }
        if ($request->getPathInfo() == '/admin') {
            return self::__routeAdmin($request);
        }
        if ($response = self::processDefaultRoutes($request)) {
            return $response;
        }

        preg_match('|(/[\w\d~_-]+){1,4}|', $request->getPathInfo(), $matches);
        if ($matches[0]) {
            $parts = explode("/", substr($matches[0], 1));
            if (isset($TPF_CONFIG['realms']) && array_key_exists($parts[0], $TPF_CONFIG['realms'])) {
                $realm = $parts[0];
                $parts = array_slice($parts, 1);
            }
            $category = $parts[0] ?? null;
            $subcategory = $parts[1] ?? null;
            $id = $parts[2] ?? null;
            if (count($parts) == 1 && is_numeric($parts[0])) {
                $category = $subcategory = null;
                $id = $parts[0];
            } else if (count($parts) == 2 && is_numeric($parts[1])) {
                $subcategory = null;
                $id = $parts[1];
            }
            return self::__route($realm, $category, $subcategory, $id, $request);
        } else {
            Logger::error(new \Exception("Route not found for " . $request->getPathInfo()));
            return new Response("Route not found error", 404);
        }
    }

    public static function __routeHome(Request $request): Response
    {
        return self::__route(null, null, null, null, $request);
    }

    public static function __routeAdmin(Request $request): Response
    {
        global $TPF_CONFIG, $TPF_REQUEST;
        if (isset($TPF_REQUEST['session']) && $TPF_REQUEST['session']->user->role == User::ROLE_CLIENT) {
            return new Response("Access restricted", 403);
        } else if (!isset($TPF_REQUEST['session'])) {
            return new RedirectResponse(($TPF_CONFIG['auth_url'] ?? '/login') . '?redirect_uri=admin');
        }
        return self::__route(null, null, null, null, $request);
    }

    public static function __route(?string $realm, ?string $category, ?string $subcategory, ?string $id, Request $request): Response
    {
        $isHome = empty($realm);
        if ($isHome && $request->getPathInfo() == '/admin') {
            $className = 'AdminController';
        } else {
            $className = self::getClassnameByRealm($realm);
        }

        if ($isHome && $className == 'AdminController' && !file_exists(PATH . '/src/Controller/' . $className .'.php')) {
            require_once PATH . '/vendor/tpf/framework/Controller/' . $className . '.php';
        } else if ($isHome && $className == 'AdminController' && file_exists(PATH . '/src/Controller/Admin/HomeController.php')) {
            require_once PATH . '/src/Controller/Admin/HomeController.php';
        } else {
            require_once PATH . '/src/Controller/' . (!$isHome ? $realm . '/' : '') . $className . '.php';
        }

        $controller = new $className;
        $action = $request->get('action') ?? (($id ?? $request->get('id')) ? 'view' : 'list');
        if ($isHome && !$request->get('action')) {
            $action = 'view';
        }

        try {
            /** @var Response $response */
            if ($action != 'list' && !$isHome) {
                if ($id === null) {
                    Logger::error(new \Exception("ID was not set for view controller"));
                    return new Response("ID was not set for view controller", 404);
                }
                $response = $controller->$action($request, $id);
            } else {
                $response = $controller->$action($request);
            }
            global $TPF_REQUEST;
            $TPF_REQUEST['controller'] = ['instance' => $controller, 'method' => $action];

            return $response;
        } catch (\Exception $e) {
            Logger::error($e);
            return new Response("Controller not found error", 404);
        }
    }

    public static function processDefaultRoutes(Request $request): ?Response
    {
        global $TPF_REQUEST;
        $canEdit = isset($TPF_REQUEST['session']) && $TPF_REQUEST['session']->user->role != User::ROLE_CLIENT;
        if ($request->getPathInfo() == ($TPF_CONFIG['auth_url'] ?? '/login')) {
            return login($request);
        }
        if ($request->getPathInfo() == ($TPF_CONFIG['logout_url'] ?? '/logout')) {
            return logout($request);
        }
        if ($request->getPathInfo() == '/tpl-test') {
            $data = ['x' => 5, 'user' => ['accounts' => [0 => 'ecad36fc', 1 => 'fd8e2cba']]];
            return new Response(render('example', $data));
        }
        if ($request->getPathInfo() == '/activate') {
            return activateUser($request);
        }
        if ($request->getPathInfo() == '/db-check') {
            return checkDBConnection($request);
        }
        if ($request->getPathInfo() == '/getSchema' || $request->getPathInfo() == '/getEntitySchema') {
            if (!$canEdit) {
                return new Response('Access denied', 403);
            }
            return getEntitySchema($request);
        }
        if ($request->getPathInfo() == '/getEntity' || $request->getPathInfo() == '/getItem') {
            return getEntity($request);
        }
        if ($request->getPathInfo() == '/getComments' || $request->getPathInfo() == '/getItemComments') {
            return getEntityComments($request);
        }
        if ($request->getPathInfo() == '/getCategories') {
            return getCategoriesByType($request);
        }
        if ($request->getPathInfo() == '/getEntities' || $request->getPathInfo() == '/getItems') {
            return getEntities($request);
        }
        if ($request->getPathInfo() == '/saveEntity' || $request->getPathInfo() == '/saveItem') {
            if (!$canEdit) {
                return new Response('Access denied', 403);
            }
            return saveEntity($request);
        }
        if ($request->getPathInfo() == '/upload' && $request->getMethod() == 'POST') {
            return uploadFile($request);
        }
        if ($request->getPathInfo() == '/removeFile' && $request->getMethod() == 'POST') {
            return removeFile($request);
        }
        if ($request->getPathInfo() == '/removeFiles' && $request->getMethod() == 'POST') {
            return removeFiles($request);
        }

        return null;
    }

    public static function getClassnameByRealm(?string $realm): string
    {
        global $TPF_CONFIG;
        if (empty($realm)) {
            return "HomeController";
        }
        return ucfirst($TPF_CONFIG['realms'][$realm]['item']) . "Controller";
    }

}
