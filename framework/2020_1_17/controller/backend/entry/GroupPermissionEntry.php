<?php declare(strict_types=1); namespace controller\backend\entry;

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

use core\field\ForeignField;
use core\IEntry;
use core\TEntry;

class GroupPermissionEntry implements IEntry
{
    use TEntry;

    protected static function initialize(): void
    {
        self::setTableName('be_group_permission');

        $group_fk_field = new ForeignField();
        $group_fk_field->setName('group_fk');
        self::addField($group_fk_field);

        $permission_fk_field = new ForeignField();
        $permission_fk_field->setName('permission_fk');
        self::addField($permission_fk_field);
    }

    public function setPermissionFk(int $id) : void
    {
        $this->__set('permission_fk', $id);
    }

    public function getPermissionFk() : int
    {
        return (int)$this->__get('permission_fk');
    }

    public function setGroupFk(int $id) : void
    {
        $this->__set('group_fk', $id);
    }

    public function getGroupFk() : int
    {
        return (int)$this->__get('group_fk');
    }
}

