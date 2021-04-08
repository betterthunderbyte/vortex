<?php declare(strict_types=1); namespace controller\bob_update\entry;

use core\field\DateTimeField;
use core\field\TextField;
use core\IEntry;
use core\TEntry;
use core\Tool;

class CustomerEntry implements IEntry
{
    use TEntry;

    protected static function initialize(): void
    {
        self::setTableName('bu_customer');

        $name_field = new TextField();
        $name_field->setName('name');
        self::addField($name_field);

        $last_online_time = new DateTimeField();
        $last_online_time->setName('last_online_time');
        self::addField($last_online_time);

        $current_version = new TextField();
        $current_version->setName('version');
        self::addField($current_version);

        $product_key = new TextField();
        $product_key->setName('product_key');
        $product_key->setLength(127);
        $product_key->setUnique(true);
        self::addField($product_key);
    }

    public function setProductKey(string $product_key) : void
    {
        $this->__set('product_key', $product_key);
    }

    public function getProductKey() : string
    {
        return (string)$this->__get('product_key');
    }

    public function setName(string $name) : void
    {
        $this->__set('name', $name);
    }

    public function getName() : string
    {
        return (string)$this->__get('name');
    }

    public function setLastOnlineTime(int $time) : void
    {
        $this->__set('last_online_time', Tool::toMysqlDateTime($time));
    }

    public function getLastOnlineTime() : int
    {
        return Tool::toTimeFromMysqlDateTime((string)$this->__get('last_online_time'));
    }

    public function setVersion(string $version) : void
    {
        $this->__set('version', $version);
    }

    public function getVersion() : string
    {
        return (string)$this->__get('version');
    }
}
