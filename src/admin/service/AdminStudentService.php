<?php

declare(strict_types=1);

namespace dev\suvera\exms\admin\service;

use dev\suvera\exms\admin\data\StudentCreateForm;
use dev\suvera\exms\data\ClassData;
use dev\suvera\exms\data\entity\Student;
use dev\suvera\exms\data\entity\StudentClass;
use dev\winterframework\exception\HttpRestException;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\Service;
use dev\winterframework\web\http\HttpStatus;
use Doctrine\ORM\EntityManager;

#[Service]
class AdminStudentService {

    #[Autowired]
    private EntityManager $em;

    public function createStudent(StudentCreateForm $form): Student {
        $student = new Student();

        if ($this->em->getRepository(Student::class)->findOneByUsername($form->username) !== null) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Username already exists');
        }
        if ($form->classes) {
            foreach ($form->classes as $class) {
                if (!ClassData::hasClass($class)) {
                    throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Invalid class ' . htmlentities($class));
                }
            }
        }

        $student->name = $form->name;
        $student->username = $form->username;
        $student->email = $form->email;
        $student->password = password_hash($form->password, PASSWORD_DEFAULT);
        $this->em->persist($student);

        if ($form->classes) {
            foreach ($form->classes as $class) {
                $studentClass = new StudentClass();
                $studentClass->studentId = $student->id;
                $studentClass->classId = $class;
                $this->em->persist($studentClass);
            }
        }

        $this->em->flush();
        return $student;
    }
}
