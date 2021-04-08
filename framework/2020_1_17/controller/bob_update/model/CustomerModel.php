<?php declare(strict_types=1); namespace controller\bob_update\model;

use controller\bob_update\entry\CustomerEntry;
use core\IModel;
use core\IStatement;
use core\Register;
use core\TModel;

class CustomerModel implements IModel
{
    use TModel;

    /**
     * @var $select_all IStatement
     */
    private static $select_all;

    /**
     * @var $select_by_product_key IStatement
     */
    private static $select_by_product_key;

    public static function initialize(): void
    {
        $db = Register::getDBConnection();

        self::$select_all = $db->prepare('SELECT * FROM `' . CustomerEntry::getTableName() . '`');

        self::$select_by_product_key = $db->prepare('SELECT * FROM `' . CustomerEntry::getTableName() . '` WHERE `product_key` = ?');
    }

    public function selectAll() : void
    {
        if(self::$select_all->execute())
        {
            $this->setCurrentStatement(self::$select_all);
        }
    }

    public function selectByProductKey(string $product_key) : void
    {
        if(self::$select_by_product_key->execute(array($product_key)))
        {
            $this->setCurrentStatement(self::$select_by_product_key);
        }

    }
}