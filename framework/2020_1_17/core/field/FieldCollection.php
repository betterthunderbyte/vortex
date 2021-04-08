<?php declare(strict_types=1);
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

namespace core\field;


class FieldCollection implements \Iterator
{
    private $fields;
    private $aliased_fields;
    private $position;

    public function add(Field $field) : void
    {
        $this->fields[] = $field;
        $this->aliased_fields[$field->getName()] = $field;
    }

    public function getByAlias(string $alias) : ?Field
    {
        return isset($this->aliased_fields[$alias]) ? $this->aliased_fields[$alias] : null;
    }

    public function getByIndex(int $index) : ?Field
    {
        return isset($this->fields[$index]) ? $this->aliased_fields[$index] : null;
    }

    public function exists(string $alias) : bool
    {
        return isset($this->aliased_fields[$alias]);
    }

    /**
     * @inheritDoc
     */
    public function current() : Field
    {
        return $this->fields[$this->position];
    }

    /**
     * @inheritDoc
     */
    public function next() : void
    {
        ++$this->position;
    }

    /**
     * @inheritDoc
     */
    public function key() : int
    {
        return $this->position;
    }

    /**
     * @inheritDoc
     */
    public function valid() : bool
    {
        return isset($this->fields[$this->position]);
    }

    /**
     * @inheritDoc
     */
    public function rewind() : void
    {
        $this->position = 0;
    }
}