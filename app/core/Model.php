<?php

namespace app\core;

use Exception;
use PDO;
use PDOException;

/**
 * Class Model
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\core
 */
abstract class Model
{
    public const RULE_REQUIRED = "required";
    public const RULE_EMAIL = "email";
    public const RULE_MIN = "min";
    public const RULE_MAX = "max";
    public const RULE_MATCH = "match";
    public const RULE_UNIQUE = "unique";
    public const RULE_EXIST = "exist";

    public array $errors = [];
    public array $attributes = [];

    public ?string $request_type = null;

    abstract public function rules() : array;
    abstract public function requiredAttributes(): array;
    abstract public function labels(): array;


    public function loadData($data): void
    {
        foreach($data as $key => $value){
            if(property_exists($this, $key)){
                $this->{$key} = $value;
                $this->attributes[] = $key;
            }
        }
        $this->removeGarbage();
        if( !empty($this->requiredAttributes()) )
        {
            $intersect_count = count(array_intersect($this->requiredAttributes(), $this->attributes));
            $requiredAttr_count = count( $this->requiredAttributes() );
            if( $requiredAttr_count !== $intersect_count ) {
                $this->addError("message", "Please provide required parameter(s)");
            }
        }
    }

    public function removeGarbage(): void
    {
        $this->attributes = array_diff($this->attributes, ["request_type"]);
    }




    public function loadAdditionalData($data = []): void
    {
        foreach($data as $key => $value){
            if( property_exists($this, $key) && !in_array($key, $this->attributes) ){
                $this->{$key} = $value;
                $this->attributes[] = $key;
            }
        }
    }




    public function addError($attribute, $message): void
    {
        $this->errors[$attribute][] = $message;
    }




    private function addErrorForRule(string $attribute, string $rule, array $params = []): void
    {
        $message = $this->errorMessages()[$rule] ?? '';
        $attrLabel = $this->labels()[$attribute] ?? $attribute;
        $message = str_replace("{label}", $attrLabel, $message);
        foreach ($params as $key => $value){
            $value =  $this->labels()[$value] ?? $value;
            $message = str_replace("{{$key}}", $value, $message);
        }
        $this->errors[$attribute][] = $message;
    }




    public function errorMessages() : array
    {
        return [
            self::RULE_REQUIRED => "{label} is required field",
            self::RULE_EMAIL => "{label} must be a valid email",
            self::RULE_MIN => "minimum length of {label} must be {min}",
            self::RULE_MAX => "maximum length of {label} must be {max}",
            self::RULE_MATCH => "{label} must be same as {match}",
            self::RULE_UNIQUE => "Record with this {label} already exists",
            self::RULE_EXIST => "Record with this {label} not exist",
        ];
    }




    public function validate() : bool
    {
        foreach ($this->rules() as $attribute => $rules){
            $value = $this->{$attribute};
            if(empty($rules)) { continue; }
            foreach ($rules as $rule){
                $ruleName = $rule;
                if(!is_string($ruleName)){
                    $ruleName = $rule[0];
                }
                if($ruleName === self::RULE_REQUIRED && !$value){
                    $this->addErrorForRule($attribute, self::RULE_REQUIRED);
                }
                if($ruleName === self::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)){
                    $this->addErrorForRule($attribute, self::RULE_EMAIL);
                }

                if($ruleName === self::RULE_MIN && strlen($value) < $rule['min']){
                    $this->addErrorForRule($attribute, self::RULE_MIN, $rule);
                }
                if($ruleName === self::RULE_MAX && strlen($value) > $rule['max']){
                    $this->addErrorForRule($attribute, self::RULE_MAX, $rule);
                }
                if($ruleName === self::RULE_MATCH && $value !== $this->{$rule['match']}){
                    $this->addErrorForRule($attribute, self::RULE_MATCH, $rule);
                }
                if($ruleName === self::RULE_UNIQUE){
                    $class = $rule["class"];
                    $attr = $rule['attribute'] ?? $attribute;
                    $where = ["$attr" => "$value"];
                    if(self::getDataByValue($class, $where)){
                        $this->addErrorForRule($attribute, self::RULE_UNIQUE);
                    }
                }
                if($ruleName === self::RULE_EXIST)
                {
                    $class = $rule["class"];
                    $attr = $rule['attribute'] ?? $attribute;
                    $where = ["$attr" => "$value"];
                    if(!self::getDataByValue($class, $where)){
                        $this->addErrorForRule($attribute, self::RULE_EXIST);
                    }
                }
            }
        }
        return empty($this->errors);
    }


    /**
     * @param $class - class or table name (str)
     * @param $where - key value pair
     * @return false|mixed|object|null
     */
    public static function getDataByValue($class, $where)
    {
        if(class_exists($class)){
            $tableName = $class::tableName();
        }else{
            $tableName = $class;
        }
        $sql = implode(" AND ", array_map( fn($attr) => "$attr = :$attr", array_keys($where) ) );
        try {
            $statement = Application::$app->db->prepare("SELECT * FROM $tableName WHERE $sql");
            foreach ($where as $key => $val)
            {
                $statement->bindValue(":$key", $val);
            }
            $statement->execute();
            return $statement->fetchObject();
        }catch (PDOException|Exception $e)
        {
            die( Controller::onError( $e->getMessage() ) );
        }
    }



}