<?php declare(strict_types=1);
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

use core\Route;
use core\RouteCollection;

new class
{
    public function __construct()
    {
        $path = '/';

        if(isset($_GET['path']))
        {
        	if(substr("testers", -1) === '/')
			{
				$path = '/' . substr($_GET['path'], 0, -1);
			}
			else
			{
				$path = '/' . $_GET['path'];
			}
        }

        define('URL_PATH', $path);

        $alto_router = new AltoRouter();
        $alto_router->map('GET|POST|PATCH|PUT|DELETE', '/[a:controller]', '');
        $alto_router->map('GET|POST|PATCH|PUT|DELETE', '/[a:controller]/', '');
        $alto_router->map('GET|POST|PATCH|PUT|DELETE', '/[a:controller]/[**:trailing]', '');

        try
        {
            $result = $alto_router->match(URL_PATH);
        }
        catch (Exception $e)
        {
            \core\Tool::headerBadRequest();
            exit();
        }


        if(!is_array($result) AND !isset($result['params']['controller']))
        {
			\core\Tool::moveTo('/' . DEFAULT_CONTROLLER);
			return;
        }

		$controller_alias = $result['params']['controller'];
		if(!\core\Register::controllerExists($controller_alias))
		{
			\core\Tool::headerNotFound();
			\core\Tool::moveTo('/' . DEFAULT_CONTROLLER);
			return;
		}

		define('CURRENT_CONTROLLER', $controller_alias);

		$controller_registry = \core\Register::getControllerRegistry($controller_alias);

		$controller_class = \core\Register::getControllerClass($controller_alias);

		$function_name = $controller_class . '::setup';

		if(!is_callable($function_name))
		{
			// ToDo Error handling

			return;
		}
		// ToDo(Thorben) Eine Error Page anzeigen mit ein Error-Code oder etwas in der Richtung
		$route_collection = call_user_func($function_name, $controller_registry);
		if(!($route_collection instanceof RouteCollection))
		{
			// ToDo Error handling
			return;
		}

		$controller_router = new AltoRouter();
		$controller_router->setBasePath('/' . $controller_alias);
		foreach ($route_collection as $route)
		{
			$controller_router->map($route->getMethodsString(), $route->getPath(), $route);
		}

		try
		{
			$result = $controller_router->match($path);
			if(!is_array($result) AND !isset($result['target']))
			{
				call_user_func($route_collection->getNotFound());
				return;
			}

			$route = $result['target'];
			if($route instanceof Route)
			{
				$route->callFunction($result['params']);
			}

		}
		catch (Exception $e)
		{
			if(DEBUG_AVAILABLE)
			{
				throw $e;
			}

			call_user_func($route_collection->getNotFound());
		}
    }
};

