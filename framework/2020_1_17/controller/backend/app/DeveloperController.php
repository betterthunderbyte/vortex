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
use controller\package_distributor\entry\SystemPackageEntry;
use core\ControllerRegistry;
use core\ControllerStatus;
use core\Register;
use core\Route;
use core\RouteCollection;
use core\Tool;
use core\TwigTemplate;
use Faker\Factory;

class DeveloperController implements IBackendApp, IBackendMenu
{

    public static function returnBackendMenu(): array
    {
    	if(!BackendController::getMemberSessionModel()->getMemberEntry()->isSuperAdmin())
		{
			return array();
		}

		if(!Register::getControllerRegistry('backend')->getStatus() == ControllerStatus::UNINSTALLED)
		{
			return array();
		}

		return array(
			'label' => 'Entwickler',
			'items' => array(
				array(
					'label' => 'Übersicht',
					'link' => '/backend/app/developer/overview'
				),
				array(
					'label' => 'Neues Release erstellen',
					'link' => '/backend/app/developer/new_release'
				),
				array(
					'label' => 'Unechte-Daten erzeugen',
					'link' => '/backend/app/developer/fake'
				)
			)
		);
    }

    public static function handleRoutes(): RouteCollection
    {
        $route_collection = new RouteCollection();

		$route_collection->add(Route::create('/new_release', function (){
			if(isset($_POST['new_release']))
			{

				if(!file_exists(FRAMEWORK_VERSION_FILE_PATH))
				{
					// ToDo(Thorben) Fehlermeldung
				}
				else
				{
                    $version = Tool::generateCurrentVersion();

                    $output_path = FILES_DIR. '/pd/' . $version;

                    Tool::copyDirectoryRecursive(FRAMEWORK_DIR, $output_path);

                    $db_data = array('database' => $version);
                    file_put_contents($output_path . '/db.json', Tool::jsonEncode($db_data));

                    $composer_dir = ROOT_DIR . '/vendor';
                    if(file_exists($composer_dir))
                    {
                        Tool::copyDirectoryRecursive($composer_dir, $output_path . '/vendor');
                    }
                    else
                    {
                        // ToDo alles rückgängig machen
                    }

                    if(!Tool::zip($output_path, $output_path . '.zip'))
                    {
                        // ToDo alles rückgängig machen
                    }

                    $package_entry = new SystemPackageEntry();
                    $package_entry->setTitle('neue Version');
                    $package_entry->setDescription('neue Version');
                    $package_entry->setDirectoryPath($output_path);
                    $package_entry->setVersion($version);
                    $package_entry->save();
				}
			}


			$new_release_template = TwigTemplate::render('backend_developer_new_release.vue', array());
			echo BackendController::renderBackendStructure('Entwickler Werkzeuge', $new_release_template);
		}));

        $route_collection->add(Route::get('/fake', function (){
            $controller = new DeveloperController();
            $controller->fakeRequest();
        }));

        return $route_collection;
    }

    public function fakeRequest() : void
    {
        $faker = Factory::create('de_DE');

/*
        for($i = 0; $i < 25; ++$i)
        {
            $permission_entry = new PermissionEntry();
            $permission_entry->setAlias($faker->sentence(1, false));
            $permission_entry->setTitle($faker->sentence(3, false));
            $permission_entry->setDescription($faker->sentence(12, true));
            try
            {
                $permission_entry->save();
            }
            catch (\Exception $e)
            {
            }
        }

        for($i = 0; $i < 10; ++$i)
        {
            $group_entry = new GroupEntry();
            $group_entry->setAlias($faker->jobTitle);
            $group_entry->setTitle($faker->jobTitle);
            $group_entry->setDescription($faker->company);
            try
            {
                $group_entry->save();
            }
            catch (\Exception $e)
            {
            }
        }

        for($i = 0; $i < 50; ++$i)
        {
            $group_permission_entry = new GroupPermissionEntry();
            $group_permission_entry->setPermissionFk($faker->numberBetween(1, 25));
            $group_permission_entry->setGroupFk($faker->numberBetween(1, 10));
            try
            {
                $group_permission_entry->save();
            }
            catch (\Exception $e)
            {
            }
        }*/

        for($i = 0; $i < 50; ++$i)
        {
            $member_entry = new MemberEntry();
            $member_entry->setGivenName($faker->firstName);
            $member_entry->setSurname($faker->lastName);
            $member_entry->setEMail($faker->email);
            $member_entry->setPassword('123asd!');
            $member_entry->setActive(true);
            $member_entry->setAdmin(false);
            $member_entry->setGroupFk($faker->numberBetween(1, 10));
            try
            {
                $member_entry->save();
            }
            catch (\Exception $e)
            {
            }
        }

        echo BackendController::renderBackendStructure('Backend - Developer Faker', '');
    }

	public function receiveControllerRegistry(ControllerRegistry $controller_registry): void
	{
		// TODO: Implement receiveControllerRegistry() method.
	}
}
