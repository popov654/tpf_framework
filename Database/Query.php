<?php

namespace Tpf\Database;

use DateTime;
use ReflectionProperty;
use Tpf\Model\AbstractEntity;
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
            $this->where .= (!empty($this->where) ? " AND " : "") . "(" . $str . ")";
        }

        return $this;
    }

    public function orWhere(array $args)
    {
        if (empty($args)) {
            return $this;
        }
        if (empty($this->where)) {
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
            $cond .= is_numeric($value) ? $value : "'" . self::mb_escape($value) . "'";
        }
        $this->where .= (strlen($this->where) > 0 ? " AND " : "") . $cond;

        return $this;
    }

    public function sortBy(array $order): Query
    {
        $this->order = $order;

        return $this;
    }

    public static function mb_escape(string $string)
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
                    preg_match("/([a-zA-Z0-9]+)Id$/", $column['property'], $matches);
                    if (isset($matches[0])) {
                        $targetFieldName = $matches[1];
                        $property = new ReflectionProperty($this->className, $targetFieldName);
                        $fullClassName = $property->getType()->getName();
                        $className = @array_pop(explode('\\', $fullClassName));

                        if (in_array($className, ['User', 'Session', 'Category', 'Comment'])) {
                            require_once PATH . '/vendor/' . VENDOR_PATH . '/Model/' . $className . '.php';
                        } else {
                            require_once PATH . '/src/Model/' . $className . '.php';
                        }

                        $obj = new $fullClassName;
                        if ($obj instanceof AbstractEntity) {
                            $fieldName = lcfirst($className);
                            $entity->{$targetFieldName} = $fullClassName::load($value, $loadEmbedded, --$maxDepth);
                        }
                    }
                }
            }
            $results[] = $entity;
        }

        return $results;
    }

    public function select($select = '*'): array
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

    public function delete()
    {
        global $dbal;

        $sql = $this->prepareDelete();
        $dbal->exec($sql);
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

    protected function prepareDelete(): string
    {
        $tableName = Repository::getTableNameByClass($this->className);

        /** base sql query */
        $sql = "DELETE FROM `" . $tableName . "`";

        /** where */
        if (!is_null($this->where)) {
            $sql.= " WHERE " . $this->where;
        }

        return $sql;
    }

    public function update($fields)
    {
        global $dbal;

        $placeholder = '';

        foreach ($fields as $key => $value) {
            if ($key == "id") continue;
            $placeholder .= '`' . $key . '` = :'. $key .', ';
        }
        $placeholder = substr($placeholder, 0, -2);

        $sql = $this->prepareUpdate($placeholder);
        $st = $dbal->prepare($sql);
        $st->execute($fields);
    }

    protected function prepareUpdate($data)
    {
        $tableName = Repository::getTableNameByClass($this->className);

        /** base sql query */
        $sql = "UPDATE `" . $tableName . "` SET " . $data;

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