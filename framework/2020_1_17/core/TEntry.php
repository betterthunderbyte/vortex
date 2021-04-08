<?php declare(strict_types=1); namespace core;
/**
 * MIT License
 *
 * Copyright (c) 2019 jeamu
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
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
 */

use core\exception\EntryFieldNotExistsException;
use core\exception\EntryStorageException;
use core\exception\EntryValueAlreadyExistsException;
use core\field\CreateTimestampField;
use core\field\Field;
use core\field\TimestampField;

trait TEntry
{
    /**
     * @var $fields array
     */
    protected static $fields;

    /**
     * @var $table_name string
     */
    protected static $table_name;
    /**
     * @var $previous_table_name string
     */
    protected static $previous_table_name;

    /**
     * @var $setters array
     */
    protected static $setters;

    /**
     * @var $getters array
     */
    protected static $getters;

    /**
     * @var $values array
     */
    protected $values = array();

    protected $id = 0;

    private $locked = false;

    public function isLocked() : bool
    {
        return true;
    }

    public function setLocked(bool $locked) : void
    {
        $this->locked = $locked;
    }

    public final function setID(int $id) : void
    {
        $this->id = $id;
    }

    public final function getID() : int
    {
        return $this->id;
    }

    protected static function initialize() : void
    {

    }

    public final static function callInitialize() : void
    {
        static::$fields = array();
        static::$setters = array();
        static::$getters = array();
        static::initialize();
    }

    public final static function getIDName() : string
    {
        return static::$table_name . '_pk';
    }

    public final static function getPreviousIDName() : string
    {
        return static::$previous_table_name . '_pk';
    }

    public final static function getTableName() : string
    {
        return static::$table_name;
    }

    public final static function getPreviousTableName() : string
    {
        return static::$previous_table_name;
    }

    protected final static function setPreviousTableName(string $table_name) : void
    {
        static::$previous_table_name = $table_name;
    }

    protected final static function setTableName(string $table_name) : void
    {
        static::$table_name = $table_name;
    }

    public final static function fieldExists(string $name) : bool
    {
        return isset(static::$fields[$name]);
    }

    protected final static function addField(Field $field, string $setter = '', string $getter = '') : void
    {
        static::$fields[$field->getName()] = $field;

        if(isset($setter{1}))
        {
            if(!method_exists(static::class, $setter))
            {
                throw new \Exception('Der Setter: ' . $setter . ' existiert nicht in der Klasse: ' . static::class);
            }

            self::$setters[$field->getName()] = $setter;
        }

        if(isset($getter{1}))
        {
            if(!method_exists(static::class, $getter))
            {
                throw new \Exception('Der getter: ' . $getter . ' existiert nicht in der Klasse: ' . static::class);
            }

            self::$getters[$field->getName()] = $getter;
        }
    }

    public final static function getFields() : array
    {
        return array_values(static::$fields);
    }

    public final static function getField(string $name) : Field
    {
        return static::$fields[$name];
    }

    public final function __set(string $name, $value) : void
    {
        if($this->locked)
        {
            return;
        }

        if($name === self::getIDName())
        {
            $this->id = intval($value);
            return;
        }

        $this->values[$name] = $value;
    }

    public final function __get(string $name)
    {
        if($name === self::getIDName())
        {
            return $this->id;
        }

        return $this->values[$name];
    }

    public final function __isset(string $name)
    {
        return isset($this->values[$name]) OR $name === self::getIDName();
    }

    public function save() : void
    {
        $this->save_hidden();
    }

    protected function save_hidden(bool $reuse_ids = false) : void
    {
        if($this->locked)
        {
            // ToDo Error
            return;
        }

        if($this->id != 0 AND $this->exists())
        {
            $update_string = '';
            $data = array();

            foreach ($this->values as $field_name => $field_value)
            {
                $field = self::$fields[$field_name];
                if($field instanceof Field AND !($field instanceof TimestampField) AND !($field instanceof CreateTimestampField))
                {
                    $update_string .= '`' . $field->getName() . '` = :' . $field->getName() . ', ';
                    $data[$field_name] = $field_value;
                }
            }

            $data[self::getIDName()] = $this->id;

            $update_string = substr($update_string, 0, -2);
            $sql = 'UPDATE `' . self::getTableName() . '` SET ' . $update_string . ' WHERE `' . self::getIDName() . '` = :' . self::getIDName() . ';';
            $update_statement = Register::getDBConnection()->prepare($sql);

            try
            {
                if(!$update_statement->execute($data))
                {
                    throw new EntryStorageException();
                }
            }
            catch (\PDOException $exception)
            {
                if($exception->errorInfo[1] === 1062)
                {
                    // ToDo Logger
                    throw new EntryValueAlreadyExistsException();
                }
            }
        }
        else
        {
            $data = array();

            $insert_string = '';
            $insert_string_values = '';

            if($reuse_ids)
            {
                $insert_string .= '`' . self::getIDName() . '`, ';
                $insert_string_values .= ':' . self::getIDName() . ', ';

                $is_first_free_statement = Register::getDBConnection()->prepare(
                    'SELECT `' . self::getIDName() . '` FROM `' . self::getTableName() . '` WHERE `' .
                    self::getIDName() . '` = 1'
                );

                if($is_first_free_statement->execute() AND $is_first_free_statement->rowCount() == 0)
                {
                    $data[self::getIDName()] = 1;
                }
                else
                {
                    $free_id_statement = Register::getDBConnection()->prepare(
                        'SELECT `' . self::getIDName() . '` + 1 AS free_id FROM `' . self::getTableName() . '` WHERE `' . self::getIDName() . '` + 1 NOT IN (SELECT `' . self::getIDName() . '` FROM `' . self::getTableName() . '`)'
                    );

                    if($free_id_statement->execute() AND $free_id_statement->rowCount() > 0)
                    {
                        $data[self::getIDName()] = $free_id_statement->fetchAssoc()['free_id'];
                    }
                    else
                    {
                        throw new \Exception('Bei der Suche nach einer freien Id ist ein Fehler aufgetreten bei der Tabelle "'. self::getTableName() .'". ');
                    }
                }
            }

            foreach ($this->values as $field_name => $field_value)
            {
                $field = self::$fields[$field_name];
                if($field instanceof Field AND !($field instanceof TimestampField) AND !($field instanceof CreateTimestampField))
                {
                    $insert_string .= '`' . $field->getName() . '`, ';
                    $insert_string_values .= ':' . $field->getName() . ', ';
                    $data[$field_name] = $field_value;
                }
            }

            $insert_string = substr($insert_string, 0, -2);
            $insert_string_values = substr($insert_string_values, 0, -2);

            $sql = 'INSERT INTO `' . self::getTableName() . '`(' . $insert_string . ') VALUES (' . $insert_string_values . ');';
            $insert_statement = Register::getDBConnection()->prepare($sql);
            try
            {
                if(!$insert_statement->execute($data))
                {
                    throw new EntryStorageException();
                }
                else
                {
                    $this->id = $insert_statement->getInsertedID();
                }
            }
            catch (\PDOException $exception)
            {
                switch ($exception->errorInfo[1])
                {
                    case 1062:
                        throw new EntryValueAlreadyExistsException();
                        break;
                    case 1364:
                        throw new \Exception('Es wurde versucht ein Entry ' . self::$table_name . ' zu speichern aber es fehlen Standardwerte! ' . $exception->errorInfo[2]);
                        break;
                    default:
                        throw $exception;
                }
            }
        }
    }

    public function delete() : void
    {
        $this->delete_hidden();
    }

    protected final function delete_hidden() : void
    {
        if($this->locked)
        {
            // ToDo Error
            return;
        }

        $delete_statement = Register::getDBConnection()->prepare(
            'DELETE FROM `' . self::getTableName() . '` WHERE `' . self::getIDName() . '` = ?;'
        );

        if(!$delete_statement->execute([$this->id]))
        {
            throw new EntryStorageException();
        }
    }

    public function exists() : bool
    {
        return $this->exists_hidden();
    }

    protected function exists_hidden() : bool
    {
        if($this->id === 0)
        {
            return false;
        }

        $exists_statement = Register::getDBConnection()->prepare(
            'SELECT `'. self::getIDName() .'` FROM `' . self::getTableName() . '` WHERE `' . self::getIDName() . '` = ?;'
        );

        if($exists_statement->execute(array($this->id)) AND $exists_statement->rowCount() > 0)
        {
            return true;
        }

        return false;
    }

    public function getRawValues() : array
    {
        $values = $this->values;
        $values[self::getIDName()] = $this->id;

        return $values;
    }

    public function set(string $name, $value) : void
    {
        if($this->locked)
        {
            return;
        }

        if($name === self::getIDName())
        {
            if(is_int($value))
            {
                $this->id = intval($value);
            }
            else
            {
                throw new EntryStorageException();
            }
            return;
        }

        if(!self::fieldExists($name))
        {
            throw new EntryFieldNotExistsException();
        }

        if(isset(self::$setters[$name]))
        {
            $fnc = self::$setters[$name];
            $this->$fnc($value);
            return;
        }

        $this->values[$name] = $value;
    }

    public function get(string $name)
    {
        if($name === self::getIDName())
        {
            return $this->id;
        }

        if(!self::fieldExists($name))
        {
            throw new EntryFieldNotExistsException();
        }

        if(isset(self::$getters[$name]))
        {
            $fnc = self::$getters[$name];
            return $this->$fnc();
        }

        return $this->values[$name];
    }

    public function isset(string $name) : bool
    {
        return $this->__isset($name);
    }

    public static function getCount() : int
    {
        if(!CONNECTION_AVAILABLE)
        {
            return 0;
        }

        $statement = Register::getDBConnection()->prepare('SELECT COUNT(`' . self::getIDName() . '`) AS c FROM `' . self::$table_name . '`');

        if($statement->execute() AND $statement->rowCount() > 0)
        {
            return $statement->fetchAssoc()['c'];
        }

        return 0;
    }

    public static function selectByID(int $id) : ?IEntry
    {
        if(!CONNECTION_AVAILABLE)
        {
            return null;
        }

        $statement = Register::getDBConnection()->prepare('SELECT * FROM `' . self::$table_name . '` WHERE `' . self::getIDName() . '` = ?');

        if($statement->execute([$id]) AND $statement->rowCount() > 0)
        {
            return $statement->fetchEntry(self::class);
        }

        return null;
    }

    public static function getHash() : string
	{
		return md5(self::$table_name . serialize(self::$fields));
	}

}