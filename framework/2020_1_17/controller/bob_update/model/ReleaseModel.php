<?php declare(strict_types=1); namespace controller\bob_update\model;

use controller\bob_update\entry\ReleaseEntry;
use core\IModel;
use core\IStatement;
use core\Register;
use core\TModel;

class ReleaseModel implements IModel
{
    use TModel;

    /**
     * @var $select_all IStatement
     */
    private static $select_all;

    /**
     * @var $select_all_without_path IStatement
     */
    private static $select_all_without_path;

    public static function initialize(): void
    {
        $db = Register::getDBConnection();
        self::$select_all = $db->prepare('SELECT * FROM `' . ReleaseEntry::getTableName() . '`');
        self::$select_all_without_path = $db->prepare(
        'SELECT `' . ReleaseEntry::getIDName() . '`, `title`, `description`, `version`, `is_patch`, `frontend_hash`, `backend_hash` FROM `' .
        ReleaseEntry::getTableName() . '`'
        );
    }

    public function selectAll() : void
    {
        self::$select_all->execute();
        $this->setCurrentStatement(self::$select_all);
    }

    public function selectAllWithoutPath() : void
    {
        self::$select_all_without_path->execute();
        $this->setCurrentStatement(self::$select_all_without_path);
    }

}
