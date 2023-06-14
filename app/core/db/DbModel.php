<?php

namespace app\core\db;

use app\core\Application;
use app\core\Model;
use Exception;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Class DbModel
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\core\database;
 */
abstract class DbModel extends Model
{
    abstract public static function tableName() : string;


    public function save(): bool
    {
        $res = false;
        $tableName = $this->tableName();
        $attributes = $this->attributes;
        try {
            $params = array_map(fn($attr) => ":$attr", $attributes);
            $statement = self::prepare("INSERT INTO $tableName (".implode(',', $attributes).") VALUES(".implode(',', $params).");");
            foreach($attributes as $attribute){
                $statement->bindValue(":$attribute", $this->{$attribute});
            }
            $res = $statement->execute();
        }catch (PDOException|Exception $e)
        {
            $this->addError('save', $e->getMessage());
        }
        return $res;
    }


    /**
     * @param $sql
     * @return PDOStatement
     */
    public static function prepare($sql): PDOStatement
    {
        return Application::$app->db->pdo->prepare($sql);
    }


    /**
     * @param array $where sequential
     * @param bool $needAll
     * @return object|bool|array
     */
    public function getData(array $where, bool $needAll = false): object|bool|array
    {
        $tableName = $this->tableName();

        if(empty($where) && $needAll === true){
            $statement = self::prepare("SELECT * FROM $tableName;");
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_OBJ);
        }

        $sql = implode(" AND ", array_map(fn($attr) => "$attr = :$attr", $where));
        $statement = self::prepare("SELECT * FROM $tableName WHERE $sql;");
        foreach ($where as $key)
        {
            $statement->bindValue(":$key", $this->{$key});
        }
        $statement->execute();
        if($needAll){
            return $statement->fetchAll(PDO::FETCH_OBJ);
        }
        return $statement->fetchObject();
    }


    /**
     * @param array $fields associative array
     * @param array $where sequential array
     * @return bool
     */
    public function updateByFields(array $fields, array $where): bool
    {
        $tableName = $this->tableName();
        $sql = implode(" AND ", array_map(fn($attr) => "$attr = :$attr", $where));
        $updateAttributes = array_keys($fields);
        $update = implode(",", array_map(fn($attr) => "$attr=:$attr", $updateAttributes));
        $statement = self::prepare("UPDATE $tableName SET $update WHERE $sql");
        foreach ($fields as $key => $value)
        {
            $statement->bindValue(":$key", $value);
        }
        foreach ($where as $key)
        {
            $statement->bindValue(":$key", $this->{$key});
        }
        return $statement->execute();
    }


    /**
     * @param array $where sequential
     * @return bool
     */
    public function update(array $where): bool
    {
        $tableName = $this->tableName();
        $sql = implode(" AND ", array_map(fn($attr) => "$attr = :$attr", $where));
        $updateAttributes = $this->attributes;
        $update = implode(",", array_map(fn($attr) => "$attr=:$attr", $updateAttributes));
        $statement = self::prepare("UPDATE $tableName SET $update WHERE $sql");
        foreach ($where as $key)
        {
            $statement->bindValue(":$key", $this->{$key});
        }
        foreach ($updateAttributes as $key)
        {
            $statement->bindValue(":$key", $this->{$key});
        }
        return $statement->execute();
    }

    /**
     * @param array $where sequential
     * @return bool
     */
    public function delete(array $where): bool
    {
        $tableName = $this->tableName();
        $sql = implode(" AND ", array_map(fn($attr) => "$attr = :$attr", $where));
        $statement = self::prepare("DELETE FROM $tableName WHERE $sql;");
        foreach ($where as $key)
        {
            $statement->bindValue(":$key", $this->{$key});
        }
        return $statement->execute();
    }


}