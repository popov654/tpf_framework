<?php

namespace Tpf\Model;

use Tpf\Database\Repository;
use Tpf\Database\Query;
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
        if ($fields !== [] && array_keys($fields) !== range(0, count($fields) - 1)) {
            $fields = array_keys($fields);
        }
        foreach ($fields as $field) {
            if (!isset($this->$field)) continue;
            $data[$field] = $this->$field;
            if ($data[$field] instanceof \DateTime) {
                $data[$field] = $data[$field]->format('Y-m-d\TH:i:s');
            }
            if ($data[$field] instanceof self) {
                $type = Repository::getTableNameByClass(get_class($data[$field]));
                $schema = self::getSchema($type);
                if ($type == 'user') {
                    unset($schema['password']);
                    unset($schema['activationToken']);
                }
                $data[$field] = $data[$field]->getFields(array_keys($schema));
            }
        }
        return $data;
    }

    public static function getSchema($table): array
    {
        global $dbal;
        /** @var \PDO $dbal */
        $columns = $dbal->query("SHOW COLUMNS FROM `" . Query::mb_escape($table) . "`")->fetchAll(\PDO::FETCH_ASSOC);

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
                $type = ($col['Field'] == 'extra' || self::isJsonObjectField($table, $col['Field'])) ? 'object' : 'array';
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
            $field = preg_replace_callback("/_[a-z]/", function ($matches) {
                return strtoupper($matches[0][1]);
            }, $col['Field']);
            $result[$field] = $type;
        }

        return $result;
    }

    public static function isJsonObjectField(string $table, string $field): bool
    {
        global $dbal;
        /** @var \PDO $dbal */
        $result = $dbal->query("SELECT count(1) AS `count` FROM `" . Query::mb_escape($table) . "` WHERE `" . Query::mb_escape($field) . "` REGEXP '^\\\\{.*\\\\}$'")->fetch(\PDO::FETCH_ASSOC);

        return ((int)$result['count']) > 0;
    }

    public function getComments(): array
    {
        $type = get_class($this);
        $result = [];
        if (!preg_match("/Entity$/", $type)) {
            $parts = explode('\\', $type);
            $type = strtolower(array_pop($parts));
            for ($i = count($parts)-1; $i >= 0; $i--) {
                if ($parts[$i] == 'Model') break;
                $type = $parts[$i] . '_' . $type;
            }
            $repository = new Repository(Comment::class);
            $result = $repository->whereEq(['type' => $type, 'entity_id' => $this->id])->fetch();
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
        (new Repository(get_class($this)))->remove($this);
    }

    public function remove(): void
    {
        $this->delete();
    }

    public function isValid(): bool
    {
        return true;
    }

    public bool $validate = true;
}