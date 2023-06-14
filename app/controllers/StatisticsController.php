<?php

namespace app\controllers;

use app\core\Controller;
use app\models\attendance\Attendance;
use app\models\student\Student;
use app\models\wallet\Order;
use app\models\wallet\Transaction;
use app\models\wallet\Wallet;

/**
 * Class StatisticsController
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\controllers
 */
class StatisticsController extends Controller
{


    public function getCount(): string
    {
        self::verifyAuthorization();
        $today = date("Y-m-d");
        return self::onSuccess(
            [
                'all' => [
                    "students" => $this->CountStudent(),
                    "transactions" => $this->CountTxns(),
                    "meal_served" => $this->CountMealServed()
                ],
                'today' => [
                    "students" => $this->CountStudent($today),
                    "transactions" => $this->CountTxns($today),
                    "meal_served" => $this->mealServedByDate($today)
                ]
            ]
        );
    }


    public function CountMealServed(): int
    {
        $countsByMealType = [
            "B" => 1,
            "LV" => 1,
            "LNV" => 1,
            "DV" => 1,
            "DVP" => 1,
            "DNV" => 1,
            "FDV" => 3,
            "FDNV" => 3,
            "ALL" => 3,
            "BL" => 2,
            "BD" => 2,
            "LD" => 2
        ];

        $orders = (new Order([]))->getAll();
        if (empty($orders)) {
            return 0;
        }

        $count = 0;
        foreach ($orders as $order) {
            $count += $countsByMealType[$order->order_type];
        }
        return $count;
    }


    public function mealServedByDate($date) : bool|array
    {
        $totalBreakfast = 0;
        $totalLunch = 0;
        $totalDinner = 0;

        $wallets = (new Wallet([]))->getAll();

        if (empty($date) || empty($wallets)) {
            return false;
        }
        foreach ($wallets as $wallet) {
            if($wallet->meal_type == "ALL") {
                $totalBreakfast += 1;
                $totalLunch += 1;
                $totalDinner += 1;
            }elseif ($wallet->meal_type == "BL"){
                $totalBreakfast += 1;
                $totalLunch += 1;
            }elseif ($wallet->meal_type == "BD"){
                $totalBreakfast += 1;
                $totalDinner += 1;
            }elseif ($wallet->meal_type == "LD"){
                $totalLunch += 1;
                $totalDinner += 1;
            }
        }
        // calc remaining
        $breakfastServed = 0;
        $lunchServed = 0;
        $dinnerServed = 0;

        $orders = (new Order([]))->getAllByDate($date);
        if (empty($orders)) {
            $orders = [];
        }
        foreach ($orders as $order) {
            // get attendance
            if(str_contains($order->txn_id, "OU")){ continue; }
            $attendance = (new Attendance(["student_id" => $order->customer, "date" => $date]))->getAttendance();
            if(empty($attendance)){ continue; }
            if($order->order_type == "ALL") {
                if($attendance->breakfast == "P"){
                    $breakfastServed += 1;
                }elseif ($attendance->lunch == "P"){
                    $lunchServed += 1;
                }elseif ($attendance->dinner == "P"){
                    $dinnerServed += 1;
                }
            }elseif ($order->order_type == "BL"){
                if($attendance->breakfast == "P"){
                    $breakfastServed += 1;
                }elseif ($attendance->lunch == "P"){
                    $lunchServed += 1;
                }
            }elseif ($order->order_type == "BD"){
                if($attendance->breakfast == "P"){
                    $breakfastServed += 1;
                }elseif ($attendance->dinner == "P"){
                    $dinnerServed += 1;
                }
            }elseif ($order->order_type == "LD"){
                if ($attendance->lunch == "P"){
                    $lunchServed += 1;
                }elseif ($attendance->dinner == "P"){
                    $dinnerServed += 1;
                }
            }
        }

        $remainingBreakfast = $totalBreakfast - $breakfastServed;
        $remainingLunch = $totalLunch - $lunchServed;
        $remainingDinner = $totalDinner - $dinnerServed;

        return [
            "breakfast" => "$remainingBreakfast / $totalBreakfast",
            "lunch" => "$remainingLunch / $totalLunch",
            "dinner" => "$remainingDinner / $totalDinner",
        ];
    }

    public function CountStudent($date=null): int
    {
        return $date != null ? count((new Student([]))->getAllByDate($date)) : count((new Student([]))->getAll());
    }

    public function CountTxns($date=null): int
    {
        return $date != null ? count((new Transaction([]))->getAllByDate($date)) :  count((new Transaction([]))->getAll());
    }




}