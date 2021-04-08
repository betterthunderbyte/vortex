<?php declare(strict_types=1); namespace controller\backend\src;

use core\ControllerRegistry;
use core\field\Field;
use core\IEntry;
use core\Logger;
use core\Register;
use core\Tool;

/**
 *  MIT License
 *
 *  Copyright (c) 2020 jeamu
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 *
 */

class BackupEntryBuilder
{
	public static function fromJsonString(string $json) : EntryBackupCollection
	{
	    $data = Tool::jsonDecode($json);
		$collection = new EntryBackupCollection();

		if(isset($data['create_time']))
        {
            $collection->setCreateTime($data['create_time']);
        }

		if(isset($data['version']))
        {
            $collection->setVersion($data['version']);
        }

		if(isset($data['backups']))
        {
            foreach ($data['backups'] as $backup)
            {
                $backup_entry = new EntryBackup();

                if(isset($backup['entry_class']))
                {
                    $backup_entry->setEntryClass($backup['entry_class']);
                }

                if(isset($backup['entry_id_name']))
                {
                    $backup_entry->setEntryIDName($backup['entry_id_name']);
                }

                if(isset($backup['entry_table_name']))
                {
                    $backup_entry->setEntryTableName($backup['entry_table_name']);
                }

                if(isset($backup['entry_fields']))
                {
                    $backup_entry->setEntryFields($backup['entry_fields']);
                }

                if(isset($backup['entry_data']))
                {
                    $backup_entry->setData($backup['entry_data']);
                }

                $collection->addEntryBackup($backup_entry);
            }
        }

		return $collection;
	}

    public static function fromControllerRegistry(ControllerRegistry $controller_registry) : EntryBackupCollection
    {
        $collection = new EntryBackupCollection();
        $collection->setVersion(Tool::getVersion());
        $collection->setCreateTime(time());

        $connection = Register::getDBConnection();

        try {
            foreach ($controller_registry->getEntries() as $entry_class)
            {
                $entry = new $entry_class();
                if($entry instanceof IEntry)
                {
                    $entry_backup = new EntryBackup();
                    $entry_backup->setEntryClass($entry_class);
                    $entry_backup->setEntryIDName($entry::getIDName());
                    $entry_backup->setEntryTableName($entry::getTableName());

                    $fields = array();
                    foreach ($entry::getFields() as $field)
                    {
                        if($field instanceof Field)
                        {
                            $fields[] = $field->asArray();
                        }
                    }

                    $entry_backup->setEntryFields($fields);

                    $data = array();

                    $statement = $connection->prepare('SELECT * FROM `'. $entry::getTableName() .'`');

                    if($statement->execute() AND $statement->rowCount() > 0)
                    {
                        while ($row = $statement->fetchAssoc())
                        {
                            $data[] = $row;
                        }
                    }

                    $entry_backup->setData($data);
                    $collection->addEntryBackup($entry_backup);
                }
            }
        }
        catch (\Exception $exception)
        {
            Logger::Exception($exception);
        }

        return $collection;
    }
}
