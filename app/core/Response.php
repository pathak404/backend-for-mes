<?php

namespace app\core;

use app\includes\Utils;

/**
 * Class Response
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\core
 */
class Response
{

    public function result($messages, $status = 0, $setHeader = 1): string
    {

        $setHeader && $this->setHeader();

        $returnData["status"] = $status;
        $returnData["data"] = $messages;

        if( is_string($messages) ){
            $returnData["data"] = $this->addMessage($messages);
        }

        if( $status && ( isset($messages->admin_id) || isset(Application::$app->request->headerData['admin_id']) ) )
        {
            $user_id = Application::$app->request->headerData['admin_id'] ?? $messages->admin_id;
            $jwtData = [
                "admin_id" => $user_id
            ];
            $returnData["jwt"] = Utils::createJWT($jwtData) ?? 0;
        }
        return json_encode($returnData);
    }


    public function addMessage($message): array
    {
        return ["message" => [$message]];
    }


    public function setStatusCode($code): void
    {
        http_response_code($code);
    }

    public function setHeader(): void
    {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header('Content-Type: application/json; charset=utf-8');
    }


}