<?php

declare(strict_types=1);

namespace dev\suvera\exms\student\service;

use dev\suvera\exms\data\entity\Student;
use dev\suvera\exms\data\entity\StudentClass;
use dev\winterframework\stereotype\Service;
use dev\winterframework\stereotype\Autowired;
use Doctrine\ORM\EntityManager;

#[Service]
class StudentContext {

    #[Autowired]
    private EntityManager $em;

    public function getStudent(): Student {
        $student = new Student();
        $student->__unserialize($_SESSION['student']);

        return $student;
    }

    public function getStudentId(): int {
        return $_SESSION['student']['id'];
    }

    public function getClassIds(): array {
        $studentClasses = $this->em->getRepository(StudentClass::class)->findBy(['studentId' => $this->getStudentId()]);
        $classIds = [];
        foreach ($studentClasses as $studentClass) {
            $classIds[] = $studentClass->classId;
        }

        return $classIds;
    }
}
