<?php declare(strict_types=1); namespace core;

use core\field\Field;

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

interface IEntry
{

    /**
     * Wenn der Entry geschlossen ist können keine Änderungen vorgenommen werden
     * @return bool
     */
    public function isLocked() : bool;
    public function setLocked(bool $locked) : void;

    public function setID(int $id) : void;
    public function getID() : int;

    public static function callInitialize() : void;

    public static function getIDName() : string;

    public static function getPreviousIDName() : string;

    public static function getTableName() : string;

    public static function getPreviousTableName() : string;

    public static function fieldExists(string $name) : bool;
    public static function getFields() : array;
    public static function getField(string $name) : Field;

    public function __set(string $name, $value) : void;

    public function __get(string $name);
    public function __isset(string $name);

    public function save() : void;

    public function delete() : void;

    public function exists() : bool;

    public function getRawValues() : array;

    public function set(string $name, $value) : void;
    public function get(string $name);
    public function isset(string $name) : bool;

    public static function getCount() : int;
    public static function selectByID(int $id) : ?IEntry;

	public static function getHash() : string;

}