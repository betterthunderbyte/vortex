<?php declare(strict_types=1); namespace core;

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

trait TModel
{
    protected $alias;

    /**
     * @var $current_statement IStatement
     */
    private $current_statement = null;

    protected function setCurrentStatement(IStatement $statement)
    {
        $this->current_statement = $statement;
    }

    public function setModelAlias(string $alias) : void
    {
        $this->alias = $alias;
    }

    public function getModelAlias() : string
    {
        return $this->alias;
    }

    public function getRow() : array
    {
        if($this->current_statement === null)
        {
            return array();
        }

        return $this->current_statement->fetchAssoc();
    }

    public function getRows() : array
    {
        if($this->current_statement === null)
        {
            return array();
        }

        return $this->current_statement->fetchAllAssoc();
    }

	public function getEntry(string $entry_class) : ?IEntry
	{
        if($this->current_statement === null)
        {
            return null;
        }

        return $this->current_statement->fetchEntry($entry_class);
	}

	public function getEntries(string $entry_class) : ?EntryCollection
	{
        if($this->current_statement === null)
        {
            return null;
        }

        return $this->current_statement->fetchAllEntry($entry_class);
	}

	public function getRowCount() : int
    {
        if($this->current_statement == null)
        {
            return 0;
        }

        return $this->current_statement->rowCount();
    }
}