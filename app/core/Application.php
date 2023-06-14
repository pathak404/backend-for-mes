<?php

namespace app\core;

use app\core\db\Database;

/**
 * Class Application
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\core
 */
class Application
{
    public static Application $app;
    public static array $config;
    public Router $router;
    public Request $request;
    public Response $response;
    public Database $db;
    public ?Controller $controller;
    public function __construct($config)
    {
        self::$config = $config;
        self::$app = $this;
        $this->response = new Response();
        $this->request = new Request();
        $this->db = new Database($config);
        $this->router = new Router($this->request, $this->response);
        $this->setTimeZone();
    }



    public function run(): void
    {

        echo $this->router->resolve();
    }

    public function setTimeZone(): void
    {
        date_default_timezone_set('Asia/Kolkata');
    }
}