<?php

namespace Tpf\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Tpf\Database\Repository;
use Tpf\Model\Session;
use Tpf\Model\User;
use Tpf\Service\Auth\Auth;
use Tpf\Service\Auth\PasswordHasher;
use Tpf\Service\Router\Router;

class ORMTest extends BasicTest
{

    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    public function testSaveLoadDelete()
    {
        global $dbal;
        dbConnect();

        $user = new User();
        $user->username = 'test';
        $user->email = 'test@tpf';
        $user->password = 'test';
        $user->registeredAt = new \DateTime();
        PasswordHasher::hashPassword($user);
        $user->save();

        self::assertNotNull($user->id, 'ID of persisted entity must not be null');
        self::assertGreaterThan(0, $user->id, 'ID of persisted entity must be greater than zero');

        $id = $user->id;

        unset($user);

        $user = User::load($id);
        self::assertNotNull($user, 'Saved user should be loaded from repository by ID');
        $user->delete();

        unset($user);
        $user = User::load($id);
        self::assertNull($user, 'Deleted user should not be loaded from repository by ID');

        $dbal->exec('ALTER TABLE `user` AUTO_INCREMENT=0');
    }

    public function testCreateEntityFromArray()
    {
        $data = [
            'username' => 'test',
            'password' => 'test',
            'firstname' => 'Test',
            'lastname' => '',
            'isActive' => true,
            'registeredAt' => new \Datetime()
        ];

        /** @var User $user */

        $user = new User();
        User::fillFromArray($user, $data);
        self::assertNotNull($user);
        self::assertNull($user->id);
        self::assertEquals('test', $user->username);
        self::assertEquals('Test', $user->firstname);
        self::assertTrue($user->isActive);
    }

    public function testGetTableNameByClass()
    {
        $className = 'App\\Model\\Blog\\Post';
        $tableName = Repository::getTableNameByClass($className);
        self::assertEquals('blog_post', $tableName);

        $className = 'Tpf\\Model\\User';
        $tableName = Repository::getTableNameByClass($className);
        self::assertEquals('user', $tableName);
    }

    public function testGetColumnsByClass()
    {
        $className = 'Tpf\\Model\\User';
        $columns = Repository::getColumnsByClass($className);
        self::assertEquals(11, count($columns));
        self::assertArrayHasKey('username', $columns);
        self::assertArrayHasKey('password', $columns);
        self::assertArrayHasKey('email', $columns);
        self::assertEquals('is_active', $columns['isActive']['name']);
    }

    public function testGetRealmEntityNames()
    {
        $classNames = getRealmEntityNames();
        self::assertEquals(count($classNames), count(array_filter($classNames, function ($el) {
            return strpos($el, 'App\\Model\\') === 0;
        })));
        print_r($classNames);
    }

    public function testGetEntitySchemaDiff()
    {
        $className = 'Tpf\\Model\\User';
        $diffs = getEntitySchemaDiff($className);
        $diff = $diffs[$className];

        self::assertEquals(0, count($diff));

        $diff[0] = ['position' => 6, 'deleteCount' => 0, 'add' => [['property' => 'bio', 'name' => 'bio', 'full' => '`bio` TEXT NOT NULL']]];

        $tableName = Repository::getTableNameByClass($className);
        $existingColumns = getEntityTableColumns($className);
        $statements = Repository::applyDiff($tableName, $existingColumns, $diff, true);

        self::assertEquals(1, count($statements));
        self::assertEquals('ALTER TABLE `user` ADD COLUMN `bio` TEXT NOT NULL AFTER `email`', trim($statements[0]));

        $diff[0] = ['position' => 6, 'deleteCount' => 1, 'add' => []];
        $statements = Repository::applyDiff($tableName, $existingColumns, $diff, true);

        self::assertEquals(1, count($statements));
        self::assertEquals('ALTER TABLE `user` DROP COLUMN `role`', trim($statements[0]));
    }
}