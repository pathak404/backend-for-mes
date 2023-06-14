<?php

namespace app\models\wallet;

use app\core\db\DbModel;
use app\models\student\Student;

/**
 * Class Wallet
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\models\wallet
 */
class Wallet extends DbModel
{
    public ?int $student_id = null;
    protected float $balance = 0.00;
    public ?string $meal_type = null;

    public function __construct($body)
    {
        $this->loadData($body);
    }


    /**
     * @param $amount
     * @return bool
     */
    public function addBalance($amount): bool
    {
        $wallet = $this->getBalance();
        if (!$wallet) {
            $this->addError("message", "Unable to retrieve student wallet");
            return false;
        }
        $this->balance = number_format((float)($wallet->balance + $amount), 2, '.', '');
        return $this->updateBalance();
    }


    /**
     * @param $amount
     * @return bool
     */
    public function deductBalance($amount): bool
    {
        $wallet = $this->getBalance();
        if (!$wallet) {
            $this->addError("message", "Unable to retrieve student wallet");
            return false;
        }
        if ($amount > $wallet->balance) {
            $this->addError("message", "Wallet balance is low");
            return false;
        }
        $this->balance = number_format((float)($wallet->balance - $amount), 2, '.', '');
        return $this->updateBalance();

    }


    public function getBalance(): object|bool
    {
        return $this->getData(["student_id"]);
    }

    public function getWallet(): object|bool
    {
        if($this->validate()){
            $this->validateSubscription();
            return $this->getData(["student_id"]);
        }
        return false;
    }

    public function getAll(): object|bool|array
    {
        return $this->getData([], true);
    }

    public function createWallet(): bool
    {
        $wallet = $this->getBalance();
        if (!$wallet) {
            return $this->save();
        }
        return false;
    }


    public function updateBalance(): bool
    {
        $this->attributes[] = "balance";
        return $this->update(["student_id"]);
    }

    public function addSubscription($days): bool
    {
        $walletData = $this->getBalance();
        if (empty($walletData)) {
            $this->addError("message", "Unable to retrieve student wallet");
            return false;
        }

        if (empty($walletData->s_validity)) {
            $time = strtotime(date("Y-m-d"));
        } else {
            $time = strtotime($walletData->s_validity);
        }
        $ExpDate = date("Y-m-d", strtotime($days, $time));
        return $this->updateByFields(["subscription" => 1, "s_validity" => $ExpDate], ["student_id"]);
    }


    public function validateSubscription(): bool
    {
        $walletData = $this->getBalance();
        if (empty($walletData)) {
            $this->addError("message", "Unable to retrieve student wallet");
            return false;
        }
        if ($walletData->subscription) {
            if (strtotime("$walletData->s_validity 23:58:00") < strtotime("now")) {
                $this->updateByFields(["subscription" => 0, "s_validity" => null], ["student_id"]);
                return false;
            }
            return true;
        }
        return false;
    }


    public function get_meal_type(): bool|string
    {
        $walletData = $this->getBalance();
        if (empty($walletData)) {
            $this->addError("message", "Unable to retrieve student wallet");
            return false;
        }
        return $walletData->meal_type;
    }

    public function update_meal_type(): bool|string
    {
        return $this->update(['student_id']);
    }

    public static function tableName(): string
    {
        return "wallet";
    }

    public function rules(): array
    {
        return [
            "student_id" => [self::RULE_REQUIRED, [self::RULE_EXIST, "class" => Student::class]]
        ];
    }

    public function requiredAttributes(): array
    {
        return [];
    }

    public function labels(): array
    {
        return [
            'student_id' => 'Student ID',
        ];
    }
}