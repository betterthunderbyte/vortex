<?php declare(strict_types=1); namespace controller\backend\model;

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

use controller\backend\entry\MemberEntry;
use controller\backend\entry\PermissionEntry;
use controller\backend\entry\SessionEntry;
use core\Config;
use core\exception\EntryValueAlreadyExistsException;
use core\IModel;
use core\TModel;
use core\Tool;

class MemberSessionModel implements IModel
{
    use TModel;

	const SAVE_DIRECTORY = FRAMEWORK_FILES_DIR . '/master_login.json';

	private $expire;
	private $key;

    private $is_logged_in;
    private $member_entry;
    private $session_entry;

    public function __construct()
    {
        $this->is_logged_in = false;
        $this->member_entry = null;
        $this->session_entry = null;

		$this->expire = 0;
		$this->key = '';
    }

    public function getSessionEntry() : ?SessionEntry
    {
        return $this->session_entry;
    }

    public function getMemberEntry() : ?MemberEntry
    {
        return $this->member_entry;
    }

    public function withPermission(string $alias) : bool
    {
        if($this->member_entry instanceof MemberEntry)
        {
            if($this->member_entry->isSuperAdmin())
            {
                return true;
            }
        }

        $member_permission_model = new MemberPermissionModel();
        $member_permission_model->selectMemberWithPermission($this->member_entry->getID(), $alias);
        $entry = $member_permission_model->getEntry(PermissionEntry::class);

        if($entry instanceof PermissionEntry)
		{
			return true;
		}

        return false;
    }

    public function loginWithKey(string $key) : void
    {
        $session_model = new SessionModel();
        $session_model->selectByKey($key);
        $session_entry = $session_model->getEntry(SessionEntry::class);
        if($session_entry instanceof SessionEntry)
        {
            if($session_entry->getExpire() < time())
            {
                return;
            }

            $member_model = new MemberModel();
            $member_model->selectMemberById($session_entry->getMemberFk());
            $this->member_entry = $member_model->getEntry(MemberEntry::class);
            if($this->member_entry instanceof MemberEntry)
            {
                $session_entry->setExpire(time() + 3600);
                $session_entry->save();
                $this->is_logged_in = true;
                $this->session_entry = $session_entry;
            }
        }
    }

    public function loginWithEmailPassword(string $email, string $password) : void
    {
        $member_model = new MemberModel();
        $member_model->selectMemberByEmail($email);
        $this->member_entry = $member_model->getEntry(MemberEntry::class);
        if($this->member_entry instanceof MemberEntry)
        {
            if($this->member_entry->isPasswordValid($password))
            {


                $session_model = new SessionModel();
                $session_model->invalidateAllFromMember($this->member_entry->getID());

                $session = new SessionEntry();
                $session->setData([]);
                $session->setExpire(time() + 3600);
                $session->setMemberFk($this->member_entry->getID());

                do
                {
                    try
                    {
                        $session->setKey(Tool::createRandomHash());
                        $session->save();
                    }
                    catch (EntryValueAlreadyExistsException $e)
                    {
                        $session->setKey(Tool::createRandomHash());
                    }

                } while($session->getID() === 0);

                $this->is_logged_in = true;
                $this->session_entry  = $session;
            }
        }
    }

    public function logout() : void
    {
        if($this->session_entry instanceof SessionEntry)
        {
            $this->session_entry->delete();
        }
    }

    public function isLoggedIn() : bool
    {
        return $this->is_logged_in;
    }

    public static function initialize(): void
    {
        // TODO: Implement initialize() method.
    }

	public function getKey() : string
	{
		return $this->key;
	}

	public function loginWithMasterKey(string $key) : void
	{
	    if(!file_exists(self::SAVE_DIRECTORY))
        {
            return;
        }

		$data = Tool::jsonDecode(file_get_contents(self::SAVE_DIRECTORY));

		if($data['key'] != $key)
		{
			return;
		}

		if($data['timeout'] <= time())
		{
			return;
		}

		$this->expire = time() + 3600;
		$this->key = $data['key'];
		$this->is_logged_in = true;

		$this->save();

		$this->member_entry = new MemberEntry();
		$this->member_entry->setGivenName('Super');
		$this->member_entry->setSurname('Admin');
		$this->member_entry->setEMail('super@admin.de');
		$this->member_entry->setActive(true);
		$this->member_entry->setAdmin(true);
		$this->member_entry->setRenewPassword(false);

		$this->session_entry = new SessionEntry();
		$this->session_entry->setMemberFk(0);
		$this->session_entry->setKey($this->key);
		$this->session_entry->setExpire($this->expire);
	}

	public function loginWithMasterPassword(string $master_password) : void
	{
		if(!Config::isLoaded())
		{
			Config::load();
		}

		if(Tool::verifyPassword($master_password, Config::getMasterPassword()))
		{
			$this->is_logged_in = true;
			$this->key = Tool::createRandomHash();
			$this->expire = time() + 3600;

			$this->save();

			$this->member_entry = new MemberEntry();
			$this->member_entry->setGivenName('Super');
			$this->member_entry->setSurname('Admin');
			$this->member_entry->setEMail('super@admin.de');
			$this->member_entry->setActive(true);
			$this->member_entry->setAdmin(true);
			$this->member_entry->setRenewPassword(false);

			$this->session_entry = new SessionEntry();
			$this->session_entry->setMemberFk(0);
			$this->session_entry->setKey($this->key);
			$this->session_entry->setExpire($this->expire);
		}
	}

	private function save() : void
	{
		file_put_contents(self::SAVE_DIRECTORY, Tool::jsonEncode(array('key' => $this->key, 'timeout' => $this->expire)));
	}
}