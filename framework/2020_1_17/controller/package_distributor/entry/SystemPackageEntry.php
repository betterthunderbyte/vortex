<?php declare(strict_types=1); namespace controller\package_distributor\entry;
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

class SystemPackageEntry implements IEntry
{
	use TEntry;
	use TTitle;
	use TDescription;

	protected static function initialize(): void
	{
		self::setTableName('pd_system_package');

		$version_field = new TextField();
		$version_field->setName('version');
		self::addField($version_field);

		$title_text_field = new TextField();
		$title_text_field->setName('title');
		self::addField($title_text_field);

		$description_text_field = new TextField();
		$description_text_field->setName('description');
		self::addField($description_text_field);

		$package_path_text_field = new TextField();
		$package_path_text_field->setName('directory_path');
		self::addField($package_path_text_field);
	}

	public function setVersion(string $version) : void
	{
		$this->__set('version', $version);
	}

	public function getVersion() : string
	{
		return (string)$this->__get('version');
	}

	public function setDirectoryPath(string $directory) : void
	{
		if(!file_exists($directory))
		{
			throw new \Exception();
		}

		$this->__set('directory_path', $directory);
	}

	public function getDirectoryPath() : string
	{
		return (string)$this->__get('directory_path');
	}
}
