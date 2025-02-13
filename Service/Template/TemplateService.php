<?php

namespace Tpf\Service\Template;


class TemplateService
{
    public static function render($template, $args = [], $suppressErrors = false): string
    {
        global $TPF_CONFIG, $TPF_REQUEST;

        $cacheBypass = ['install'];

        $cacheTime = $TPF_CONFIG['cache']['templates'] ?? 'auto';
        $cachedTemplatePath = PATH . '/var/cache/templates/' . $template . '.tpf';
        $isExpired = $cachedTemplatePath != 'auto' && is_numeric($cacheTime) &&
            file_exists($cachedTemplatePath) && time() - filemtime($cachedTemplatePath) > (int) $cacheTime;

        if ($cacheTime == 'auto' && file_exists($cachedTemplatePath) && filemtime(self::getTemplatePath($template, $suppressErrors)) > filemtime($cachedTemplatePath)) {
            $isExpired = true;
        }

        if (!$isExpired && file_exists($cachedTemplatePath)) {
            $content = file_get_contents($cachedTemplatePath);
        } else {
            $content = self::doRender($template, $args, $suppressErrors);
            if (!in_array($template, $cacheBypass)) {
                createDirectories($cachedTemplatePath);
                file_put_contents($cachedTemplatePath, $content);
            }
        }

        ob_start();
        eval('$globals = ' . arrayToCode($TPF_REQUEST) . '; $params = ' . arrayToCode($_GET) . '; $args = ' . arrayToCode($args) . '; echo ' . "'" . $content . "'" . ';');
        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    public static function getTemplatePath(string $template, bool $suppressErrors = false): string
    {
        if (file_exists(PATH . '/vendor/' . VENDOR_PATH . '/templates/' . $template . '.tpf')) {
            return PATH . '/vendor/' . VENDOR_PATH . '/templates/' . $template . '.tpf';
        } else if (!$suppressErrors || file_exists(PATH . '/templates/' . $template . '.tpf')) {
            return PATH . '/templates/' . $template . '.tpf';
        } else {
            return '';
        }
    }

    public static function doRender($template, $args = [], $suppressErrors = false): string
    {
        $path = self::getTemplatePath($template);
        if ($suppressErrors && empty($path)) return '';

        $content = file_get_contents($path);

        return self::compile($content, $args, $suppressErrors);
    }

    public static function compile(string $string, $args = [], $suppressErrors = false): string{
        $replacements = [];

        /* Removing comments */

        $content = preg_replace("/[ \t]*\\{#.*#\\}\r?\n?/", "", $string);

        /* Processing includes */

        $content = preg_replace_callback('/\{\{\s*@?include ([\w\d~\/_-]+)\s*\}\}/', function ($matches) use ($content, &$replacements, $args) {
            $suppressErrors = str_contains($matches[0], '@include');
            $str = render($matches[1], $args, $suppressErrors);
            $replacements[] = [$matches[0], $str];
            return $matches[0];
        }, $content);

        foreach ($replacements as $r) {
            $start = strpos($content, $r[0]);
            $end = $start + strlen($r[0]);
            $content = substr($content, 0, $start) . trim($r[1]) . substr($content, $end);
        }

        /* Processing expressions */

        $content = preg_replace('/\b(globals|params)\.([[:alpha:]_]\w*)/', "\$\\1['\\2']", $content);

        $replacements = [];

        $content = preg_replace_callback('/\{\{\s*(.*?)\s*\}\}/', function ($matches) use ($content, &$replacements) {
            $regexp = '((end|else\s+)?(if|while)|endfor|set|for\s+([[:alpha:]_]\w*)\s+in\s+)|else';
            preg_match("/^\{\{\s*".$regexp."/", $matches[0], $m);
            $type = (!empty($m) && $m[0] == 'else') ? $m[0] : ($m[1] ?? 'expr');
            $matches[1] = trim(preg_replace("/^".$regexp."/", "", $matches[1]));
            $str = preg_replace('/(^|\s)(~index~|:index)(\s|$)/', '$index', $matches[1]);
            $str = preg_replace('/("[^"]*"|\'[^\']*\'|\{\{ include )(*SKIP)(*F)|(?<![\w_\[\]\$.])([[:alpha:]_][\w\d_]*)(?![\w\(])/', "\$args['\\2']", $str);
            $str = preg_replace('/(?!\w|\])\.([[:alpha:]_]\w*)/', "['\\1']", $str);
            if (strpos($type, 'for ') !== 0) $str = '(' . $str . ')';
            $replacements[] = [$matches[0], $str, $type];
            return $matches[0];
        }, $content);

        $pos = 0;
        $start = 0;
        $end = 0;
        foreach ($replacements as $r) {
            $start = strpos($content, $r[0]);
            $end = $start + strlen($r[0]);
            $quoted = str_replace("'", "\\'", substr($content, $pos, $start - $pos));
            $content = substr($content, 0, $pos) . $quoted . substr($content, $start);
            $start = strpos($content, $r[0]);
            $end = $start + strlen($r[0]);
            $repl = "' . ". $r[1] . " . '";
            if (preg_match('/^((else\s+)?if|for|while)/', $r[2])) {
                if (!str_starts_with($r[2], 'for')) {
                    $repl = "'; " . $r[2] . " (". $r[1] . ") echo '";
                } else {
                    preg_match("/^for\s+([[:alpha:]_]\w*)\s+in\s+/", $r[2], $parts);
                    $repl = "'; \$index = 1; foreach (".$r[1]." as \$".$parts[1].") { \$args['".$parts[1]."'] = \$".$parts[1]."; echo '";
                }
            } else if (preg_match('/^end(if|for|while)/', $r[2])) {
                if ($r[2] == 'endfor') {
                    $repl = "'; \$index++; } echo '";
                } else {
                    $repl = "'; echo '";
                }
            } else if ($r[2] == 'set') {
                $repl = "'; " . $r[1] . "; echo '";
            } else if ($r[2] == 'else') {
                $repl = "'; else echo '";
            }
            $pos = $start + strlen($repl);
            $content = substr($content, 0, $start) . $repl . substr($content, $end);
            $end = $pos;
        }
        $content = substr($content, 0, $end) . str_replace("'", "\\'", substr($content, $end));

        return $content;
    }
}
