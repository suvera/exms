<?php

declare(strict_types=1);

namespace dev\suvera\exms\admin\service;

use dev\suvera\exms\admin\data\SubjectCreateForm;
use dev\suvera\exms\data\entity\Subject;
use dev\winterframework\exception\HttpRestException;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\Service;
use dev\winterframework\web\http\HttpStatus;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

#[Service]
class SubjectService {

    #[Autowired]
    private EntityManager $em;

    public function create(SubjectCreateForm $form): Subject {
        $subject = new Subject();

        if ($this->em->getRepository(Subject::class)->findOneByName($form->name) !== null) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Subject already exists');
        }

        $subject->name = $form->name;
        $this->em->persist($subject);
        $this->em->flush();
        return $subject;
    }

    public function update(int $id, SubjectCreateForm $form): Subject {
        $subject = $this->em->getRepository(Subject::class)->findOneById($id);

        if ($subject === null) {
            throw new HttpRestException(HttpStatus::$NOT_FOUND, 'Subject not found');
        }

        $query = $this->em->createQuery("SELECT c FROM dev\\suvera\\exms\\data\\entity\\Subject c WHERE c.id != ?1 AND c.name = ?2 ");
        $query->setParameter(1, $id);
        $query->setParameter(2, $form->name);

        $result = $query->getResult();
        if (count($result) > 0) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Subject already exists with sane name');
        }

        $subject->name = $form->name;
        $this->em->persist($subject);
        $this->em->flush();
        return $subject;
    }

    public function getOne(int $id): Subject {
        $subject = $this->em->getRepository(Subject::class)->findOneById($id);
        if ($subject === null) {
            throw new HttpRestException(HttpStatus::$NOT_FOUND, 'Subject not found');
        }
        return $subject;
    }

    public function getAll(): array {
        return $this->em->getRepository(Subject::class)->findAll();
    }

    public function getList(int $offset, int $limit): Paginator {

        if ($offset < 0) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Offset must be greater than zero');
        }

        if ($limit < 1) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Limit must be greater than zero');
        }

        $dql = "SELECT c FROM dev\\suvera\\exms\\data\\entity\\Subject c ORDER BY c.id ASC";

        $query = $this->em->createQuery($dql)
            ->setHint(Paginator::HINT_ENABLE_DISTINCT, false)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $paginator = new Paginator($query, fetchJoinCollection: false);
        return $paginator;
    }
}
