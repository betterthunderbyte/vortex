<?php declare(strict_types=1); namespace core;
/**
 * MIT License
 *
 * Copyright (c) 2019 jeamu
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
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
 *
 */

use PDO;
use PDOException;

class Connection implements IConnection
{

    /**
     * @var $pdo PDO
     */
    private $pdo;

    private $is_connected;

    public function __construct()
    {
        $this->is_connected = false;
    }

    public function get_pdo() : ?PDO
	{
		return $this->pdo;
	}

    public function connect(string $host, string $user, string $password) : void
    {
        $dsn = 'mysql:host=' . $host . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        try
        {
            $this->pdo = new PDO(
                $dsn,
                $user,
                $password,
                $options
            );

            $this->is_connected = true;
        }
        catch (PDOException $e)
        {
        }
    }

    public function connectWithDatabase(string $host, string $user, string $password, string $database) : void
    {
        $dsn = 'mysql:host=' . $host . ';dbname=' . $database . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        try
        {
            $this->pdo = new PDO(
                $dsn,
                $user,
                $password,
                $options
            );

            $this->is_connected = true;
        }
        catch (PDOException $e)
        {

        }
    }

    public function prepare(string $sql) : IStatement
    {
        if(!$this->is_connected)
        {
            // ToDo(Thorben) ordentliche Fehlermeldung
            throw new \Exception();
        }

        $statement = new \core\Statement();
        $statement->set_pdo($this->pdo);

        try
        {
            $pdo_statement = $this->pdo->prepare($sql);
            if($pdo_statement instanceof \PDOStatement)
            {
                $statement->set_pdo_statement($pdo_statement);
            }
        }
        catch (PDOException $e)
        {
            Logger::Error($e->getMessage(), ' Code ', (string)$e->getCode());
        }

        return $statement;
    }
}