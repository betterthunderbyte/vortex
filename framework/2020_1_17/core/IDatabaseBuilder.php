<?php declare(strict_types=1); namespace core;

use core\field\Field;

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

interface IDatabaseBuilder
{
    public function getAllTables(string $database) : array;

    public function getAllFields(string $database, string $table) : array;

    public function databaseExists(string $database) : bool;

    public function tableExists(string $database, string $table) : bool;

    public function fieldExists(string $database, string $table, string $field_name) : bool;

    public function createDatabase(string $database) : void;

    public function dropDatabase(string $database) : void;

    public function createTable(string $database, string $table) : void;

    public function dropTable(string $database, string $table) : void;

    public function renameTable(string $database, string $from, string $to) : void;
    public function addField(string $database, string $table, Field $field) : void;

    public function dropField(string $database, string $table, string $name) : void;

    public function editField(string $database, string $table, string $name, Field $to)  : void;

    public function renameField(string $database, string $table, string $from, Field $to)  : void;
}