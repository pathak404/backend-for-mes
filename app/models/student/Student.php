<?php

namespace app\models\student;

use app\core\Application;
use app\core\db\DbModel;
use app\models\admin\Admin;
use app\models\wallet\Wallet;
use PDO;

/**
 * Class Student
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\models\student
 */
class Student extends DbModel
{
    public ?int $student_id = null;
    public ?string $year = null;
    public ?string $full_name = null;
    public ?string $father_name = null;
    public ?string $phone = null;
    public ?string $branch = null;

    public ?string $meal_type = null;

    public string $find_by = 'student_id';
    public ?int $admin_id = null;

    public ?int $limit = null;

    public function __construct($body)
    {
        $this->loadData($body);
    }

    public function removeGarbage(): void
    {
        $this->attributes = array_diff($this->attributes, ["request_type", "find_by", "meal_type", "admin_id", "limit"]);
    }

    public static function tableName(): string
    {
        return 'students';
    }



    public function addStudent(): object|bool|array
    {
        if ($this->validate()) {
            $data =  $this->save() ? self::getDataByValue(Student::class, ["phone" => "$this->phone"]) : false;
            if($data && is_object($data)){
                $walletData = [
                    "meal_type" => $this->meal_type,
                    "subscription" => 0,
                    "balance" => 0.00,
                    "s_validity" => null,
                    "student_id" => $data->student_id
                ];
                (new Wallet($walletData))->createWallet();
                $data = array_merge((array)$data, $walletData);
                return (object)$data;
            }
            return $data;
        }
        return false;
    }

    public function getStudent(): object|bool|array
    {
        if ($this->validate()) {
            $data = $this->getData(["$this->find_by"]);
            if($data) {
                // get wallet
                $walletModal = new Wallet(["student_id" => $data->student_id]);
                $walletModal->validateSubscription();
                $wallet = $walletModal->getBalance();
                return (object)array_merge((array)$data, (array)$wallet);
            }
        }
        return false;
    }

    public function getAll(): object|bool|array
    {
        // no internal validation
        if(!empty($this->limit)) {
            $query = "Select * from students order by student_id desc limit $this->limit;";
            $stmt = Application::$app->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        }
        return $this->getData([], true);
    }

    public function getAllByDate($date): object|bool|array
    {
        // no internal validation
        if(empty($date)) { return false; }
        $query = "Select * from students where created_at like '%$date%' order by student_id desc;";
        if(!empty($this->limit)) {
            $query = "Select * from students where created_at like '%$date%' order by student_id desc limit $this->limit;";
        }
        $stmt = Application::$app->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function updateStudent(): bool|string
    {
        $id = $this->student_id;
        if($this->find_by == "phone"){
            $data = $this->getData(["phone"]);
            $id = $data->student_id;
        }
        if(!empty($this->meal_type)){
            (new Wallet(["meal_type" => $this->meal_type, "student_id" => $id]))->update_meal_type();
        }
        return ($this->validate() && $this->update(["$this->find_by"])) ? "Student data updated successfully" : false;
    }


    public function deleteStudent(): bool|string
    {
        if($this->validate()){
            $this->delete(["$this->find_by"]);
            $query = "DELETE FROM attendance WHERE student_id=$this->student_id;";
            $stmt = Application::$app->db->prepare($query);
            $stmt->execute();
            $query = "DELETE FROM transactions WHERE student_id=$this->student_id;";
            $stmt = Application::$app->db->prepare($query);
            $stmt->execute();
            $query = "DELETE FROM orders WHERE customer=$this->student_id;";
            $stmt = Application::$app->db->prepare($query);
            $stmt->execute();
            $query = "DELETE FROM wallet WHERE student_id=$this->student_id;";
            $stmt = Application::$app->db->prepare($query);
            $stmt->execute();
            return "Student data deleted successfully";
        }
        return false;
    }






    public function rules(): array
    {
        $rule = match ($this->request_type) {
            'put', 'delete', 'get' => [
                "$this->find_by" => [self::RULE_REQUIRED, [self::RULE_EXIST, "class" => self::class]],
            ],
            'post' => [
                "phone" => [self::RULE_REQUIRED, [self::RULE_UNIQUE, "class" => self::class], [self::RULE_MIN, 'min' => 10], [self::RULE_MAX, 'max' => 10]],
                "meal_type" => [self::RULE_REQUIRED]
            ]
        };
        $rule['admin_id'] = [self::RULE_REQUIRED, [self::RULE_EXIST, "class" => Admin::class]];
        return $rule;
    }


    public function requiredAttributes(): array
    {
        return [];
    }

    public function labels(): array
    {
        return [
            'student_id' => 'Student ID',
            'full_name' => 'Full Name',
            'father_name' => "Father's Name",
            'branch' => 'Branch',
            'year' => 'Year',
            'meal_type' => 'Meal Type',
            'phone' => 'Phone',
            'admin_id' => 'Admin ID',
        ];
    }

}