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
use core\IModel;
use core\IStatement;
use core\Register;
use core\TModel;

class GroupPermissionModel implements IModel
{
	use TModel;


	/**
	 * @var $select_group_permission_by_group_and_permission IStatement
	 */
	private static $select_group_permission_by_group_and_permission;

	/**
	 * @var $select_group_permission_by_group IStatement
	 */
	private static $select_group_permission_by_group;

	/**
	 * @var $delete_group_permission_by_group_and_permission IStatement
	 */
	private static $delete_group_permission_by_group_and_permission;

	public static function initialize(): void
	{
		$db = Register::getDBConnection();
		self::$select_group_permission_by_group_and_permission = $db->prepare(
			'SELECT * FROM `' . GroupPermissionEntry::getTableName() .'` WHERE `group_fk` = ? AND `permission_fk` = ?'
		);

		self::$select_group_permission_by_group = $db->prepare(
			'SELECT * FROM `' . GroupPermissionEntry::getTableName() .'` WHERE `group_fk` = ?'
		);

		self::$delete_group_permission_by_group_and_permission = $db->prepare(
			'DELETE FROM `' . GroupPermissionEntry::getTableName() .'` WHERE `group_fk` = ? AND `permission_fk` = ?'
		);
	}

	/**
	 * @param int $group_id
	 * @param int $permission_id
	 * @return int Anzahl gelöschter Einträge
	 */
	public function deleteGroupPermissionByGroupAndPermission(int $group_id, int $permission_id) : int
	{
		if(self::$delete_group_permission_by_group_and_permission->execute(array($group_id, $permission_id)))
		{
			return self::$delete_group_permission_by_group_and_permission->rowCount();
		}

		return 0;
	}

	public function selectGroupPermissionByGroupAndPermission(int $group_id, int $permission_id) : void
	{
		if(self::$select_group_permission_by_group_and_permission->execute(array($group_id, $permission_id)))
		{
			$this->setCurrentStatement(self::$select_group_permission_by_group_and_permission);
		}
	}

	public function selectGroupPermissionByGroup(int $group_id) : void
	{
		if(self::$select_group_permission_by_group->execute(array($group_id)))
		{
			$this->setCurrentStatement(self::$select_group_permission_by_group);
		}
	}

}
