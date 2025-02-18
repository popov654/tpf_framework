<?php

namespace Tpf\Model;

use Tpf\Database\Repository;

class User extends AbstractEntity
{
    /**
     * User:
     * @property int $id
     * @property string $username
     * @property string $password
     * @property string $photo
     * @property string $firstname
     * @property string $lastname
     * @property string $email
     * @property int $role
     * @property bool $isActive
     * @property string $activationToken
     * @property datetime $registeredAt
     * @property datetime $lastLoginAt
     */

    public ?int $id;
    public string $username;
    public ?string $password;
    public ?string $hashedPassword;
    public string $photo;
    public string $firstname;
    public string $lastname;
    public string $email;
    public int $role;
    public bool $isActive;
    public string $activationToken;
    public \DateTime $registeredAt;
    public \DateTime | null $lastLoginAt;

    const ROLE_CLIENT = 0;
    const ROLE_ADMIN = 1;
    const ROLE_EDITOR = 2;

    const MIN_USERNAME_LENGTH = 3;

    public function __construct()
    {
        parent::__construct();
        $this->photo = '';
        $this->firstname = '';
        $this->lastname = '';
        $this->role = self::ROLE_CLIENT;
        $this->registeredAt = new \Datetime();

        self::$requirements = [
            'username' => [
                [
                    'function' => function() {
                        return \Tpf\Validator\StringFormat::notShorterThan($this->username, self::MIN_USERNAME_LENGTH);
                    },
                    'message' => 'must be at least ' . self::MIN_USERNAME_LENGTH . ' characters long'
                ],
                [
                    'function' => function() {
                        return \Tpf\Validator\Unique::isUniqueFieldForClass(self::class, $this->id ?? 0, 'username', $this->username);
                    },
                    'message' => 'must be unique'
                ],
            ],
            'email' => [
                [
                    'function' => function() {
                        return \Tpf\Validator\StringFormat::isEmail($this->email);
                    },
                    'message' => 'has incorrect format'
                ],
            ]
        ];
    }

    public function getFullName(): string
    {
        return trim(($this->firstname ?? '') . ' ' . ($this->lastname ?? ''));
    }

    public function getRoleName(): string
    {
        return $this->role == 1 ? 'Admin' : ($this->role == 2 ? 'Manager' : 'User');
    }

}
