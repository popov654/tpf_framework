<?php

namespace Tpf\Service\Router;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tpf\Model\User;
use Tpf\Service\Logger;
use Tpf\Service\ErrorPage;
use Tpf\Service\UsersService;


class Router
{
    public static function route(Request $request): Response
    {
        global $TPF_CONFIG, $TPF_REQUEST;
        $realm = $TPF_CONFIG['default_realm'] ?? 'blog';

        $TPF_REQUEST['show_errors'] = !isset($TPF_CONFIG['debug']) || !$TPF_CONFIG['debug'];

        require_once PATH . '/vendor/' . VENDOR_PATH . '/Core/controllers.php';

        if ($request->getPathInfo() == '/') {
            return self::routeHome($request);
        }
        if ($request->getPathInfo() == '/admin') {
            return self::routeAdmin($request);
        }
        if ($response = self::processDefaultRoutes($request)) {
            return $response;
        }
        if ($response = self::processSystemAssets($request)) {
            return $response;
        }

        if (file_exists(PATH . '/config/routes.php')) {
            require_once PATH . '/config/routes.php';
            if (isset($TPF_CONFIG['routes'])) {
                foreach($TPF_CONFIG['routes'] as $key => $value) {
                    if ($key != $request->getPathInfo()) {
                        $pattern = preg_replace("/{(\w+)}/", "(?<\\1>[^\/]+)", $key);
                        if (!preg_match('#^' . $pattern . '$#', $request->getPathInfo(), $matches)) {
                            continue;
                        }
                        foreach ($matches as $param => $val) {
                            if (!is_numeric($param)) {
                                $request->attributes->set($param, $val);
                            }
                        }
                    }
                    if (strpos($value,'::')) {
                        list($className, $method) = explode('::', $value);
                        if (!file_exists(PATH . '/src/Controller/' . $className . '.php')) {
                            return ErrorPage::createResponse(404, 'Class ' . $className . ' not found');
                        }
                        require_once PATH . '/src/Controller/' . $className . '.php';
                        if (strpos($className,'/')) {
                            $className = @array_pop(explode('/', $className));
                        }
                        $controller = new $className;
                        if (!method_exists($controller, $method)) {
                            return ErrorPage::createResponse(404, 'Method ' . $method . ' not found in class ' . $className);
                        }
                        return $controller->$method($request);
                    }
                }
            }
        }

        preg_match('|(/[\w\d.~_-]+){1,4}|', $request->getPathInfo(), $matches);
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
            return ErrorPage::makeResponse(404, "Route not found error");
        }
    }

    public static function routeHome(Request $request): Response
    {
        return self::__route(null, null, null, null, $request);
    }

    public static function routeAdmin(Request $request): Response
    {
        global $TPF_CONFIG, $TPF_REQUEST;
        if (isset($TPF_REQUEST['session']) && $TPF_REQUEST['session']->user->role == User::ROLE_CLIENT) {
            return ErrorPage::createResponse(403, "Access restricted");
        } else if (!isset($TPF_REQUEST['session'])) {
            return new RedirectResponse(($TPF_CONFIG['auth_url'] ?? '/login') . '?redirect_uri=admin');
        }
        ob_start();
        require_once PATH . '/vendor/' . VENDOR_PATH . '/Core/admin.php';
        $response = ob_get_clean();
        return new Response($response);
        //return self::__route(null, null, null, null, $request);
    }

    private static function __route(?string $realm, ?string $category, ?string $subcategory, ?string $id, Request $request): Response
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
                    return ErrorPage::makeResponse(404, "ID was not set for view controller");
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
            return ErrorPage::makeResponse(404, "Controller not found error");
        }
    }

    private static function processDefaultRoutes(Request $request): ?Response
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
        if ($request->getPathInfo() == '/updateProfile' && $request->getMethod() == 'POST') {
            return updateProfile($request);
        }
        if ($request->getPathInfo() == '/activate') {
            return activateUser($request);
        }
        if ($request->getPathInfo() == '/db-check') {
            return checkDBConnection($request);
        }
        if ($request->getPathInfo() == '/getUsersRoles') {
            return getUsersRoles($request);
        }
        if ($request->getPathInfo() == '/getAllEntityTypes') {
            return getAllEntityTypes($request);
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
            return $request->get('export') !== null ? exportEntities($request) : getEntities($request);
        }
        if ($request->getPathInfo() == '/saveEntity' || $request->getPathInfo() == '/saveItem') {
            if (!$canEdit) {
                return new Response('Access denied', 403);
            }
            return saveEntity($request);
        }
        if ($request->getPathInfo() == '/importEntities' || $request->getPathInfo() == '/importItems') {
            if (!$canEdit) {
                return new Response('Access denied', 403);
            }
            return importEntities($request);
        }
        if ($request->getPathInfo() == '/setEntityCategory' || $request->getPathInfo() == '/setItemCategory' || $request->getPathInfo() == '/setCategory') {
            if (!$canEdit) {
                return new Response('Access denied', 403);
            }
            return setEntitiesCategory($request);
        }
        if ($request->getPathInfo() == '/deleteEntity' || $request->getPathInfo() == '/deleteItem') {
            if (!$canEdit) {
                return new Response('Access denied', 403);
            }
            return deleteEntities($request);
        }
        if ($request->getPathInfo() == '/restoreEntity' || $request->getPathInfo() == '/restoreItem') {
            if (!$canEdit) {
                return new Response('Access denied', 403);
            }
            return restoreEntities($request);
        }
        if ($request->getPathInfo() == '/editComment') {
            if (!$canEdit) {
                return new Response('Access denied', 403);
            }
            return editComment($request);
        }
        if ($request->getPathInfo() == '/createCategory') {
            if (!$canEdit) {
                return new Response('Access denied', 403);
            }
            return createCategory($request);
        }
        if ($request->getPathInfo() == '/renameCategory') {
            if (!$canEdit) {
                return new Response('Access denied', 403);
            }
            return renameCategory($request);
        }
        if ($request->getPathInfo() == '/deleteCategory') {
            if (!$canEdit) {
                return new Response('Access denied', 403);
            }
            return deleteCategories($request);
        }
        if ($request->getPathInfo() == '/restoreCategory') {
            if (!$canEdit) {
                return new Response('Access denied', 403);
            }
            return restoreCategories($request);
        }
        if ($request->getPathInfo() == '/deleteComment') {
            if (!$canEdit) {
                return new Response('Access denied', 403);
            }
            $request->parameters->add('type', 'comment');
            return deleteEntities($request);
        }
        if ($request->getPathInfo() == '/restoreComment') {
            if (!$canEdit) {
                return new Response('Access denied', 403);
            }
            $request->parameters->add('type', 'comment');
            return restoreEntities($request);
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

    private static function processSystemAssets(Request $request)
    {
        if (preg_match("/\/tpf\/([^\/]+(?<!css|js|icons)\/)*(?:(css|js|icons)\/)?([^\/]+\/)*((?:[a-z0-9_-]+\.)+)((?:\\2|css|js)(?:\.map)?|gif|png|jpg|webp|svg)$/i", $request->getPathInfo(), $matches)) {
            $path = PATH . '/vendor/' . VENDOR_PATH . '/assets/' . ($matches[1] ?? '') . $matches[2] . '/' . ($matches[3] ?? '') . $matches[4] . $matches[5];
            if (file_exists($path)) {
                $types = ['css' => 'text/css', 'js' => 'text/javascript', 'ttf' => 'font/ttf', 'woff' => 'font/woff', 'eot' => 'font/eot', 'html' => 'text/html', 'svg' => 'image/svg+xml', 'gif' => 'image/gif', 'jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
                $response = new Response(file_get_contents($path));
                $type = preg_match("/^(css|js)\.map$/", $matches[5]) ? substr($matches[5], 0, -4) : $matches[5];
                if (isset($types[$type])) {
                    $response->headers->add(['Content-Type' => $types[$type]]);
                } else {
                    $response->headers->add(['Content-Type' => 'text/plain']);
                }
                session_cache_limiter(false);
                $response->headers->add(['Cache-Control' => 'public, max-age=604800']);
                $response->headers->add(['Expires' => gmdate('D, d M Y H:i:s', time() + 3600 * 24 * 365) . ' GMT']);

                return $response;
            }
            return new Response("", Response::HTTP_NOT_FOUND);
        }
    }

    public static function isStaticResource(string $path): bool
    {
        return preg_match("/\.(html|htm|css|js|eot|ttf|woff|woff2|gif|png|jpg|webp|svg)$/i", $path);
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
