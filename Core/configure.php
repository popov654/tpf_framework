<?php

use Tpf\Database\Repository;
use Tpf\Model\User;
use Tpf\Service\Auth\PasswordHasher;


define('CONFIG_PATH', PATH . '/config/config.php');

function configure(): array
{
    global $TPF_CONFIG;

    if (!file_exists(CONFIG_PATH) && file_exists(PATH . '/vendor/' . VENDOR_PATH . '/config.sample.php')) {
        copy(PATH . '/vendor/' . VENDOR_PATH . '/config.sample.php', CONFIG_PATH);
    }

    $tables = array_merge(['User', 'Session'], getRealmEntityNames());

    dbConnect();
    createDB($tables);

    $errors = [];

    if (!isset($TPF_CONFIG['secret']) || $TPF_CONFIG['secret'] == md5('changeme')) {

        try {
            $TPF_CONFIG['secret'] = base64_encode(random_bytes(36));
        } catch (\Exception $e) {
            $errors[] = ['description' => 'Error while generating random secret key', 'exception' => $e];
        }
        $config = file_get_contents(dirname(__DIR__) . '/vendor/tpf/config.php');
        $config = preg_replace(pattern: '/(\$TPF_CONFIG = \[\n\s*)(\'secret\' => md5\(\'changeme\'\),\n)?/', replacement: "\\1'secret' => '" . $TPF_CONFIG['secret'] . "',\n", subject: $config);

        if ($TPF_CONFIG['db']['db_password'] == 'password' && isset($_POST['db_host']) && isset($_POST['db_user'])) {
            $config = updateDBConfig($config);
        }

        file_put_contents(CONFIG_PATH, $config);

    } else if (isset($_POST['db_host']) || isset($_POST['db_name']) || isset($_POST['db_user']) || isset($_POST['db_password'])) {

        $needToRename = isset($_GET['db_name']) && $_POST['db_name'] != $TPF_CONFIG['db']['db_name'];
        $oldPrefix = $TPF_CONFIG['db']['table_prefix'] ?? null;

        $config = file_get_contents(CONFIG_PATH);
        $config = updateDBConfig($config);

        if ($needToRename) {
            renameDB(Query::mb_escape($_GET['db_name']), $oldPrefix);
        }

        file_put_contents(CONFIG_PATH, $config);
    }

    foreach ($_POST as $key => $value) {
        $_POST[$key] = preg_replace("/[^a-z\d!?\$&#~+=_-]/i", "", $value);
    }

    $adminUser = User::load(1);

    if (!$adminUser || isset($_POST['administrator_username'])) {
        if (!$adminUser) {
            $adminUser = new User();
        }
        $adminUser->username = $_POST['administrator_username'] ?? 'admin';
        $adminUser->password = $_POST['administrator_password'] ?? 'password';
        $adminUser->email = $_POST['administrator_email'] ?? 'admin@' . $_SERVER['HTTP_HOST'];
        $adminUser->firstname = 'Administrator';
        $adminUser->activationToken = '';
        $adminUser->rol = User::ROLE_ADMIN;
        PasswordHasher::hashPassword($adminUser);
        $adminUser->registeredAt = new Datetime();
        $adminUser->isActive = true;
        $adminUser->save();
    }

    return $errors;
}

function updateDBConfig($config): string
{
    global $TPF_CONFIG;

    $TPF_CONFIG['db']['host'] = $_POST['db_host'] ?? $TPF_CONFIG['db']['host'];
    $TPF_CONFIG['db']['database'] = $_POST['db_name'] ?? $TPF_CONFIG['db']['database'];
    $TPF_CONFIG['db']['user'] = $_POST['db_user'] ?? $TPF_CONFIG['db']['user'];
    $TPF_CONFIG['db']['password'] = $_POST['db_password'] ?? $TPF_CONFIG['db']['password'];

    return preg_replace("/'db' => [[^\]]*]/", "'db' => [
            'host' => '".      $TPF_CONFIG['db']['host']."',
            'database' => '".  $TPF_CONFIG['db']['database']."',
            'user' => '".      $TPF_CONFIG['db']['user']."',
            'password' => '".  $TPF_CONFIG['db']['password']."'
        ]", $config);
}