<?php declare(strict_types=1); namespace controller\bob_update;

use controller\bob_update\entry\CustomerEntry;
use controller\bob_update\entry\ReleaseEntry;
use controller\bob_update\model\CustomerModel;
use controller\bob_update\model\ReleaseModel;
use core\Config;
use core\ControllerRegistry;
use core\IController;
use core\JsonWebToken;
use core\Route;
use core\RouteCollection;
use core\Tool;

class BoBUpdateController implements IController
{
    /**
     * @var $jwt_token JsonWebToken
     */
    private static $jwt_token;

    public static function setup(ControllerRegistry $controller_registry): RouteCollection
    {
        $route_collection = new RouteCollection();

        self::$jwt_token = new JsonWebToken();
        self::$jwt_token->setSecret(Config::getSecret());

        $token = Tool::getBearerToken();

        if(isset($token{0}) AND self::$jwt_token->validateJsonToken($token))
        {
            self::$jwt_token->fromJsonToken($token);

            if(self::$jwt_token->getBool('download_updates', false))
            {
                $route_collection->add(Route::get( '/api/releases.json', function (){
                    $release_model = new ReleaseModel();
                    $release_model->selectAllWithoutPath();

                    $releases = array();

                    foreach ($release_model->getRows() as $release)
                    {
                        $release['frontend_download_url'] = '/bobupdate/api/releases/' . $release['bu_release_pk'] . '/download/frontend.zip';
                        $release['backend_download_url'] = '/bobupdate/api/releases/' . $release['bu_release_pk'] . '/download/backend.zip';

                        $releases[] = $release;
                    }

                    Tool::returnJsonData($releases);
                    Tool::headerOK();
                }));

                $route_collection->add(Route::get('/api/releases/[i:id]/download/backend.zip',
                function (int $id){
                    $release_entry = ReleaseEntry::selectByID($id);
                    if($release_entry->exists() AND $release_entry instanceof ReleaseEntry)
                    {
                        Tool::headerReturnFile($release_entry->getBackendPath());
                        Tool::headerOK();
                    }

                    Tool::headerNotFound();
                }));

                $route_collection->add(Route::get( '/api/releases/[i:id]/download/frontend.zip', function (int $id){
                    $release_entry = ReleaseEntry::selectByID($id);
                    if($release_entry->exists() AND $release_entry instanceof ReleaseEntry)
                    {
                        Tool::headerReturnFile($release_entry->getFrontendPath());
                        Tool::headerOK();
                    }

                    Tool::headerNotFound();
                }));
            }
        }
        else
        {
            $route_collection->add(Route::post( '/api/login.json', function (){

                $data = Tool::jsonDecode(Tool::getInputData());

                if(isset($data['product_key']))
                {
                    $customer_model = new CustomerModel();
                    $customer_model->selectByProductKey($data['product_key']);

                    $customer_entry = $customer_model->getEntry(CustomerEntry::class);

                    if($customer_entry instanceof CustomerEntry)
                    {
                        self::$jwt_token->setBool('download_updates', true);
                        self::$jwt_token->setExpirationTime(time() + 3600);
                        Tool::returnJsonData(array('token' => self::$jwt_token->export()));
                        Tool::headerOK();
                        return;
                    }

                }

                Tool::headerForbidden();
            }));
        }

        return $route_collection;
    }
}
