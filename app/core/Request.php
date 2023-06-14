<?php

namespace app\core;

use app\includes\Utils;

/**
 * Class Request
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\core
 */
class Request
{
    public array $headerData;
    public array $bodyData;

    public function getUrl(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        if ($position === false) return $path;
        return substr($path, 0, $position);
    }

    public function method(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }


    public function isGet(): bool
    {
        return $this->method() === 'get';
    }


    public function isPost(): bool
    {
        return $this->method() === 'post';
    }

    public function isPut(): bool
    {
        return $this->method() === 'put';
    }


    public function isDelete(): bool
    {
        return $this->method() === 'delete';
    }


    public function contentType()
    {
        $content_type = $_SERVER['HTTP_CONTENT_TYPE'] ?? $_SERVER["CONTENT_TYPE"];
        $pos = strpos($content_type, ";");
        if ($pos === false) return $content_type;
        return substr($content_type, 0, $pos);
    }


    public function isJSONContentType(): bool
    {
        return $this->contentType() === 'application/json';
    }

    public function isURLEncodedContentType(): bool
    {
        return $this->contentType() === 'application/x-www-form-urlencoded';
    }


    private function getFiles($body): array
    {
        if ($this->contentType() === 'multipart/form-data') {
            foreach ($_FILES as $key => $val) {
                if (is_uploaded_file($val['tmp_name']) && file_exists($val["tmp_name"])) {
                    $body[$key] = $val;
                }
            }
        }
        return $body;
    }


    public function getBody(): array
    {
        $body = ['request_type' => $this->method()];

        if ($this->isGet()) {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        if ($this->isPost()) {
            if (!$this->isJSONContentType()) {
                foreach ($_POST as $key => $value) {
                    $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
                $body = $this->getFiles($body);
            }
            if ($this->isJSONContentType()) {
                $json = file_get_contents("php://input");
                $data = json_decode($json, true);
                if ($data) {
                    foreach ($data as $key => $value) {
                        $body[$key] = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);
                    }
                }
            }
        }

        if (($this->isPut() || $this->isDelete()) && $this->isJSONContentType()) {
            $json = file_get_contents("php://input");
            $data = json_decode($json, true);
            if ($data) {
                foreach ($data as $key => $value) {
                    $body[$key] = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }
        }

        $this->bodyData = $body;
        if (!empty($this->headerData)) {
            $body = array_merge($this->headerData, $body);
        }
        return $body;
    }


    public function getAuthorizationToken()
    {
        if (isset($_SERVER["HTTP_AUTHORIZATION"])) {
            $authToken = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } else if (function_exists("apache_request_headers") && isset(apache_request_headers()['Authorization'])) {
            $authToken = trim(apache_request_headers()['Authorization']);
        }
        return (!empty($authToken) && preg_match("/Bearer\s(\S+)/", $authToken, $matches)) ? $matches[1] : false;
    }


    public function verifyAuthorization(): array|string
    {
        if ($authData = $this->getAuthorizationToken()) {
            return Utils::verifyJWT($authData);

        }
        return "jwt - Authorization error";
    }

}
