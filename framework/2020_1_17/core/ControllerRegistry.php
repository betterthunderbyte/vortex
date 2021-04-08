<?php declare(strict_types=1); namespace core;

use core\field\Field;

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

// ToDo(Thorben) Die möglichkeit eine JSON-Datei mit allen Redaktionellen Texten versehen die als Standardgelten

class ControllerStatus
{
    const INSTALLED = 1;
    const UPGRADE_REQUIRED = 2;
    const UNINSTALLED = 0;
}

class ControllerRegistry implements IAsArray
{
    private $alias;
    private $class;
    private $version;
    private $title;
    private $description;

    private $entries;

    private $post_init_called;

	/**
	 * @var InstallerPackageCollection
	 */
	private $installer_package_collection;

    public final function __construct()
	{
		$this->alias = '';
		$this->class = '';
		$this->version = '';
		$this->title = '';
		$this->description = '';

		$this->post_init_called = false;

		$this->entries = array();

		$this->installer_package_collection = new InstallerPackageCollection();

		$this->init();

		if(!isset($this->alias[0]))
		{
			throw new \Exception('Der Alias nicht angegeben!');
		}

		if(!isset($this->class[0]))
		{
			throw new \Exception('Die Klasse wurde nicht angegeben!');
		}

	}

	protected function init() : void
	{

	}

	protected function postInit() : void
    {

    }

    public function callPostInit() : void
    {
        if(!$this->post_init_called)
        {
            $this->postInit();
            $this->post_init_called = true;
        }
    }

	public final function addEntry(string $entry_class) : void
	{
		$this->entries[] = $entry_class;
	}

	public final function getEntries() : array
	{
		return $this->entries;
	}

    /**
     * In dieser Funktion prüft ob das System installiert wurde. Ansonsten überschreiben und selber implementieren.
     * @return int
     */
	public function getStatus() : int
	{
		return ControllerStatus::UNINSTALLED;
	}

	/**
	 * Diese Funktion kann überschrieben werden wenn dieser Controller installiert werden muss
	 * @return bool
	 */
	public function ableToInstall() : bool
	{
		return false;
	}

	public function upgradeFromBackup(array $backup_data) : bool
	{
		$this->uninstallTableStructure();
		$this->installTableStructure();



		return false;
	}

	/**
	 * Erstellt die Basisstruktur der Tabellen
	 * @return bool
	 */
	public function installTableStructure() : bool
	{
		$builder = new MariaDbBuilder();

		try
		{
			foreach ($this->entries as $entry_class)
			{
				$entry = new $entry_class();
				if($entry instanceof \core\IEntry)
				{
					if(!$builder->tableExists(DATABASE, $entry::getTableName()))
					{
						$builder->createTable(DATABASE, $entry::getTableName());
					}

					foreach ($entry::getFields() as $field)
					{
						if(!$builder->fieldExists(DATABASE, $entry::getTableName(), $field->getName()))
						{
							$builder->addField(DATABASE, $entry::getTableName(), $field);
						}
					}

					/*
					foreach ($builder->getAllFields(DATABASE, $entry::getTableName()) as $field_name)
					{
						if(!$entry::fieldExists($field_name) AND $field_name != $entry::getIDName())
						{
							$builder->dropField(DATABASE, $entry::getTableName(), $field_name);
						}
					}*/
				}
			}

			return true;
		}
		catch (\Exception $exception)
		{


		}

		return false;
	}

	/**
	 * Löscht alle Tabellen
	 */
	public function uninstallTableStructure() : void
	{
		$builder = new MariaDbBuilder();

		try
		{
			foreach ($this->entries as $entry_class)
			{
				$entry = new $entry_class();
				if($entry instanceof \core\IEntry)
				{
					if($builder->tableExists(DATABASE, $entry::getTableName()))
					{
						$builder->dropTable(DATABASE, $entry::getTableName());
					}

					/*
										foreach ($entry::getFields() as $field)
										{
											if(!$builder->fieldExists(DATABASE, $entry::getTableName(), $field->getName()))
											{
												$builder->addField(DATABASE, $entry::getTableName(), $field);
											}
										}


										foreach ($builder->getAllFields(DATABASE, $entry::getTableName()) as $field_name)
										{
											if(!$entry::fieldExists($field_name) AND $field_name != $entry::getIDName())
											{
												$builder->dropField(DATABASE, $entry::getTableName(), $field_name);
											}
										}*/
				}
			}
		}
		catch (\Exception $exception)
		{


		}
	}

	/**
	 * Aktualisiert die Tabellenstrukture (Nur im Debugmodus)
	 */
	public function upgradeTableStructure() : void
	{
		$builder = new MariaDbBuilder();

		try
		{
			foreach ($this->entries as $entry_class)
			{
				$entry = new $entry_class();
				if($entry instanceof \core\IEntry)
				{
					if(!$builder->tableExists(DATABASE, $entry::getTableName()))
					{
						$builder->createTable(DATABASE, $entry::getTableName());
					}

					foreach ($entry::getFields() as $field)
					{
						if(!$builder->fieldExists(DATABASE, $entry::getTableName(), $field->getName()))
						{
							$builder->addField(DATABASE, $entry::getTableName(), $field);
						}
					}

					foreach ($builder->getAllFields(DATABASE, $entry::getTableName()) as $field_name)
					{
						if(!$entry::fieldExists($field_name) AND $field_name != $entry::getIDName())
						{
							$builder->dropField(DATABASE, $entry::getTableName(), $field_name);
						}
					}
				}
			}
		}
		catch (\Exception $exception)
		{


		}
	}

	/**
	 * Erstellt ein Backup von den Daten und die Struktur
	 */
	public function backupTableData() : array
	{
		$connection = Register::getDBConnection();

		$entry_backups = array();

		try
		{
			foreach ($this->entries as $entry_class)
			{
				$entry = new $entry_class();
				if($entry instanceof IEntry)
				{
					$entry_backup = new EntryBackup();
					$entry_backup->setEntryClass($entry_class);
					$entry_backup->setEntryIdName($entry::getIDName());
					$entry_backup->setEntryTableName($entry::getTableName());
					$entry_backup->setVersion(Tool::getVersion());

					$fields = array();

					foreach ($entry::getFields() as $field)
					{
						if($field instanceof Field)
						{
							$fields[] = $field->asArray();
						}
					}

					$entry_backup->setFields($fields);

					$data = array();

					$statement = $connection->prepare('SELECT * FROM `' . $entry::getTableName() . '`');

					if($statement->execute() AND $statement->rowCount() > 0)
					{
						while ($row = $statement->fetchAllAssoc())
						{
							$data[] = $row;
						}
					}

					$entry_backup->setData($data);

					$entry_backups[$entry_backup->getEntryTableName()] = $entry_backup;
				}
			}
		}
		catch (\Exception $exception)
		{


		}

		return $entry_backups;
	}



	/**
	 * Diese Funktion soll die notwendigen Daten erstellen, dies sollte für den Zweck überschrieben werden
	 */
	public function setup() : void
	{

	}

	public final function getAlias() : string
    {
        return $this->alias;
    }

    public final function getClass() : string
    {
        return $this->class;
    }

    public final function getTitle() : string
    {
        return $this->title;
    }

    public final function getDescription() : string
    {
        return $this->description;
    }

    protected final function setAlias(string $alias) : void
	{
		$this->alias = $alias;
	}

	protected final function setClass(string $class) : void
	{
		$this->class = $class;
	}

	protected final function setTitle(string $title) : void
	{
		$this->title = $title;
	}

	protected final function setDescription(string $description) : void
	{
		$this->description = $description;
	}

	public final function getVersion() : string
	{
		return $this->version;
	}

	protected final function setVersion(string $version) : void
	{
		$this->version = $version;
	}

	protected final function addInstallPackage(IInstallPackage $install_package) : void
	{
		$this->installer_package_collection->add($install_package);
	}

	public final function getInstallerPackageCollection() : InstallerPackageCollection
	{
		return $this->installer_package_collection;
	}

    public function asArray() : array
	{
		$install_steps = array();

		foreach ($this->installer_package_collection as $install_package)
		{
			$install_steps[] = array('title' => $install_package->getTitle(), 'description' => $install_package->getDescription());
		}

		return array('alias' => $this->alias, 'class' => $this->class, 'version' => $this->version, 'title' => $this->title, 'description' => $this->description, 'is_installed' => $this->getStatus(), 'install_steps' => $install_steps);
	}
}
