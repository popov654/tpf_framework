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
     * @property text $image
     * @property bool $isActive
     * @property bool $isDeleted
     * @property datetime $createdAt
     * @property datetime $modifiedAt
     * @property json $idPath
     * @property json $path
     */

    public ?int $id;
    public string $type;
    public int $parent;
    public string $name;
    public ?string $image;

    public bool $isActive;
    public bool $isDeleted;
    public \Datetime $createdAt;
    public \Datetime $modifiedAt;

    public array $idPath = [];
    public array $path = [];

    
    public function __construct()
    {
        parent::__construct();
        $now = new \DateTime();
        $this->createdAt = $now;
        $this->modifiedAt = $now;
    }

    public function setParent(int $id)
    {
        $this->parent = $id;
        $this->updateParents();
    }

    public function updateParents()
    {
        $parent = $this->parent;
        $this->parents = [$this->id];
        $this->path = [$this->name];
        $maxDepth = 100;
        while ($parent && $maxDepth > 0) {
            $parentCategory = Category::load($parent);
            array_unshift($this->parents, $parentCategory->id);
            array_unshift($this->path, $parentCategory->name);
            $parent = $parentCategory->parent;
            $maxDepth--;
        }
    }
}
