<?php

namespace Tpf\Model;

use Tpf\Database\AbstractEntity;
use Tpf\Database\Repository;

class Session extends AbstractEntity
{
    /**
     * Session:
     * @property int $id
     * @property string $type
     * @property int $userId
     * @property string $secureSessionId
     * @property datetime $expiredAt
     */

    public int | null $id;
    public string $type;
    public int $userId;
    public User $user;
    public string $secureSessionId;
    public ?\Datetime $expiredAt;

}