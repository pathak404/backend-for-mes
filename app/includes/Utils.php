<?php

namespace app\includes;

use app\core\Application;
use DateTimeImmutable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Class Utils
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\includes
 */
class Utils
{
    public static function getServerName(): string
    {
        $url = 'http://';
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        {
            $url = 'https://';
        }
        $url .= $_SERVER['HTTP_HOST'];
        return $url;
    }

    public static function createJWT($data): string
    {
        $issued_at = new DateTimeImmutable();
        $expire     = $issued_at->modify('+5 days')->getTimestamp();

        $payload = [
            'iat'  => $issued_at->getTimestamp(),
            'iss'  => Application::$config['servername'],
            'nbf'  => $issued_at->getTimestamp(),
            'exp'  => $expire
        ];

        foreach ($data as $key => $value)
        {
            $payload[$key] = $value;
        }
        return JWT::encode($payload, Application::$config['jwt_secret'], 'HS256');
    }


    public static function verifyJWT($jwt): array|string
    {
        try {
            $issued_at = new DateTimeImmutable();
            $decoded = JWT::decode($jwt, new Key(Application::$config['jwt_secret'], 'HS256'));
            if ($decoded->iss !== Application::$config['servername'] ||
                $decoded->nbf > $issued_at->getTimestamp() ||
                $decoded->exp < $issued_at->getTimestamp())
            {
                return "jwt - expired";
            }
            return isset($decoded->admin_id) ? (array)$decoded : "jwt: admin_id is required";
        }
        catch (\Exception $e)
        {
            return "jwt - " .$e->getMessage();
        }
    }



    public static function random_str(int $length = 8, string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string
    {
        if ($length < 1) {
            return false;
        }
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            try {
                $pieces [] = $keyspace[random_int(0, $max)];
            } catch (\Exception $e) {
                --$i;
            }
        }
        return implode('', $pieces);
    }



    public static function random_smallCase_str($len): string
    {
        return self::random_str($len, '0123456789abcdefghijklmnopqrstuvwxyz');
    }

    public static function random_capitalCase_str($len): string
    {
        return self::random_str($len, '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }

    public static function random_numbers($len): string
    {
        return self::random_str($len, '1234567890');
    }


    public static function isAssoc($arr): bool
    {
        if (empty($arr)) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

}