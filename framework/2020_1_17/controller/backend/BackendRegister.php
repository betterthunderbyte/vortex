<?php declare(strict_types=1); namespace controller\backend;

/**
 * MIT License
 *
 * Copyright (c) 2020 jeamu
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

class BackendRegister
{
    private function __construct() { }

    private static $backend_controllers = array();
    private static $backend_controllers_with_menu = array();

    public static function backendControllerExists(string $alias) : bool
    {
        return isset(self::$backend_controllers[$alias]);
    }

    public static function getBackendController(string $alias) : string
    {
        if(!isset(self::$backend_controllers[$alias]))
        {
            throw new \Exception();
        }

        return self::$backend_controllers[$alias];
    }

    public static function registerBackendController(string $alias, string $backend_controller) : void
    {
        if(is_subclass_of($backend_controller, IBackendApp::class, true))
        {
            self::$backend_controllers[$alias] = $backend_controller;
        }

        if(is_subclass_of($backend_controller, IBackendMenu::class, true))
        {
            self::$backend_controllers_with_menu[$alias] = $backend_controller;
        }
    }

    public static function getAllBackendControllers() : array
    {
        return self::$backend_controllers;
    }

    public static function getAllBackendControllersWithMenu() : array
    {
        return self::$backend_controllers_with_menu;
    }
}
