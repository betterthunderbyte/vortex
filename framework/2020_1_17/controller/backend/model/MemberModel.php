<?php declare(strict_types=1); namespace controller\backend\model;
use controller\backend\entry\MemberEntry;
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

class MemberModel implements IModel
{
    use TModel;

    /**
     * @var $select_all_statment IStatement
     */
    static $select_all_statment;

    /**
     * @var $select_by_email_statement IStatement
     */
    static $select_by_email_statement;

    /**
     * @var $select_by_id_statement IStatement
     */
    static $select_by_id_statement;

    /**
     * @var $select_if_active_statement IStatement
     */
    static $select_if_active_statement;

    /**
     * @var $select_all_in_group_statement IStatement
     */
    static $select_all_in_group_statement;

	/**
	 * @var $select_all_statement_limited IStatement
	 */
	static $select_all_statement_limited;


	public static function initialize(): void
    {
        $db = Register::getDBConnection();

        self::$select_all_statment = $db->prepare(
            'SELECT * FROM `' . MemberEntry::getTableName() . '`'
        );

        self::$select_by_email_statement = $db->prepare(
        'SELECT * FROM `' . MemberEntry::getTableName() . '` WHERE `email` = ?;'
        );

        self::$select_by_id_statement = $db->prepare(
            'SELECT * FROM `' . MemberEntry::getTableName() . '` WHERE `' . MemberEntry::getIDName() . '` = ?'
        );

        self::$select_if_active_statement = $db->prepare(
            'SELECT * FROM `' . MemberEntry::getTableName() . '` WHERE `active` = ?'
        );

        self::$select_all_in_group_statement = $db->prepare(
            'SELECT * FROM `' . MemberEntry::getTableName() . '` WHERE `group_fk` = ?'
        );

        self::$select_all_statement_limited = $db->prepare(
        	'SELECT * FROM `' . MemberEntry::getTableName() . '` LIMIT :limit OFFSET :offset'
        );
    }

	public function emailExists(string $email) : bool
	{
		if(self::$select_by_email_statement->execute([$email]) AND self::$select_by_email_statement->rowCount() > 0)
		{
			return true;
		}

		return false;
	}

    public function selectMembersLimited(int $count, int $page) : void
	{
		$page -= 1;

		if(self::$select_all_statement_limited->execute(array('limit' => $count, 'offset' => $count * $page)))
		{
			$this->setCurrentStatement(self::$select_all_statement_limited);
		}
	}

    public function selectMemberByEmail(string $email) : void
    {
        if(self::$select_by_email_statement->execute([$email]))
		{
			$this->setCurrentStatement(self::$select_by_email_statement);
		}

    }

    public function selectMemberById(int $id) : void
    {
    	if(self::$select_by_id_statement->execute([$id]))
		{
			$this->setCurrentStatement(self::$select_by_id_statement);
		}
    }

    public function selectAll() : void
    {
    	if(self::$select_all_statment->execute())
		{
			$this->setCurrentStatement(self::$select_all_statment);
		}
    }
}
