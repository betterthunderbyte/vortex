<?php declare(strict_types=1);
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

use core\ControllerStatus;

\core\Register::registerController(new class extends \core\ControllerRegistry {
	protected function init(): void
	{
		$this->setAlias('backend');
		$this->setVersion('0.0.1');
		$this->setClass(controller\backend\BackendController::class);
		$this->setTitle('Backend');
		$this->setDescription('Grundlegendes System zur Verwaltung');

		\core\Register::registerTemplateDirectory(FRAMEWORK_DIR . '/controller/backend/templates');

		$this->addEntry(\controller\backend\entry\GroupEntry::class);
		$this->addEntry(\controller\backend\entry\MemberEntry::class);
		$this->addEntry(\controller\backend\entry\SessionEntry::class);
		$this->addEntry(\controller\backend\entry\PermissionEntry::class);
		$this->addEntry(\controller\backend\entry\GroupPermissionEntry::class);
		//$this->addEntry(\controller\backend\entry\EditorialTextEntry::class);

		$this->addInstallPackage(
			new class extends \core\InstallPackage
			{
				protected function init(): void
				{
					$this->setTitle('Notwendige Daten erzeugen.');
					$this->setDescription('Daten wie Gruppen und Rechte werden erzeugt.');
				}

				public function install(): bool
				{
					file_put_contents(FRAMEWORK_FILES_DIR . '/backend_install.txt', '');

					if(!file_exists(FRAMEWORK_FILES_DIR . '/backend_install.txt'))
					{
						return false;
					}

					return true;
				}

				public function uninstall(): void
				{
					if(file_exists(FRAMEWORK_FILES_DIR . '/backend_install.txt'))
					{
						unlink(FRAMEWORK_FILES_DIR . '/backend_install.txt');
					}
				}
			}
		);


		if($this->getStatus() == ControllerStatus::INSTALLED)
		{
			\core\Register::registerModel(\controller\backend\model\MemberModel::class);
			\core\Register::registerModel(\controller\backend\model\MemberSessionModel::class);
			\core\Register::registerModel(\controller\backend\model\SessionModel::class);
			\core\Register::registerModel(\controller\backend\model\GroupModel::class);
			\core\Register::registerModel(\controller\backend\model\PermissionModel::class);
			\core\Register::registerModel(\controller\backend\model\MemberPermissionModel::class);
			//\core\Register::registerModel(\controller\backend\model\EditorialTextModel::class);
			\core\Register::registerModel(\controller\backend\model\GroupPermissionModel::class);
		}
	}

	public function setup(): void
	{
		$admin_group = new \controller\backend\entry\GroupEntry();
		$admin_group->setAlias('administrators');
		$admin_group->setTitle('Administratoren');
		$admin_group->setDescription('');
		$admin_group->save();


		$member_management = new \controller\backend\entry\PermissionEntry();
		$member_management->setAlias('member-management');
		$member_management->setTitle('Mitglieder Verwaltung');
		$member_management->setDescription('Erlaubt es Mitglieder zu verwalten.');
		$member_management->save();

		$group_permission_member_management = new \controller\backend\entry\GroupPermissionEntry();
		$group_permission_member_management->setGroupFk($admin_group->getID());
		$group_permission_member_management->setPermissionFk($member_management->getID());
		$group_permission_member_management->save();


		$permission_assignment = new \controller\backend\entry\PermissionEntry();
		$permission_assignment->setAlias('permission-assignment');
		$permission_assignment->setTitle('Rechte und Gruppen zuweisen');
		$permission_assignment->setDescription('Kann Rechte zu Gruppen ändern und Mitglieder in die verschiedenen Gruppen zuweisen.');
		$permission_assignment->save();

		$group_permission_permission_assignment = new \controller\backend\entry\GroupPermissionEntry();
		$group_permission_permission_assignment->setGroupFk($admin_group->getID());
		$group_permission_permission_assignment->setPermissionFk($permission_assignment->getID());
		$group_permission_permission_assignment->save();


		$system_management = new \controller\backend\entry\PermissionEntry();
		$system_management->setAlias('system-management');
		$system_management->setTitle('System Verwaltung');
		$system_management->setDescription('Kann auf die Systemeinstellungen zugreifen.');
		$system_management->save();

		$group_permission_system_management = new \controller\backend\entry\GroupPermissionEntry();
		$group_permission_system_management->setGroupFk($admin_group->getID());
		$group_permission_system_management->setPermissionFk($system_management->getID());
		$group_permission_system_management->save();



		$admin_entry = new \controller\backend\entry\MemberEntry();
		$admin_entry->setEMail('update@vortex-framework.de');
		$admin_entry->setSurname('Vortex Admin');
		$admin_entry->setGivenName('Update');
		$admin_entry->setAdmin(true);
		$admin_entry->setActive(true);
		$admin_entry->setRenewPassword(false);
		$admin_entry->setPassword('123asd!');
		$admin_entry->setData(array());
		$admin_entry->setGroupFk($admin_group->getID());
		$admin_entry->save();

		$admin_entry = new \controller\backend\entry\MemberEntry();
		$admin_entry->setEMail('admin@admin.de');
		$admin_entry->setSurname('');
		$admin_entry->setGivenName('Administrator');
		$admin_entry->setAdmin(false);
		$admin_entry->setActive(true);
		$admin_entry->setRenewPassword(false);
		$admin_entry->setPassword('123asd!');
		$admin_entry->setData(array());
		$admin_entry->setGroupFk($admin_group->getID());
		$admin_entry->save();
	}

	public function getStatus(): int
	{
		if(file_exists(FRAMEWORK_FILES_DIR . '/backend_install.txt'))
		{
			return ControllerStatus::INSTALLED;
		}

        return ControllerStatus::UNINSTALLED;
	}

	public function ableToInstall(): bool
	{
		return true;
	}
});

\controller\backend\BackendRegister::registerBackendController('system', \controller\backend\app\SystemController::class);
\controller\backend\BackendRegister::registerBackendController('admin', \controller\backend\app\AdministratorController::class);
\controller\backend\BackendRegister::registerBackendController('account', \controller\backend\app\AccountController::class);
\controller\backend\BackendRegister::registerBackendController('developer', \controller\backend\app\DeveloperController::class);
