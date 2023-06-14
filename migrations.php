<?php

/**
 * Author: Abhishek Kumar Pathak
 * Email: officialabhishekpathak@gmail.com
 */

use app\core\Application;

require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


$config = [
    'path' => [
        'app' => __DIR__.'/app'
    ],
    'db' => [
        "dsn" => $_ENV['DB_DSN'],
        "username" => $_ENV['DB_USERNAME'],
        "password" => $_ENV['DB_PASSWORD']
    ]
];

$app = new Application($config);


$app->db->applyMigrations();


