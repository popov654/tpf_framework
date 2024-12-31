<?php

namespace Tpf\Database;

class ValidationException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}