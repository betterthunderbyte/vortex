<?php declare(strict_types=1);
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


\core\Register::registerController(new class extends \core\ControllerRegistry{
	protected function init(): void
	{
        \core\Register::registerTemplateDirectory(FRAMEWORK_DIR . '/controller/package_distributor/templates');

		$this->setAlias('package_distributor');
		$this->setTitle('Softwarepaketverteiler');
		$this->setDescription('Verteilt Softwarepakete');
		$this->setVersion('0.0.1');
		$this->setClass(\controller\package_distributor\PackageDistributorController::class);


		$this->addEntry(\controller\package_distributor\entry\ApplicationEntry::class);
		$this->addEntry(\controller\package_distributor\entry\PackageEntry::class);
		$this->addEntry(\controller\package_distributor\entry\PackageRecipientEntry::class);
		$this->addEntry(\controller\package_distributor\entry\SystemPackageEntry::class);

		$this->addInstallPackage(
			new class extends \core\InstallPackage
			{
				public function returnTitle(): string
				{
					return 'Setze Status';
				}

				public function returnDescription(): string
				{
					return 'Setze Status das der Controller installiert wurde.';
				}

				public function Install(): bool
				{
					file_put_contents(FRAMEWORK_FILES_DIR . '/package_distributor_installed.txt', '');

					if(!file_exists(FRAMEWORK_FILES_DIR . '/package_distributor_installed.txt'))
					{
						return false;
					}

					return true;
				}

				public function Uninstall(): void
				{
					if(file_exists(FRAMEWORK_FILES_DIR . '/package_distributor_installed.txt'))
					{
						unlink(FRAMEWORK_FILES_DIR . '/package_distributor_installed.txt');
					}
				}
			}
		);
	}

	public function ableToInstall(): bool
	{
		return true;
	}

	public function getStatus(): int
	{
		if(file_exists(FRAMEWORK_FILES_DIR . '/package_distributor_installed.txt'))
		{
			return \core\ControllerStatus::INSTALLED;
		}

		return \core\ControllerStatus::UNINSTALLED;
	}
});