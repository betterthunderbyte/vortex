<?php declare(strict_types=1);

use core\ControllerStatus;

\core\Register::registerController(new class extends \core\ControllerRegistry{
	protected function init(): void
	{
		$this->setAlias('bobupdate');
		$this->setClass(\controller\bob_update\BoBUpdateController::class);
		$this->setTitle( 'BoB Update Service');
		$this->setDescription('Stellt Updates bereit und empfängt Statusberichte.');

        $this->addEntry(\controller\bob_update\entry\ReleaseEntry::class);
        $this->addEntry(\controller\bob_update\entry\CustomerEntry::class);

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
					file_put_contents(FRAMEWORK_FILES_DIR . '/bob_update_install.txt', '');

					if(!file_exists(FRAMEWORK_FILES_DIR . '/bob_update_install.txt'))
					{
						return false;
					}

					return true;
				}

				public function Uninstall(): void
				{
					if(file_exists(FRAMEWORK_FILES_DIR . '/bob_update_install.txt'))
					{
						unlink(FRAMEWORK_FILES_DIR . '/bob_update_install.txt');
					}
				}
			}
		);

		if($this->getStatus() == ControllerStatus::INSTALLED)
		{
			\core\Register::registerModel(\controller\bob_update\model\ReleaseModel::class);
			\core\Register::registerModel(\controller\bob_update\model\CustomerModel::class);
		}
	}

    public function ableToInstall(): bool
    {
        return true;
    }

    public function isInstalled(): bool
	{
		if(file_exists(FRAMEWORK_FILES_DIR . '/bob_update_install.txt'))
		{
			return true;
		}

		return false;
	}
});

\core\Register::registerTemplateDirectory(FRAMEWORK_DIR . '/controller/bob_update/templates');


