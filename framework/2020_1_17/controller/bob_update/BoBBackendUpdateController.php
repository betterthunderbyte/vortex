<?php declare(strict_types=1); namespace controller\bob_update;

use controller\backend\BackendController;
use controller\backend\IBackendApp;
use controller\backend\IBackendMenu;
use controller\bob_update\entry\ReleaseEntry;
use controller\bob_update\model\CustomerModel;
use controller\bob_update\model\ReleaseModel;
use core\ControllerRegistry;
use core\ControllerStatus;
use core\exception\EntryStorageException;
use core\exception\EntryValueAlreadyExistsException;
use core\Register;
use core\Route;
use core\Tool;

class BoBBackendUpdateController implements IBackendApp, IBackendMenu
{
    public static function returnBackendMenu(): array
    {
		if(Register::getControllerRegistry('bobupdate')->getStatus() == ControllerStatus::INSTALLED)
		{
			return array(
				'label' => 'BoB Update',
				'items' => array(
					array(
						'label' => 'Übersicht',
						'link' => '/backend/app/bobupdate/overview'
					),
					array(
						'label' => 'Kunden',
						'link' => '/backend/app/bobupdate/customers'
					),
					array(
						'label' => 'Releases',
						'link' => '/backend/app/bobupdate/releases'
					),
				)
			);
		}

		return array();
    }

    public static function handleRoutes(): \core\RouteCollection
    {
        define('BOB_UPDATE_DIRECTORY', ROOT_DIR . '/files/bob_update');

        if(!file_exists(BOB_UPDATE_DIRECTORY))
        {
            mkdir(BOB_UPDATE_DIRECTORY, 0777, true);
        }

        $route_collection = new \core\RouteCollection();

        $route_collection->add(Route::get('/overview', function (){
            echo BackendController::renderBackendStructure('Backend - BoB-Update', '');
        }));

        $route_collection->add(Route::get( '/releases', function (){
            $controller = new BoBBackendUpdateController();
            $controller->releaseTableRequest();
        }));

        $route_collection->add(Route::get('/customers', function (){
            $controller = new BoBBackendUpdateController();
            $controller->customerTableRequest();
        }));

        $route_collection->add(Route::post('/api/release', function (){
            $controller = new BoBBackendUpdateController();
            $controller->postReleaseRequest();
        }));

        $route_collection->add(Route::delete( '/api/release/[i:id]', function (int $id){
            $controller = new BoBBackendUpdateController();
            $controller->deleteReleaseRequest($id);
        }));

        return $route_collection;
    }

    public function deleteReleaseRequest(int $id) : void
    {
        $release_entry = ReleaseEntry::selectByID($id);

        if($release_entry->exists())
        {
            try
            {
                $release_entry->delete();
                Tool::headerOK();
            }
            catch (EntryStorageException $exception)
            {
                Tool::headerBadRequest();
                Tool::returnJsonData(array('message' => 'Der angegebene Release konnte nicht gelöscht werden!'));
            }

            return;
        }

        Tool::headerNotFound();
        Tool::returnJsonData(array('message' => 'Der angegebene Release existiert nicht mehr.'));
    }

    public function postReleaseRequest() : void
    {
        set_time_limit(0);
        // ToDo Prüfen ob die Requirements stimmen
        //ini_get('upload_max_filesize');
        //ini_get('post_max_size');
        //ini_get('memory_limit ');


        if(!isset($_FILES['backend_file']))
        {
            Tool::headerBadRequest();
            return;
        }

        if(!isset($_FILES['frontend_file']))
        {
            Tool::headerBadRequest();
            return;
        }

        if(!isset($_POST['version']))
        {
            Tool::headerBadRequest();
            return;
        }

        $backend_file_path = BOB_UPDATE_DIRECTORY . '/backend_' . uniqid() . '_' . md5($_POST['version']) . '.zip';
        $frontend_file_path = BOB_UPDATE_DIRECTORY . '/frontend_' . uniqid() . '_' . md5($_POST['version']) . '.zip';

        if(!move_uploaded_file($_FILES['backend_file']['tmp_name'], $backend_file_path))
        {

        }

        $backend_file_hash = hash_file('sha256', $backend_file_path);

        if(!move_uploaded_file($_FILES['frontend_file']['tmp_name'], $frontend_file_path))
        {

        }

        $frontend_file_hash = hash_file('sha256', $frontend_file_path);

        $release_entry = new ReleaseEntry();
        $release_entry->setVersion($_POST['version']);
        $release_entry->setBackendPath($backend_file_path);
        $release_entry->setBackendHash($backend_file_hash);
        $release_entry->setFrontendPath($frontend_file_path);
        $release_entry->setFrontendHash($frontend_file_hash);

        if(isset($_POST['title']))
        {
            $release_entry->setTitle($_POST['title']);
        }
        else
        {
            $release_entry->setTitle("Kein Titel");
        }

        if(isset($_POST['description']))
        {
            $release_entry->setDescription($_POST['description']);
        }
        else
        {
            $release_entry->setDescription("Keine Beschreibung");
        }

        if(isset($_POST['is_patch']))
        {
            $release_entry->setPatch((bool)$_POST['is_patch']);
        }
        else
        {
            $release_entry->setPatch(false);
        }

        try
        {
            $release_entry->save();
            Tool::headerOK();
            Tool::returnJsonData($release_entry->getRawValues());
        }
        catch (EntryValueAlreadyExistsException $exception)
        {
            Tool::headerBadRequest();
            Tool::returnJsonData(array('message' => 'Die Version existiert schon!'));
        }
    }

    public function releaseTableRequest() : void
    {
        $release_model = new ReleaseModel();
        $release_model->selectAll();
        $releases = $release_model->getRows();

        $release_table_view = Register::getTemplateSystem()::render(
            'bob_update_releases_table_view',
            array('releases' => $releases)
         );

        echo BackendController::renderBackendStructure('Backend - BoB-Update', $release_table_view);
    }

    public function customerTableRequest() : void
    {
        $customer_model = new CustomerModel();
        $customer_model->selectAll();


        $customer_table_view = Register::getTemplateSystem()::render(
            'bob_update_customer_table_view',
            array('customers' => $customer_model->getRows())
        );

        echo BackendController::renderBackendStructure('Backend - BoB-Update', $customer_table_view);
    }

	public function receiveControllerRegistry(ControllerRegistry $controller_registry): void
	{
		// TODO: Implement receiveControllerRegistry() method.
	}
}