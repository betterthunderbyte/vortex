<?php declare(strict_types=1); namespace core;
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

class Route
{
	use TAsArray;

    const GET = 'GET';
    const PATCH = 'PATCH';
    const DELETE = 'DELETE';
    const POST = 'POST';
    const PUT = 'PUT';

    private $path;
    private $methods;
    private $function;


    public static function create(string $path, callable $function, array $methods = array(Route::GET, Route::POST)) : Route
    {
        $route = new Route();
        $route->setPath($path);
        $route->setFunction($function);
        $route->setMethods($methods);

        return $route;
    }

    public static function get(string $path, callable $function, array $methods = array(Route::GET)) : Route
    {
        $route = new Route();
        $route->setPath($path);
        $route->setFunction($function);
        $route->setMethods($methods);

        return $route;
    }

    public static function post(string $path, callable $function, array $methods = array(Route::POST)) : Route
    {
        $route = new Route();
        $route->setPath($path);
        $route->setFunction($function);
        $route->setMethods($methods);

        return $route;
    }

    public static function delete(string $path, callable $function, array $methods = array(Route::DELETE)) : Route
    {
        $route = new Route();
        $route->setPath($path);
        $route->setFunction($function);
        $route->setMethods($methods);

        return $route;
    }

    public static function patch(string $path, callable $function, array $methods = array(Route::PATCH)) : Route
    {
        $route = new Route();
        $route->setPath($path);
        $route->setFunction($function);
        $route->setMethods($methods);

        return $route;
    }

	public static function put(string $path, callable $function, array $methods = array(Route::PUT)) : Route
	{
		$route = new Route();
		$route->setPath($path);
		$route->setFunction($function);
		$route->setMethods($methods);

		return $route;
	}

    public function __construct()
    {
        $this->path = '';
        $this->methods = array(self::GET, self::POST);
        $this->function = function(IController $controller) : void { };
    }

    public function setPath(string $path) : void
    {
    	if(substr($path, -1) === '/')
		{
			$this->path = substr($path, 0, -1);
		}
		else
		{
			$this->path = $path;
		}
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function setMethods(array $methods) : void
    {
        if(count($methods) === 0)
        {
            return;
        }

        $this->methods = array_values($methods);

        $length = count($this->methods);
        for($i = 0; $i < $length; ++$i)
        {
            switch ($this->methods[$i])
            {
                case self::GET:
                case self::POST:
                case self::DELETE:
                case self::PATCH:
                case self::PUT:
                    break;
                default:
                    unset($this->methods[$i]);
                    break;
            }
        }

        $this->methods = array_values($this->methods);
    }

    public function getMethods() : array
    {
        return $this->methods;
    }

    public function getMethodsString() : string
    {
        $method_string = $this->methods[0];
        $length = count($this->methods);
        for($i = 1; $i < $length; ++$i)
        {
            $method_string .= '|' . $this->methods[$i];
        }

        return $method_string;
    }

    public function setFunction(callable $function) : void
    {
        $this->function = $function;
    }

    public function callFunction(array $params) : void
    {
        call_user_func_array($this->function, $params);
    }
}

