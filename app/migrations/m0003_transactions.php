<?php

/**
 * Class m0003_transactions
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\migrations
 */
class m0003_transactions
{
    public function up(): void
    {
        $db = \app\core\Application::$app->db;
        $sql = "CREATE TABLE transactions (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                txn_id VARCHAR(180) NOT NULL,
                customer VARCHAR(15) NOT NULL,
                txn_amount DECIMAL(8, 2) NOT NULL DEFAULT 0.00,
                txn_type VARCHAR(40) NOT NULL,
                txn_desc VARCHAR(255) NOT NULL,
                payment_method VARCHAR(20) NULL,
                txn_status VARCHAR(20) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=INNODB;";
        $db->pdo->exec($sql);
    }

    public function down(): void
    {
        $db = \app\core\Application::$app->db;
        $sql = "DROP TABLE transactions;";
        $db->pdo->exec($sql);
    }

}