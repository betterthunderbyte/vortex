<?php declare(strict_types=1); namespace controller\bob_update\entry;

use controller\backend\TDescription;
use controller\backend\TTitle;
use core\field\BoolField;
use core\field\TextField;
use core\IEntry;
use core\TEntry;

class ReleaseEntry implements IEntry
{
    use TEntry {
        delete as deleteTrait;
    }
    use TTitle;
    use TDescription;

    public function setVersion(string $version) : void
    {
        $this->__set('version', $version);
    }

    public function getVersion() : string
    {
        return (string)$this->__get('version');
    }

    public function setPatch(bool $patch) : void
    {
        $this->__set('is_patch', (int)$patch);
    }

    public function getPatch() : bool
    {
        return (bool)$this->__get('is_patch');
    }

    public function delete(): void
    {
        $this->deleteTrait();
        if(file_exists($this->getBackendPath()))
        {
            unlink($this->getBackendPath());
        }

        if(file_exists($this->getFrontendPath()))
        {
            unlink($this->getFrontendPath());
        }
    }

    protected static function initialize(): void
    {
        self::setTableName('bu_release');

        $title_field = new TextField();
        $title_field->setName('title');
        self::addField($title_field);

        $description_field = new TextField();
        $description_field->setName('description');
        self::addField($description_field);

        $version_field = new TextField();
        $version_field->setName('version');
        $version_field->setLength(128);
        $version_field->setUnique(true);
        self::addField($version_field);

        $is_patch_field = new BoolField();
        $is_patch_field->setName('is_patch');
        self::addField($is_patch_field);

        $frontend_path = new TextField();
        $frontend_path->setName('frontend_path');
        self::addField($frontend_path);

        $frontend_hash = new TextField();
        $frontend_hash->setName('frontend_hash');
        self::addField($frontend_hash);

        $backend_path = new TextField();
        $backend_path->setName('backend_path');
        self::addField($backend_path);

        $backend_hash = new TextField();
        $backend_hash->setName('backend_hash');
        self::addField($backend_hash);
    }

    public function setFrontendHash(string $hash) : void
    {
        $this->__set('frontend_hash', $hash);
    }

    public function getFrontendHash() : string
    {
        return (string)$this->__get('frontend_hash');
    }

    public function setBackendHash(string $hash) : void
    {
        $this->__set('backend_hash', $hash);
    }

    public function getBackendHash() : string
    {
        return (string)$this->__get('backend_hash');
    }

    public function setBackendPath(string $path) : void
    {
        $this->__set('backend_path', $path);
    }

    public function getBackendPath() : string
    {
        return (string)$this->__get('backend_path');
    }

    public function setFrontendPath(string $path) : void
    {
        $this->__set('frontend_path', $path);
    }

    public function getFrontendPath() : string
    {
        return (string)$this->__get('frontend_path');
    }
}
