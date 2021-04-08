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

class EntryBackup implements IAsArray
{
	use TAsArray;

	private $entry_class;
	private $entry_id_name;
	private $entry_table_name;
	private $version;
	private $fields;
	private $data;

	public function __construct()
	{
		$this->entry_class = '';
		$this->entry_id_name = '';
		$this->entry_table_name = '';
		$this->version = '';
		$this->fields = array();
		$this->data = array();
	}

	public function setFields(array $fields) : void
	{
		$this->fields = $fields;
	}

	public function getFields() : array
	{
		return $this->fields;
	}

	public function setEntryTableName(string $table) : void
	{
		$this->entry_table_name = $table;
	}

	public function getEntryTableName() : string
	{
		return $this->entry_table_name;
	}

	public function setEntryIdName(string $name) : void
	{
		$this->entry_id_name = $name;
	}

	public function getEntryIdName() : string
	{
		return $this->entry_id_name;
	}

	public function setEntryClass(string $entry_class) : void
	{
		$this->entry_class = $entry_class;
	}

	public function setVersion(string $version) : void
	{
		$this->version = $version;
	}

	public function setData(array $data) : void
	{
		$this->data = $data;
	}

	public function getEntryClass() : string
	{
		return $this->entry_class;
	}

	public function getVersion() : string
	{
		return $this->version;
	}

	public function getData() : array
	{
		return $this->data;
	}
}
