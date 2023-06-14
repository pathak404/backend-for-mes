<?php

/**
 * Author: Abhishek Kumar Pathak
 * Email: officialabhishekpathak@gmail.com
 */


use app\controllers\AdminController;
use app\controllers\AttendanceController;
use app\controllers\StatisticsController;
use app\controllers\StudentController;
use app\controllers\WalletController;
use app\core\Application;
use app\includes\Utils;

require dirname(__DIR__). '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();


$config = [
    'servername' => Utils::getServerName(),
    'jwt_secret' => $_ENV['JWT_SECRET'],
    'path' => [
        'root' => dirname(__DIR__),
        'app' => dirname(__DIR__).'/app',
        'storage' => dirname(__DIR__).'/app/storage'
    ],
    'db' => [
        "dsn" => $_ENV['DB_DSN'],
        "username" => $_ENV['DB_USERNAME'],
        "password" => $_ENV['DB_PASSWORD']
    ]
];

$app = new Application($config);


$app->router->get('/', function (){
    header("Content-Type: application/json");
    echo json_encode([
        "status" => 0,
        "data" => [
            "message" => [
                "Hello guys, Chai pee lo ...",
            ]
        ]
    ]);
});






// admin
$app->router->post('/auth', [AdminController::class, 'auth']);
$app->router->post('/admin', [AdminController::class, 'create_account']);
$app->router->get('/admin', [AdminController::class, 'get_account']);
$app->router->put('/admin', [AdminController::class, 'update_account']);
$app->router->delete('/admin', [AdminController::class, 'delete_account']);
$app->router->get('/admin_all', [AdminController::class, 'get_all_accounts']);

// student
$app->router->post('/student', [StudentController::class, 'create_account']);
$app->router->get('/student', [StudentController::class, 'get_account']);
$app->router->put('/student', [StudentController::class, 'update_account']);
$app->router->delete('/student', [StudentController::class, 'delete_account']);
$app->router->get('/student_all', [StudentController::class, 'get_all_accounts']);

// attendance
$app->router->get('/attendance', [AttendanceController::class, 'getAttendance']);

// order
$app->router->post('/order', [WalletController::class, 'create_order']);
$app->router->get('/order', [WalletController::class, 'get_order']);
$app->router->get('/order_all', [WalletController::class, 'get_all_order']);

// txn
$app->router->post('/transaction', [WalletController::class, 'add_transaction']);
$app->router->get('/transaction', [WalletController::class, 'get_transactions']);
$app->router->get('/transaction_all', [WalletController::class, 'get_all_transactions']);

// wallet
$app->router->get('/wallet', [WalletController::class, 'get_wallet']);
$app->router->get('/wallet_all', [WalletController::class, 'get_all_wallet']);

// Statistics
$app->router->get('/statistics', [StatisticsController::class, 'getCount']);

// update subscription
$app->router->get('/update_subscription', [WalletController::class, 'updateSubscription']);



// migration
//$app->router->get('/migrate', function(){
//    Application::$app->db->applyMigrations();
//});


$app->run();





// for testing purpose
//jwt
//$issued_at = new DateTimeImmutable();
//$expire     = $issued_at->modify('+120 minutes')->getTimestamp();
//
//$payload = [
//    'iat'  => $issued_at->getTimestamp(),
//    'iss'  => $config['servername'],
//    'nbf'  => $issued_at->getTimestamp(),
//    'exp'  => $expire,
//    'userid'   => 11221,
//    'isadmin' => 0
//];

//echo $jwt = JWT::encode($payload, $config['jwt_secret'], 'HS256');

// auth token
//$userdata = '{"username": "abhipathak03", "password": "abhishek03"}';
//$userdata = '{
//    "username" : "abhipathak03",
//    "email" : "test@test.com03",
//    "phone" : "876626828503",
//    "password":"abhishek03"
//}';
//echo $app->encryptData($userdata);