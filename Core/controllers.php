<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Tpf\Database\Repository;
use Tpf\Model\AbstractEntity;
use Tpf\Model\User;
use Tpf\Model\Category;
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
    $type = getEntityType($request->get('type'), $tables);
    if (!$type) {
        return new JsonResponse(['error' => 'Unknown type'], 400);
    }

    $className = getFullClassNameByType($type);

    $schema = $className::getSchema($type);

    return new JsonResponse($schema, 200);
}

function getEntities(Request $request): Response
{
    global $TPF_REQUEST, $dbal;

    $tables = getRealmEntityNames();

    if (isset($TPF_REQUEST['session']) && $TPF_REQUEST['session']->user->role == User::ROLE_ADMIN) {
        $tables[] = 'user';
    }

    $type = getEntityType($request->get('type'), $tables);
    if (!$type) {
        return new JsonResponse(['error' => 'Unknown type'], 400);
    }

    $className = getFullClassNameByType($type);

    $repository = new Repository($className);
    $repository->setOffset($request->get('offset') ?? 0);
    $repository->setLimit($request->get('count') ?? 25);
    if ($request->get('category')) {
        $repository->filterByCategory($request->get('category'), $request->get('excludeSubCats') !== null);
    }
    $entities = $repository->fetch();

    $fields = ['id', 'name', 'image', 'createdAt', 'modifiedAt'];
    $result = [];
    foreach ($entities as $entity) {
        $result[] = $entity->getFields($fields);
    }

    return new JsonResponse($result, 200);
}

function getEntity(Request $request): Response
{
    if (!$request->get('id')) {
        return new JsonResponse(['error' => 'Bad request'], 400);
    }

    global $TPF_REQUEST, $dbal;

    $tables = getRealmEntityNames();

    if (isset($TPF_REQUEST['session']) && $TPF_REQUEST['session']->user->role == User::ROLE_ADMIN) {
        $tables[] = 'user';
    }

    $type = getEntityType($request->get('type'), $tables);
    if (!$type) {
        return new JsonResponse(['error' => 'Unknown type'], 400);
    }
    $className = getFullClassNameByType($type);

    $repository = new Repository($className);
    $entity = $repository->fetchOne($request->get('id'));

    if (!$entity) {
        return new JsonResponse(['error' => 'Element not found'], 404);
    }

    $fields = array_keys($className::getSchema($type));

    return new JsonResponse($entity->getFields($fields), 200);
}

function getEntityComments(Request $request): Response
{
    if (!$request->get('id')) {
        return new JsonResponse(['error' => 'Bad request'], 400);
    }

    global $TPF_REQUEST, $dbal;

    $tables = getRealmEntityNames();

    if (isset($TPF_REQUEST['session']) && $TPF_REQUEST['session']->user->role == User::ROLE_ADMIN) {
        $tables[] = 'user';
    }

    $type = getEntityType($request->get('type'), $tables);
    if (!$type) {
        return new JsonResponse(['error' => 'Unknown type'], 400);
    }
    $className = getFullClassNameByType($type);

    $repository = new Repository($className);
    $entity = $repository->fetchOne($request->get('id'));

    if (!$entity) {
        return new JsonResponse(['error' => 'Element not found'], 404);
    }

    $commentsRepository = new Repository(Comment::class);
    $commentsRepository->setOffset($request->get('offset') ?? 0);
    $commentsRepository->setLimit($request->get('count') ?? 100);
    $comments = $commentsRepository->whereEq(['type' => $type, 'entity_id' => $entity->id])->fetch();

    $fields = array_keys($className::getSchema('comment'));

    $result = [];

    foreach ($comments as $comment) {
        $result[] = $comment->getFields($fields);
    }

    return new JsonResponse($result, 200);
}

function getCategoriesByType(Request $request): Response
{
    if (!$request->get('type')) {
        return new JsonResponse(['error' => 'Bad request'], 400);
    }
    $repository = new Repository(Category::class);
    $categories = $repository->whereEq(['type' => $request->get('type')])->fetch();

    $result = [];

    foreach ($categories as $category) {
        $result[] = $category->getFields(array_keys(AbstractEntity::getSchema('category')));
    }

    foreach ($result as &$category) {
        $category['id_path'] = [$category['id']];
        $category['path'] = [$category['name']];

        if ($category['parent'] > 0) {
            $current = $category;
            while ($current != null && $current['parent'] != 0) {
                $current = array_values(array_filter($result, function($item) use (&$current) {
                    return $item['id'] == $current['parent'];
                }));
                if (!empty($current)) {
                    $current = $current[0];
                    array_unshift($category['id_path'], $current['id']);
                    array_unshift($category['path'], $current['name']);
                } else {
                    break;
                }
            }
        }
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



const MAX_UPLOAD_SIZE = 1024*50*1024;

function uploadFile(Request $request): Response
{
    global $TPF_CONFIG;

    $upload_dir = ($TPF_CONFIG['upload_dir'] ?? '/media/');

    $file = $request->files->get('file');
    $name = $file->getClientOriginalName();

    if ($name == ".htaccess" || preg_match("/\.php[^\.]*$/", $name)) exit();

    if ($file->getSize() > MAX_UPLOAD_SIZE) {
        return new JsonResponse(['error' => 'Upload max size exceeeded']);
    }
    $extension = substr($name, strrpos($name, "."));
    $uname = uniqid().$extension;
    $dir = in_array($extension, ['.jpg', '.png', '.jpeg', '.gif', '.bmp', '.tif', '.tiff', '.webp']) ? 'images' :
            (in_array($extension, ['.avi', '.mp4', '.mpg', '.m4v', '.mov', '.mkv', '.flv']) ? 'videos' : 'files');
    $file->move(PATH . '/public' . $upload_dir . $dir, $uname);

    return new JsonResponse(['status' => 'ok', 'url' => $upload_dir . $dir . '/' . $uname]);
}

function removeFile(Request $request): Response
{
    global $TPF_CONFIG;

    $upload_dir = ($TPF_CONFIG['upload_dir'] ?? '/media/');

    if (file_exists(PATH . '/public' . $upload_dir . $request->get('file'))) {
        unlink(PATH . '/public' . $upload_dir . $request->get('file'));
    } else {
        return new JsonResponse(['error' => 'File not found']);
    }

    return new JsonResponse(['status' => 'ok']);
}

function removeFiles(Request $request): Response
{
    global $TPF_CONFIG;

    $upload_dir = ($TPF_CONFIG['upload_dir'] ?? '/media/');

    $files = json_decode($request->get('files'), true);

    foreach ($files as $file) {
        if (file_exists(PATH . '/public' . $upload_dir . $file)) {
            unlink(PATH . '/public' . $upload_dir . $file);
        }
    }

    return new JsonResponse(['status' => 'ok']);
}
