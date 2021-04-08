<?php declare(strict_types=1); namespace controller\assets;
use core\ControllerRegistry;
use core\Register;
use core\Route;
use core\RouteCollection;
use core\Tool;

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

class AssetsController implements \core\IController
{
    public static function setup(ControllerRegistry $controller_registry): \core\RouteCollection
    {
        $route_collection = new RouteCollection();

        $route_collection->add(\core\Route::get('/[*:path].[a:type]', function (string $path, string $type) {
        	$path = str_replace(array('..'), array(''), $path);

            if(AssetsController::requestAsset($path, $type))
            {
                return;
            }

            foreach (glob(FRAMEWORK_DIR . '/controller/*/assets') as $cp)
            {
                if(file_exists($cp . '/' .  $path . '.' . $type))
                {
                    \core\Tool::headerReturnFile($cp . '/' .  $path . '.' . $type);
                    return;
                }
            }

            Tool::headerNotFound();
        }));

        return $route_collection;
    }

    private static function requestAsset(string $name, string $type) : bool
    {
        $asset_path = FRAMEWORK_DIR . '/assets/' . $name . '.' . $type;
        if(file_exists($asset_path))
        {
            \core\Tool::headerReturnFile($asset_path);
            return true;
        }

        return false;
    }
}
