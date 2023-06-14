<?php

namespace app\models\attendance;

use app\core\Application;
use app\core\db\DbModel;
use app\models\student\Student;
use PDO;

/**
 * Class Attendance
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\models\attendance
 */
class Attendance extends DbModel
{
    public string $breakfast = 'NP';
    public string $lunch = 'NP';
    public string $dinner = 'NP';
    public ?string $date = null; // yyyy-mm-dd
    public float $amount = 00.00;
    public ?int $student_id = null;

    public ?int $month = null;
    public ?int $year = null;


    public function __construct($body)
    {
        $this->loadData($body);
    }

    public static function tableName(): string
    {
        return "attendance";
    }


    public function removeGarbage(): void
    {
        $this->attributes = array_diff($this->attributes, ["request_type", "month", "year"]);
    }



    public function getAttendance(): object|bool|array
    {
        if( empty($this->student_id) ){
            $this->addError("message", "please provide Student ID");
            return false;
        }
        // check student_id exist or not
        if(!self::getDataByValue(Student::class, ["student_id" => $this->student_id])){
            $this->addError("message", "Invalid Student ID");
            return false;
        }


        if(!empty($this->date)){
            return $this->getData(["student_id", "date"]);
        }
        if(!empty($this->month)){
            return $this->getAttendanceByMonthYear();
        }
        $this->addError("message", "Please provide date(yyyy-mm-dd) /month (1-12) / year (YYYY). month and year also acceptable");
        return false;
    }


    public function getAttendanceByMonthYear(): bool|array
    {
        if(empty($this->year)){
            $this->year = date("Y");
        }
        $query = "SELECT * FROM attendance WHERE student_id=$this->student_id AND MONTH(date) = $this->month AND YEAR(date) = $this->year;";
        /** @var $query */
        $statement = Application::$app->db->prepare($query);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_OBJ);
    }





    public function mark(): bool
    {
        $getCurrent = $this->getData(["student_id", "date"]);
        if ($getCurrent) {
            $this->amount = $getCurrent->amount + $this->amount;
            return $this->update(["student_id", "date"]);
        }
        return $this->save();
    }




    public function rules(): array
    {
        return [];
    }

    public function requiredAttributes(): array
    {
        return [];
    }
    public function labels(): array
    {
        return [];
    }
}