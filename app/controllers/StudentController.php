<?php

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\models\student\Student;

/**
 * Class StudentController
 * @author Abhishek Kumar Pathak <officialabhishekpathak@gmail.com>
 * @package app\controllers
 */
class StudentController extends Controller
{
    public ?Student $student = null;
    public function __construct()
    {
        self::verifyAuthorization();
        $this->student = new Student(Application::$app->request->getBody());
    }


    public function create_account(): string
    {
        if($data = $this->student->addStudent())
        {
            return self::onSuccess($data);
        }
        return self::onError($this->student->errors);
    }


    public function get_account(): string
    {
        if($data = $this->student->getStudent()){
            return self::onSuccess($data);
        }
        return self::onError($this->student->errors);
    }

    public function get_all_accounts(): string
    {
        if($data = $this->student->getAll()){
            return self::onSuccess($data);
        }elseif (empty($this->student->errors)){
            return $this->onSuccess([]);
        }
        return self::onError($this->student->errors);
    }


    public function update_account(): string
    {
        if($data = $this->student->updateStudent()){
            return self::onSuccess($data);
        }
        return self::onError($this->student->errors);
    }


    public function delete_account(): string
    {
        if($data = $this->student->deleteStudent()){
            return self::onSuccess($data);
        }
        return self::onError($this->student->errors);
    }
}