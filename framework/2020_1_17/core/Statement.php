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

use PDO;
use PDOStatement;

class Statement implements IStatement
{
    /**
     * @var $pdo_statement PDOStatement
     */
    private $pdo_statement;
    /**
     * @var $pdo PDO
     */
    private $pdo;

    public function __construct()
    {
    }

    public function set_pdo_statement(PDOStatement $pdo_statement) : void
    {
        $this->pdo_statement = $pdo_statement;
    }

    public function set_pdo(PDO $pdo) : void
    {
        $this->pdo = $pdo;
    }

    public function execute(array $values = []): bool
    {
        if($this->pdo_statement == null)
        {
            return false;
        }

        return $this->pdo_statement->execute($values);
    }

    public function fetchAssoc(): array
    {
        $result = $this->pdo_statement->fetch(PDO::FETCH_ASSOC);

        if(is_bool($result))
		{
			return array();
		}

        return $result;
    }

    public function fetchAllAssoc(): array
    {
        return $this->pdo_statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchEntry(string $entry_class) : ?IEntry
    {
        $entry = $this->pdo_statement->fetchObject($entry_class);
        if($entry instanceof IEntry)
        {
            return $entry;
        }

        return null;
    }
    public function fetchAllEntry(string $entry_class) : EntryCollection
    {
        $entry_collection = new EntryCollection();
        $entry_collection->overwrite($this->pdo_statement->fetchAll(PDO::FETCH_CLASS, $entry_class));

        return $entry_collection;
    }

    public function getInsertedID(): int
    {
        return intval($this->pdo->lastInsertId());
    }

    public function rowCount(): int
    {
        return $this->pdo_statement->rowCount();
    }
}
