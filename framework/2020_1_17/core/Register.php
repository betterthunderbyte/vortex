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

class Register
{
	private static $controllers = array();
	private static $connection = null;
	private static $db_connection = null;
    private static $entries = array();
    private static $template_directories = array();
    private static $models = array();
    private static $template_system = null;
    private static $assets_paths = array();

	private function __construct() { }

    public static function registerAssetPath(string $path) : void
    {
        self::$assets_paths[md5($path)] = $path;
    }

    public static function getAllAssetPaths() : array
    {
        return array_values(self::$assets_paths);
    }

	public static function registerController(ControllerRegistry $controller_registry) : void
	{
		self::$controllers[$controller_registry->getAlias()] = $controller_registry;

		foreach ($controller_registry->getEntries() as $entry)
		{
			self::registerEntry($entry);
		}
	}

    public static function controllerExists(string $alias) : bool
    {
        return isset(self::$controllers[$alias]);
    }

	public static function getAllControllers() : array
	{
		return self::$controllers;
	}

    public static function getControllerRegistry(string $alias) : ?ControllerRegistry
	{
		if(isset(self::$controllers[$alias]))
		{
			return self::$controllers[$alias];
		}

		return null;
	}

	public static function getControllerClass(string $alias) : string
    {
        if(!isset(self::$controllers[$alias]))
        {
            throw new \Exception('Der Controller mit den Alias ' . $alias . ' existiert nicht!');
        }

        return self::$controllers[$alias]->getClass();
    }

    public static function isControllerInstalled(string $alias) : bool
	{
		if(!isset(self::$controllers[$alias]))
		{
			throw new \Exception('Der Controller mit den Alias ' . $alias . ' existiert nicht!');
		}

		return self::$controllers[$alias]->isInstalled();
	}

	public static function registerConnection(IConnection $connection)
    {
		self::$connection = $connection;
    }

    public static function & getConnection() : ?IConnection
	{
		return self::$connection;
	}

	public static function registerDBConnection(IConnection $connection)
	{
		self::$db_connection = $connection;
	}

	public static function & getDBConnection() : ?IConnection
	{
		return self::$db_connection;
	}

	public static function registerEntry(string $entry_class) : void
    {
        self::$entries[] = $entry_class;
    }

    public static function getAllEntries() : array
    {
        return self::$entries;
    }

    public static function registerTemplateDirectory(string $path) : void
    {
        self::$template_directories[] = $path;
    }

    public static function getAllTemplateDirectories() : array
    {
        return self::$template_directories;
    }

	public static function registerModel(string $model_class) : void
	{
	    if(is_subclass_of($model_class, IModel::class, true))
        {
            self::$models[] = $model_class;
        }
	}

	public static function getAllModels()
	{
        return self::$models;
	}

	public static function registerTemplateSystem(ITemplate $template_system) : void
    {
        self::$template_system = $template_system;
    }

    public static function getTemplateSystem() : ITemplate
    {
        return self::$template_system;
    }

    public static function initializeEntries() : void
    {
        foreach (self::$entries as $entry)
        {
            if(method_exists($entry, 'callInitialize'))
            {
                call_user_func($entry . '::callInitialize');
            }
        }
    }

    public static function initializeModels() : void
    {
        if(!CONNECTION_AVAILABLE)
        {
            return;
        }

        foreach (self::$models as $model)
        {
            if(method_exists($model, 'initialize'))
            {
                call_user_func($model . '::initialize');
            }
        }
    }
}
