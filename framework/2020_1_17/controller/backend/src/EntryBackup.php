<?php declare(strict_types=1); namespace controller\backend\src;


/**
 * MIT License
 *
 * Copyright (c) 2020 jeamu
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

use core\IAsArray;
use core\TAsArray;
use core\Tool;

class EntryBackup implements IAsArray
{
    use TAsArray;

    private $entry_class;
    private $entry_id_name;
    private $entry_table_name;
    private $entry_fields;
    private $entry_data;

	public function __construct()
    {
        $this->setEntryClass('');
        $this->setEntryIDName('');
        $this->setEntryTableName('');
        $this->setEntryFields(array());
        $this->setData(array());

    }

	public function setEntryClass(string $entry_class) : void
    {
        $this->entry_class = $entry_class;
    }

    public function getEntryClass() : string
    {
        return $this->entry_class;
    }

    public function setEntryIDName(string $id_name) : void
    {
        $this->entry_id_name = $id_name;
    }

    public function getEntryIdName() : string
    {
        return $this->entry_id_name;
    }

    public function setEntryTableName(string $table_name) : void
    {
        $this->entry_table_name = $table_name;
    }

    public function getEntryTableName() : string
    {
        return $this->entry_table_name;
    }

    public function setEntryFields(array $fields) : void
    {
        $this->entry_fields = $fields;
    }

    public function getEntryFields() : array
    {
        return $this->entry_fields;
    }

    public function setData(array $data) : void
    {
        $this->entry_data = $data;
    }

    public function getData() : array
    {
        return $this->entry_data;
    }
}

