<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tpf\Database\AbstractEntity;
use Tpf\Model\User;
use Tpf\Service\Auth\LoginService;
use Tpf\Service\UsersService;

function configureFramework(Request $request): Response
{
    global $TPF_CONFIG;

    $firstRun = !isset($TPF_CONFIG['secret']) || $TPF_CONFIG['secret'] == md5('changeme');
    if ($request->getMethod() == 'post' &&
        ($request->get('db_host') || $request->get('database') ||
         $request->get('db_user') || $request->get('db_password') || $firstRun)) {
        try {
            $errors = configure();
            return new JsonResponse(['status' => 'finished', 'errors' => $errors]);
        } catch (Exception $e) {
            return new JsonResponse(['status' => 'fail', 'exception' => $e->getMessage()]);
        }
    }

    return new JsonResponse(['status' => 'noop']);
}

function login(Request $request): Response
{
    if ($request->getMethod() == 'GET') {
        return new Response(render('login'));
    }
    $session = LoginService::login($request);
    if ($session) {
        if ($request->get('redirect_uri')) {
            return new RedirectResponse($request->get('redirect_uri'));
        }
        return $session->type == 'cookie' ? new RedirectResponse($TPF_CONFIG['url_to_redirect_after_login'] ?? '/') :
            new Response('', Response::HTTP_NO_CONTENT);
    }

    return new Response('Access denied', Response::HTTP_FORBIDDEN);
}

function logout(Request $request): Response
{
    global $TPF_REQUEST;
    if (!isset($TPF_REQUEST['session'])) {
        return new RedirectResponse('/');
    }
    LoginService::logout($request);

    return new RedirectResponse('/');
}

function activateUser(Request $request): Response
{
    if (!$request->get('user')) {
        return new Response("Bad request", 400);
    }
    /** @var User $user */
    $user = User::load($request->get('user'));
    if (!$user) {
        return new Response("Bad request", 400);
    }
    if ($user->activationToken == $request->get('token')) {
        UsersService::activateUser($user);
    }

    return new Response("Activation successful");
}

function checkDBConnection(Request $request): Response
{
    $dbname = $request->get('db_name');
    if ($request->get('port')) {
        $dbname .= ':' . $request->get('port');
    }
    $result = false;
    try {
        new PDO(($request->get('db_type') ?? 'mysql') . ':dbname=' . $dbname . ';host=' . $request->get('db_host'),
            $request->get('db_user') ?? '', $request->get('db_pass') ?? '');
        $result = true;
    } catch (PDOException $e) {}

    return new JsonResponse(['result' => $result]);
}

function getEntitySchema(Request $request): Response
{
    if (!$request->get('type')) {
        return new JsonResponse(['error' => 'Bad request'], 400);
    }
    $tables = array_merge(['user'], getRealmEntityNames());
    $type = $request->get('type');
    if (!in_array($type, $tables)) {
        $entities = array_values(array_filter($tables, function ($table) use ($type) {
            return preg_match("/^" . $type . "_/", $table);
        }));
        if (empty($entities)) {
            return new JsonResponse(['error' => 'Type not found'], 404);
        }
        $type = $entities[0];
    }

    global $dbal;
    /** @var PDO $dbal */
    $columns = $dbal->query("SHOW COLUMNS FROM `" . $type . "`")->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($columns as $col) {
        $type = 'text';
        if (preg_match("/^int/", $col['Type'])) {
            $type = 'int';
        } else if (preg_match("/^float/", $col['Type'])) {
            $type = 'float';
        } else if (preg_match("/^tinyint/", $col['Type'])) {
            $type = 'bool';
        } else if (preg_match("/^json/", $col['Type'])) {
            $type = 'array';
        } else if (preg_match("/^date/", $col['Type'])) {
            $type = 'date';
        } else if (preg_match("/^time/", $col['Type'])) {
            $type = 'time';
        }
        if ($type == 'text' && preg_match("/(^|_)(photo|image|picture)(_|$)/", $col['Field'])) {
            $type = 'image';
        }
        if (($type == 'text' || $type == 'array') && preg_match("/(^|_)(photos|images|pictures)(_|$)/", $col['Field'])) {
            $type = 'image_list';
        }
        if (in_array($col['Field'], ['author_id', 'created_at', 'modified_at'])) {
            continue;
        }
        $result[$col['Field']] = $type;
    }

    return new JsonResponse($result, 200);
}

function saveEntity(Request $request): Response
{
    global $TPF_REQUEST;

    if (!$request->get('type')) {
        return new JsonResponse(['error' => 'Bad request'], 400);
    }

    $tables = getRealmEntityNames();

    if (isset($TPF_REQUEST['session']) && $TPF_REQUEST['session']->user->role == User::ROLE_ADMIN) {
        $tables[] = 'user';
    }

    $type = getEntityType($request->get('type'), $tables);
    if (!$type) {
        return new JsonResponse(['error' => 'Unknown type'], 400);
    }
    $className = getFullClassNameByType($type);

    try {
        $data = json_decode($request->getContent(), true);

        if (!in_array(strtolower($type), $tables)) {
            return new JsonResponse(['error' => 'Unknown type'], 400);
        }

        if (!$request->get('id')) {
            $entity = new $className();
        } else {
            $entity = $className::load($request->get('id'));
        }

        if (!isset($data['modifiedAt'])) {
            $data['modifiedAt'] = new \DateTime();
        }
        AbstractEntity::fillFromArray($entity, $data);
        $entity->save();

        return new JsonResponse(['result' => 'ok'], 200);
    } catch (Exception $e) {
        return new JsonResponse(['error' => 'Bad request', 'exception' => $e->getMessage()], 400);
    }
}

function getEntityType(string $type, array $tables): ?string
{
    if (!in_array($type, $tables)) {
        $entities = array_values(array_filter($tables, function ($table) use ($type) {
            return preg_match("/^" . $type . "_/", $table);
        }));
        if (empty($entities)) {
            return null;
        }
        $type = $entities[0];
    }

    return $type;
}

function getFullClassNameByType(string $type): string
{
    $class = ucfirst(preg_replace("/^(([a-z0-9])+_)*/", "", $type));
    if ($class != 'User') {
        $path = ucfirst(preg_replace_callback("/_[a-z]/", function ($match) {
            return '/' . strtoupper($match[0][1]);
        }, $type));
        require_once PATH . '/src/Model/' . $path . '.php';
        $className = 'App\\Model\\' . str_replace('/', '\\', $path);
    } else {
        $className = User::class;
    }

    return $className;
}
