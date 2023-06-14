<?php

namespace app\core;

/**
 * Class Router
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\core
 */
class Router
{

    public Request $request;
    public Response $response;
    protected array $routes;
    public function __construct(Request $req, Response $res)
    {
        $this->request = $req;
        $this->response = $res;
    }

    public function get($path, $callback): void
    {
        $this->routes['get'][$path] = $callback;
    }
    public function post($path, $callback): void
    {
        $this->routes['post'][$path] = $callback;
    }
    public function put($path, $callback): void
    {
        $this->routes['put'][$path] = $callback;
    }
    public function delete($path, $callback): void
    {
        $this->routes['delete'][$path] = $callback;
    }

    public function resolve()
    {
        $path = $this->request->getUrl();
        $method = $this->request->method();
        $callback = $this->routes[$method][$path] ?? false;
        
        if($callback === false){
            $this->response->setHeader();
            $this->response->setStatusCode(404);
            echo "not found :(";
            exit;
        }
        if(is_array($callback)){
            Application::$app->controller = new $callback[0]();
            $callback[0] = Application::$app->controller;
        }
        return call_user_func($callback, $this->request);
    }

}