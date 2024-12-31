<?php

namespace Tpf\Database;

abstract class AbstractEntity
{

    public function __construct()
    {
        $reflection = new \ReflectionClass(get_class($this));
        $properties = $reflection->getProperties();
        foreach ($properties as $property) {
            if ($property->isInitialized($this)) {
                continue;
            }
            if ($property->getType()->allowsNull()) {
                $property->setValue($this, null);
            } else if ($property->getType()->getName() == 'bool') {
                $property->setValue($this, false);
            } else if ($property->getType()->getName() == 'string') {
                $property->setValue($this,'');
            } else if (preg_match("/(int|float)/", $property->getType()->getName())) {
                $property->setValue($this,0);
            }
        }
    }

    public static function load($id, $loadEmbedded = false, $maxDepth = 3): ?object
    {
        return (new Repository(get_called_class()))->fetchOne($id);
    }

    /**
     * @throws ValidationException
     */
    public function save(): void
    {
        if ($this->validate && !$this->isValid()) {
            throw new ValidationException('Data validation error');
        }
        (new Repository(get_class($this)))->save($this);
    }

    public function delete(): void
    {
        (new Repository(get_class($this)))->delete($this);
    }

    public function isValid(): bool
    {
        return true;
    }

    public bool $validate = true;
}