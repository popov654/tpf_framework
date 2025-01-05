<?php

namespace Tpf\Tests;

use PHPUnit\Framework\TestCase;

abstract class BasicTest extends TestCase
{
    public function __construct(string $name)
    {
        parent::__construct($name);

        global $TPF_CONFIG, $TPF_REQUEST;

        $TPF_REQUEST = [];
        while (!preg_match("/tpf(\\\\|\\/)framework$/", getcwd())) {
            chdir('..');
        }
        require_once './config.sample.php';
        require_once './Core/core.php';

        $TPF_CONFIG['db']['database'] = 'framework';
    }
}