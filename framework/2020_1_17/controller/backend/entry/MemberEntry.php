<?php declare(strict_types=1); namespace controller\backend\entry;
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

use core\field\BoolField;
use core\field\ForeignField;
use core\IEntry;
use core\TEntry;
use core\field\TextField;
use core\Tool;

class MemberEntry implements IEntry
{
    use TEntry;

    protected static function initialize(): void
    {
        self::setTableName('be_member');
        self::setPreviousTableName('be_member');

        $password_field = new TextField();
        $password_field->setName('password');
        self::addField(
            $password_field,
            'setPassword',
            'getPassword'
            );

        $given_name_field = new TextField();
        $given_name_field->setName('given_name');
        self::addField($given_name_field);

        $surname_field = new TextField();
        $surname_field->setName('surname');
        self::addField($surname_field);

        $email_field = new TextField();
        $email_field->setName('email');
        $email_field->setUnique(true);
        $email_field->setLength(512);
        self::addField($email_field);

        $active_field = new BoolField();
        $active_field->setName('active');
        self::addField($active_field);

        $group_fk_field = new ForeignField();
        $group_fk_field->setName('group_fk');
        self::addField($group_fk_field);

        $data_field = new TextField();
        $data_field->setName('data');
        self::addField($data_field);

        $admin_field = new BoolField();
        $admin_field->setName('admin');
        self::addField($admin_field);

        $renew_password_field = new BoolField();
        $renew_password_field->setName('renew_password');
        self::addField($renew_password_field);
    }

    public function setRenewPassword(bool $state) : void
    {
        $this->__set('renew_password', (int)$state);
    }

    public function getRenewPassword() : bool
    {
        return (bool)$this->__get('renew_password');
    }

    public function setAdmin(bool $state) : void
    {
        $this->__set('admin', (int)$state);
    }

    public function isSuperAdmin() : bool
    {
        return (bool)$this->__get('admin');
    }

    public function setData(array $data) : void
    {
        $this->__set('data', Tool::jsonEncode($data));
    }

    public function getData() : array
    {
        return Tool::jsonDecode((string)$this->__get('data'));
    }

    public function setGroupFk(int $id) : void
    {
        $this->__set('group_fk', $id);
    }

    public function getGroupFk() : int
    {
        return (int)$this->__get('group_fk');
    }

    public function setActive(bool $state) : void
    {
        $this->__set('active', $state);
    }

    public function isActive() : bool
    {
        return (bool)$this->__get('active');
    }

    public function setEMail(string $email) : void
    {
        $this->__set('email', $email);
    }

    public function getEmail() : string
    {
        return (string)$this->__get('email');
    }

    /**
     * Der Vorname
     * @param string $name
     */
    public function setGivenName(string $name) : void
    {
        $this->__set('given_name', $name);
    }

    public function getGivenName() : string
    {
        return (string)$this->__get('given_name');
    }

    /**
     * Der Nachname
     * @param string $surname
     */
    public function setSurname(string $surname) : void
    {
        $this->__set('surname', $surname);
    }

    public function getSurname() : string
    {
        return (string)$this->__get('surname');
    }

    public function getPassword() : string
    {
        return (string)$this->__get('password');
    }

    public function setPassword(string $password) : void
    {
        $this->__set('password', Tool::hashPassword($password));
    }

    public function isPasswordValid(string $password) : bool
    {
        return Tool::verifyPassword($password, $this->getPassword());
    }
}