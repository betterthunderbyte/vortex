<?php declare(strict_types=1); namespace controller\backend\src;
use controller\backend\BackendController;
use core\Config;
use core\ControllerRegistry;
use core\Register;
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

class SystemSettings
{
	public static function view() : void
	{
		$controllers = array();

		foreach (Register::getAllControllers() as $controller)
		{
			if($controller instanceof ControllerRegistry)
			{
				$controllers[] = $controller->asArray();
			}
		}

		$settings = Register::getTemplateSystem()::render(
			'backend_system_settings.vue',
			array(
				'controllers' => $controllers,
				'settings' => Config::getRaw()
			)
		);

		echo BackendController::renderBackendStructure('Backend - Systemeinstellungen', $settings);
	}


	public static function saveRequest() : void
	{
		try
		{
			$data = Tool::jsonDecode(Tool::getInputData());
		}
		catch (\Exception $e)
		{
			Tool::headerBadRequest();
			return;
		}

		if(isset($data['db_host']))
		{
			Config::setDatabaseHost($data['db_host']);
		}

		if(isset($data['db_user']))
		{
			Config::setDatabaseUser($data['db_user']);
		}

		if(isset($data['db_password']))
		{
			Config::setDatabasePassword($data['db_password']);
		}

		if(isset($data['debug']))
		{
			Config::setDebug($data['debug']);
		}

		if(isset($data['default_controller']))
		{
			Config::setDefaultController($data['default_controller']);
		}

		if(isset($data['master_password']))
		{
			Config::setMasterPassword($data['master_password']);
		}

		if(isset($data['secret']))
		{
			Config::setSecret($data['secret']);
		}

		if(isset($data['cache']))
		{
			Config::setCache($data['cache']);
		}

		if(isset($data['update_server']))
		{
			Config::setUpdateServer($data['update_server']);
		}

		if(isset($data['product_key']))
		{
			Config::setProductKey($data['product_key']);
		}

		Config::save();
		Tool::headerOK();
	}
}