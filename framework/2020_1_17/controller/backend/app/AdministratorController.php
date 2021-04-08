<?php declare(strict_types=1); namespace controller\backend\app;

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

use controller\backend\BackendController;
use controller\backend\entry\GroupEntry;
use controller\backend\entry\GroupPermissionEntry;
use controller\backend\entry\MemberEntry;
use controller\backend\entry\PermissionEntry;
use controller\backend\IBackendApp;
use controller\backend\IBackendMenu;
use controller\backend\model\GroupModel;
use controller\backend\model\GroupPermissionModel;
use controller\backend\model\MemberModel;
use controller\backend\model\PermissionModel;
use core\ControllerStatus;
use core\Register;
use core\Route;
use core\RouteCollection;
use core\Tool;

class AdministratorController implements IBackendApp, IBackendMenu
{
    public static function returnBackendMenu(): array
    {
		if(Register::getControllerRegistry('backend')->getStatus() === ControllerStatus::UNINSTALLED)
		{
			return array();
		}

		if(!BackendController::getMemberSessionModel()->withPermission('member-management') AND
		!BackendController::getMemberSessionModel()->withPermission('permission-assignment'))
		{
			return array();
		}

		$menu = array(
			'items' => array()
		);

		if(BackendController::getMemberSessionModel()->withPermission('member-management'))
		{
			$menu['items'][] = array(
				'label' => 'Mitglieder',
				'link' => '/backend/app/admin/members'
			);
		}

		if(BackendController::getMemberSessionModel()->withPermission('permission-assignment'))
		{
			$menu['items'][] = array(
				'label' => 'Gruppen',
				'link' => '/backend/app/admin/groups'
			);

		/*
			$menu['items'][] = array(
				'label' => 'Einstellungen',
				'link' => '/backend/app/admin/configuration'
			);*/
		}

		$menu['label'] = 'Administration';

		return $menu;
    }

    public static function handleRoutes(): RouteCollection
    {
        $route_collection = new RouteCollection();

		if(BackendController::getMemberSessionModel()->withPermission('member-management'))
		{
			$route_collection->add(Route::get('/members', function (){
				AdministratorController::memberViewRequest();
			}));

            $route_collection->add(Route::delete('/api/member/[i:id]', function (int $id){
				AdministratorController::deleteMemberRequest($id);
            }));

            $route_collection->add(Route::post('/api/member', function (){
				AdministratorController::createMemberRequest();
            }));
		}

		if(!BackendController::getMemberSessionModel()->withPermission('permission-assignment'))
		{
			return $route_collection;
		}

        $route_collection->add(Route::get( '/groups', function (){
        	// ToDo(Thorben) Gruppen schnell Bearbeitung implementieren
			AdministratorController::groupViewRequest();
        }));

        $route_collection->add(Route::get( '/configuration', function (){
			AdministratorController::configurationViewRequest();
        }));

        $route_collection->add(Route::post( '/api/group', function (){
			AdministratorController::postInsertGroupRequest();
        }));

		$route_collection->add(Route::delete( '/api/group/[i:id]', function (int $id){
			AdministratorController::deleteGroup($id);
		}));

        $route_collection->add(Route::get( '/groups/edit/[i:id]', function (int $id) {
			AdministratorController::groupEditRequest($id);
        }));

        $route_collection->add(Route::get( '/api/permissions/[i:group_id]/[i:count]/[i:page]', function (int $id, int $count, int $page){
			AdministratorController::getPermissionCountFromGroupRequest($id, $count, $page);
        }));

		$route_collection->add(Route::get('/api/members/[i:count]/[i:page]', function (int $count, int $page){
			AdministratorController::selectMemberLimited($count, $page);
		}));

		$route_collection->add(Route::post('/api/group/[i:group_id]', function (int $group_id){
			$data = Tool::jsonDecode(Tool::getInputData());

			if(!isset($data['permission_id']))
			{
				Tool::headerBadRequest();
				return;
			}

			$group_permission_model = new GroupPermissionModel();
			$group_permission_model->selectGroupPermissionByGroupAndPermission($group_id, $data['permission_id']);

			$entry = $group_permission_model->getEntry(GroupPermissionEntry::class);

			if($entry instanceof GroupPermissionEntry)
			{
				Tool::headerOK();
				return;
			}

			$entry = new GroupPermissionEntry();
			$entry->setGroupFk($group_id);
			$entry->setPermissionFk($data['permission_id']);
			$entry->save();

			Tool::headerOK();

		}));

		$route_collection->add(Route::delete( '/api/group/[i:group_id]/[i:permission_id]', function (int $group_id, int $permission_id){
			$group_permission_model = new GroupPermissionModel();
			$count = $group_permission_model->deleteGroupPermissionByGroupAndPermission($group_id, $permission_id);
			Tool::headerOK();
		}));

		$route_collection->add(Route::post('/api/member/[i:member_id]', function (int $member_id){
			$data = Tool::jsonDecode(Tool::getInputData());

			if(!isset($data['group_id']))
			{
				Tool::headerBadRequest();
				return;
			}

			$member_entry = MemberEntry::selectByID($member_id);

			if($member_entry instanceof MemberEntry)
			{
				// ToDo Prüfen ob die Gruppe existiert
				$member_entry->setGroupFk($data['group_id']);
				$member_entry->save();

				Tool::headerOK();
				return;
			}

			Tool::headerBadRequest();
		}));

        return $route_collection;
    }

    public static function groupEditRequest(int $group_id) : void
    {
        $group_model = new GroupModel();
        $group_model->selectById($group_id);
        $group = $group_model->getEntry(GroupEntry::class);

        $permission_model_with_group = new PermissionModel();
        $permission_model_with_group->selectAllFromGroup($group_id, 10, 0);
        $permissions = $permission_model_with_group->getRows();

        $member_model = new MemberModel();
        $member_model->selectMembersLimited(10, 1);
		$members = $member_model->getRows();

        $backend_group_edit = Register::getTemplateSystem()::render('backend_group_edit.vue', array(
            'group' => $group->getRawValues(),
            'permissions' => $permissions,
            'permission_count' => PermissionEntry::getCount(),
            'members' => $members,
			'member_count' => MemberEntry::getCount()
        ));
        echo BackendController::renderBackendStructure('Backend - Gruppe bearbeiten', $backend_group_edit);
    }

    public static function getPermissionCountFromGroupRequest(int $group_id, int $count, int $page) : void
    {
        $permission_model = new PermissionModel();
        $permission_model->selectAllFromGroup($group_id, $count, $page * $count - $count);
        $data = $permission_model->getRows();

        Tool::returnJsonData(array('permissions' => $data, 'max_count' => PermissionEntry::getCount()));
    }

    public static function selectMemberLimited(int $count, int $page) : void
	{
		$member_model = new MemberModel();
		$member_model->selectMembersLimited($count, $page);

		Tool::headerOK();
		Tool::returnJsonData(array('members' => $member_model->getRows(), 'max_count' => MemberEntry::getCount()));
	}

    public static function deleteGroup(int $id) : void
	{
		$group_model = new GroupModel();
		$group_model->selectById($id);

		$entry = $group_model->getEntry(GroupEntry::class);
		if($entry == null)
		{
			Tool::returnJsonError(403, 'Die Gruppe existiert nicht mehr!');
			return;
		}

		$entry->delete();
		Tool::headerOK();
	}

    public static function postInsertGroupRequest() : void
    {
        $data = Tool::getInputData();
        if(!isset($data{0}))
        {
            Tool::headerBadRequest();
            return;
        }

        $data = Tool::jsonDecode($data);
        if(!isset($data['alias']))
        {
            Tool::headerBadRequest();
            Tool::returnJsonData(array('message' => 'Der Alias fehlt!'));
            return;
        }

        if(!isset($data['title']))
        {
            Tool::headerBadRequest();
            Tool::returnJsonData(array('message' => 'Der Titel fehlt!'));
            return;
        }

        $group_entry = new GroupEntry();
        $group_entry->setAlias($data['alias']);
        $group_entry->setTitle($data['title']);
        if(isset($data['description']))
        {
            $group_entry->setDescription($data['description']);
        }

        try
        {
            $group_entry->save();
            Tool::headerOK();
            Tool::returnJsonData($group_entry->getRawValues());
            return;
        }
        catch (\Exception $e)
        {
            Tool::headerBadRequest();
            Tool::returnJsonData(array('message' => $e->getMessage()));
        }
    }

    public static function memberViewRequest() : void
    {
        $member_model = new MemberModel();
        $member_model->selectAll();

        $member_view = Register::getTemplateSystem()::render('backend_member_view.vue', array('members' => $member_model->getRows()));
        echo BackendController::renderBackendStructure('Administration - Mitglieder', $member_view);
    }

    public static function createMemberRequest() : void
	{
		// ToDo(Thorben) Mitglieder löschen funktion einbauen

		$data = Tool::getInputData();
		if(!isset($data{0}))
		{
			Tool::headerBadRequest();
			return;
		}

		$data = Tool::jsonDecode($data);

		if(!isset($data['email']))
		{
			Tool::returnJsonError(403, 'Die E-Mail Adresse fehlt!');
			return;
		}

		if(strlen($data['email']) < 3)
		{
			Tool::returnJsonError(403, 'Die E-Mail ist zu kurz!');
			return;
		}

		if(!isset($data['password']))
		{
			Tool::returnJsonError(403, 'Das Passwort fehlt!');
			return;
		}

		if(strlen($data['password']) < 6)
		{
			Tool::returnJsonError(403, 'Das Passwort ist zu kurz!');
			return;
		}

		$member_model = new MemberModel();
		if($member_model->emailExists($data['email']))
		{
			Tool::returnJsonError(403, 'Die E-Mail Adresse wird schon verwendet!');
			return;
		}

		$member_entry = new MemberEntry();
		$member_entry->setEMail($data['email']);
		$member_entry->setPassword($data['password']);
		$member_entry->setData(array());

		if(isset($data['given_name']))
		{
			$member_entry->setGivenName($data['given_name']);
		}
		else
		{
			$member_entry->setGivenName('');
		}

		if(isset($data['surname']))
		{
			$member_entry->setSurname($data['surname']);
		}
		else
		{
			$member_entry->setSurname('');
		}

		$member_entry->setGroupFk(0);
		$member_entry->setActive(true);
		if(isset($data['renew_password']) AND is_bool($data['renew_password']))
		{
			$member_entry->setRenewPassword($data['renew_password']);
		}
		else
		{
			$member_entry->setRenewPassword(false);
		}


		$member_entry->setAdmin(false);

		try
		{
			$member_entry->save();
			Tool::headerOK();
			Tool::returnJsonData($member_entry->getRawValues());
			return;
		}
		catch (\Exception $e)
		{
			Tool::headerBadRequest();
			Tool::returnJsonData(array('message' => $e->getMessage()));
		}
	}

    public static function deleteMemberRequest(int $id) : void
	{
		$member_model = new MemberModel();
		$member_model->selectMemberById($id);
		$member_entry = $member_model->getEntry(MemberEntry::class);

		$current_member = BackendController::getMemberSessionModel()->getMemberEntry();

		if($current_member instanceof MemberEntry)
		{
			if($member_entry instanceof MemberEntry)
			{
				if($member_entry->isSuperAdmin() && !$current_member->isSuperAdmin())
				{
					Tool::returnJsonError(403, 'Sie haben nicht genügend Rechte!');
					return;
				}

				if($member_entry->getID() == $current_member->getID())
				{
					Tool::returnJsonError(403, 'Sie können sich nicht selbst löschen!');
					return;
				}

				$member_entry->delete();
				Tool::headerOK();
				return;
			}

		}


		Tool::headerBadRequest();

	}

    public static function groupViewRequest() : void
    {
        $group_model = new GroupModel();
        $group_model->selectAllGroups();

        $group_view = Register::getTemplateSystem()::render('backend_group_view.vue', array('groups' => $group_model->getRows()));
        echo BackendController::renderBackendStructure('Administration - Gruppen', $group_view);
    }

    public static function configurationViewRequest() : void
    {
        $setting_view = Register::getTemplateSystem()::render('backend_admin_configuration_view.vue', array());
        echo BackendController::renderBackendStructure('Administration - Einstellungen', $setting_view);
    }
}
