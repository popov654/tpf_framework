<?php

namespace Tpf\Model;

class Comment extends AbstractEntity
{
    /**
     * Comment:
     * @property int $id
     * @property string $type
     * @property int $entityId
     * @property text $text
     * @property int $authorId
     * @property bool $isActive
     * @property bool $isDeleted
     * @property datetime $createdAt
     * @property datetime $modifiedAt
     */

    public int $id;
    public string $type;
    public int $entityId;
    public string $text;

    public int $authorId;
    public User $author;
    public bool $isActive;
    public bool $isDeleted;
    public \Datetime $createdAt;
    public \Datetime $modifiedAt;

}