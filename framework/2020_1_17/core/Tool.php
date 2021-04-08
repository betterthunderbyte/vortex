<?php declare(strict_types=1); namespace core;
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


class Tool
{
    private function __construct() { }

    public static function zip(string $input_path, string $output_path, string $password = '') : bool
    {
        if(!file_exists($input_path))
        {
            return false;
        }

        if(file_exists($output_path))
        {
            return false;
        }

        $output = array();
        $result = 0;

        $path = getcwd();
        chdir(dirname($output_path));
        exec('zip -rq ' . basename($output_path) . ' ' . basename($input_path), $output, $result);
        chdir($path);
        return $result == 0;
    }

    public static function unzip(string $input_path, string $output_path, string $password = '') : bool
    {
        if(!file_exists($input_path))
        {
            return false;
        }

        if(file_exists($output_path))
        {
            return false;
        }

        $output = array();
        $return = 0;
        exec('unzip ' . $input_path . ' -d ' . $output_path, $output, $return);

        return $return == 0;
    }

	public static function deleteDirectoryRecursive(string $path) : void
	{
		if (is_dir($path))
		{
			$files = scandir($path);
			foreach ($files as $file)
			{
				if ($file != "." && $file != "..")
				{
					self::deleteDirectoryRecursive("$path/$file");
				}
			}

			rmdir($path);
		}
		else if (file_exists($path))
		{
			unlink($path);
		}
	}

	public static function copyDirectoryRecursive(string $source, string $dest) : void
	{
		if (file_exists($dest))
		{
			self::deleteDirectoryRecursive($dest);
		}

		if (is_dir($source))
		{
			mkdir($dest, 0777, true);
			$files = scandir($source);
			foreach ($files as $file)
			{
				if ($file != "." && $file != "..")
				{
					self::copyDirectoryRecursive("$source/$file", "$dest/$file");
				}
			}
		}
		else if (file_exists($source))
		{
			copy($source, $dest);
		}
	}

    public static function hashPassword(string $password) : string
    {
        return password_hash($password, PASSWORD_ARGON2I);
    }

    public static function verifyPassword(string $password, string $hash) : bool
    {
        return password_verify($password, $hash);
    }

    public static function getVersion() : string
    {
        if(file_exists(FRAMEWORK_VERSION_FILE_PATH))
        {
            return self::jsonDecode(file_get_contents(FRAMEWORK_VERSION_FILE_PATH))['version'];
        }

        return '';
    }

    public static function generateCurrentVersion() : string
    {
        $quarter = (round(date('n') / 3, 0, PHP_ROUND_HALF_UP));
        if($quarter === 0)
        {
            $quarter = 1;
        }

        return date('Y') . '_' . $quarter . "_" . date("z");
    }

    public static function cleanName(string $name) : string
    {
        $length = strlen($name);
        for($i = 0; $i < $length; ++$i)
        {
            if(!is_numeric($name[$i]))
            {
                $name = substr($name, $i);
                break;
            }
        }
        $name = str_replace(array(
            ' ', 'Q', 'W', 'E', 'R', 'T', 'Z', 'U', 'I', 'O', 'P', 'A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'Y', 'X', 'C', 'V', 'B', 'N', 'M'
        ), array(
            '_', '_q', '_w', '_e', '_r', '_t', '_z', '_u', '_i', '_o', '_p', '_a', '_s', '_d', '_f', '_g', '_h', '_j', '_k', '_l', '_y', '_x', '_c', '_v', '_b', '_n', '_m'
        ), $name);
        $name = str_replace('__', '_', $name);
        if($name[0] === '_')
        {
            $name = substr($name, 1);
        }
        return $name;
    }

    public static function jsonDecode(string $json) : array
    {
        try
        {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        }
        catch (\Exception $exception)
        {
            throw $exception;
        }
    }

    public static function jsonEncode(array $data) : string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    }

    public static function jsonEncodeObject(object $data) : string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    }

    public static function jsonDecodeObject(string $json) : object
    {
        return json_decode($json, false, 512, JSON_THROW_ON_ERROR);
    }

    public static function createAlias(string $name) : string
    {
        $custom_alias = strtolower($name);
        if($custom_alias[0] === ' ')
        {
            $custom_alias = substr($custom_alias, 1);
        }
        $custom_alias = self::removeNumbersAtBeginning($custom_alias);
        if($custom_alias[0] === ' ')
        {
            $custom_alias = substr($custom_alias, 1);
        }
        $custom_alias = str_replace(array('-', ' ', 'ä', 'ö', 'ü'), array('_', '_', 'ae', 'oe', 'ue'), $custom_alias);
        $custom_alias = self::clearCustomAlias($custom_alias);
        $custom_alias = str_replace('__', '_', $custom_alias);
        return $custom_alias;
    }

    public static function removeNumbersAtBeginning(string $string) : string
    {
        $length = strlen($string);
        for($i = 0; $i < $length; ++$i)
        {
            if(!is_numeric($string[$i]))
            {
                return substr($string, $i);
            }
        }
        return $string;
    }

    public static function clearCustomAlias(string $custom_alias, string $allowed = 'qwertzuiopasdfghjklyxcvbnm_1234567890') : string
    {
        $result = '';
        $length = strlen($custom_alias);
        for($i = 0; $i < $length; ++$i)
        {
            if(stripos($allowed, $custom_alias[$i]) !== false)
            {
                $result .= $custom_alias[$i];
            }
        }
        return $result;
    }

    public static function createRandomHash($length = 15) :string
    {
        $strong = true;
        return bin2hex(openssl_random_pseudo_bytes($length, $strong));
    }

    public static function toMysqlDate(int $time = 0) : string
    {
        return date("Y-m-d", $time);
    }

    public static function toMysqlDateTime(int $time = 0) : string
    {
        return date("Y-m-d H:i:s", $time);
    }

    public static function toTimeFromMysqlDateTime(string $mysql_date_time) : int
    {
        return strtotime($mysql_date_time);
    }

    public static function getFileExtension(string $file) : string
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }

    public static function headerDenyCache() : void
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }

    public static function createAccessToken(string $value) : string
    {
        $values = Tool::base64urlDecode(Tool::jsonEncode(['val' => $value, 'exp' => time() + 1800]));
        $key = Tool::base64urlDecode(hash_hmac("sha256", $values, SECRET));

        return $values . '.' . $key;
    }

    public static function validateAccessToken(string $token, string & $value_out) : bool
    {
        $parts = explode('.', $token);

        if(count($parts) != 2)
        {
            return false;
        }

        $hash = hash_hmac("sha256", $parts[0], SECRET);

        if(Tool::base64urlEncode($parts[1]) != $hash)
        {
            return false;
        }

        $values = Tool::jsonDecode(Tool::base64urlEncode($parts[0]));

        if(!isset($values['exp']))
        {
            return false;
        }

        if($values['exp'] < time())
        {
            return false;
        }

        if(!isset($values['val']))
        {
            return false;
        }

        $value_out = $values['val'];

        return true;
    }

    public static function headerReturnFileMemory(string $name, string $data) : void
    {
        header('Content-Disposition: attachment; filename="' . $name . '"');

        $mime = '';

        switch (Tool::getFileExtension($name))
        {
            case 'css':
                $mime = 'text/css';
                break;
            case 'svg':
                $mime = 'image/svg+xml';
                break;
            case 'zip':
                $mime = 'application/zip';
                break;
            case 'csv':
                $mime = 'text/comma-separated-values';
                break;
            default:
                break;
        }

        Tool::headerOK();
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . strlen($data));
        echo $data;
    }

    public static function headerReturnFile(string $path) : void
    {
        if(file_exists($path))
        {
            header('Content-Disposition: attachment; filename="'.basename($path).'"');

            $mime = '';

            switch (Tool::getFileExtension($path))
            {
                case 'css':
                    $mime = 'text/css';
                    break;
                case 'js':
                    $mime = 'application/javascript';
                    break;
                case 'gif':
                    $mime = 'image/gif';
                    break;
				case 'svg':
					$mime = 'image/svg+xml';
					break;
                case 'zip':
                    $mime = 'application/zip';
                    break;
                case 'csv':
                    $mime = 'text/comma-separated-values';
                    break;
                case 'map':
                    $mime = 'application/json';
                    break;
                default:
                    $mime = 'text/plain';
                break;
            }

			Tool::headerOK();
            header('Content-Type: ' . $mime);
            header('Content-Length: ' . filesize($path));
            echo readfile($path);
            exit;
        }
        else
        {
            self::headerNotFound();
        }
    }

    public static function returnImageData(string $path) : void
    {
        if(file_exists($path))
        {
            switch (self::getFileExtension($path))
            {
                case '.gif': header("Content-Type: image/gif"); break;
                case '.png': header("Content-Type: image/png"); break;
                case '.jpeg':
                case '.jpg': header("Content-Type: image/jpeg"); break;
                default:
                    return;
                    break;
            }
            header('Content-Length: ' . filesize($path));
            echo file_get_contents($path);
            exit;
        }
    }

    public static function returnJsonError(int $code, string $message, array $errors = array()) : void
    {
    	$content = array('code' => $code, 'message' => $message);

    	if(count($errors) > 0)
		{
			$content['errors'] = $errors;
		}

        self::headerBadRequest();
        self::returnJsonData($content);
        exit(1);
    }

    /**
     * Generiert ein Json-String und setzt Header, sowie gibt das per echo aus
     * @param array $data
     */
    public static function returnJsonData(array $data) : void
    {
        $data = Tool::jsonEncode($data);
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Length: ' . strlen($data));
        echo $data;
    }

    public static function getInputData() : string
    {
        $data = file_get_contents('php://input');
        if(is_string($data))
        {
            return $data;
        }
        return "";
    }

    /**
     * 200
     */
    public static function headerOK() : void
    {
        header('HTTP/1.0 200 OK', true, 200);
    }

    /**
     * 201
     */
    public static function headerCreated() : void
    {
        header('HTTP/1.0 201 Created', true, 201);
    }

    /**
     * 202
     */
    public static function headerAccepted() : void
    {
        header('HTTP/1.0 202 Accepted', true, 202);
    }

    /**
     * 204
     */
    public static function headerNoContent() : void
    {
        header('HTTP/1.0 204 No Content', true, 204);
    }

    /**
     * 205
     */
    public static function headerResetContent() : void
    {
        header('HTTP/1.0 205 Reset Content', true, 205);
    }

    /**
     * 400
     */
    public static function headerBadRequest() : void
    {
        header('HTTP/1.0 400 Bad Request', true, 400);
    }

    /**
     * 401
     */
    public static function headerUnauthorized() : void
    {
        header('HTTP/1.0 401 Unauthorized', true, 401);
    }

    /**
     * 403
     */
    public static function headerForbidden() : void
    {
        header('HTTP/1.0 403 Forbidden', true, 403);
    }

    /**
     * 404
     */
    public static function headerNotFound() : void
    {
        header('HTTP/1.0 404 Not Found', true, 404);
    }

    /**
     * 405
     */
    public static function headerMethodNotAllowed() : void
    {
        header('HTTP/1.0 405 Method Not Allowed', true, 405);
    }

    /**
     * 410
     */
    public static function headerGone() : void
    {
        header('HTTP/1.0 410 Gone', true, 410);
    }

    /**
     * 411
     */
    public static function headerLengthRequired() : void
    {
        header('HTTP/1.0 411 Length Required', true, 411);
    }

    /**
     * 415
     */
    public static function headerUnsupportedMediaType() : void
    {
        header('HTTP/1.0 415 Unsupported Media Type', true, 415);
    }

    /**
     * 423
     */
    public static function headerLocked() : void
    {
        header('HTTP/1.0 423 Locked ', true, 423);
    }

    /**
     * 426
     */
    public static function headerUpgradeRequired() : void
    {
        header('HTTP/1.0 426 Upgrade Required ', true, 426);
    }

    /**
     * 503
     */
    public static function headerServiceUnavailable() : void
    {
        header('HTTP/1.0 503 Service Unavailable ', true, 503);
    }

    /**
     * Leitet den Benutzer auf eine andere Seite weiter
     *
     * Benutzt schon den BasePath
     *
     * @param string $path
     */
    public static function moveTo(string $path) : void
    {
        $move = 'Location: ' . Tool::basePath() . $path;
        header($move);
        exit();
    }

    /**
     * Gibt die aktuelle URL zurück
     * Beispiel:
     * /mbf
     * oder nichts
     * @return string
     */
    public static function basePath() : string
    {
        $path = dirname($_SERVER['SCRIPT_NAME']);
        if(isset($path{2}))
        {
            return $path;
        }
        return '';
    }

    public static function issetArray(array $array, array $keys) : bool
    {
        foreach ($keys as $key)
        {
            if(!isset($array[$key]))
            {
                return false;
            }
        }
        return true;
    }

    public static function base64urlEncode(string $data) : string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64urlDecode(string $data) : string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    public static function getAuthorization() : string
    {
        $headers = getallheaders();
        return isset($headers['Authorization']) ? $headers['Authorization'] : '';
    }

    public static function getBearerToken() : string
    {
        $authorization = Tool::getAuthorization();

        $parts = explode(' ', $authorization);

        if(count($parts) === 2)
        {
            $first = $parts[0];
            $second = $parts[1];

            if($first === 'Bearer')
            {
                return $second;
            }
        }

        return '';
    }

}