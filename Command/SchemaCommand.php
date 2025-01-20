<?php

use Tpf\Database\Repository;

class SchemaCommand
{
    public static function update(array $args)
    {
        $diffs = getEntitySchemaDiff($args[0] ?? '*');

        print "Calculating diff..." . "\r\n";

        foreach ($diffs as $className => $diff) {
            if (empty($diff)) continue;

            $tableName = Repository::getTableNameByClass($className);
            $existingColumns = getEntityTableColumns($className);
            $statements = Repository::applyDiff($tableName, $existingColumns, $diff, isset($args['dump-sql']));

            $n = 0;

            foreach ($statements as $statement) {
                print $statement . ";\r\n";
                $n++;
            }

            if ($n > 0) print "\r\n";
        }
        if (!isset($args['dump-sql'])) {
            print "Schema is up to date.";
        }
    }
}