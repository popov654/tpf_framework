<?php

namespace Tpf\Service;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;

class Logger
{
    public static function error(\Exception $e)
    {
        $logger = new MonologLogger('default');
        if (!file_exists(PATH . '/var/log')) {
            if (!file_exists(PATH . '/var')) {
                mkdir(PATH . '/var');
            }
            mkdir(PATH . '/var/log');
            touch(PATH . '/var/log/error.log');
        }
        $stream_handler = new StreamHandler(PATH . '/var/log/error.log');
        $logger->pushHandler($stream_handler);
        $logger->error($e->getMessage());
    }
}