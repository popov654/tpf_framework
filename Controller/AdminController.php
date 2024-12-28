<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController
{
    public function view(Request $request): Response
    {
        global $TPF_REQUEST;

        return new Response(render('admin'));
        //return new Response($TPF_REQUEST['session'] ? "<h1>Administration Panel</h1><p>Welcome, " . $TPF_REQUEST['session']->user->username . "</p>" : "Access denied");
    }
}