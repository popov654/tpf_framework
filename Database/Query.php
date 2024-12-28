<?php

namespace Tpf\Database;

use DateTime;
use ReflectionProperty;
use Tpf\Service\Logger;


class Query
{
    protected $className;
    protected $limit = null;
    protected $offset = null;
    protected $where = null;
    protected array $order = ["id" => "desc"];

    public function __construct($className)
    {
        $this->className = $className;
    }

    public function setOffset(?int $offset)
    {
        $this->offset = is_null($offset) ? null : intval($offset);
        return $this;
    }

    public function setLimit(?int $limit)
    {
        $this->limit = is_null($limit) ? null : intval($limit);
        return $this;
    }

    public function where(array $args)
    {
        $this->where = "";

        return $this->andWhere($args);
    }

    public function andWhere(array $args)
    {
        if (empty($args)) {
            return $this;
        }
        foreach ($args as $str) {
            $this->where .= (strlen($this->where) > 0 ? " AND " : "") . "(" . $str . ")";
        }

        return $this;
    }

    public function orWhere(array $args)
    {
        if (empty($args)) {
            return $this;
        }
        if (strlen($this->where) == 0) {
            return $this->where($args);
        }
        $this->where = "(" . $this->where . ") OR ";
        foreach ($args as $str) {
            $this->where .= "(" . $str . ")";
        }

        return $this;
    }

    public function whereEq(array $args)
    {
        $this->where = "";

        return $this->andWhereEq($args);
    }

    public function andWhereEq(array $args)
    {
        $cond = "";
        foreach ($args as $name => $value) {
            if (!empty($cond)) {
                $cond .= " AND ";
            }
            $cond .= "`$name` = ";
            $cond .= is_numeric($value) ? $value : "'" . $this->mb_escape($value) . "'";
        }
        $this->where .= (strlen($this->where) > 0 ? " AND " : "") . $cond;

        return $this;
    }

    public function sortBy(array $order): Query
    {
        $this->order = $order;

        return $this;
    }

    public function mb_escape(string $string)
    {
        return preg_replace("/[\\x00\\x0A\\x0D\\x1A\\x22\\x27\\x5C]/u", "\\\$0", $string);
    }

    /**
     * @method array fetch(bool $loadEmbedded = false, int $maxDepth = 3)
     * returns entities by query
     */
    public function fetch($loadEmbedded = false, $maxDepth = 3): array
    {
        $columns = Repository::getColumnsByClass($this->className);

        $sql = $this->prepareSelect("*");

        /** sort order */
        $orderColumn = array_key_first($this->order);
        $sql .= sprintf(" ORDER BY `%s` %s", $orderColumn, $this->order[$orderColumn]);

        /** offset & limit */
        if (!is_null($this->offset) || !is_null($this->limit)) {

            $sql.= " LIMIT ";
            if (!is_null($this->offset)) {

                $sql.= $this->offset;
                if (!is_null($this->limit))
                    $sql .= ", ";
            }

            if (!is_null($this->limit))
                $sql .= $this->limit;
        }

        /** find result */
        global $dbal;
        $st = $dbal->query($sql);
        $data = $st->fetchAll(\PDO::FETCH_OBJ);

        $results = [];

        foreach ($data as $row) {

            $entity = new $this->className();
            foreach ($columns as $column) {
                $value = $row->{$column["name"]};
                try {
                    $property = new ReflectionProperty($this->className, $column['property']);
                    $type = $property->getType()->getName();
                    if (strtolower($type) == 'datetime' && $value != null) {
                        $value = DateTime::createFromFormat("Y-m-d H:i:s", $value);
                    }
                    if (strtolower($type) == 'array' && $value != null) {
                        $value = json_decode($value, true);
                    }
                } catch (\Exception $ex) {
                    Logger::error($ex);
                }
                $entity->{$column['property']} = $value;

                if (is_numeric($value) && $loadEmbedded && $maxDepth > 0) {
                    preg_match("/([a-z0-9]+)Id$/", $column['property'], $matches);
                    if (isset($matches[0])) {
                        $className = preg_replace_callback("/(^|_)([a-z])/", function ($matches) {
                            return strtoupper($matches[2]);
                        }, $matches[1]);
                        $fullClassName = 'Tpf\\Model\\' . $className;
                        $obj = new $fullClassName;
                        if ($obj instanceof AbstractEntity) {
                            $fieldName = preg_replace_callback("/^[A-Z]/", function ($matches) {
                                return strtolower($matches[0]);
                            }, $className);
                            $entity->{$fieldName} = $fullClassName::load($value, $loadEmbedded, --$maxDepth);
                        }
                    }
                }
            }
            $results[] = $entity;
        }

        return $results;
    }

    public function select($select): array
    {
        global $dbal;

        $sql = $this->prepareSelect($select);
        $st = $dbal->query($sql);

        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function count()
    {
        $result = $this->select("count(1) as `count`");
        return isset($result[0])
            ? intval($result[0]["count"])
            : 0;
    }

    protected function prepareSelect($select): string
    {
        $tableName = Repository::getTableNameByClass($this->className);

        /** base sql query */
        $sql = "SELECT " . $select . "FROM `" . $tableName . "`";

        /** where */
        if (!is_null($this->where)) {
            $sql.= " WHERE " . $this->where;
        }

        return $sql;
    }

    public static function exec($sql)
    {
        global $dbal;

        $dbal->exec($sql);
    }
}