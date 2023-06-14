<?php

namespace app\models\wallet;

use app\core\Application;
use app\core\db\DbModel;
use app\includes\Utils;
use app\models\attendance\Attendance;
use app\models\student\Student;
use PDO;

/**
 * Class Order
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\models\wallet
 */
class Order extends DbModel
{
    public ?int $student_id = null;

    public ?float $txn_amount = 0;
    public ?string $service_date = null; // yyyy-mm-dd
    public null|string|array $order_type = null; // regular: breakfast / lunch / dinner,  check below for unregular
    public ?string $payment_method = "wallet";
    public ?string $customer = null; // regular or phone no.
    public ?string $txn_id = null;
    public ?int $order_id = null;
    public ?string $request_type = null;

    public ?int $limit = null;

    public function removeGarbage(): void
    {
        $this->attributes = array_diff($this->attributes, ["request_type", "limit", "student_id"]);
    }

    // order types
    public array $unregular_fee_details_one_day = [
        "FDV" => [
            "name" => "Full Day Veg",
            "amount" => 120,
        ],
        "FDNV" => [
            "name" => "Full Day Non Veg",
            "amount" => 140,
        ],
    ];

    public array $unregular_fee_details = [
        "B" => [
            "name" => "Breakfast",
            "amount" => 25
        ],
        "LV" => [
            "name" => "Lunch Veg",
            "amount" => 50,
        ],
        "LNV" => [
            "name" => "Lunch Non Veg",
            "amount" => 70,
        ],
        "DV" => [
            "name" => "Dinner Veg",
            "amount" => 50,
        ],
        "DVP" => [
            "name" => "Dinner Veg Paneer",
            "amount" => 70,
        ],
        "DNV" => [
            "name" => "Dinner Non Veg",
            "amount" => 70,
        ],
    ];


    public array $regular_fee_details = [
        "ALL" => [
            "name" => "Breakfast + Lunch + Dinner",
            "amount" => 100
        ],
        "BL" => [
            "name" => "Breakfast + Lunch",
            "amount" => 73.34,
        ],
        "BD" => [
            "name" => "Breakfast + Dinner",
            "amount" => 73.34,
        ],
        "LD" => [
            "name" => "Lunch + Dinner",
            "amount" => 90,
        ],
    ];


    public function __construct($body)
    {
        $this->loadData($body);
    }


    public function create(): object|bool|array
    {
        if (!$this->validate()) {
            return false;
        }
        $this->initOrder();

        // regular - student
        if ($this->customer == "regular") {
            $is_already_paid = $this->isAttendance();
            $walletModel = new Wallet(["student_id" => $this->student_id]);
            $meal_type = $walletModel->get_meal_type();
            if (!$this->regular_has_order_type($meal_type)) {
                return false;
            }
            if ($is_already_paid) {
                $prevAttendance = (new Attendance(["student_id" => $this->student_id, "date" => $this->service_date]))->getAttendance();
                if($prevAttendance && $prevAttendance->{$this->order_type} == "P") {
                    $this->addError("message", "Already Served");
                    return false;
                }
                // only mark attendance
                $attendanceBody = [
                    "student_id" => "$this->student_id",
                    "date" => $this->service_date,
                    "$this->order_type" => "P"
                ];
            } else {
                // get wallet
                $subscription = $walletModel->validateSubscription();
                if (!$subscription) {
                    $this->addError("message", "Subscription Expired");
                    return false;
                }
                // get cost
                if ($meal_type) {
                    $this->txn_id = "OR" . $meal_type . date("Ymd", strtotime($this->service_date)) . Utils::random_capitalCase_str(8);
                    $this->txn_amount = $this->regular_fee_details[$meal_type]["amount"] ?? null;
                    if (empty($this->txn_amount)) {
                        $this->addError("message", "Invalid Meal Type");
                        return false;
                    }
                    $this->hasPrevOrders($walletModel, $meal_type);
                } else {
                    $this->errors = $this->errors + $walletModel->errors;
                    return false;
                }
                // deduct balance if available
                if (!($walletModel->deductBalance($this->txn_amount))) {
                    $this->errors = $this->errors + $walletModel->errors;
                    return false;
                }
                // add txn
                $desc = $this->regular_fee_details[$meal_type]["name"];
                $this->addOrderTxn($this->student_id, $desc);
                $attendanceBody = [
                    "student_id" => $this->student_id,
                    "amount" => $this->txn_amount,
                    "date" => $this->service_date,
                    "$this->order_type" => "P"
                ];
            }
            $this->setStudentAsCustomer();
            (new Attendance($attendanceBody))->mark();
            if (!$is_already_paid) {
                // change the order type
                $this->order_type = $meal_type;
                $this->save();
            }
            return $this->getData(["customer", "service_date"]);
        } else {
            // unregular
            // check if full order
            if ($data = $this->getData(["customer", "service_date", "order_type"])) {
                $is_full_day_order = $this->unregular_fee_details_one_day[$data->order_type] ?? false;
                if ($is_full_day_order) {
                    return $data;
                }
            }

            $this->txn_amount = $this->unregular_fee_details[$this->order_type]["amount"] ?? $this->unregular_fee_details_one_day[$this->order_type]["amount"];
            $this->txn_id = "OU" . $this->order_type . date("Ymd", strtotime($this->service_date)) . Utils::random_capitalCase_str(8);
            // add txn
            $desc = $this->unregular_fee_details[$this->order_type]["name"] ?? $this->unregular_fee_details_one_day[$this->order_type]["name"];
            $this->addOrderTxn($this->customer, $desc);
            return $this->save() ? $this->getData(["customer", "service_date", "order_type"]) : false;
        }
    }


    private function addOrderTxn($customer, $desc){
        $txnBody = [
            "payment_method" => "$this->payment_method",
            "txn_desc" => $desc,
            "txn_type" => "order",
            "txn_status" => "success",
            "txn_amount" => "$this->txn_amount",
            "customer" => "$customer",
            "txn_id" => "$this->txn_id"
        ];
        (new Transaction($txnBody))->addOrderTxn();
    }


    private function initOrder()
    {
        if (empty($this->service_date)) {
            $this->service_date = date("Y-m-d");
            $this->attributes[] = "service_date";
        }
        array_push($this->attributes, "txn_id", "txn_amount");
    }

    public function setStudentAsCustomer()
    {
        $this->customer = "$this->student_id";
        //  no need add in attrs: regular if std, phone if unreg
    }


    public function isAttendance(): bool|string
    {
        $body = [
            "student_id" => $this->student_id,
            "date" => $this->service_date
        ];
        $attendanceModal = new Attendance($body);
        $data = $attendanceModal->getAttendance();
        if ($data) {
            if (empty($data->amount)) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function regular_has_order_type($meal_type): bool
    {
        if (!$meal_type) {
            $this->addError("message", "Unable to retrieve Meal Type");
        }
        if (str_contains(strtolower($this->regular_fee_details[$meal_type]["name"]), strtolower($this->order_type))) {
            return true;
        }
        $this->addError("message", "Your meal type does not contains $this->order_type");
        return false;
    }


    private function prevOrderCreate($walletModel, $prevDate, $userData, $meal_type)
    {
        $created_at = strtotime(date("Y-m-d", strtotime($userData->created_at)));
        $prevDate_unix = strtotime($prevDate);
        if ($created_at < $prevDate_unix) {
            // deduct if balance
            if ($walletModel->deductBalance($this->txn_amount)) {
                $txn_id = "ORNT" . $meal_type . date("Ymd", strtotime($prevDate)) . Utils::random_capitalCase_str(8);
                $txnBody = [
                    "payment_method" => "$this->payment_method",
                    "txn_desc" => $this->regular_fee_details[$meal_type]["name"],
                    "txn_type" => "order",
                    "txn_status" => "success",
                    "txn_amount" => "$this->txn_amount",
                    "student_id" => "$this->student_id",
                    "txn_id" => "$txn_id"
                ];
                (new Transaction($txnBody))->addOrderTxn();
                $attendanceBody = [
                    "student_id" => "$this->student_id",
                    "amount" => $this->txn_amount,
                    "date" => $prevDate,
                ];
                (new Attendance($attendanceBody))->mark();
            }
            // not deducted
        }
    }


    public function hasPrevOrders($walletModel, $meal_type): bool
    {
        $prevDate1 = date("Y-m-d", strtotime("-1 day"));
        $prevDate2 = date("Y-m-d", strtotime("-2 days"));
        $prevDate3 = date("Y-m-d", strtotime("-3 days"));

        $prevDate1Data = self::getDataByValue(self::class, ["customer" => "$this->student_id", "service_date" => "$prevDate1"]);
        $prevDate2Data = self::getDataByValue(self::class, ["customer" => "$this->student_id", "service_date" => "$prevDate2"]);
        $prevDate3Data = self::getDataByValue(self::class, ["customer" => "$this->student_id", "service_date" => "$prevDate3"]);

        if (empty($prevDate1Data) && empty($prevDate2Data) && empty($prevDate3Data)) {
            return false;
        }

        $userData = self::getDataByValue(Student::class, ["student_id" => "$this->student_id"]);
        if (empty($prevDate1Data) && !empty($prevDate2Data)) {
            $this->prevOrderCreate($walletModel, $prevDate1, $userData, $meal_type);
        }

        if (empty($prevDate1Data) && empty($prevDate2Data) && !empty($prevDate3Data)) {
            $this->prevOrderCreate($walletModel, $prevDate1, $userData, $meal_type);
            $this->prevOrderCreate($walletModel, $prevDate2, $userData, $meal_type);
        }
        return true;
    }


    // get order
    public function getOrder(): object|bool|array
    {
        if (!$this->validate()) {
            return false;
        }
        if (!empty($this->order_id)) {
            return $this->getData(["order_id"]);
        }
        if (!empty($this->customer) && !empty($this->service_date)) {
            return $this->getData(["customer", "service_date"], true);
        }
        return $this->getData(["customer"], true);
    }

    // get all orders
    public function getAll(): object|bool|array
    {
        if (!empty($this->limit)) {
            $query = "Select * from orders order by order_id desc limit $this->limit;";
            $stmt = Application::$app->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        }

        return $this->getData([], true);
    }

    public function getAllByDate($date): object|bool|array
    {
        if(empty($date)) { return false; }
        $query = "Select * from orders where service_date like '%$date%' order by order_id desc;";
        if (!empty($this->limit)) {
            $query = "Select * from orders where service_date like '%$date%' order by order_id desc limit $this->limit;";
        }
        $stmt = Application::$app->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }




    public static function tableName(): string
    {
        return "orders";
    }

    public function rules(): array
    {
        return match ($this->request_type) {
            "get" => [
                "customer" => empty($this->order_id) ? [self::RULE_REQUIRED, [self::RULE_EXIST, "class" => self::class]] : null,
                "order_id" => empty($this->customer) ? [self::RULE_REQUIRED, [self::RULE_EXIST, "class" => self::class]] : null,
            ],
            'post' => [
                "customer" => $this->customer != "regular" ?
                    [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 10], [self::RULE_MAX, 'max' => 10]]
                    : null,
                "order_type" => [self::RULE_REQUIRED],
                "student_id" => $this->customer == "regular" ?
                    [self::RULE_REQUIRED, [self::RULE_EXIST, "class" => Student::class]]
                    : null
            ]
        };
    }

    public function requiredAttributes(): array
    {
        return [];
    }

    public function labels(): array
    {
        return [
            'order_id' => 'Order ID',
            'txn_id' => 'Txn ID',
            'txn_amount' => 'Txn Amount',
            'order_type' => 'Order Type',
            'student_id' => 'Student ID',
            'customer' => 'Customer',
            'payment_method' => 'Payment Method',
            'service_date' => 'Service ID',
            'admin_id' => 'Admin ID'
        ];
    }


}