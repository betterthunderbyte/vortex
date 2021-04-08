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

class Installer
{
	private $errors;

	public function __construct()
	{
		$this->errors = array();
	}

	public function installController(ControllerRegistry $controller_registry) : void
	{
		if(!$controller_registry->installTableStructure())
		{
			$controller_registry->uninstallTableStructure();
			$this->errors[] = 'Es konnte nicht die Tabellenstruktur vom Controller ' . $controller_registry->getAlias() . ' ' . $controller_registry->getTitle() . ' erstellt werden!';
			return;
		}

		$controller_registry->setup();

		$this->install($controller_registry->getInstallerPackageCollection());
	}

	public function install(InstallerPackageCollection $collection) : void
	{
		$roll_back = false;
		$step = 0;

		for($i = 0; $i < $collection->getCount(); ++$i)
		{
			$installer_package = $collection->at($i);

			try
			{
				$installer_package->install();
			}
			catch (\Exception $exception)
			{
				$step = $i;
				$roll_back = true;
				$this->errors[] = 'Bei der Installation vom Packet: ' . $installer_package->getTitle() . ' mit der Beschreibung ' . $installer_package->getDescription() . ' ist folgender Fehler entstanden! Exception: ' . $exception->getMessage();
				break;
			}
		}

		if($roll_back)
		{
			for($i = $step; $i >= 0; --$i)
			{
				$installer_package = $collection->at($i);

				try
				{
					$installer_package->Uninstall();
				}
				catch (\Exception $exception)
				{
					$this->errors[] = 'Bei der Deinstallation vom Packet: ' . $installer_package->getTitle() . ' mit der Beschreibung ' . $installer_package->getDescription() . ' ist folgender Fehler entstanden! Exception: ' . $exception->getMessage();
					return;
				}
			}
		}
	}

	public function uninstallController(ControllerRegistry $controller_registry) : void
	{
		$controller_registry->uninstallTableStructure();
		$this->uninstall($controller_registry->getInstallerPackageCollection());
	}

	public function uninstall(InstallerPackageCollection $collection) : void
	{
		for($i = $collection->getCount(); $i > 0; --$i)
		{
			$installer_package = $collection->at($i - 1);

			try
			{
				$installer_package->uninstall();
			}
			catch (\Exception $exception)
			{

				$this->errors[] = 'Bei der Deinstallation vom Packet: ' . $installer_package->getTitle() . ' mit der Beschreibung ' . $installer_package->getDescription() . ' ist folgender Fehler entstanden! Exception: ' . $exception->getMessage();
				return;
			}
		}
	}

	public function getErrorMessages() : array
	{
		return $this->errors;
	}

	public function isSuccessful() : bool
	{
		return !isset($this->errors[0]);
	}
}
