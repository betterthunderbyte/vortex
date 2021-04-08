<?php declare(strict_types=1); namespace core;

use core\field\BlobField;
use core\field\BoolField;
use core\field\CreateTimestampField;
use core\field\DateField;
use core\field\DateTimeField;
use core\field\DecimalField;
use core\field\DoubleField;
use core\field\Field;
use core\field\ForeignField;
use core\field\IntField;
use core\field\SetField;
use core\field\TextField;
use core\field\TimeField;
use core\field\TimestampField;
use PDOException;

/**
 * MIT License
 *
 * Copyright (c) 2019 jeamu
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

class MariaDbBuilder implements IDatabaseBuilder
{
    private static $pdo = null;

    public function __construct()
    {
        if (self::$pdo === null) {
            $connection = Register::getConnection();
            if($connection instanceof Connection)
			{
				self::$pdo = $connection->get_pdo();
			}
        }
    }

    public function getCompleteData(string $database, string $table): array
    {
        if (!isset($database{0})) {
            return array();
        }

        if (!isset($table{0})) {
            return array();
        }

        $sql = 'SELECT * FROM `' . strtolower($database) . '`.`' . strtolower($table) . '`';

        $get_complete_data = self::$pdo->prepare($sql);

        $data = array();

        if ($get_complete_data->execute(array()))
        {
            $data = $get_complete_data->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $data;
    }

    public function getAllTables(string $database): array
    {
        if (!isset($database{0})) {
            return array();
        }

        $sql = 'SHOW TABLES FROM `' . strtolower($database) . '`';

        $get_all_tables = self::$pdo->prepare($sql);

        $tables = array();

        if ($get_all_tables->execute(array())) {
            while ($row = $get_all_tables->fetch()) {
                if (count($row) > 0) {
                    $tables[] = $row[0];
                }
            }
        }

        return $tables;
    }

    // ToDo weitermachen für das updaten
    public function getAllFields(string $database, string $table): array
    {
        if (!isset($database{0}))
        {
            return array();
        }

        if (!isset($table{0}))
        {
            return array();
        }


        $sql = 'SHOW FIELDS FROM `' . strtolower($database) . '`.`' . $table . '`';

        $get_all_fields = self::$pdo->prepare($sql);

        $fields = array();

        if ($get_all_fields->execute(array())) {
            while ($row = $get_all_fields->fetch(\PDO::FETCH_ASSOC)) {
                if (count($row) > 0) {
                    $fields[] = $row['Field'];
                }
            }
        }

        return $fields;
    }

    public function databaseExists(string $database): bool
    {
        if (!isset($database{0}))
        {
            return false;
        }

        $sql = 'SHOW DATABASES LIKE "' . strtolower($database) . '"';

        $show_database = self::$pdo->prepare($sql);

        if ($show_database->execute()) {
            while ($row = $show_database->fetch()) {
                if (count($row) > 0 AND $row[0] === strtolower($database)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function tableExists(string $database, string $table): bool
    {
        if (!isset($database{0}))
        {
            return false;
        }

        if (!isset($table{0}))
        {
            return false;
        }

        if (!$this->databaseExists($database))
        {
            return false;
        }

        $sql = 'SHOW TABLES FROM `' . strtolower($database) . '` LIKE "' . strtolower($table) . '"';

        $show_tables = self::$pdo->prepare($sql);
        if ($show_tables->execute([])) {
            while ($row = $show_tables->fetch()) {
                if (count($row) > 0 AND $row[0] === strtolower($table)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function fieldExists(string $database, string $table, string $field_name): bool
    {
        if (!isset($database{0}))
        {
            return false;
        }

        if (!isset($table{0}))
        {
            return false;
        }

        if (!isset($field_name{0}))
        {
            return false;
        }

        $sql = 'SHOW COLUMNS FROM `' . strtolower($database) . '`.`' . strtolower($table) . '`';

        $show_columns = self::$pdo->prepare($sql);
        try {
            if ($show_columns->execute()) {
                while ($row = $show_columns->fetch()) {
                    if (isset($row['Field']) AND $row['Field'] === strtolower($field_name)) {
                        return true;
                    }
                }
            }
        } catch (PDOException $e) {
            echo print_r($e->getMessage(), true);
        }
        return false;
    }

    public function createDatabase(string $database): void
    {
        if (!isset($database{0})) {
            return;
        }

        set_time_limit(0);
        //Logger::Log("Create Database ", $database);

        $sql = 'CREATE DATABASE IF NOT EXISTS `' . strtolower($database) . '` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

        $create_database_statement = self::$pdo->prepare($sql);

        if (!$create_database_statement->execute([])) {
            //Logger::Error("Failed to create Database ", $database, " SQL: ", $sql);
            //throw new FailedToCreateDatabaseException();
        }
    }

    public function dropDatabase(string $database): void
    {
        if (!isset($database{0}))
        {
            return;
        }

        //Logger::Log("Drop Database ", $database);
        $sql = 'DROP DATABASE IF EXISTS `' . strtolower($database) . '`';

        $drop_database_statement = self::$pdo->prepare($sql);

        if (!$drop_database_statement->execute([])) {
            //Logger::Error("Failed to drop Database ", $database, " SQL: ", $sql);
            //throw new FailedToDropDatabaseException();
        }
    }

    public function createTable(string $database, string $table): void
    {
        if (!isset($database{0}))
        {
            return;
        }

        if (!isset($table{0}))
        {
            return;
        }

        $sql = 'CREATE TABLE IF NOT EXISTS `' . strtolower($database) . '`.`' . strtolower($table) . '` (`' . strtolower($table) . '_pk` INT AUTO_INCREMENT PRIMARY KEY) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

        $create_table_statement = self::$pdo->prepare($sql);

        if (!$create_table_statement->execute([])) {
            //Logger::Error("Failed to create Table ", $table, " Database ", $database, " SQL: ", $sql);
            //throw new FailedToCreateTableException();
        }
    }

    public function dropTable(string $database, string $table): void
    {
        if (!isset($database{0}))
        {
            return;
        }

        if (!isset($table{0}))
        {
            return;
        }

        $sql = 'DROP TABLE `' . strtolower($database) . '`.`' . strtolower($table) . '`';

        $drop_table_statement = self::$pdo->prepare($sql);

        if (!$drop_table_statement->execute([])) {
            //Logger::Error("Failed to drop Table ", $table, " in Database ", $database, " SQL: ", $sql);
            //throw new FailedToDropTableException();
        }
    }

    public function renameTable(string $database, string $from, string $to): void
    {
        if (!isset($database{0}))
        {
            return;
        }

        if (!isset($from{0}))
        {
            return;
        }

        if (!isset($to{0}))
        {
            return;
        }

        $sql = 'RENAME TABLE `' . strtolower($database) . '`.`' . strtolower($from) . '` TO `' . strtolower($database) . '`.`' . strtolower($to) . '`;';

        $rename_table_statement = self::$pdo->prepare($sql);

        if (!$rename_table_statement->execute([])) {
            //Logger::Error("Failed to rename Table From ", $from, " To ", $to, " in Database: ", $database, " SQL: ", $sql);
            //throw new FailedToRenameTableException();
        }
    }

    public function addField(string $database, string $table, Field $field): void
    {
        if (!isset($database{0}))
        {
            return;
        }

        if (!isset($table{0}))
        {
            return;
        }

        $sql = 'ALTER TABLE `' . strtolower($database) . '`.`' . strtolower($table) . '` ADD `' . $field->getName() . '` ' . $this->createFieldString(strtolower($database), strtolower($table), $field);

        $add_field_statement = self::$pdo->prepare($sql);

        if (!$add_field_statement->execute([]))
        {
            //Logger::Error("Failed to create Field ", $field->getName(), " Table: ", $table, " Database: ", $database, " SQL: ", $sql);
            //throw new FailedToAddFieldException(print_r($add_field_statement->errorInfo()));
        }

        $sql = $this->createUniqueString(strtolower($database), strtolower($table), $field);
        if(isset($sql{1}))
        {
            $add_unique_statement = self::$pdo->prepare($sql);
            if(!$add_unique_statement->execute())
            {
            }
        }
    }

    public function dropField(string $database, string $table, string $name): void
    {
        if (!isset($database{0}))
        {
            return;
        }

        if (!isset($table{0}))
        {
            return;
        }

        if (!isset($name{0}))
        {
            return;
        }

        $sql = 'ALTER TABLE `' . strtolower($database) . '`.`' . strtolower($table) . '` DROP COLUMN `' . strtolower($name) . '`; ';

        $drop_field_statement = self::$pdo->prepare($sql);

        if (!$drop_field_statement->execute([])) {
            //Logger::Error("Failed to drop Field ", $name, " Table ", $table, " Database ", $database, " SQL: ", $sql);
            ///throw new FailedToDropFieldException(print_r($drop_field_statement->errorInfo()));
        }

        // ToDo Drop Unique Field
    }

    public function editField(string $database, string $table, string $name, Field $to): void
    {
        if (!isset($database{0}))
        {
            return;
        }

        if (!isset($table{0}))
        {
            return;
        }

        if (!isset($name{0}))
        {
            return;
        }

        $sql = 'ALTER TABLE `' . strtolower($database) . '`.`' . mb_strtolower($table) . '` ALTER COLUMN `' . $to->getName() . '` ' . $this->createFieldString(mb_strtolower($database), mb_strtolower($table), $to);

        $edit_field_statement = self::$pdo->prepare($sql);

        if (!$edit_field_statement->execute([]))
        {
            //Logger::Error("Failed to change Field ", $database, " SQL: ", $sql);
            //throw new FailedToModifyFieldException();
        }

        // ToDo Edit Unique Field
    }

    public function renameField(string $database, string $table, string $from, Field $to): void
    {
        if (!isset($database{0}))
        {
            return;
        }

        if (!isset($table{0}))
        {
            return;
        }

        if (!isset($from{0}))
        {
            return;
        }

        $sql = 'ALTER TABLE `' . strtolower($database) . '`.`' . strtolower($table) . '` CHANGE COLUMN `' . strtolower($from) . '` `' . strtolower($to->getName()) . '` ' .
            $this->createFieldString(strtolower($database), strtolower($table), $to);

        $rename_field_statement = self::$pdo->prepare($sql);

        if (!$rename_field_statement->execute([]))
        {
            //Logger::Error("Failed to rename Field ", $database, " SQL: ", $sql);
            //throw new FailedToRenameFieldException();
        }
    }

    private function createUniqueString(string $database, string $table, Field $field) : string
    {
        $sql = '';

        switch (get_class($field))
        {
            case ForeignField::class:
            case IntField::class:
                if($field->isUnique())
                {
                    $sql = 'CREATE UNIQUE INDEX `' . $table . '_' . $field->getName() . '` ON `' . $database . '`.`' . $table . '` (`' . $field->getName() . '`);';
                }
                /*
                else
                {
                    $sql = 'DROP INDEX IF EXISTS `' . $table . '_' . $field->getName() . '` ON `' . $table . '`;';
                }*/
                break;
            case Field::class:
            case TextField::class:
                if($field->getLength() > 0)
                {
                    if($field->isUnique())
                    {
                        $sql = 'CREATE UNIQUE INDEX `' . $table . '_' . $field->getName() . '` ON `' . $database . '`.`' . $table . '` (`' . $field->getName() . '`);';
                    }/*
                    else
                    {
                        $sql = 'DROP INDEX IF EXISTS `' . $table . '_' . $field->getName() . '` ON `' . $table . '`;';
                    }*/
                }

                break;

            default:
        }

        return $sql;
    }

    private function createFieldString(string $database, string $table, Field $field): string
    {
        $sql = '';

        switch (get_class($field)) {
            case DateField::class:

                $sql .= 'date ' . ($field->getNull() ? 'DEFAULT NULL' : 'NOT NULL') . ';';
                break;
            case DateTimeField::class:
                $sql .= 'datetime ' . ($field->getNull() ? 'DEFAULT NULL' : 'NOT NULL') . ';';
                break;
            case DecimalField::class:
                if ($field instanceof DecimalField) {
                    $sql .= 'decimal(' . $field->getIntegralPart() . ', ' . $field->getFractionalPart() . ') ' . ($field->getNull() ? 'DEFAULT NULL' : 'NOT NULL') . ';';
                }
                break;
            case DoubleField::class:
                $sql .= 'double ' . ($field->getNull() ? 'DEFAULT NULL' : 'NOT NULL') . ';';
                break;
            case ForeignField::class:
            case IntField::class:
                $sql .= 'int(11) ' . ($field->getNull() ? 'DEFAULT NULL' : 'NOT NULL') . ';';
                break;
            case SetField::class:
                $set_string = '';

                if ($field instanceof SetField)
                {
                    foreach ($field->getOptions() as $option)
                    {
                        $set_string .= '\'' . $option . '\', ';
                    }

                    $set_string = substr($set_string, 0, -2);

                    if ($field->getDefault())
                    {
                        $sql .= 'set(' . $set_string . ') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "' . $field->getDefault() . '";';
                    }
                    else
                    {
                        $sql .= 'set(' . $set_string . ') COLLATE utf8mb4_unicode_ci ' . ($field->getNull() ? 'DEFAULT NULL' : 'NOT NULL') . ';';
                    }
                }
                break;
            case BoolField::class:
                $sql .= 'tinyint(1) ' . ($field->getNull() ? 'DEFAULT NULL' : 'NOT NULL') . ';';
                break;
            case Field::class:
            case TextField::class:
                if($field->getLength() > 0)
                {
                    $sql = 'varchar(255) COLLATE utf8mb4_unicode_ci ' . ($field->getNull() ? 'DEFAULT NULL' : 'NOT NULL') . ';';
                }
                else
                {
                    $sql .= 'text COLLATE utf8mb4_unicode_ci ' . ($field->getNull() ? 'DEFAULT NULL' : 'NOT NULL') . ';';
                }
                break;
            case TimeField::class:
                $sql .= 'time NOT ' . ($field->getNull() ? 'DEFAULT NULL' : 'NOT NULL') . ';';
                break;
            case BlobField::class:
                if ($field instanceof BlobField) {
                    switch ($field->getSize()) {
                        case BlobField::TINY:
                            $sql .= 'TINYBLOB ' . ($field->getNull() ? 'DEFAULT NULL' : 'NOT NULL') . ';';
                            break;
                        case BlobField::SMALL:
                            $sql .= 'MEDIUMBLOB ' . ($field->getNull() ? 'DEFAULT NULL' : 'NOT NULL') . ';';
                            break;
                        case BlobField::MEDIUM:
                            $sql .= 'BLOB ' . ($field->getNull() ? 'DEFAULT NULL' : 'NOT NULL') . ';';
                            break;
                        case BlobField::BIG:
                            $sql .= 'LONGBLOB ' . ($field->getNull() ? 'DEFAULT NULL' : 'NOT NULL') . ';';
                            break;
                    }
                }

                break;
            case CreateTimestampField::class:
                $sql .= 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;';
                break;
            case TimestampField::class:
                $sql .= 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;';
                break;
            default:
        }

        return $sql;
    }
}