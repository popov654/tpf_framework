<?php

namespace Tpf\Model;


abstract class BasicEntity extends AbstractEntity
{
    public int $id;
    public string $name;
    public string $text;
    public string $image;
    public array $tags = [];
    public int $authorId;
    public User $author;
    public bool $isActive;
    public bool $isDeleted;
    public \Datetime $createdAt;
    public \Datetime $modifiedAt;


    public function __construct()
    {
        global $TPF_REQUEST;
        $now = new \Datetime();
        parent::__construct();
        if (isset($TPF_REQUEST['session'])) {
            $this->authorId = $TPF_REQUEST['session']->user->id;
        }
        $this->createdAt = $now;
        $this->modifiedAt = $now;
    }

}