<?php

namespace Tpf\Database;

use Tpf\Model\User;
use Tpf\Model\Category;

class Repository extends Query
{
    public function __construct(string $className)
    {
        parent::__construct($className);
        if ($className == Category::class) {
            $this->order = ["parent" => "asc", "id" => "asc"];
        }
    }

    protected const PRIMARY_COLUMN_KEY = "id";

    /**
     * @method self filterByCategory(int $categoryId, ?bool $excludeSubcategories = false)
     */
    public function filterByCategory(int|string $categoryId, ?bool $excludeSubcategories = false)
    {
        if (!$excludeSubcategories) {
            return $this->where(["`categories` REGEXP '(\\\\[|,\\\\s?)\"?". $categoryId ."\"?(,|\\\\])'"]);
        } else {
            return $this->where(["`categories` REGEXP '(\\\\[|,\\\\s?)\"?". $categoryId ."\"?\\\\]'"]);
        }
    }

    /**
     * @method self filterByTags(array $tags, ?bool $findAny = false)
     */
    public function filterByTags(array $tags, ?bool $findAny = false)
    {
        foreach ($tags as $tag) {
            if (!$findAny) {
                $this->andWhere(["`tags` REGEXP '(\\\\[|,)\"". self::mb_escape($tag) ."\"(,|\\\\])'"]);
            } else {
                $this->orWhere(["`tags` REGEXP '(\\\\[|,)\"". self::mb_escape($tag) ."\"(,|\\\\])'"]);
            }
        }

        return $this;
    }

    /**
     * @method self filterByName(string $search, ?bool $strict = false)
     */
    public function filterByName(string $search, ?bool $strict = false, ?bool $searchInText = false)
    {
        if ($this->className == User::class) {
            $this->andWhere(["`username` LIKE '". $search ."%'"]);
            return;
        }
        if (!$strict) {
            $search = implode("(\\\\s+\\\\w+)*\\\\s+", preg_split("/\s+/", self::mb_escape($search)));
            $this->andWhere(["`name` REGEXP '(^|[\\\\s\",._-])". $search ."'"]);
        } else {
            $search = implode("\\\\s+", preg_split("/\s+/", self::mb_escape($search)));
            $this->andWhere(["`name` REGEXP '(^|[\\\\s\",._-])". $search ."([\\\\s\",._-]|$)'"]);
        }
        if ($this->className != Category::class && $searchInText) {
            if (!$strict) {
                $str = " OR `text` REGEXP '(^|[\\\\s\",._-])". $search ."'";
            } else {
                $str = " OR `text` REGEXP '(^|[\\\\s\",._-])". $search ."([\\\\s\",._-]|$)'";
            }
            $pos = strrpos($this->where, "'");
            $this->where = substr($this->where, 0, $pos+1) . $str . substr($this->where,$pos+1);
        }

        return $this;
    }

    /**
     * @method object|null fetchOne(int $id, bool $loadEmbedded = false, int $maxDepth = 3)
     */
    public function fetchOne(int $id, bool $loadEmbedded = false, int $maxDepth = 3)
    {
        $query = clone $this;
        $query
            ->whereEq(["id" => $id])
            ->setLimit(1);

        $results = $query->fetch($loadEmbedded, $maxDepth);

        return count($results) > 0
            ? $results[0]
            : null;
    }

    /**
     * @method object|null findOneBy(array $args, bool $loadEmbedded = false, int $maxDepth = 3)
     */
    public function findOneBy($args, bool $loadEmbedded = false, int $maxDepth = 3)
    {
        $query = clone $this;
        $query->whereEq($args);

        $results = $query->fetch($loadEmbedded, $maxDepth);

        return count($results) > 0
            ? $results[0]
            : null;
    }

    /**
     * @method bool save(object $entity)
     * @throws \Exception
     */
    public function save($entity)
    {
        global $dbal;

        $tableName = static::getTableNameByClass($this->className);
        $columns = static::getColumnsByClass($this->className);

        $data = [];
        array_walk($columns, function($column, $key) use ($entity, &$data) {
            $data[ $column['name'] ] = $entity->$key;
            if ($data[ $column['name'] ] instanceof \Datetime) {
                $data[ $column['name'] ] = $data[ $column['name'] ]->format('Y-m-d H:i:s');
            }
            if (is_array($data[ $column['name'] ])) {
                $data[ $column['name'] ] = json_encode($data[ $column['name'] ]);
            }
            if (is_bool($data[ $column['name'] ])) {
                $data[ $column['name'] ] = (int) $data[ $column['name'] ];
            }
        });

        if (empty($data["id"])) {

            // insert

            $keys = '';
            $values = '';

            unset($data['id']);

            foreach ($data as $key => $value) {
                if ($key == "id") continue;
                $keys .= '`' . $key . '`, ';
                $values .= ':' . $key . ', ';
                if ($value === false) {
                    $data[$key] = 0;
                }
            }
            $keys = substr($keys, 0, -2);
            $values = substr($values, 0, -2);

            $st = $dbal->prepare("INSERT INTO " . $tableName . " (" . $keys . ") VALUES (" . $values . ")");
            $st->execute($data);
            $entity->id = $dbal->lastInsertId();

        } else {

            // update

            $placeholder = '';

            foreach ($data as $key => $value) {
                if ($key == "id") continue;
                $placeholder .= '`' . $key . '` = :'. $key .', ';
            }
            $placeholder = substr($placeholder, 0, -2);

            $st = $dbal->prepare("UPDATE " . $tableName . " SET " . $placeholder . " WHERE `id` = :id");
            $st->execute($data);
        }

        return true;
    }

    /**
     * @method void remove(object $entity)
     */
    public function remove($entity)
    {
        global $dbal;

        $tableName = static::getTableNameByClass($this->className);

        $st = $dbal->prepare("DELETE FROM " . $tableName . " WHERE `id` = :id");
        $st->execute(['id' => $entity->id]);
    }

    /**
     * @method void createTableByClass(string $className, ?string $tableName)
     */
    public static function createTableByClass($className, $tableName)
    {
        global $dbal, $TPF_CONFIG;

        $charset = $TPF_CONFIG['db']['charset'] ?? 'utf8';
        $tableName = $tableName ?? static::getTableNameByClass($className);

        /** create table */
        $columns = static::getColumnsByClass($className);
        $columns = array_column($columns, "full");

        $sqlColumns = implode(",\n", $columns);
        $sql = "CREATE TABLE IF NOT EXISTS `$tableName` (" . $sqlColumns . ", PRIMARY KEY(`id`)) CHARSET " .$charset;

        static::exec($sql);

        /** create indexes */

        $oldIndexes = $dbal->query("SHOW INDEX FROM $tableName")->fetchAll(\PDO::FETCH_ASSOC);
        $oldIndexes = array_column($oldIndexes, "Key_name");

        $indexes = static::getIndexesByClass($className);
        foreach ($indexes as $index) {

            if (!in_array($index->id, $oldIndexes))
                static::createIndex($tableName, $index);
        }
    }

    public static function createIndex($tableName, $index)
    {
        $columns = $index->columns;
        $columns = array_map(function($name) {
            
            $name = preg_replace("#([a-z])([A-Z])#su", "$1_$2", $name);
            return mb_strtolower($name);

        }, $columns);

		$sql = "CREATE %s INDEX %s ON %s (`%s`)";
		$sql = sprintf($sql,
            /** optional unique */ $index->isUnique ? "UNIQUE" : "",
            /** key name */ $index->id,
            /** table name */ $tableName,
            /** columns */ implode("`,`", $columns)
        );
		
		static::exec($sql);
    }

    /**
     * @method string getTableNameByClass(string $className)
     */
    public static function getTableNameByClass($className)
    {
        global $TPF_CONFIG;

        $parts = explode("\\", $className);
        $name = lcfirst(array_pop($parts));

        while ($parts[count($parts)-1] != 'Model') {
            $name = lcfirst(array_pop($parts)) . '_' . $name;
        }

        $name = preg_replace("#([a-z])([A-Z])#su", "$1_$2", $name);
        $name = mb_strtolower($name);

        return implode("", [
            isset($TPF_CONFIG['db']['table_prefix']) ? $TPF_CONFIG['db']['table_prefix'] . '_' : '',
            $name,
        ]);
    }

    /**
     * @method array getColumnsByClass(string $className)
     * @throws ORMException
     */
    public static function getColumnsByClass($className): array
    {
        $columns = [];

        $reflection = new \ReflectionClass($className);
        $code = file_get_contents($reflection->getFileName());
        //$comment = $reflection->getDocComment();
        
        $properties = [];
        if (preg_match_all("#\@property\s+(?<type>\w+)\s*\\$(?<name>\w+)#uim", $code, $properties, PREG_SET_ORDER)) {

            foreach ($properties as $property) {

                $name = $property["name"];
                $name = preg_replace("#([a-z])([A-Z])#su", "$1_$2", $name);
                $name = mb_strtolower($name);

                $columnFull = "";
                switch ($property["type"]) {

                    case "string":
                        $columnFull = "`$name` VARCHAR(255) NOT NULL DEFAULT ''";
                        break;

                    case "int":
                        $columnFull = "`$name` INTEGER(11) NOT NULL DEFAULT 0";
                        if ($name == static::PRIMARY_COLUMN_KEY)
                            $columnFull .= " AUTO_INCREMENT";
                        break;

                    case "float":
                        $columnFull = "`$name` FLOAT NOT NULL DEFAULT 0";
                        break;

                    case "bool":
                        $columnFull = "`$name` TINYINT(1) NOT NULL";
                        break;

                    case "text":
                        $columnFull = "`$name` TEXT NOT NULL";
                        break;

                    case "datetime":
                        $columnFull = "`$name` DATETIME DEFAULT NULL";
                        break;

                    case "json":
                        $columnFull = "`$name` JSON NOT NULL";
                        break;

                    default:
                        throw new ORMException("Unknown property type {$property['type']}");
                        break;
                }

                $columns[$property["name"]] = [
                    "property" => $property["name"],
                    "name" => $name,
                    "full" => $columnFull,
                ];
            }
        }
		
        return $columns;
    }

    /**
     * @method array getIndexesByClass(string $className)
     */
    public static function getIndexesByClass($className)
    {
        $reflection = new \ReflectionClass($className);
        $code = file_get_contents($reflection->getFileName());

        $results = [];
        preg_match_all("#\@(?<type>\w*)\s*index\s*\((?<columns>[\\$\w\s\,]+)\)#mui", $code, $results, PREG_SET_ORDER);

        $indexes = [];
        foreach($results as $result)
		{
			$columns = $result["columns"];
			$columns = preg_replace("#\s|\\$#", "", $columns);
            $id = "AUTO_" . md5(mb_strtolower($columns));

			$columns = explode(",", $columns);

            $indexes[] = (object)[
                "isUnique" => (bool) preg_match("#unique#sui", $result["type"]),
                "columns" => $columns,
                "id" => $id,
            ];
		}

		return $indexes;
    }
}