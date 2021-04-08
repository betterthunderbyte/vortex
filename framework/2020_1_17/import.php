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

use core\Autoloader;
use core\Register;

if(file_exists(FRAMEWORK_DIR . '/vendor'))
{
    require FRAMEWORK_DIR . '/vendor/autoload.php';
}
else
{
    require ROOT_DIR . '/vendor/autoload.php';
}

require FRAMEWORK_DIR . '/core/Autoloader.php';

Autoloader::register();

new class {
    function __construct()
    {
        define('FRAMEWORK_VERSION_FILE_PATH', FRAMEWORK_DIR . '/version.json');
        define('FRAMEWORK_DATABASE_FILE_PATH', FRAMEWORK_DIR . '/db.json');

        define('FRAMEWORK_FILES_DIR', FRAMEWORK_DIR . '/files');

        if(!file_exists(FRAMEWORK_FILES_DIR))
        {
            mkdir(FRAMEWORK_FILES_DIR);
        }

        if(\core\Config::exists())
        {
            \core\Config::load();
        }
        else
        {
            \core\Config::save();
        }

        if(\core\Config::isDebugEnabled())
        {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
            ini_set('error_reporting', '1');
            ini_set('log_errors', '1');
            ini_set('error_log', '/home/vortex/www/log/php-error.log');
            if(isset($_SERVER['REMOTE_ADDR']))
            {
                ini_set('xdebug.remote_host', $_SERVER['REMOTE_ADDR']);
            }


            define('CURRENT_VERSION', \core\Tool::generateCurrentVersion());
            file_put_contents(FRAMEWORK_VERSION_FILE_PATH, \core\Tool::jsonEncode(array('version' => CURRENT_VERSION)));

            set_error_handler(function ($severity, $message, $file, $line)
            {
                throw new \ErrorException($message, $severity, $severity, $file, $line);
            });

            define('DEBUG_AVAILABLE', true);
        }
        else
        {
            ini_set('display_errors', '-1');
            ini_set('display_startup_errors', '-1');
            ini_set('error_reporting', '-1');
            ini_set('log_errors', '1');
            ini_set('error_log', '/home/vortex/www/log/php-error.log');

            define('DEBUG_AVAILABLE', false);
        }

        if(!defined('CURRENT_VERSION'))
        {
            define('CURRENT_VERSION', \core\Tool::getVersion());
        }


        define('DATABASE_FILE_PATH', FRAMEWORK_DIR . '/db.json');

        if(file_exists(DATABASE_FILE_PATH))
        {
            $database_settings = \core\Tool::jsonDecode(file_get_contents(DATABASE_FILE_PATH));
            define('DATABASE', $database_settings['database']);
        }
        else
        {
            define('DATABASE', CURRENT_VERSION);
        }

        $connection = new \core\Connection();
        $connection->connect(\core\Config::getDatabaseHost(), \core\Config::getDatabaseUser(), \core\Config::getDatabasePassword());

        if($connection->get_pdo() !== null)
        {
            define('CONNECTION_AVAILABLE', true);
            \core\Register::registerConnection($connection);

            $builder = new \core\MariaDbBuilder();

            if(!$builder->databaseExists(DATABASE))
            {
                $builder->createDatabase(DATABASE);
            }

            $db_connection = new \core\Connection();
            $db_connection->connectWithDatabase(\core\Config::getDatabaseHost(), \core\Config::getDatabaseUser(), \core\Config::getDatabasePassword(), DATABASE);
            \core\Register::registerDBConnection($db_connection);
        }

        define('DEFAULT_CONTROLLER', \core\Config::getDefaultController());

        if(!defined('CONNECTION_AVAILABLE'))
        {
            define('CONNECTION_AVAILABLE', false);
        }

        foreach (glob(FRAMEWORK_DIR .'/controller/*/Registry.php') as $registry_path)
        {
            require_once $registry_path;
        }

        Register::registerTemplateSystem(new \core\TwigTemplate());
        \core\TwigTemplate::initialize();

        Register::initializeEntries();

        foreach (Register::getAllControllers() as $controller_registry)
        {
            if($controller_registry instanceof \core\ControllerRegistry)
            {
                $controller_registry->callPostInit();
            }
        }

        \core\Register::initializeModels();
    }
};

