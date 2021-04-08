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

class InstallerPackageCollection implements \Iterator
{
	private $install_packages;
	private $count;
	private $position;

	public function __construct()
	{
		$this->install_packages = array();
		$this->count = 0;
		$this->position = 0;
	}

	public function add(IInstallPackage $install_package) : void
	{
		$this->install_packages[] = $install_package;
		$this->count += 1;
	}

	public function at(int $index) : ?IInstallPackage
	{
		if(isset($this->install_packages[$index]))
		{
			return $this->install_packages[$index];
		}

		return null;
	}

	public function getCount() : int
	{
		return $this->count;
	}

	/**
	 * @inheritDoc
	 */
	public function current() : IInstallPackage
	{
		return $this->install_packages[$this->position];
	}

	/**
	 * @inheritDoc
	 */
	public function next() : void
	{
		++$this->position;
	}

	/**
	 * @inheritDoc
	 */
	public function key() : int
	{
		return $this->position;
	}

	/**
	 * @inheritDoc
	 */
	public function valid() : bool
	{
		return isset($this->routes[$this->position]);
	}

	/**
	 * @inheritDoc
	 */
	public function rewind() : void
	{
		$this->position = 0;
	}
}