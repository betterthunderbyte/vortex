<?php declare(strict_types=1); namespace controller\backend\app;

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

use controller\backend\BackendController;
use controller\backend\entry\MemberEntry;
use controller\backend\IBackendApp;
use controller\backend\IBackendMenu;
use controller\backend\model\MemberModel;
use controller\backend\src\BackupEntryBuilder;
use controller\backend\src\SystemSettings;
use core\ControllerRegistry;
use core\ControllerStatus;
use core\EntryBackup;
use core\Installer;
use core\Register;
use core\Route;
use core\RouteCollection;
use core\Tool;
use core\TwigTemplate;

class SystemController implements IBackendApp, IBackendMenu
{
    public static function handleRoutes(): RouteCollection
    {
        $route_collection = new RouteCollection();

		$session_model = BackendController::getMemberSessionModel();

		if(!$session_model->withPermission('system-management'))
		{
			return $route_collection;
		}

        $route_collection->add(Route::get(
			'/overview',
			function () : void {
				$controller = new SystemController();
				$controller->overviewRequest();
			}
		));

        $route_collection->add(Route::get(
        '/settings',
			function () : void
			{
				SystemSettings::view();
			}
		));

		$route_collection->add(Route::get(
		'/backup/overview',
			function (){
				SystemController::backupOverviewRequest();
			}
		));

		$route_collection->add(Route::get('/api/create_backup', function (){

			$backup_path = ROOT_DIR . '/backup/' . time();

			if(!file_exists($backup_path))
			{
				mkdir($backup_path, 0777, true);
			}

			$backup_data = array();

			foreach (Register::getAllControllers() as $controller)
			{
				$backup = BackupEntryBuilder::fromControllerRegistry($controller);
				$backup_data[] = $backup->asArray();
			}

			file_put_contents($backup_path . '/' . Tool::getVersion() . '.json', Tool::jsonEncode($backup_data));

			Tool::zip(FILES_DIR, $backup_path . '/files_backup.zip');

			Tool::returnJsonData(array('message' => 'works'));
		}));

		$member_entry = $session_model->getMemberEntry();

		if($member_entry instanceof MemberEntry AND $member_entry->isSuperAdmin())
		{
			if(Register::getControllerRegistry('backend')->getStatus()  == ControllerStatus::INSTALLED)
			{
				$route_collection->add(Route::get(
					'/superadmin',
					function () : void
					{
						$member_model = new MemberModel();
						$member_model->selectAll();

						$view = Register::getTemplateSystem()::render('backend_system_superadmin.vue', array('members' => $member_model->getRows()));

						echo BackendController::renderBackendStructure('Backend - Superadmin', $view);
					}
				));

				$route_collection->add(Route::patch(
					'/superadmin/[i:id]',
					function (int $id) : void
					{
						$member_model = new MemberModel();
						$member_model->selectMemberById($id);

						$member_entry = $member_model->getEntry(MemberEntry::class);
						if($member_entry instanceof MemberEntry)
						{
							$member_entry->setAdmin(true);
							$member_entry->save();
							Tool::headerOK();
							return;
						}

						Tool::headerBadRequest();
					}
				));

				$route_collection->add(Route::delete(
					'/superadmin/[i:id]',
					function (int $id) : void
					{
						$member_model = new MemberModel();
						$member_model->selectMemberById($id);

						$member_entry = $member_model->getEntry(MemberEntry::class);
						if($member_entry instanceof MemberEntry)
						{
							$member_entry->setAdmin(false);
							$member_entry->save();
							Tool::headerOK();
							return;
						}

						Tool::headerBadRequest();
					}
				));
			}

			$route_collection->add(Route::get(
				'/apps',
				function () : void
				{
					SystemController::appsOverviewRequest();
				}
			));

			$route_collection->add(Route::get(
				'/install/controller/[:action]',
				function (string $alias) : void
				{
					$controller_registry = Register::getControllerRegistry($alias);
					if($controller_registry == null)
					{
						Tool::headerBadRequest();
						Tool::returnJsonData(array('message' => 'Der Controller wurde nicht gefunden um diesen zu installieren.'));
						return;
					}

					$installer = new Installer();
					$installer->installController($controller_registry);

					if($installer->isSuccessful())
					{
						Tool::headerOK();
						return;
					}
					else
					{
						Tool::returnJsonError(
							403,
							'Der Controller konnte nicht erfolgreich installiert werden.',
							$installer->getErrorMessages()
						);
						return;
					}
				}
			));

			$route_collection->add(Route::get(
				'/uninstall/controller/[:action]',
				function (string $alias) : void
				{
					$controller_registry = Register::getControllerRegistry($alias);
					if($controller_registry == null)
					{
						Tool::headerBadRequest();
						Tool::returnJsonData(array('message' => 'Der Controller wurde nicht gefunden um diesen zu installieren.'));
						return;
					}

					$backup_path = FILES_DIR . '/backup';

					if(!file_exists($backup_path))
					{
						mkdir($backup_path);
					}

					$backup_data = array();

					foreach (Register::getAllControllers() as $controller)
					{
					    $backup = BackupEntryBuilder::fromControllerRegistry($controller);
                        $backup_data[] = $backup->asArray();
					}

					file_put_contents($backup_path . '/V' . Tool::getVersion() . '_CT' . time() . '.json', Tool::jsonEncode($backup_data));

					$installer = new Installer();
					$installer->uninstallController($controller_registry);

					if($installer->isSuccessful())
					{
						Tool::headerOK();
						return;
					}
					else
					{
						Tool::returnJsonError(
							403,
							'Der Controller konnte nicht erfolgreich installiert werden.',
							$installer->getErrorMessages()
						);
						return;
					}
				}
			));
		}


		$route_collection->add(Route::post('/api/settings', function (){
			SystemSettings::saveRequest();
			// ToDo(Thorben) Die Möglichkeit den Cache manuell zu löschen
		}));

		$route_collection->add(Route::get('/logs', function (){
			// ToDo Log Dateien registrieren und eine Auswahl anbieten diese anzuzeigen
			$php_error_log = array();

			try
			{
				$php_error_log_file = new \SplFileObject(ROOT_DIR . '/log/php-error.log');

				while ($line = $php_error_log_file->fgets())
				{
					$php_error_log[] = $line;
				}
			}
			catch (\Exception $e)
			{

			}

			$log_view_template = TwigTemplate::render('backend_system_logs_view.vue', array('php_error_logs' => $php_error_log));
			echo BackendController::renderBackendStructure('System - Logs', $log_view_template);
		}));

        return $route_collection;
    }

    public static function appsOverviewRequest() : void
	{
		$controller_registry_data = array();

		foreach (Register::getAllControllers() as $controller_registry)
		{
			if($controller_registry instanceof ControllerRegistry)
			{
				if($controller_registry->ableToInstall())
				{
					$controller_registry_data[] = $controller_registry->asArray();
				}
			}
		}

		$system_updates_template_view = Register::getTemplateSystem()::render(
			'backend_system_apps_view.vue', array(
				'installable_controller_registries' => $controller_registry_data
			)
		);
		echo BackendController::renderBackendStructure('Backend - System - Updates', $system_updates_template_view);
	}

    private function overviewRequest() : void
    {
    	$template = Register::getTemplateSystem()::render('backend_system_overview.vue', array(
    		'free_disk_space_gibibyte' => round(floatval(disk_free_space('/') / (1024 * 1024 * 1024)), 2)

    	));

        echo BackendController::renderBackendStructure('Backend - System', $template);
    }

    private static function backupOverviewRequest() : void
	{
		$template = Register::getTemplateSystem()::render('backend_system_backup_overview.vue', array());
		echo BackendController::renderBackendStructure('Backend - Backup', $template);
	}

	private static function createBackupRequest() : void
	{

	}

    public static function returnBackendMenu(): array
    {
		$session_model = BackendController::getMemberSessionModel();

		if(!$session_model->withPermission('system-management'))
		{
			return array();
		}

		$menu = array(
			'label' => 'System',
			'items' => array(
				array(
					'label' => 'Übersicht',
					'link' => '/backend/app/system/overview'
				),
				array(
					'label' => 'Logs',
					'link' => '/backend/app/system/logs'
				),
				array(
					'label' => 'Backup',
					'link' => '/backend/app/system/backup/overview'
				),
				array(
					'label' => 'Einstellungen',
					'link' => '/backend/app/system/settings'
				)
			)
		);

		if($session_model->getMemberEntry()->isSuperAdmin())
		{
			$menu['items'][] = array(
				'label' => 'Anwendungen',
				'link' => '/backend/app/system/apps'
			);

			if(Register::getControllerRegistry('backend')->getStatus()  == ControllerStatus::INSTALLED)
			{
				$menu['items'][] = array(
					'label' => 'Superadmin',
					'link' => '/backend/app/system/superadmin'
				);
			}
		}

		return $menu;
    }
}