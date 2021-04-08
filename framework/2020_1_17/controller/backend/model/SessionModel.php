<?php declare(strict_types=1); namespace controller\backend\model;
use controller\backend\entry\SessionEntry;
use core\IModel;
use core\IStatement;
use core\Register;
use core\TModel;
use core\Tool;

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

class SessionModel implements IModel
{
    use TModel;

    /**
     * @var $select_by_key_statement IStatement
     */
    static $select_by_key_statement;

    /**
     * @var $select_all_by_member_id_statement IStatement
     */
    static $invalidate_all_from_member_statement;

    public static function initialize(): void
    {
        $db = Register::getDBConnection();

        self::$select_by_key_statement = $db->prepare(
        'SELECT * FROM `' . SessionEntry::getTableName() . '` WHERE `key` = ?;'
        );

        self::$invalidate_all_from_member_statement = $db->prepare(
            'UPDATE `' . SessionEntry::getTableName() . '` SET `expire` = ? WHERE `member_fk` = ?'
        );
    }

    public function selectByKey(string $key) : void
    {
        if(self::$select_by_key_statement->execute([$key]) AND self::$select_by_key_statement->rowCount() > 0)
        {
            $this->setCurrentStatement(self::$select_by_key_statement);
        }

    }

    /**
     * Sucht nach allen Sessions vom Mitglied und macht diese ungültig
     * @param int $id
     */
    public function invalidateAllFromMember(int $id) : void
    {
        self::$invalidate_all_from_member_statement->execute([Tool::toMysqlDateTime(time() - 1), $id]);
    }
}
