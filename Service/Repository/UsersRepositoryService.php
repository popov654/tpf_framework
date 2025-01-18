<?php

namespace Tpf\Service\Repository;

use Tpf\Database\Repository;
use Tpf\Model\User;

class UsersRepositoryService
{
    private Repository $repository;

    public function __construct()
    {
        $this->repository = new Repository(User::class);
    }

    public function getUsersWithCounters(array $counters, array $where = []): array
    {
        $columns = ['*'];
        foreach ($counters as $key => $value) {
            $columns[] = '(SELECT COUNT(*) FROM `' . $key . '` WHERE `' . $key . '`.`' . $value . '`=`user`.`id`) AS `' . $key . '_count`';
        }
        $select = implode(',', $columns);

        return $this->repository->whereEq($where)->select($select);
    }
}