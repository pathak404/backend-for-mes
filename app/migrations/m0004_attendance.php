<?php

/**
 * Class m0004_attendance
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\migrations
 * TODO: set global timezone UTC +05:30 in db
 */
class m0004_attendance
{
    public function up(): void
    {
        $db = \app\core\Application::$app->db;
        $sql = "CREATE TABLE attendance (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                date DATE NOT NULL DEFAULT CURRENT_DATE,
                student_id INT UNSIGNED NOT NULL,
                amount DECIMAL(8, 2) NOT NULL DEFAULT 0.00,
                breakfast VARCHAR(2) null DEFAULT 'NP',
                lunch VARCHAR(2) null DEFAULT 'NP',
                dinner VARCHAR(2) null DEFAULT 'NP'
                ) ENGINE=INNODB;";
        $db->pdo->exec($sql);
    }

    public function down(): void
    {
        $db = \app\core\Application::$app->db;
        $sql = "DROP TABLE attendance;";
        $db->pdo->exec($sql);
    }
}