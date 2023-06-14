<?php

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\Request;
use app\models\admin\Admin;

/**
 * Class AdminController
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\controllers
 */
class AdminController extends Controller
{
    private ?Admin $admin = null;
    public function __construct()
    {
        $this->admin = new Admin(Application::$app->request->getBody());
    }


    public function auth(): string
    {
        if($adminData = $this->admin->auth())
        {
            return self::onSuccess($adminData);
        }
        return self::onError($this->admin->errors);
    }



    public function create_account(): string
    {
        if($data = $this->admin->addAdmin())
        {
            return self::onSuccess($data);
        }
        return self::onError($this->admin->errors);
    }


    public function get_account(Request $request): string
    {
        self::verifyAuthorization();
        // required to verify & load header data
        $this->admin = new Admin($request->getBody());
        if($data = $this->admin->getAdmin()){
            return self::onSuccess($data);
        }
        return self::onError($this->admin->errors);
    }

    public function get_all_accounts(Request $request): string
    {
        self::verifyAuthorization();
        $this->admin = new Admin($request->getBody());
        if($data = $this->admin->getAll()){
            return self::onSuccess($data);
        }elseif (empty($this->admin->errors)){
            return $this->onSuccess([]);
        }
        return self::onError($this->admin->errors);
    }


    public function update_account(Request $request): string
    {
        self::verifyAuthorization();
        $this->admin = new Admin($request->getBody());
        if($data = $this->admin->updateAdmin()){
            return self::onSuccess($data);
        }
        return self::onError($this->admin->errors);
    }


    public function delete_account(Request $request): string
    {
        self::verifyAuthorization();
        $this->admin = new Admin($request->getBody());
        if($data = $this->admin->deleteAdmin()){
            return self::onSuccess($data);
        }
        return self::onError($this->admin->errors);
    }


}