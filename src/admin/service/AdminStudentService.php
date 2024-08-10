<?php

declare(strict_types=1);

namespace dev\suvera\exms\admin\service;

use dev\suvera\exms\admin\data\StudentCreateForm;
use dev\suvera\exms\admin\data\StudentUpdateForm;
use dev\suvera\exms\data\ClassData;
use dev\suvera\exms\data\entity\Student;
use dev\suvera\exms\data\entity\StudentClass;
use dev\suvera\exms\utils\Utility;
use dev\winterframework\exception\HttpRestException;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\Service;
use dev\winterframework\web\http\HttpStatus;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

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

        $form->password = trim($form->password);
        if (empty($form->password)) {
            $form->password = Utility::generateStrongPassword(12);
        }

        $student->name = $form->name;
        $student->username = $form->username;
        $student->email = $form->email;
        $student->password = password_hash($form->password, PASSWORD_DEFAULT);
        $this->em->persist($student);

        if ($form->classes) {
            foreach ($form->classes as $class) {
                $studentClass = new StudentClass();
                $studentClass->student = $student;
                $studentClass->classId = $class;
                $this->em->persist($studentClass);
            }
        }

        $this->em->flush();
        return $student;
    }

    public function updateStudent(int $id, StudentUpdateForm $form): ?Student {
        $student = $this->em->getRepository(Student::class)->findOneById($id);
        if ($student === null) {
            throw new HttpRestException(HttpStatus::$NOT_FOUND, 'Student not found');
        }

        foreach ($form->classes as $class) {
            if (!ClassData::hasClass($class)) {
                throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Invalid class ' . htmlentities($class));
            }
        }

        $student->name = $form->name;
        $student->email = $form->email;
        $this->em->persist($student);

        $this->em->createQuery('DELETE FROM dev\suvera\exms\data\entity\StudentClass c WHERE c.studentId = :student_id')
            ->setParameter('student_id', $student->id)
            ->execute();

        foreach ($form->classes as $class) {
            $studentClass = new StudentClass();
            $studentClass->student = $student;
            $studentClass->classId = $class;
            $this->em->persist($studentClass);
        }

        $this->em->flush();
        return $student;
    }

    public function getOne(int $id): Student {
        $subject = $this->em->getRepository(Student::class)->findOneById($id);
        if ($subject === null) {
            throw new HttpRestException(HttpStatus::$NOT_FOUND, 'Student not found');
        }
        return $subject;
    }

    public function getList(int $offset, int $limit, string $search = ''): Paginator {

        if ($offset < 0) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Offset must be greater than zero');
        }

        if ($limit < 1) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Limit must be greater than zero');
        }

        $dql = "SELECT c FROM dev\\suvera\\exms\\data\\entity\\Student c ";
        if (!empty($search)) {
            $dql .= " WHERE c.name LIKE :search ";
        }
        $dql .=  ' ORDER BY c.id DESC';

        $query = $this->em->createQuery($dql)
            ->setHint(Paginator::HINT_ENABLE_DISTINCT, false)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if (!empty($search)) {
            $query->setParameter('search', '%' . addcslashes($search, '%_') . '%');
        }

        $paginator = new Paginator($query, fetchJoinCollection: false);
        return $paginator;
    }
}
