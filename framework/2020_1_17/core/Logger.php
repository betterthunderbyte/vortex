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
class Logger
{
    public static function Exception(\Exception $exception) : void
    {
        $message_full = 'Exception File: ';

        $message_full .= $exception->getFile();
        $message_full .= ' Line: ';
        $message_full .= $exception->getLine();
        $message_full .= ' Code: ';
        $message_full .= $exception->getCode();
        $message_full .= ' Message ';
        $message_full .= $exception->getMessage();

        file_put_contents(ROOT_DIR . '/log/error.log', $message_full, FILE_APPEND);
    }

    public static function Error(...$messages) : void
    {
        $message_full = '';
        foreach ($messages as $message)
        {
            if(is_string($message))
            {
                $message_full .= ' ' . $message;
            }
            else if(is_array($message))
            {
                $message_full .= ' ' . print_r($message, true);
            }
            else if(is_int($message))
            {
                $message_full .= ' ' . $message;
            }
            else if(is_float($message))
            {
                $message_full .= ' ' . $message;
            }
            else if(is_double($message))
            {
                $message_full .= ' ' . $message;
            }
            else if(is_bool($message))
            {
                $message_full .= ' ' . ($message ? 'True' : 'False');
            }
            else if(is_null($message))
            {
                $message_full .= ' Null';
            }
        }

        file_put_contents(ROOT_DIR . '/log/error.log', date('Y-m-d H:i:s') . '|'. $message_full . '\n', FILE_APPEND);
    }

    public static function Warning(...$messages) : void
    {

    }

    public static function Log(...$messages) : void
    {
        $message_full = '';
        foreach ($messages as $message)
        {
            if(is_string($message))
            {
                $message_full .= ' ' . $message;
            }
            else if(is_array($message))
            {
                $message_full .= ' ' . print_r($message, true);
            }
            else if(is_int($message))
            {
                $message_full .= ' ' . $message;
            }
            else if(is_float($message))
            {
                $message_full .= ' ' . $message;
            }
            else if(is_double($message))
            {
                $message_full .= ' ' . $message;
            }
            else if(is_bool($message))
            {
                $message_full .= ' ' . ($message ? 'True' : 'False');
            }
            else if(is_null($message))
            {
                $message_full .= ' Null';
            }
        }

        file_put_contents(ROOT_DIR . '/log/default.log', date('Y-m-d H:i:s') . '|'. $message_full . '\n', FILE_APPEND);
    }
}
