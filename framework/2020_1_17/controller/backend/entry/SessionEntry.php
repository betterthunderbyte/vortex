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

use core\field\CreateTimestampField;
use core\field\DateTimeField;
use core\field\ForeignField;
use core\field\TextField;
use core\field\TimestampField;
use core\IEntry;
use core\TEntry;
use core\Tool;

class SessionEntry implements IEntry
{
    use TEntry;

    protected static function initialize(): void
    {
        self::setTableName('be_session');

        $update_field = new TimestampField();
        $update_field->setName('update_time');
        self::addField($update_field);

        $create_time_field = new CreateTimestampField();
        $create_time_field->setName('create_time');
        self::addField($create_time_field);

        $key_field = new TextField();
        $key_field->setName('key');
        $key_field->setUnique(true);
        $key_field->setLength(255);
        self::addField($key_field);

        $data_field = new TextField();
        $data_field->setName('data');
        self::addField($data_field);

        $expire_time = new DateTimeField();
        $expire_time->setName('expire');
        self::addField($expire_time);

        $member_fk = new ForeignField();
        $member_fk->setName('member_fk');
        self::addField($member_fk);
    }

    public function getUpdateTime() : int
    {
        return Tool::toTimeFromMysqlDateTime((string)$this->__get('update_time'));
    }

    public function setData(array $data) : void
    {
        $this->__set('data', Tool::jsonEncode($data));
    }

    public function getData() : array
    {
        return Tool::jsonDecode((string)$this->__get('data'));
    }

    public function setKey(string $key) : void
    {
        $this->__set('key', $key);
    }

    public function getKey() : string
    {
        return (string)$this->__get('key');
    }

    public function setExpire(int $time) : void
    {
        $this->__set('expire', Tool::toMysqlDateTime($time));
    }

    public function getExpire() : int
    {
        return Tool::toTimeFromMysqlDateTime((string)$this->__get('expire'));
    }

    public function setMemberFk(int $id) : void
    {
        $this->__set('member_fk', $id);
    }

    public function getMemberFk() : int
    {
        return (int)$this->__get('member_fk');
    }
}
