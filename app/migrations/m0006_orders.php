<?php


/**
 * Class m0006_orders
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\migrations
 */
class m0006_orders
{
    public function up(): void
    {
        $db = \app\core\Application::$app->db;
        $sql = "CREATE TABLE orders (
                order_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                customer VARCHAR(15) NOT NULL,
                txn_amount DECIMAL(8, 2) NOT NULL DEFAULT 0.00,
                txn_id VARCHAR(150) NULL,
                payment_method VARCHAR(20) NULL,
                service_date DATE NOT NULL,
                order_type VARCHAR(6) NOT NULL
                ) ENGINE=INNODB;";
        $db->pdo->exec($sql);
        $db->pdo->exec("ALTER TABLE orders AUTO_INCREMENT=100;");
    }

    public function down(): void
    {
        $db = \app\core\Application::$app->db;
        $sql = "DROP TABLE orders;";
        $db->pdo->exec($sql);
    }

}