<?php declare(strict_types=1); namespace controller\package_distributor;

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
use controller\backend\IBackendApp;
use controller\backend\IBackendMenu;
use core\ControllerStatus;
use core\Register;
use core\Route;
use core\RouteCollection;

class PackageDistributorBackendApp implements IBackendApp, IBackendMenu
{

	public static function handleRoutes(): RouteCollection
	{
		$route_collection = new RouteCollection();

        $route_collection->add(Route::get('/systems', function (){ PackageDistributorBackendApp::systemListRequest(); }));

		return $route_collection;
	}

	public static function systemListRequest() : void
    {
        $template = Register::getTemplateSystem()::render('pd_system_release_table_view.vue', array());

        BackendController::renderBackendStructure('Package Distributor - Release View', $template);
    }

	/**
	 * @inheritDoc
	 */
	public static function returnBackendMenu(): array
	{
        if(!Register::getControllerRegistry('package_distributor')->getStatus() == ControllerStatus::INSTALLED)
        {
            return array();
        }

		$session_model = BackendController::getMemberSessionModel();

		if(!$session_model->withPermission('package_distributor'))
		{
			return array();
		}

		return array(
			'label' => 'Package Distributor',
			'items' => array(
				array(
					'label' => 'Systeme',
					'link' => '/backend/app/package_distributor/systems'
				),
				array(
					'label' => 'Anwendungen',
					'link' => '/backend/app/package_distributor/systems'
				),
				array(
					'label' => 'Empfänger',
					'link' => '/backend/app/package_distributor/recipient'
				)
			)
		);
	}
}
