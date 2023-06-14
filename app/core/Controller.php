<?php

namespace app\core;

/**
 * Class Controller
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\core
 */
class Controller
{


    public static function onSuccess($messages): string
    {
        return Application::$app->response->result($messages, 1);
    }

    public static function onError($messages): string
    {
        return Application::$app->response->result($messages);
    }



    public static function verifyAuthorization($mustAdmin = true): void
    {
        $headerData = Application::$app->request->verifyAuthorization();
        if(!is_array($headerData))
        {
            die(self::onError($headerData));
        }

        if($mustAdmin && empty($headerData["admin_id"])){
            die(self::onError("jwt - Only admin can access this"));
        }
        Application::$app->request->headerData = $headerData;
    }





}