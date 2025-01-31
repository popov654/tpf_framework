<?php

namespace Tpf\Tests;

use PHPUnit\Framework\TestCase;
use Tpf\Service\Template\TemplateService;

class TemplateTest extends BasicTest
{

    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    public function testExpression()
    {
        require_once './Core/core.php';

        $result = TemplateService::compile('Some {{ word }} text', ['word' => 'short']);
        self::assertEquals("Some ' . (\$args['word']) . ' text", $result);

        $result = TemplateService::compile('User {{ data.user.id }}', ['data' => ['user' => ['id' => 7]]]);
        self::assertEquals("User ' . (\$args['data']['user']['id']) . '", $result);

        $result = TemplateService::compile('{{ x * 3 }}', ['x' => 4]);
        self::assertEquals("' . (\$args['x'] * 3) . '", $result);

        $result = TemplateService::compile('Session ID: {{ globals.session.id }}', []);
        self::assertEquals("Session ID: ' . (\$globals['session']['id']) . '", $result);
    }

    public function testFunctionCall()
    {
        require_once './Core/core.php';

        $result = TemplateService::compile('<p>Welcome, {{ isset(globals.session) ? globals.session.user.username : \'guest\' }}</p>');
        self::assertEquals("<p>Welcome, ' . (isset(\$globals['session']) ? \$globals['session']['user']['username'] : 'guest') . '</p>", $result);
    }

    public function testComments()
    {
        require_once './Core/core.php';

        $result = TemplateService::compile('No {# f*cking #} comments', []);
        self::assertEquals("No comments", $result);
    }

    public function testSetVariables()
    {
        require_once './Core/core.php';

        $result = TemplateService::compile('{{ set n = 1 }}n is {{ n }}', []);
        self::assertEquals("'; (\$args['n'] = 1); echo 'n is ' . (\$args['n']) . '", $result);
    }

}
