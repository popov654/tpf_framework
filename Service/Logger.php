<?php

namespace Tpf\Service;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;

class Logger
{
    public static function error(\Exception $e)
    {
        $logger = new MonologLogger('default');
        if (!file_exists(dirname(__DIR__).'/../../var/log')) {
            if (!file_exists(dirname(__DIR__).'/../../var')) {
                mkdir(dirname(__DIR__).'/../../var');
            }
            mkdir(dirname(__DIR__).'/../../var/log');
            touch(dirname(__DIR__).'/../../var/log/error.log');
        }
        $stream_handler = new StreamHandler(dirname(__DIR__).'/../../var/log/error.log');
        $logger->pushHandler($stream_handler);
        $logger->error($e->getMessage());
    }
}