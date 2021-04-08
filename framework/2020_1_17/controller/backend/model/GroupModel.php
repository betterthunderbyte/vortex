<?php declare(strict_types=1); namespace controller\backend\model;

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

use controller\backend\entry\GroupEntry;
use core\IModel;
use core\IStatement;
use core\Register;
use core\TModel;

class GroupModel implements IModel
{
    use TModel;

    /**
     * @var $select_all_groups_statement IStatement
     */
    private static $select_all_groups_statement;

    /**
     * @var $select_by_id_statement IStatement
     */
    private static $select_by_id_statement;

    public static function initialize(): void
    {
        $db = Register::getDBConnection();
        self::$select_all_groups_statement = $db->prepare('SELECT * FROM `' . GroupEntry::getTableName() . '`');
        self::$select_by_id_statement = $db->prepare('SELECT * FROM `' . GroupEntry::getTableName() . '` WHERE `' . GroupEntry::getIDName() . '` = ?');
    }

    public function selectById(int $id) : void
    {
        self::$select_by_id_statement->execute(array($id));
        $this->setCurrentStatement(self::$select_by_id_statement);
    }

    public function selectAllGroups() : void
    {
        self::$select_all_groups_statement->execute();
        $this->setCurrentStatement(self::$select_all_groups_statement);
    }
}
