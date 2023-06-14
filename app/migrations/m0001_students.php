<?php

/**
 * Class m0001_users
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package ${NAMESPACE} (do not add )
 */
class m0001_students
{
    public function up(): void
    {
        $db = \app\core\Application::$app->db;
        $sql = "CREATE TABLE students (
                student_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                year VARCHAR(5) NULL,
                full_name VARCHAR(80) NULL,
                father_name VARCHAR(80) NULL,
                branch VARCHAR(50) NULL,
                phone VARCHAR(12) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=INNODB;";
        $db->pdo->exec($sql);
        $db->pdo->exec("ALTER TABLE students AUTO_INCREMENT=1000;");
    }

    public function down(): void
    {
        $db = \app\core\Application::$app->db;
        $sql = "DROP TABLE students;";
        $db->pdo->exec($sql);
    }
}