<?php

namespace Tpf\Model;

class Category extends AbstractEntity
{
    /**
     * Category:
     * @property int $id
     * @property string $type
     * @property int $parent
     * @property string $name
     * @property bool $isActive
     * @property bool $isDeleted
     * @property datetime $createdAt
     * @property datetime $modifiedAt
     */

    public int $id;
    public string $type;
    public int $parent;
    public string $name;

    public bool $isActive;
    public bool $isDeleted;
    public \Datetime $createdAt;
    public \Datetime $modifiedAt;

}