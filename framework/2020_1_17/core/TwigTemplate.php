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

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\DebugExtension;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class TwigTemplate implements ITemplate
{
    private static $initialized = false;
    private static $global_data = array();

    /**
     * @var $twig_instance Environment
     */
    private static $twig_instance = null;

    public static function initialize() : void
    {
        if(self::$initialized)
        {
            return;
        }

        $loaders = [
        ];

        foreach (Register::getAllTemplateDirectories() as $template_directory)
        {
            $loaders[] = new FilesystemLoader($template_directory);
        }

        $loader = new ChainLoader($loaders);

        $settings = [];

        if(DEBUG_AVAILABLE)
        {
            $settings['debug'] = true;
        }

        if(Config::isCacheEnabled())
        {
            $settings['cache'] = CACHE_DIR . '/twig';
        }
		else
		{
			$settings['auto_reload'] = true;
		}

        self::$twig_instance = new Environment(
            $loader,
            $settings
        );

        if(DEBUG_AVAILABLE)
        {
            self::$twig_instance->addExtension(new DebugExtension());
        }

        // ToDo in die Doku aufnehmen
        $json_encode_fnc = new TwigFunction('to_json_object', function ($data){
            if(is_array($data))
            {
                return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }

            return '';
        });

        self::$twig_instance->addFunction($json_encode_fnc);

        // ToDo in die Doku aufnehmen
        $create_access_token_fnc = new TwigFunction('create_access_token', function ($data){
            if(is_string($data))
            {
                return Tool::createAccessToken($data);
            }

            return '';
        });

        self::$twig_instance->addFunction($create_access_token_fnc);

        // ToDo Module

        self::$initialized = true;
    }

    public static function renderFromSource(string $content, array $data = array()) : string
    {
        try
        {
            $t = self::$twig_instance->createTemplate($content);
            return self::$twig_instance->render($t, $data);
        }
        catch (SyntaxError $e)
        {
            return $e->getMessage();
        }
        catch (LoaderError $e)
        {
            return $e->getMessage();
        }
        catch (RuntimeError $e)
        {
            return $e->getMessage();
        }
    }

    public static function render(string $template_alias, array $data = array()): string
    {
    	$data['thash'] = md5($template_alias);
        $data['current_url'] = $_SERVER['REQUEST_URI'];
        $data['base_url'] = Tool::basePath();
        $data['current_controller'] = CURRENT_CONTROLLER;
        $data['debug_available'] = DEBUG_AVAILABLE;
        $data['current_version'] = CURRENT_VERSION;

        $data = array_merge(self::$global_data, $data);

        try
        {

            return self::$twig_instance->render($template_alias  . '.twig', $data);
        }
        catch (LoaderError $e)
        {
            if(DEBUG_AVAILABLE)
            {
                return $e->getMessage();
            }
        }
        catch (RuntimeError $e)
        {
            return $e->getMessage();
        }
        catch (SyntaxError $e)
        {
            return $e->getMessage();
        }

        return '';
    }

    public static function addString(string $key, string $data): void
    {
        self::$global_data[$key] = $data;
    }

    public static function addBool(string $key, bool $data): void
    {
        self::$global_data[$key] = $data;
    }

    public static function addArray(string $key, array $data): void
    {
        self::$global_data[$key] = $data;
    }

    public static function addObject(string $key, object $data): void
    {
        self::$global_data[$key] = $data;
    }

    public static function addInt(string $key, int $data): void
    {
        self::$global_data[$key] = $data;
    }

    public static function addFloat(string $key, float $data): void
    {
        self::$global_data[$key] = $data;
    }

    public static function addFunction(string $alias, callable $function): void
	{
		self::$twig_instance->addFunction(new TwigFunction($alias, $function));
	}
}