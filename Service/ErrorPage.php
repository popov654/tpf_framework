<?php

namespace Tpf\Service;

use Symfony\Component\HttpFoundation\Response;

class ErrorPage
{
    public static function createResponse(int $code, ?string $description = null): Response
    {
        global $TPF_REQUEST;
        if (file_exists(PATH . '/vendor/' . VENDOR_PATH . '/templates/error/' . $code . '.tpf')) {
            return new Response(render('error/' . $code, ['description' => $description, 'error' => $TPF_REQUEST['error'] ?? null]), $code);
        }
        $statuses = [400 => 'Bad request', 403 => 'Forbidden', 404 => 'Not found', 500 => 'Server error'];
        return new Response($statuses[$code] ?? '', $code);
    }
}