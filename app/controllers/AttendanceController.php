<?php

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\models\attendance\Attendance;

/**
 * Class AttendanceController
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\controllers
 */
class AttendanceController extends Controller
{
    public ?Attendance $attendance = null;
    public function __construct()
    {
        self::verifyAuthorization();
        $this->attendance = new Attendance(Application::$app->request->getBody());
    }


    public function getAttendance(): string
    {
        if($data = $this->attendance->getAttendance()) {
            return self::onSuccess($data);
        }else if (empty($this->attendance->errors)){
            return self::onSuccess([]);
        }
        return self::onError($this->attendance->errors);
    }




}