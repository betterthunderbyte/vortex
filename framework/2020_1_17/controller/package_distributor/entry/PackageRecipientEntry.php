<?php declare(strict_types=1); namespace controller\package_distributor\entry;

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

use core\field\DateTimeField;
use core\field\TextField;
use core\IEntry;
use core\TEntry;

class PackageRecipientEntry implements IEntry
{
	use TEntry;

	protected static function initialize(): void
	{
		self::setTableName('pd_package_recipient');

		$given_name_field = new TextField();
		$given_name_field->setName('given_name');
		self::addField($given_name_field);

		$surnamme_field = new TextField();
		$surnamme_field->setName('surname');
		self::addField($surnamme_field);

		$expire_field = new DateTimeField();
		$expire_field->setName('expire');
		self::addField($expire_field);

		$product_key = new TextField();
		$product_key->setName('product_key');
		self::addField($product_key);
	}
}
