<?php declare(strict_types=1); namespace core;
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

class Config
{
	private function __construct() { }

	private static $settings = array(
        'db_host' => 'localhost',
        'db_user' => 'vortex',
        'db_password' => '123asd!',
        'debug' => true,
        'default_controller' => 'backend',
        'master_password' => '$argon2i$v=19$m=65536,t=4,p=1$bHg5bVN2NzRTaWhsWEhBdQ$qs11mt4YURh9qkC6gXpOxjp5tXxifh97Vw3BV28nGmY',
        'secret' => '123asd!',
        'cache' => false,
        'update_server' => '',
        'product_key' => ''
    );

    private static $loaded = false;

    const CONFIG_PATH = CONFIG_DIR . '/config.json';

	public static function getRaw() : array
	{
		return self::$settings;
	}

    public static function setProductKey(string $product_key) : void
    {
        self::$settings['product_key'] = $product_key;
    }

    public static function getProductKey() : string
    {
        return self::$settings['product_key'];
    }

    public static function getUpdateServer() : string
    {
        return self::$settings['update_server'];
    }

    public static function setUpdateServer(string $update_server_url) : void
    {
        self::$settings['update_server'] = $update_server_url;
    }

    public static function getDatabaseHost() : string
    {
        return self::$settings['db_host'];
    }

    public static function setDatabaseHost(string $host) : void
    {
        self::$settings['db_host'] = $host;
    }

    public static function getDatabaseUser() : string
    {
        return self::$settings['db_user'];
    }

    public static function setDatabaseUser(string $user) : void
    {
        self::$settings['db_user'] = $user;
    }

    public static function getDatabasePassword() : string
    {
        return self::$settings['db_password'];
    }

    public static function setDatabasePassword(string $password) : void
    {
        self::$settings['db_password'] = $password;
    }

    public static function isDebugEnabled() : bool
    {
        return self::$settings['debug'];
    }

    public static function setDebug(bool $debug) : void
    {
        self::$settings['debug'] = $debug;
    }

    public static function getDefaultController() : string
    {
        return self::$settings['default_controller'];
    }

    public static function setDefaultController(string $controller) : void
    {
        self::$settings['default_controller'] = $controller;
    }

    public static function getMasterPassword() : string
    {
        return self::$settings['master_password'];
    }

    public static function setMasterPassword(string $password) : void
    {
        self::$settings['master_password'] = Tool::hashPassword($password);
    }

    public static function getSecret() : string
    {
        return self::$settings['secret'];
    }

    public static function setSecret(string $secret) : void
    {
        self::$settings['secret'] = $secret;
    }

    public static function isCacheEnabled() : bool
    {
        return self::$settings['cache'];
    }

    public static function setCache(bool $cache) : void
    {
        self::$settings['cache'] = $cache;
    }

	/**
	 * Gibt true zurück wenn die Configdatei existiert
	 * @return bool
	 */
	public static function exists() : bool
    {
        return file_exists(self::CONFIG_PATH);
    }

	/**
	 * Gibt true zurück wenn die Configdatei schon geladen wurde
	 * @return bool
	 */
	public static function isLoaded() : bool
    {
        return self::$loaded;
    }

	/**
	 *	Speichert die Änderungen ab
	 */
	public static function save() : void
    {
        file_put_contents(self::CONFIG_PATH, Tool::jsonEncode(self::$settings));
    }

	/**
	 * Lädt die Configdatei wenn diese nicht schon geladen wurde
	 * @throws \Exception
	 */
	public static function load() : void
    {
        if(self::$loaded)
        {
            return;
        }

        $temp = Tool::jsonDecode(file_get_contents(self::CONFIG_PATH));

        foreach ($temp as $key => $value)
		{
			self::$settings[$key] = $value;
		}


        self::$loaded = true;
    }
}
