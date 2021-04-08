<?php declare(strict_types=1); namespace controller\backend\entry;
use controller\backend\TAlias;
use controller\backend\TDescription;
use controller\backend\TTitle;
use core\field\TextField;
use core\IEntry;
use core\TEntry;

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

class PermissionEntry implements IEntry
{
    use TEntry;
    use TAlias;
    use TTitle;
    use TDescription;

    protected static function initialize(): void
    {
        self::setTableName('be_permission');

        $alias_field = new TextField();
        $alias_field->setName('alias');
        $alias_field->setUnique(true);
        $alias_field->setLength(255);
        self::addField($alias_field);

        $title_field = new TextField();
        $title_field->setName('title');
        self::addField($title_field);

        $description_field = new TextField();
        $description_field->setName('description');
        self::addField($description_field);
    }
}