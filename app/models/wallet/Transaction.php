<?php

namespace app\models\wallet;

use app\core\Application;
use app\core\db\DbModel;
use app\includes\Utils;
use PDO;


/**
 * Class Transaction
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\models\wallet
 */
class Transaction extends DbModel
{
    public ?int $student_id = null;

    public ?string $customer = null;
    public ?float $txn_amount = null;
    public ?string $txn_type = null; // withdraw / subscription
    public ?string $txn_desc = null;
    public ?string $txn_id = null;
    public ?string $payment_method = "cash";  //cash / online
    public ?string $txn_status = null; // pending // failed // success

    public ?int $limit = null;

    public function removeGarbage(): void
    {
        $this->attributes = array_diff($this->attributes, ["request_type", "limit"]);
    }


    public array $subscription_thirty_days = [
        "ALL" => 3000,
        "BL" => 2200,
        "BD" => 2200,
        "LD" => 2700
    ];
    public array $subscription_twenty_days = [
        "ALL" => 2000,
        "BL" => 1468,
        "BD" => 1468,
        "LD" => 1800
    ];
    public array $subscription_ten_days = [
        "ALL" => 1000,
        "BL" => 734,
        "BD" => 734,
        "LD" => 900
    ];

    public function __construct($body)
    {
        $this->loadData($body);
        if (!empty($this->student_id)) {
            $this->customer = "$this->student_id";
            $this->attributes[] = "customer";
        }
    }


    public function addTxn(): bool|string
    {
        if ($this->validate()) {
            $wallet = new Wallet(["student_id" => $this->student_id]);
            $this->setTxnID();
            $this->attributes = array_diff($this->attributes, ["student_id"]);

            if ($this->txn_type == "subscription") {
                if($this->addSubscription($wallet)){
                    return "Subscription added successfully";
                }
                return false;
            }


            if ($this->txn_type == "withdraw") {
                if (!($wallet->deductBalance($this->txn_amount) && $this->save())) {
                    $this->errors = $this->errors + $wallet->errors;
                    return false;
                }
                return "Withdraw success";
            }
            $this->addError("message", "Invalid txn_type");
            return false;
        }
        return false;
    }


    private function addSubscription($wallet): bool
    {
        if (!($wallet->addBalance($this->txn_amount) && $this->save())) {
            $this->errors = $this->errors + $wallet->errors;
            return false;
        }
        $meal_type = $wallet->get_meal_type();
        if ($wallet->validateSubscription()) {
            if ($this->subscription_ten_days[$meal_type] == $this->txn_amount) {
                return $wallet->addSubscription("+10 days");
            } else if ($this->subscription_twenty_days[$meal_type] == $this->txn_amount) {
                return $wallet->addSubscription("+20 days");
            } else if ($this->subscription_thirty_days[$meal_type] == $this->txn_amount) {
                return $wallet->addSubscription("+30 days");
            }
        } else {
            if ($this->subscription_ten_days[$meal_type] == $this->txn_amount) {
                return $wallet->addSubscription("+9 days");
            } else if ($this->subscription_twenty_days[$meal_type] == $this->txn_amount) {
                return $wallet->addSubscription("+19 days");
            } else if ($this->subscription_thirty_days[$meal_type] == $this->txn_amount) {
                return $wallet->addSubscription("+29 days");
            }
        }
        return false;
    }


    public function setTxnID()
    {
        if (!in_array("payment_method", $this->attributes)) {
            $this->attributes[] = "payment_method";
        }
        // set txn id
        if (empty($this->txn_id)) {
            $this->txn_id = strtoupper($this->payment_method) . date("Ymd") . Utils::random_capitalCase_str(8);
            $this->attributes[] = "txn_id";
        }
    }

    public function getTxn(): object|bool|array
    {
        if ($this->validate()) {
            $this->txn_status = "success";
            return $this->getData(["customer", "txn_status"], true);
        }
        return false;
    }

    public function getAll(): object|bool|array
    {
        $this->txn_status = "success";
        if (!empty($this->limit)) {
            $query = "Select * from transactions where txn_status='success' order by id desc limit $this->limit;";
            $stmt = Application::$app->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        }
        return $this->getData(['txn_status'], true);
    }

    public function getAllByDate($date): object|bool|array
    {
        if(empty($date)) { return false; }
        $this->txn_status = "success";
        $query = "Select * from transactions where txn_status='success' and created_at like '%$date%' order by id desc;";
        if (!empty($this->limit)) {
            $query = "Select * from transactions where txn_status='success' and created_at like '%$date%' order by id desc limit $this->limit;";
        }
        $stmt = Application::$app->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function addOrderTxn(): bool
    {
        return $this->save();
    }


    public static function tableName(): string
    {
        return "transactions";
    }

    public function rules(): array
    {
        if ($this->txn_type == "subscription") {
            return [
                "student_id" => [self::RULE_REQUIRED],
                "txn_amount" => [self::RULE_REQUIRED],
                "txn_type" => [self::RULE_REQUIRED],
                "payment_method" => [self::RULE_REQUIRED],
                "txn_status" => [self::RULE_REQUIRED],
            ];
        }
        if ($this->txn_type == "withdraw") {
            return [
                "txn_type" => [self::RULE_REQUIRED],
                "txn_amount" => [self::RULE_REQUIRED],
                "student_id" => [self::RULE_REQUIRED]
            ];
        }

        return [
            "customer" => [self::RULE_REQUIRED, [self::RULE_EXIST, "class" => self::class]]
        ];
    }

    public function requiredAttributes(): array
    {
        if ($this->txn_type == "subscription") {
            return ["txn_amount", "txn_type", "payment_method", "txn_status", "student_id"];
        }
        if ($this->txn_type == "withdraw") {
            return ["student_id", 'txn_amount', 'txn_type'];
        }
        return [];
    }

    public function labels(): array
    {
        return [
            'txn_status' => 'Txn Status',
            'txn_id' => 'Txn ID',
            'txn_amount' => 'Txn Amount',
            'txn_type' => 'Txn Type',
            'customer' => 'Customer',
            'student_id' => 'Student ID',
            'payment_method' => 'Payment Method',
            'txn_desc' => 'Txn Description'
        ];
    }
}