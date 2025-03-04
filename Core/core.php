<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tpf\Database\Query;
use Tpf\Database\Repository;
use Tpf\Database\ORMException;
use Tpf\Model\User;
use Tpf\Service\Auth\Auth;
use Tpf\Service\ErrorPage;
use Tpf\Service\Router\Router;
use Tpf\Service\Template\TemplateService;

define('VENDOR_PATH', 'tpf/framework');
define('LEVELS', count(explode('/', VENDOR_PATH)) + 2);
define('PATH', dirname(__DIR__, LEVELS));

require_once PATH . '/config/config.php';
require_once PATH . '/vendor/'. VENDOR_PATH .'/Core/configure.php';

class AppKernel
{
    public static function process(Request $request): Response
    {
        global $TPF_CONFIG, $TPF_REQUEST;

        try {
            $TPF_REQUEST['show_errors'] = !isset($TPF_CONFIG['debug']) || !$TPF_CONFIG['debug'];
            $TPF_REQUEST['session'] = null;
            if (!Router::isStaticResource($request->getPathInfo())) {
                $TPF_REQUEST['session'] = Auth::authenticate($request);
            }
            $response = Router::route($request);
        } catch (Throwable $t) {
            $message = $t->getMessage() . '<div style="font-size: 0.8em">in file ' . $t->getFile() . ' on line ' . $t->getLine() . '</div>';
            return ErrorPage::createResponse(500, $message);
        }

        return $response;
    }
}

function render($template, $args = [], $suppressErrors = false): string
{
    return TemplateService::render($template, $args, $suppressErrors);
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
            foreach (get_object_vars($value) as $key => $val) {
                if (!isset($value->$key)) {
                    continue;
                }
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

function createDB(array $tables): void
{
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

function renameDB(string $newName, ?string $oldPrefix = null): void
{
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

function transformKeys(array $data, array $keys): array
{
    $result = $data;
    foreach ($keys as $from => $to) {
        if (isset($result[$from])) {
            $result[$to] = $result[$from];
            unset($result[$from]);
        }
    }

    return $result;
}

function array_filter_keys(array $array, array $keys): array
{
    return array_intersect_key($array, array_flip($keys));
}

function array_filter_values(callable $callable, array $array): array
{
    return array_values(array_filter($array, $callable));
}

function array_find(callable $callable, array $array): mixed
{
    $res = array_filter_values($callable, $array);
    return !empty($res) ? $res[0] : null;
}

function getRealmEntityNames(string $realm = '*'): array
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

function getRealmEntityClasses(string $realm = '*'): array
{
    global $TPF_CONFIG;

    $realms = $realm == '*' ? array_map(function ($el) {
        return ucfirst($el);
    }, array_keys($TPF_CONFIG['realms']) ?? []) : [ucfirst($realm)];

    $classes = [];

    foreach ($realms as $realm) {
        $path = PATH . '/src/Model/' . $realm;
        $dir = opendir($path);
        $files = getFilesInDir($path, $dir);
        $classes = array_merge($classes, array_map(function ($file) {
            return preg_replace_callback('/\\\\[a-z]/', function ($matches) {
                return strtoupper($matches[0]);
            },
                substr(
                    str_replace('/', '\\',
                        str_replace(PATH . '/src/', 'App/', $file)
                    ), 0, -4));
        }, $files));
    }

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

    function equals($col1, $col2) {
        $repl = ['(' => '\\(', ')' => '\\)'];
        return $col1['Field'] == $col2['name'] && preg_match("/^" . strtr($col1['Type'], $repl) . ".*/i", $col2['type']);
    }

    foreach ($classes as $class) {
        $actualColumns = Repository::getColumnsByClass($class);
        try {
            $existingColumns = getEntityTableColumns($class);
        } catch (\PDOException $e) {
            Repository::createTableByClass($class, null);
        }

        $actualColumns = array_values($actualColumns);
        $i = 0; $j = 0;
        $result[$class] = [];

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
        $tables = array_merge($tables, ['user', 'session', 'comment', 'category']);
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
    if (class_exists('Tpf\\Model\\' . $class)) {
        return 'Tpf\\Model\\' . $class;
    }

    $path = ucfirst(preg_replace_callback("/_[a-z]/", function ($match) {
        return '/' . strtoupper($match[0][1]);
    }, $type));

    $className = 'App\\Model\\' . str_replace('/', '\\', $path);
    loadParentClasses(PATH . '/src/Model/' . $path . '.php');

    require_once PATH . '/src/Model/' . $path . '.php';

    return $className;
}

function getFilePathByClass($className): string
{
    $className = preg_replace('/^Tpf\\\\Model\\\\/', '', $className);

    if (in_array($className, ['User', 'Session', 'Category', 'Comment', 'AbstractEntity', 'BasicEntity'])) {
        return PATH . '/vendor/' . VENDOR_PATH . '/Model/' . $className . '.php';
    } else {
        if (($pos = strpos($className, '\\Model\\')) !== false) $className = substr($className, $pos + strlen('\\Model\\'));
        return PATH . '/src/Model/' . str_replace('\\', '/', $className)  . '.php';
    }
}

function loadParentClasses($path)
{
    $content = file_get_contents($path);
    preg_match("/^class\\s+(\\w+)\\s+(?:extends (\\w+))?\\s*{\\s*$/im", $content, $parent);

    while ($parent) {
        preg_match("/^namespace\\s+([a-z0-9\\\\_-]+);\\s*$/im", $content, $namespace);
        preg_match("/^use\\s+(?:((?:[a-z0-9_-]+\\\\)*" . $parent[2] . ")|([a-z0-9\\\\_-]+)\\s+as\\s+". $parent[2] .");\\s*$/im", $content, $uses);
        $className = ($namespace ? $namespace[1] . '\\' : '') . $parent[2];
        if ($uses) {
            $className = $uses[1] ?: $uses[2];
        }
        $parentPath = getFilePathByClass($className);
        if (!class_exists($className)) {
            require_once $parentPath;
        }

        $content = file_get_contents($parentPath);
        preg_match("/^class\\s+(\\w+)\\s+(?:extends (\\w+))?\\s*{\\s*$/im", $content, $parent);
    }
}
