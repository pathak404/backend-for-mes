<?php

namespace app\models\admin;

use app\core\Application;
use app\core\db\DbModel;

/**
 * Class Admin
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\models\admin
 */
class Admin extends DbModel
{
    public ?int $admin_id = null;
    public ?string $full_name = null;
    public ?string $phone = null;
    public ?string $password = null;
    public ?string $email = null;


    public function __construct($body)
    {
        $this->loadData($body);
    }

    public static function tableName(): string
    {
        return "admin";
    }


    public function auth(): object|bool|array
    {
        // no internal validation
        if(empty($this->password) || empty($this->phone)){
            $which = empty($this->password) ? "password" : "phone";
            $this->addError("$which", "$which is a required field");
            return false;
        }
        if ($data = $this->getData(['phone'])) {
            if (password_verify($this->password, $data->password)) {
                return $data;
            }
            $this->addError("password", "invalid password");
            return false;
        }
        $this->addError("phone", "No account found");
        return false;
    }


    public function encrypt_password()
    {
        if (!empty($this->password)) {
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        }else{
            $this->attributes = array_diff($this->attributes, ["request_type", "password"]);
        }
    }

    public function addAdmin(): object|bool|array
    {
        if ($this->validate()) {
            $this->encrypt_password();
            return $this->save() ? $this->getData(["phone", "email"]) : false;
        }
        return false;
    }

    public function getAdmin(): object|bool|array
    {
        if ($this->validate()) {
            return $this->getData(["admin_id"]);
        }
        return false;
    }

    public function getAll(): object|bool|array
    {
        // no internal validation
        return $this->getData([], true);
    }

    public function updateAdmin(): object|bool|array
    {
        $this->encrypt_password();
        return ($this->validate() && $this->update(["admin_id"])) ? $this->getData(["admin_id"]) : false;
    }

    public function deleteAdmin(): bool|string
    {
        $current_admin_id = Application::$app->request->headerData['admin_id'];
        $deletion_admin_id =  Application::$app->request->bodyData['admin_id'] ?? false;
        if(empty($deletion_admin_id)){
            $this->addError("admin_id", "Please Provide admin_id");
            return false;
        }
        if($current_admin_id == $deletion_admin_id){
            $this->addError("message", "You cannot delete your own account");
            return false;
        }
        return ($this->validate() && $this->delete(["admin_id"])) ? "Student data deleted successfully" : false;
    }

    public function rules(): array
    {
        return match ($this->request_type) {
            'put', 'delete', 'get' => [
                "admin_id" => [self::RULE_REQUIRED, [self::RULE_EXIST, "class" => self::class]],
            ],
            'post' => [
                "phone" => [self::RULE_REQUIRED, [self::RULE_UNIQUE, "class" => self::class], [self::RULE_MIN, 'min' => 10], [self::RULE_MAX, 'max' => 10]],
                "email" => [self::RULE_REQUIRED, [self::RULE_UNIQUE, "class" => self::class], [self::RULE_MAX, 'max' => 100]],
                "password" => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 6], [self::RULE_MAX, 'max' => 30]],
            ]
        };
    }


    public function requiredAttributes(): array
    {
        return match ($this->request_type) {
            'put', 'delete', 'get' => ['admin_id'],
            'post' => ['phone', 'email', 'password']
        };
    }

    public function labels(): array
    {
        return [
            'admin_id' => 'Admin ID',
            'full_name' => 'Full Name',
            'email' => "Email",
            'password' => 'Password',
            'phone' => 'Phone'
        ];
    }

}