<?php declare(strict_types=1);
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

define('ROOT_DIR', __DIR__);
define('ASSETS_DIR', ROOT_DIR . '/assets');
define('TMP_DIR', ROOT_DIR . '/tmp');
define('CACHE_DIR', ROOT_DIR . '/cache');
define('FILES_DIR', ROOT_DIR . '/files');
define('CONFIG_DIR', ROOT_DIR . '/config');

define('SYSTEM_FILE_PATH', CONFIG_DIR . '/system.json');
define('SYSTEM_SWITCH_FILE_PATH', CONFIG_DIR . '/system_switch.json');

if(!file_exists(SYSTEM_FILE_PATH))
{
    exit('Kein System File Path');
}

if(!file_exists(ROOT_DIR . '/vendor'))
{
    exit('Kein Vendor Folder');
}

if(!file_exists(ROOT_DIR . '/vendor/autoload.php'))
{
    exit('Kein Autoloader');
}

new class
{
    public function __construct()
    {
        if(isset($_COOKIE['system_switch']) AND file_exists(SYSTEM_SWITCH_FILE_PATH))
        {
            $system_switch_config = json_decode(file_get_contents(SYSTEM_SWITCH_FILE_PATH), true, 512, JSON_THROW_ON_ERROR);

            if(isset($system_switch_config['key']) AND $system_switch_config['key'] === $_COOKIE['system_switch'] AND isset($system_switch_config['framework_system']))
            {
                // ToDo Expire hinzufügen
                define('FRAMEWORK_SYSTEM', $system_switch_config['framework_system']);
                define('FRAMEWORK_DIR', ROOT_DIR .'/framework/' . $system_switch_config['framework_system']);
                require FRAMEWORK_DIR . '/import.php';
                return;
            }
        }

        $system_config = json_decode(file_get_contents(SYSTEM_FILE_PATH), true, 512, JSON_THROW_ON_ERROR);

        if(isset($system_config['framework_system']))
        {
            define('FRAMEWORK_SYSTEM', $system_config['framework_system']);
            define('FRAMEWORK_DIR', ROOT_DIR .'/framework/' . $system_config['framework_system']);
        }

        require FRAMEWORK_DIR . '/import.php';
    }
};