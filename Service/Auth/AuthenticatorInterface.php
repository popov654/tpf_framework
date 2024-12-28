<?php

namespace Tpf\Service\Auth;

use Symfony\Component\HttpFoundation\Request;
use Tpf\Model\Session;

interface AuthenticatorInterface
{
    public function authenticate(Request $request): ?Session;
}