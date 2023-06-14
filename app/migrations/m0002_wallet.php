<?php

/**
 * Class m0002_wallet
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\migrations
 */
class m0002_wallet
{
    public function up(): void
    {
        $db = \app\core\Application::$app->db;
        $sql = "CREATE TABLE wallet (
                student_id INT UNSIGNED NOT NULL PRIMARY KEY,
                balance DECIMAL(8, 2) NOT NULL DEFAULT 0.00,
                meal_type VARCHAR(3) NOT NULL,
                subscription TINYINT UNSIGNED NOT NULL DEFAULT 0,
                s_validity DATE NULL
                ) ENGINE=INNODB;";
        $db->pdo->exec($sql);
    }

    public function down(): void
    {
        $db = \app\core\Application::$app->db;
        $sql = "DROP TABLE wallet;";
        $db->pdo->exec($sql);
    }

}