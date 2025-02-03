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

    protected array $joinTables = [];

    public static array $identityMap = [];

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

    public function where(array|string $args, bool $or = false)
    {
        $this->where = "";

        if (gettype($args) == 'string') $args = [$args];

        if ($args !== [] && array_keys($args) !== range(0, count($args) - 1)) {
            return $this->whereEq($args, $or);
        }

        if (gettype($args) == 'string') $args = [$args];

        if (empty($args)) {
            return $this;
        }
        if (count($args) == 1 && empty($this->where)) {
            $this->where = $args[0];
            return $this;
        }
        if (!empty($this->where) && !preg_match('/^\\(.*\\)$/', $this->where)) {
            $this->where = '(' . $this->where . ')';
        }
        foreach ($args as $str) {
            $this->where .= (!empty($this->where) ? ($or ? " OR " : " AND ") : "") . "(" . $str . ")";
        }

        return $this;
    }

    public function and()
    {
        if (!empty($this->where)) {
            $this->where = preg_replace("/\\s+(AND|OR)\\s+$/", "", $this->where);
        }
        $this->where = !empty($this->where) ? '(' . $this->where . ')' . " AND " : '';

        return $this;
    }

    public function or()
    {
        if (!empty($this->where)) {
            $this->where = preg_replace("/\\s+(AND|OR)\\s+$/", "", $this->where);
        }
        $this->where = !empty($this->where) ? '(' . $this->where . ')' . " OR " : '';

        return $this;
    }

    public function andWhere(array|string $args, bool $or = false)
    {
        return $this->and()->where($args, $or);
    }

    public function orWhere(array|string $args, bool $or = false)
    {
        return $this->or()->where($args, $or);
    }

    public function andWhereEq(array $args, bool $or = false)
    {
        return $this->and()->whereEq($args, $or);
    }

    public function orWhereEq(array $args, bool $or = false)
    {
        return $this->or()->whereEq($args, $or);
    }

    public function whereEq(array $args, bool $or = false)
    {
        if (!is_null($this->where) && !preg_match("/\\s+(AND|OR)\\s+$/", $this->where)) {
            $this->where = "";
        }

        $cond = "";
        foreach ($args as $name => $value) {
            if (is_array($value)) {
                $value = $this->processChildWhere(null, $name, $value);
                if ($value) {
                    if (!empty($cond)) {
                        $cond .= $or ? " OR " : " AND ";
                    }
                    $cond .= $value;
                }
                continue;
            }
            if (!empty($cond)) {
                $cond .= $or ? " OR " : " AND ";
            }
            $cond .= "`$name` = ";
            $cond .= is_numeric($value) ? $value : "'" . self::mb_escape($value) . "'";
        }
        if (!empty($this->where)) {
            $this->where .= '(' . $cond . ')';
        } else {
            $this->where = $cond;
        }

        return $this;
    }

    protected function processChildWhere(?string $table, string $field, array $cond): ?string
    {
        $path = getFilePathByClass($this->className);
        require_once $path;

        $property = new ReflectionProperty($this->className, $field);
        if ($property->getType()->isBuiltin()) {
            throw new ORMException('Cannot apply composite condition to scalar field');
        }
        $type = $property->getType()->getName();
        $tableName = Repository::getTableNameByClass($type);

        if (!$table) {
            $table = Repository::getTableNameByClass($this->className);
        }

        $colName = preg_replace_callback("/(?<!\b)[A-Z]/", function ($matches) {
            return '_' . strtolower($matches[0]);
        }, $field) . '_id';

        $columns = array_values(array_filter(Repository::getColumnsByClass($this->className), function ($column) use ($colName) {
            return $column['name'] == $colName;
        }));

        if (!empty($columns)) {
            $where = [];
            if (empty(array_filter($this->joinTables, function ($table) use ($tableName) {
                return $table['table'] == $tableName;
            }))) {
                $this->joinTables[] = ['table' => $tableName, 'fromTable' => $table, 'column' => $colName, 'refColumn' => 'id'];
            }

            if ($cond !== [] && array_keys($cond) !== range(0, count($cond) - 1)) {
                foreach ($cond as $column => $value) {
                    if (is_array($value)) {
                        $value = $this->processChildWhere($tableName, $column, $value);
                        if ($value) {
                            $where[] = $value;
                        }
                        continue;
                    }
                    $where[] = '`' . $tableName . '`.`' . $column . '` = ' . (is_numeric($value) ? $value : "'" . self::mb_escape($value) . "'");
                }
            } else {
                foreach ($cond as $value) {
                    $where[] = $value;
                }
            }

            return implode(' AND ', $where);
        }

        return null;
    }

    public function sortBy(array $order): Query
    {
        $this->order = $order;

        return $this;
    }

    public static function mb_escape(?string $string)
    {
        return $string ? preg_replace("/[\\x00\\x0A\\x0D\\x1A\\x22\\x27\\x5C]/u", "\\\\0", $string) : '';
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
        $sql .= $this->getOrderExpr();

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
                    if (strtolower($type) == 'string' && !$property->getType()->allowsNull() && $value === null) {
                        $value = '';
                    }
                    if (preg_match("/^int|float$/", strtolower($type)) && !$property->getType()->allowsNull() && $value === null) {
                        $value = 0;
                    }
                    if (strtolower($type) == 'datetime' && $value != null) {
                        $value = DateTime::createFromFormat("Y-m-d H:i:s", $value);
                    }
                    else if (strtolower($type) == 'date' && $value != null) {
                        $value = DateTime::createFromFormat("Y-m-d", $value);
                    }
                    else if (strtolower($type) == 'array' && $value != null) {
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
                        if (!property_exists($this->className, $targetFieldName)) continue;
                        $property = new ReflectionProperty($this->className, $targetFieldName);
                        $fullClassName = $property->getType()->getName();
                        $parts = explode('\\', $fullClassName);
                        $className = end($parts);

                        if (isset(self::$identityMap[$fullClassName]) && isset(self::$identityMap[$fullClassName][$value])) {
                            $entity->{$targetFieldName} = self::$identityMap[$fullClassName][$value];
                            continue;
                        }

                        $path = getFilePathByClass($fullClassName);
                        require_once $path;

                        loadParentClasses($path);
                        $obj = new $fullClassName;
                        if ($obj instanceof AbstractEntity) {
                            $fieldName = lcfirst($className);
                            $obj = $fullClassName::load($value, $loadEmbedded, $maxDepth-1);
                            $entity->{$targetFieldName} = $obj;
                            if (!isset(self::$identityMap[$fullClassName])) {
                                self::$identityMap[$fullClassName] = [];
                            }
                            self::$identityMap[$fullClassName][$obj->id] = $obj;
                        }
                    }
                }
            }
            $results[] = $entity;
        }

        return $results;
    }

    public function join(string $className, string $column, string $refColumn = 'id', ?string $fromTable = null)
    {
        $this->joinTables[] = ['table' => Repository::getTableNameByClass($className), 'fromTable' => $fromTable, 'column' => $column, 'refColumn' => $refColumn];

        return $this;
    }

    public function reset()
    {
        $this->joinTables = [];

        return $this;
    }

    public function select($select = '*'): array
    {
        global $dbal;

        $sql = $this->prepareSelect($select);

        /** sort order */
        $sql .= $this->getOrderExpr();

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
        if (!empty($this->joinTables)) {
            $select = implode(', ', array_map(function($str) use($tableName) {
                return "`" . $tableName . "`." . $str;
            }, preg_split("/\s*,\s*/", $select)));
        }
        $sql = "SELECT " . $select . " FROM `" . $tableName . "`";

        if (!empty($this->joinTables)) {
            foreach ($this->joinTables as $tableData) {
                $sql.= " LEFT JOIN `" . $tableData['table'] . "` ON `" . ($tableData['fromTable'] ?? $tableName) . "`.`" . $tableData['column'] . "` = `" . $tableData['table'] . "`.`" . $tableData['refColumn'] . "`";
            }
        }

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

    protected function getOrderExpr(): string
    {
        if (empty($this->order)) {
            return '';
        }
        $parts = [];
        foreach ($this->order as $key => $value) {
            $parts[] = sprintf("`%s` %s", $key, $value);
        }

        return ' ORDER BY ' . implode(', ', $parts);
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

        $fields = array_map(fn($value) => is_bool($value) ? (int) $value : $value, $fields);

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
