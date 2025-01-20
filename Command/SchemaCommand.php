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

    public static function diff(array $args)
    {
        $diffs = getEntitySchemaDiff($args[0] ?? '*');

        print "Calculating diff..." . "\r\n";

        foreach ($diffs as $className => $diff) {
            if (empty($diff)) continue;

            $existingColumns = getEntityTableColumns($className);

            $del = []; $add = [];

            foreach ($diff as $entry) {
                for ($i = 0; $i < $entry['deleteCount']; $i++) {
                    $el = $existingColumns[$entry['position'] + $i];
                    $del[] = strtoupper($el['Type']) . ' ' . $el['Field'];
                }
                foreach ($entry['add'] as $field) {
                    $add[] = implode(' ', array_reverse(array_slice(explode(' ', $field['full']), 0, 2)));
                }
            }

            $str = trim((!empty($del) ? '- ' . implode(', ', $del) : '') . '; ' . (!empty($add) ? '+ ' . implode(', ', $add) : ''), '; ');

            if (!empty($str) > 0) {
                print "\r\n" . $className . ":\r\n";
                print $str . "\r\n";
            }
        }
    }
}