<?php declare(strict_types=1); namespace controller\backend;

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

use AltoRouter;
use controller\backend\entry\SessionEntry;
use controller\backend\model\MemberSessionModel;
use core\ControllerRegistry;
use core\ControllerStatus;
use core\IController;
use core\Register;
use core\Route;
use core\RouteCollection;
use core\Tool;


class BackendController implements IController, IBackendMenu
{
    private static $instance = null;

    /**
     * @var $member_session_model MemberSessionModel
     */
    private static $member_session_model = null;

    public static function & instance() : ?BackendController
    {
        return self::$instance;
    }

    public static function getMemberSessionModel() : ?MemberSessionModel
    {
        return self::$member_session_model;
    }

    public function __construct()
    {
        self::$instance = $this;
    }

    public static function setup(ControllerRegistry $controller_registry) : RouteCollection
    {
        $route_collection = new RouteCollection();
        self::$member_session_model = new MemberSessionModel();

		if($controller_registry->getStatus() == ControllerStatus::INSTALLED)
		{
			if(isset($_POST['email']) AND isset($_POST['password']))
			{
				$email = $_POST['email'];
				$password = $_POST['password'];

				self::$member_session_model->loginWithEmailPassword($email, $password);
				$session_entry = self::$member_session_model->getSessionEntry();
				if($session_entry instanceof SessionEntry)
				{
					setcookie('backend_key', $session_entry->getKey(), $session_entry->getExpire() + (24 * 3600), '/backend', '', true);

					if(self::$member_session_model->getMemberEntry()->getRenewPassword())
					{
						Tool::moveTo('/backend/renew');
					}
					else
					{
						Tool::moveTo('/backend/dashboard');
					}
				}
			}
			else if(isset($_COOKIE['backend_key']))
			{
				self::$member_session_model->loginWithKey($_COOKIE['backend_key']);

				$session_entry = self::$member_session_model->getSessionEntry();
				if($session_entry instanceof SessionEntry)
				{
					setcookie('backend_key', $session_entry->getKey(), $session_entry->getExpire() + (24 * 3600), '/backend', '', true);
				}
			}
		}
		else
		{
			if(isset($_POST['master_password']))
			{
				$password = $_POST['master_password'];

				self::$member_session_model->loginWithMasterPassword($password);
				$session_entry = self::$member_session_model->getSessionEntry();
				if($session_entry instanceof SessionEntry)
				{
					setcookie('backend_key', $session_entry->getKey(), $session_entry->getExpire() + (24 * 3600), '/backend', '', true);
					Tool::moveTo('/backend/dashboard');
				}
			}
			else if(isset($_COOKIE['backend_key']))
			{
				self::$member_session_model->loginWithMasterKey($_COOKIE['backend_key']);

				$session_entry = self::$member_session_model->getSessionEntry();
				if($session_entry instanceof SessionEntry)
				{
					setcookie('backend_key', $session_entry->getKey(), $session_entry->getExpire() + (24 * 3600), '/backend', '', true);
				}
			}
		}

		if(self::$member_session_model->isLoggedIn())
		{
			if(self::$member_session_model->getMemberEntry()->getRenewPassword())
			{
				$route_collection->add(Route::create('/', function () {
					Tool::moveTo('/backend/renew');
				}));

				$route_collection->add( Route::create('/renew', function () {
					$errors = array();

					if(isset($_POST['renew']))
					{
						$password1 = $_POST['password'];
						$password2 = $_POST['password_again'];

						if($password1 !== $password2)
						{
							$errors[] = 'Die beiden Passwörter stimmen nicht über ein!';
						}
						else if(Tool::verifyPassword($password1, self::$member_session_model->getMemberEntry()->getPassword()))
						{
							$errors[] = 'Es darf nicht das alte Passwort sein!';
						}
						else if(strlen($password1) < 6) // ToDo(Thorben) Die Mondestlänge den Administrator als Einstellung anbieten
						{
							$errors[] = 'Das Passwort sollte mindestens 6 Zeichen lang sein!';
						}

						if(count($errors) === 0)
						{
							self::$member_session_model->getMemberEntry()->setPassword($password1);
							self::$member_session_model->getMemberEntry()->setRenewPassword(false);
							self::$member_session_model->getMemberEntry()->save();

							Tool::moveTo('/backend/dashboard');
						}
					}

					$renew_password_template = Register::getTemplateSystem()::render('backend_login_password_renew.vue', array('errors' => $errors));
					echo Register::getTemplateSystem()::render('backend_base', array('body' => $renew_password_template));
				}));

				return $route_collection;
			}

			foreach (glob(FRAMEWORK_DIR . '/controller/*/BackendRegistry.php') as $path)
			{
				require_once $path;
			}

			$route_collection->add(Route::create('/', function () {
				Tool::moveTo('/backend/dashboard');
			}));

			$route_collection->add( Route::create('/dashboard', function () {
				BackendController::dashboardPage();
			}));

			$route_collection->add(Route::create('/logout', function () {
				BackendController::logoutPage();
			}));

			$route_collection->add(Route::create('/app/[a:app]', function (string $app) {
				$controller = new BackendController();
				$controller->runApplication($app);
			}, array(Route::DELETE, Route::GET, Route::PATCH, Route::POST, Route::PUT)));

			$route_collection->add(Route::create( '/app/[a:app]/', function (string $app) {
				$controller = new BackendController();
				$controller->runApplication($app);
			}, array(Route::DELETE, Route::GET, Route::PATCH, Route::POST, Route::PUT)));

			$route_collection->add( Route::create( '/app/[a:app]/[**:trailing]', function (string $app) {
				$controller = new BackendController();
				$controller->runApplication($app);
			}, array(Route::DELETE, Route::GET, Route::PATCH, Route::POST, Route::PUT)));

			$route_collection->setNotFound(function (){
				Tool::moveTo('/backend/dashboard');
			});
		}
		else
		{
			if($controller_registry->getStatus() == ControllerStatus::INSTALLED)
			{
				$route_collection->add(Route::create('/login', function (){
					BackendController::loginPage();
				}));

				$route_collection->add(Route::create('/help', function (){
					// ToDo Help
				}));

				$route_collection->add(Route::create( '/password_forgotten', function (){
					// ToDo Password Forgotten
				}));

				$route_collection->add(Route::create('/register', function (){
					// ToDo Register
				}));
			}
			else
			{
				$route_collection->add(Route::create('/login', function (){
					BackendController::masterLoginPage();
				}));
			}

			$route_collection->setNotFound(
				function () { Tool::moveTo('/backend/login'); }
			);
		}

        return $route_collection;
    }

    public function runApplication(string $backend_application) : void
    {
        if(!BackendRegister::backendControllerExists($backend_application))
        {
            \core\Tool::headerNotFound();
            return;
        }
        $backend_app_class = BackendRegister::getBackendController($backend_application);

        if(!method_exists($backend_app_class, 'handleRoutes'))
        {
            \core\Tool::headerNotFound();
            return;
        }

        $route_collection = call_user_func($backend_app_class . '::handleRoutes');

        if(!($route_collection instanceof RouteCollection))
        {
            \core\Tool::headerNotFound();
            return;
        }

        $alto_router = new AltoRouter();
        $alto_router->setBasePath('/' . CURRENT_CONTROLLER . '/app/' . $backend_application);

        foreach ($route_collection as $route)
        {
			$alto_router->map($route->getMethodsString(), $route->getPath(), $route);
        }

        $result = $alto_router->match(URL_PATH);
        if(is_array($result) AND isset($result['target']))
        {
            $route = $result['target'];
            if($route instanceof Route)
            {
                $route->callFunction($result['params']);
            }
        }
    }

    public static function loginPage() : void
    {
        $login_view = Register::getTemplateSystem()::render('backend_login', []);

        Tool::headerOK();
        echo Register::getTemplateSystem()::render('backend_base', [
            'title' => 'Backend - Login',
            'head' => '',
            'body' => $login_view,
            'script' => ''
        ]);
    }

    public static function dashboardPage() : void
    {
        echo self::renderBackendStructure('Backend - Dashboard', '');
    }

    public static function logoutPage() : void
    {
        self::$member_session_model->logout();
        setcookie('backend_key', '', 0);

        $logout_view = Register::getTemplateSystem()::render('backend_logout', []);

        echo Register::getTemplateSystem()::render('backend_base', [
            'title' => 'Backend - Login',
            'head' => '',
            'body' => $logout_view,
            'script' => ''
        ]);
    }

    public static function masterLoginPage() : void
	{
		$login_master = Register::getTemplateSystem()::render('backend_login_master', array());
		echo Register::getTemplateSystem()::render('backend_base', array('body' => $login_master));
	}

    public static function renderBase(string $title, string $body, string $head, string $script = '') : string
    {
        return Register::getTemplateSystem()::render('backend_base', [
            'title' => $title,
            'head' => $head,
            'body' => $body,
            'script' => $script
        ]);
    }

    public static function renderBackendStructure(string $title, string $body, string $head = '', string $script = '') : string
    {
        $menu = array();

        $menu['backend'] = self::returnBackendMenu();

        foreach (BackendRegister::getAllBackendControllersWithMenu() as $alias => $backend_controller)
        {
            $menu[$alias] = call_user_func($backend_controller . '::returnBackendMenu');
        }

        $base_structure_view = Register::getTemplateSystem()::render('backend_structure.vue', [
            'body' => $body,
            'menu' => $menu,
        ]);

        return Register::getTemplateSystem()::render('backend_base', [
            'title' => $title,
            'head' => $head,
            'body' => $base_structure_view,
            'script' => $script
        ]);
    }

    public static function returnBackendMenu(): array
    {

    	// ToDo(Thorben) Die Möglichkeit Tags einzubauen
    	if(Register::getControllerRegistry('backend')->getStatus() == ControllerStatus::INSTALLED)
		{
			return array(
				'label' => 'Allgemein',
				'items' => array(
					array(
						'label' => 'Dashboard',
						'link' => '/backend/dashboard'
					)
				)
			);
		}

		return array();
    }
}
