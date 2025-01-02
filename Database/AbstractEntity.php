<?php

namespace Tpf\Database;

use Tpf\Model\User;
use Tpf\Service\Auth\PasswordHasher;

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

    public static function fromJSON(?string $data): self
    {
        $class = get_called_class();
        $obj = new $class;

        if ($data) {
            $data = json_decode($data, true);
            self::fillFromArray($obj, $data);
        }

        return $obj;
    }

    public static function fillFromArray(object $entity, array $data): void
    {
        $reflection = new \ReflectionClass(get_class($entity));
        $properties = $reflection->getProperties();

        $props = [];
        foreach ($properties as $property) {
            $props[$property->getName()] = $property->getType()->getName();
        }

        foreach ($data as $key => $value) {
            if (!isset($props[$key])) {
                continue;
            }
            if ($props[$key] == 'string') {
                $entity->$key = (string) $value;
            } else if ($props[$key] == 'int') {
                $entity->$key = (int) $value;
            } else if ($props[$key] == 'float') {
                $entity->$key = (float) $value;
            } else if ($props[$key] == 'bool') {
                $entity->$key = (bool) $value;
            } else {
                $entity->$key = $value;
            }
            if (get_class($entity) == User::class && $key == 'password') {
                PasswordHasher::hashPassword($entity);
            }
        }
        if (get_class($entity) == User::class && !isset($data['registeredAt'])) {
            $entity->registeredAt = new \Datetime();
        }
    }

    public function getFields(array $fields): array
    {
        $data = [];
        foreach ($fields as $field) {
            if (!isset($this->$field)) continue;
            $data[$field] = $this->$field;
            if ($data[$field] instanceof \DateTime) {
                $data[$field] = $data[$field]->format('Y-m-d\TH:i:s');
            }
        }
        return $data;
    }

    public static function getSchema($type): array
    {
        global $dbal;
        /** @var \PDO $dbal */
        $columns = $dbal->query("SHOW COLUMNS FROM `" . $type . "`")->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($columns as $col) {
            $type = 'text';
            if (preg_match("/^int/", $col['Type'])) {
                $type = 'int';
            } else if (preg_match("/^float/", $col['Type'])) {
                $type = 'float';
            } else if (preg_match("/^tinyint/", $col['Type'])) {
                $type = 'bool';
            } else if (preg_match("/^json/", $col['Type'])) {
                $type = 'array';
            } else if (preg_match("/^date/", $col['Type'])) {
                $type = 'date';
            } else if (preg_match("/^time/", $col['Type'])) {
                $type = 'time';
            }
            if ($type == 'text' && preg_match("/(^|_)(photo|image|picture)(_|$)/", $col['Field'])) {
                $type = 'image';
            }
            if (($type == 'text' || $type == 'array') && preg_match("/(^|_)(photos|images|pictures)(_|$)/", $col['Field'])) {
                $type = 'image_list';
            }
            if ($col['Field'] == 'name' || $col['Field'] == 'title') {
                $type = 'short_text';
            }
            if (in_array($col['Field'], ['author_id', 'created_at', 'modified_at'])) {
                continue;
            }
            $field = preg_replace_callback("/_[a-z]/", function ($matches) {
                return strtoupper($matches[0][1]);
            }, $col['Field']);
            $result[$field] = $type;
        }

        return $result;
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