<?php

use Tpf\Database\Query;
use Tpf\Database\Repository;
use Tpf\Database\ORMException;
use Tpf\Model\User;

define('VENDOR_PATH', 'tpf/framework');
define('LEVELS', count(explode('/', VENDOR_PATH)) + 2);
define('PATH', dirname(__DIR__, LEVELS));

require_once PATH . '/config/config.php';
require_once PATH . '/vendor/'. VENDOR_PATH .'/Core/configure.php';

function render($template, $args = [], $suppressErrors = false): string
{
    global $TPF_CONFIG, $TPF_REQUEST;

    $cacheBypass = ['install'];

    $cacheTime = $TPF_CONFIG['cache']['templates'] ?? 'auto';
    $cachedTemplatePath = PATH . '/var/cache/templates/' . $template . '.tpf';
    $isExpired = $cachedTemplatePath != 'auto' && is_numeric($cacheTime) &&
                    file_exists($cachedTemplatePath) && time() - filemtime($cachedTemplatePath) > (int) $cacheTime;

    if ($cacheTime == 'auto' && filemtime(getTemplatePath($template, $suppressErrors)) > filemtime($cachedTemplatePath)) {
        $isExpired = true;
    }

    if (!$isExpired && file_exists($cachedTemplatePath)) {
        $content = file_get_contents($cachedTemplatePath);
    } else {
        $content = doRender($template, $args, $suppressErrors);
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

function getTemplatePath(string $template, bool $suppressErrors = false): string
{
    if (file_exists(PATH . '/vendor/' . VENDOR_PATH . '/templates/' . $template . '.tpf')) {
        return PATH . '/vendor/' . VENDOR_PATH . '/templates/' . $template . '.tpf';
    } else if (!$suppressErrors || file_exists(PATH . '/templates/' . $template . '.tpf')) {
        return PATH . '/templates/' . $template . '.tpf';
    } else {
        return '';
    }
}

function doRender($template, $args = [], $suppressErrors = false): string
{
    $path = getTemplatePath($template);
    if ($suppressErrors && empty($path)) return '';

    $content = file_get_contents($path);

    return compile($content, $args, $suppressErrors);
}

function compile(string $string, $args = [], $suppressErrors = false): string{
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
        $regexp = "((end)?(if|while)|endfor|set|for\s+([[:alpha:]_]\w*)\s+in\s+)";
        preg_match("/^\{\{\s*".$regexp."/", $matches[0], $m);
        $type = $m[1] ?? 'expr';
        $matches[1] = trim(preg_replace("/^".$regexp."/", "", $matches[1]));
        $str = preg_replace('/\b(\~index\~|:index)\b/', '$index', $matches[1]);
        $str = preg_replace('/("[^"]*"|\'[^\']*\'|\{\{ include )(*SKIP)(*F)|(?<![\w_\[\]\$.])([[:alpha:]_][\w\d_]*)(?![\w\(])/', "\$args['\\2']", $str);
        $str = preg_replace('/(?!\w|\])\.([[:alpha:]_]\w*)/', "['\\1']", $str);
        $str = '(' . $str . ')';
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
        if (preg_match('/^(if|for|while)/', $r[2])) {
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
        }
        $pos = $start + strlen($repl);
        $content = substr($content, 0, $start) . $repl . substr($content, $end);
        $end = $pos;
    }
    $content = substr($content, 0, $end) . str_replace("'", "\\'", substr($content, $end));

    return $content;
}

function createDirectories($path): void
{
    $path = normalizePath($path);
    $path = array_slice(preg_split("/(\\\\|\\/)/", $path), 0, -1);
    $str = '';
    foreach ($path as $dir) {
        if ($str != '') $str .= DIRECTORY_SEPARATOR;
        $str .= $dir;
        if (!file_exists($str)) {
            mkdir($str);
        }
    }
}

function normalizePath($path): string
{
    $path = preg_split("/(\\\\|\\/)/", $path);

    for ($i = 0; $i < count($path); $i++) {
        $dir = $path[$i];
        if ($dir == '.') continue;
        if ($dir == '..' && $i > 0) {
            array_splice($path, $i-1, 2);
            $i-=2;
        }
    }

    return implode(DIRECTORY_SEPARATOR, $path);
}

function render2()
{
    $content = '';
    include 'test.php';
    return $content;
}

function arrayToCode($value): string
{
    if (gettype($value) != 'array') {
        if (is_object($value)) {
            if ($value instanceof \Datetime) {
                return "'" . $value->format('c') . "'";
            }
            if (isset($value->toArray)) {
                return arrayToCode($value->toArray());
            }
            if (isset($value->toString)) {
                return "'" . str_replace("'", "\\'", $value->toString()) . "'";
            }
            $result = '';
            foreach (get_class_vars(get_class($value)) as $key => $val) {
                if ($result != '') {
                    $result .= ', ';
                }
                $result .= "'" . $key . "' => " . arrayToCode($value->$key);
            }
            return '[' . $result . ']';
        }
        if (is_null($value)) {
            return 'null';
        }
        if (is_numeric($value)) {
            return $value;
        }
        return "'" . str_replace("'", "\\'", $value) . "'";
    }
    $result = '';
    foreach ($value as $key => $val) {
        if ($result != '') {
            $result .= ', ';
        }
        $result .= "'" . $key . "' => " . arrayToCode($val);
    }
    return '[' . $result . ']';
}

function dbConnect(?bool $force = false): void
{
    global $dbal, $TPF_CONFIG;
    if (!$force && $dbal) {
        return;
    }
    try {
        $dbal = new PDO(($TPF_CONFIG['db']['type'] ?? 'mysql:') . (isset($TPF_CONFIG['db']['database']) ? 'dbname=' . $TPF_CONFIG['db']['database'] . ';' : '') . 'host=' . ($TPF_CONFIG['db']['host'] ?? 'localhost'),
            $TPF_CONFIG['db']['user'] ?? 'admin',
            $TPF_CONFIG['db']['password'] ?? 'password',
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '" . ($TPF_CONFIG['db']['charset'] ?? 'utf8') . "'")
        );
    } catch (PDOException $e) {
        $dbName = $TPF_CONFIG['db']['database'];
        $dbal = new PDO(($TPF_CONFIG['db']['type'] ?? 'mysql') . ':host=' . ($TPF_CONFIG['db']['host'] ?? 'localhost'),
            $TPF_CONFIG['db']['user'] ?? 'admin',
            $TPF_CONFIG['db']['password'] ?? 'password',
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '" . ($TPF_CONFIG['db']['charset'] ?? 'utf8') . "'")
        );
        $dbal->exec("CREATE DATABASE IF NOT EXISTS `". $TPF_CONFIG['db']['database'] ."`");
        sleep(1);
        $dbal->exec("USE `" . $TPF_CONFIG['db']['database'] . "`");
    }
}

function createDB(array $tables): void {
    global $dbal, $TPF_CONFIG;

    $systemTables = ['user', 'session', 'category', 'comment'];

    foreach ($tables as $table) {
        $table = strtolower($table);
        if (in_array($table, $systemTables)) {
            $path = PATH . '/vendor/' . VENDOR_PATH . '/Model/' . $table . '.php';
            $fullClassName = 'Tpf\\Model\\' . ucfirst($table);
        } else {
            $realm = explode('_', $table)[0];
            $parts = explode('_', $table);
            $className = implode('\\', array_slice(array_map(function($el) { return ucfirst($el); }, $parts), 1));
            $path = PATH . '/src/Model/' . $realm . '/' . str_replace('\\', '/', $className) . '.php';
            $fullClassName = 'App\\Model\\' . ucfirst(explode('_', $table)[0]) . '\\' . $className;
            require_once $path;
        }
        Repository::createTableByClass($fullClassName, $table);
    }
}

function renameDB(string $newName, ?string $oldPrefix = null): void {
    global $dbal, $TPF_CONFIG;
    $oldName = $TPF_CONFIG['db']['database'];
    $dbal->exec("CREATE DATABASE `". $newName ."`");
    $tables = $dbal->query("SHOW TABLES")->fetchAll(PDO::FETCH_NUM);
    foreach ($tables as $table) {
        $oldTableName = $newTableName = $table[0];
        if ($oldPrefix && $oldPrefix != ($TPF_CONFIG['db']['table_prefix'] ?? null)) {
            $newPrefix = $TPF_CONFIG['db']['table_prefix'] ? $TPF_CONFIG['db']['table_prefix'] . '_' : '';
            $newTableName = preg_replace("/^" . $oldPrefix . "_/", $newPrefix, $newTableName);
        }
        echo $oldTableName . "-> " . $newTableName . "\n";
        $dbal->exec("RENAME TABLE `".$oldName."`.`".$oldTableName."` TO `". $newName ."`.`".$newTableName."`");
    }
    $dbal->exec("DROP DATABASE `". $oldName ."`");
}

function getRealmEntityNames($realm = '*'): array
{
    global $TPF_CONFIG;
    $result = [];
    foreach ($TPF_CONFIG['realms'] as $name => $params) {
        if ($realm == '*' || $realm == $name) {
            $result = array_merge($result, getRealmEntityClasses($name));
        }
    }
    return $result;
}

function getRealmTableNames($realm = '*'): array
{
    return array_map(function($className) {
        return Repository::getTableNameByClass($className);
    }, getRealmEntityNames($realm));
}

function getRealmEntityClasses(string $realm): array
{
    if ($realm == '') return [];
    $path = PATH . '/src/Model/' . $realm;
    $dir = opendir($path);
    $files = getFilesInDir($path, $dir);
    $classes = array_map(function ($file) {
        return preg_replace_callback('/\\\\[a-z]/', function ($matches) {
            return strtoupper($matches[0]);
        },
        substr(
            str_replace('/', '\\',
                str_replace(PATH . '/src/', 'App/', $file)
        ), 0, -4));
    }, $files);

    return $classes;
}

function getFilesInDir(string $path, mixed $dir): array
{
    $files = [];
    while ($file = readdir($dir)) {
        if ($file == '.' || $file == '..' || substr($file, strlen($file) - 4) != '.php') {
            continue;
        }
        if (is_dir($path . '/' . $file)) {
            $files = array_merge($files, getFilesInDir($path . '/' . $file));
        } else {
            $files[] = $path . '/' . $file;
        }
    }

    return $files;
}

function getEntitySchemaDiff(string $className = '*'): array
{
    $classes = $className == '*' ? getRealmEntityClasses() : [$className];
    $result = [];
    foreach ($classes as $class) {
        $actualColumns = Repository::getColumnsByClass($className);
        $existingColumns = getEntityTableColumns($className);

        $actualColumns = array_values($actualColumns);
        $i = 0; $j = 0;
        $result[$class] = [];

        function equals($col1, $col2) {
            $repl = ['(' => '\\(', ')' => '\\)'];
            return $col1['Field'] == $col2['name'] && preg_match("/^" . strtr($col1['Type'], $repl) . ".*/i", $col2['type']);
        }

        array_walk($actualColumns, function (&$el) {
            $el['type'] = strtoupper(explode(' ', $el['full'])[1]);
        });

        while ($j < count($actualColumns) && $i < count($existingColumns)) {
            if (equals($existingColumns[$i], $actualColumns[$j])) {
                $i++; $j++;
                continue;
            }
            $i1 = $i+1;
            $res = false;
            while ($i1 < count($existingColumns) && !($res = equals($existingColumns[$i1], $actualColumns[$j]))) {
                $i1++;
            }
            if ($i1 < count($existingColumns) && $res) {
                $result[$class][] = ['position' => $i, 'deleteCount' => $i1 - $i, 'add' => []];
                $i = $i1+1;
                $j++;
                continue;
            }
            $j1 = $j+1;
            $res = false;
            while ($j1 < count($actualColumns) && !($res = equals($existingColumns[$i], $actualColumns[$j1]))) {
                $j1++;
            }
            if ($j1 < count($actualColumns) && $res) {
                $result[$class][] = ['position' => $i, 'deleteCount' => 0, 'add' => array_slice($actualColumns, $j, $j1-$j)];
                $j = $j1+1;
                $i++;
                continue;
            }
            $n = 1;
            $flag = false;
            while ($i + $n < count($existingColumns)) {
                $i1 = $i+$n;
                $j1 = $j+1;
                $res = false;
                while ($j1 < count($actualColumns) && !($res = equals($existingColumns[$i1], $actualColumns[$j1]))) {
                    $j1++;
                }
                if ($j1 < count($actualColumns) && $res) {
                    $result[$class][] = ['position' => $i, 'deleteCount' => $i1 - $i, 'add' => array_slice($actualColumns, $j, $j1-$j)];
                    $j = $j1+1;
                    $i = $i1+1;
                    $flag = true;
                    break;
                }
                $n++;
            }
            // We should never get up to here
            if (!$flag) throw new ORMException('Error while calculating diff for class ' . $class);
        }
    }

    return $result;
}

function getEntityTableColumns($className): array
{
    $table = Repository::getTableNameByClass($className);

    global $dbal;
    dbConnect();

    /** @var \PDO $dbal */
    $columns = $dbal->query("SHOW COLUMNS FROM `" . Query::mb_escape($table) . "`")->fetchAll(\PDO::FETCH_ASSOC);

    return $columns;
}

function getEntityType(string $type): ?string
{
    global $TPF_REQUEST;

    $tables = getRealmTableNames();

    if (isset($TPF_REQUEST['session']) && $TPF_REQUEST['session']->user->role == User::ROLE_ADMIN) {
        $tables[] = 'user';
    }

    if (!in_array($type, $tables)) {
        $entities = array_values(array_filter($tables, function ($table) use ($type) {
            return preg_match("/^" . $type . "_/", $table);
        }));
        if (empty($entities)) {
            return null;
        }
        $type = $entities[0];
    }

    return $type;
}

function getFullClassNameByType(string $type): string
{
    $class = ucfirst(preg_replace("/^(([a-z0-9])+_)*/", "", $type));
    if ($class != 'User') {
        $path = ucfirst(preg_replace_callback("/_[a-z]/", function ($match) {
            return '/' . strtoupper($match[0][1]);
        }, $type));
        require_once PATH . '/src/Model/' . $path . '.php';
        $className = 'App\\Model\\' . str_replace('/', '\\', $path);
    } else {
        $className = User::class;
    }

    return $className;
}

function getFilePathByClass($className): string
{
    $className = preg_replace('/^Tpf\\\\Model\\\\/', '', $className);

    if (in_array($className, ['User', 'Session', 'Category', 'Comment'])) {
        return PATH . '/vendor/' . VENDOR_PATH . '/Model/' . $className . '.php';
    } else {
        $pos = strpos($className, '\\Model') + strlen('\\Model') + 1;
        return PATH . '/src/Model/' . str_replace('\\', '/', substr($className, $pos))  . '.php';
    }
}
