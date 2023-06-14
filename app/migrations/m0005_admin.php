<?php

/**
 * Class m0005_admin
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\migrations
 */
class m0005_admin
{
    public function up(): void
    {
        $db = \app\core\Application::$app->db;
        $sql = "CREATE TABLE admin (
                admin_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                password VARCHAR(255) NULL,
                full_name VARCHAR(80) NULL,
                email VARCHAR(100) NULL,
                phone VARCHAR(12) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=INNODB;";
        $db->pdo->exec($sql);
        $db->pdo->exec("ALTER TABLE admin AUTO_INCREMENT=100;");
    }

    public function down(): void
    {
        $db = \app\core\Application::$app->db;
        $sql = "DROP TABLE admin;";
        $db->pdo->exec($sql);
    }
}