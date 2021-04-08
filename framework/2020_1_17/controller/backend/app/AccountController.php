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
use core\ControllerStatus;
use core\Register;
use core\Route;
use core\RouteCollection;

class AccountController implements IBackendApp, IBackendMenu
{
    public static function returnBackendMenu(): array
    {
		if(Register::getControllerRegistry('backend')->getStatus() == ControllerStatus::INSTALLED)
		{
			return array(
				'label' => 'Account',
				'items' => array(
					array(
						'label' => 'Profil',
						'link' => '/backend/app/account/profile'
					)
				)
			);
		}

		return array();
    }

    public static function handleRoutes(): RouteCollection
    {
    	$route_collection = new RouteCollection();
    	$route_collection->add(Route::create('/profile', function (){
    		AccountController::pageView();
    	}));

        return $route_collection;
    }

    public static function pageView() : void
	{
		$member_update_error = '';
		$renew_password_error = '';

		if(isset($_POST['submit_member_data']))
		{
			$member = BackendController::getMemberSessionModel()->getMemberEntry();

			$member_model = new MemberModel();
			if($_POST['email'] !== $member->getEmail() AND $member_model->emailExists($_POST['email']))
			{
				$member_update_error = 'Diese E-Mailadresse wird schon verwendet!';
			}

			if(strlen($member_update_error) === 0)
			{
				if($member instanceof MemberEntry)
				{
					$member->setSurname($_POST['surname']);
					$member->setGivenName($_POST['given_name']);
					$member->setEMail($_POST['email']);
					$member->save();
				}
			}
		}

		if(isset($_POST['submit_password_renew']))
		{
			if(strlen($_POST['first_password']) < 8)
			{
				$renew_password_error = 'Das Passwort ist keine 8 Zeichen lang!';
			}

			if($_POST['first_password'] !== $_POST['second_password'])
			{
				$renew_password_error = 'Die Passwörter sind nicht gleich!';
			}

			if(strlen($renew_password_error) === 0)
			{
				$member = BackendController::getMemberSessionModel()->getMemberEntry();
				if($member instanceof MemberEntry)
				{
					$member->setPassword($_POST['first_password']);
					$member->save();
				}
			}
		}

		$template = Register::getTemplateSystem()::render('backend_account_profile.vue', array(
			'member' => BackendController::getMemberSessionModel()->getMemberEntry()->getRawValues(),
			'renew_password_error' => $renew_password_error,
			'member_update_error' => $member_update_error
		));
		echo BackendController::renderBackendStructure('Account - Profil', $template);
	}
}
