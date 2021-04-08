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

use controller\backend\entry\GroupPermissionEntry;
use controller\backend\entry\PermissionEntry;
use core\IModel;
use core\IStatement;
use core\Register;
use core\TModel;

class PermissionModel implements IModel
{
    use TModel;

    /**
     * @var $select_all_with_group_statement IStatement
     */
    private static $select_all_with_group_statement;

    /**
     * @var $select_all_not_with_group_statement IStatement
     */
    private static $select_all_not_with_group_statement;

    /**
     * @var $select_all_from_group_statement IStatement
     */
    private static $select_all_from_group_statement;

    /**
     * @var $count_statement IStatement
     */
    private static $count_statement;

    public static function initialize(): void
    {
        $db = Register::getDBConnection();

        self::$select_all_with_group_statement = $db->prepare(
        'SELECT `' . PermissionEntry::getIDName() . '`, `title`, `description`, `alias` FROM `' . GroupPermissionEntry::getTableName() . '`, `' . PermissionEntry::getTableName() . '` WHERE `group_fk` = ? AND `permission_fk` = `' . PermissionEntry::getIDName() . '`'
        );

        self::$select_all_not_with_group_statement = $db->prepare(
        'SELECT * FROM `' . PermissionEntry::getTableName() . '` WHERE `' . PermissionEntry::getIDName() . '` NOT IN (SELECT `permission_fk` FROM `' . GroupPermissionEntry::getTableName() . '` WHERE `group_fk` = ?)'
        );

        self::$select_all_from_group_statement = $db->prepare(
        'SELECT *, IFNULL((SELECT 1 FROM `' . GroupPermissionEntry::getTableName() . '` WHERE `group_fk` = :group_pk AND `permission_fk` = p.`' . PermissionEntry::getIDName() . '`), 0) AS `group_use` FROM `' . PermissionEntry::getTableName() . '` as p LIMIT :limit OFFSET :offset'
        );

        self::$count_statement = $db->prepare(
        'SELECT COUNT(`' . PermissionEntry::getIDName() . '`) AS c FROM `' . PermissionEntry::getTableName() . '`'
        );
    }

    public function selectAllFromGroup(int $group_id, int $limit, int $offset) : void
    {
        self::$select_all_from_group_statement->execute(array('group_pk' => $group_id, 'limit' => $limit, 'offset' => $offset));
        $this->setCurrentStatement(self::$select_all_from_group_statement);
    }

    public function permissionCount() : int
    {
        if(self::$count_statement->execute())
        {
            return self::$count_statement->fetchAssoc()['c'];
        }

        return 0;
    }

    public function selectAllWithGroup(int $group_id) : void
    {
        self::$select_all_with_group_statement->execute(array($group_id));
        $this->setCurrentStatement(self::$select_all_with_group_statement);
    }

    public function selectAllNotWithGroup(int $group_id) : void
    {
        self::$select_all_not_with_group_statement->execute(array($group_id));
        $this->setCurrentStatement(self::$select_all_not_with_group_statement);
    }
}
