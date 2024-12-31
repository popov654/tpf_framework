<?php

namespace Tpf\Database;

class ORMException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}