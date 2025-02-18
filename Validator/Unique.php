<?php

namespace Tpf\Validator;

use Tpf\Database\Query;

class Unique
{
    public static function isUniqueFieldForClass(string $class, int|string $objectId, string $field, string $value): bool
    {
        return (new Query($class))->where(["`" . $field . "` = '" . $value . "'", '`id` != ' . $objectId])->count() == 0;
    }
}