<?php declare(strict_types=1); namespace controller\backend\model;
use controller\backend\entry\GroupPermissionEntry;
use controller\backend\entry\MemberEntry;
use controller\backend\entry\PermissionEntry;
use core\IModel;
use core\IStatement;
use core\Register;
use core\TModel;

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

class MemberPermissionModel implements IModel
{
    use TModel;

    /**
     * @var $select_member_with_permission_statement IStatement
     */
    private static $select_member_with_permission_statement;

    public static function initialize(): void
    {
        $db = Register::getDBConnection();

       self::$select_member_with_permission_statement = $db->prepare(
       'SELECT `' . PermissionEntry::getTableName() . '`.`' . PermissionEntry::getIDName() . '`, `' . PermissionEntry::getTableName() . '`.`alias`, `' . PermissionEntry::getTableName() . '`.`title`, `' . PermissionEntry::getTableName() . '`.`description` '.
       'FROM `' . MemberEntry::getTableName() . '`, `' . GroupPermissionEntry::getTableName() . '`, `' . PermissionEntry::getTableName() . '`'.
       'WHERE `' . MemberEntry::getTableName() . '`.`group_fk` = `' . GroupPermissionEntry::getTableName() . '`.`group_fk` AND `' . GroupPermissionEntry::getTableName() . '`.`permission_fk` = `' . PermissionEntry::getTableName() . '`.`' . PermissionEntry::getIDName() . '` AND `' . MemberEntry::getTableName() . '`.`' . MemberEntry::getIDName() . '` = ? AND `' . PermissionEntry::getTableName() . '`.`alias` = ?'
       );
    }

    public function selectMemberWithPermission(int $member_id, string $permission_alias) : void
    {
        self::$select_member_with_permission_statement->execute([$member_id, $permission_alias]);
        $this->setCurrentStatement(self::$select_member_with_permission_statement);
    }
}
