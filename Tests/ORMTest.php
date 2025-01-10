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
            'firstName' => 'Test',
            'lastName' => '',
            'isActive' => true,
            'registeredAt' => new \Datetime()
        ];

        /** @var User $user */
        $user = User::fromArray($data);
        self::assertNotNull($user);
        self::assertNull($user->id);
        self::assertEquals('test', $user->username);
        self::assertEquals('test', $user->password);
        self::assertTrue($user->isActive);
    }
}