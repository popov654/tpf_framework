<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        configure();
    }
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
    } catch (\PDOException $ex) {}

    return new JsonResponse(['result' => $result]);
}
