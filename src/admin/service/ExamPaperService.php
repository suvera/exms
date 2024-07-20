<?php

declare(strict_types=1);

namespace dev\suvera\exms\admin\service;

use dev\suvera\exms\admin\data\ExamPaperCreateForm;
use dev\suvera\exms\admin\data\ExamQuestionsForm;
use dev\suvera\exms\data\ClassData;
use dev\suvera\exms\data\entity\ExamPaper;
use dev\suvera\exms\data\entity\ExamPaperClass;
use dev\suvera\exms\data\entity\ExamPaperQuestion;
use dev\suvera\exms\data\entity\Subject;
use dev\suvera\exms\data\ExamPaperStatus;
use dev\suvera\exms\utils\MyCollection;
use dev\winterframework\exception\HttpRestException;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\Service;
use dev\winterframework\txn\stereotype\Transactional;
use dev\winterframework\web\http\HttpStatus;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

#[Service]
class ExamPaperService {

    #[Autowired]
    private EntityManager $em;

    public function create(ExamPaperCreateForm $form): ExamPaper {
        $examPaper = new ExamPaper();

        if ($this->em->getRepository(ExamPaper::class)->findOneByName($form->name) !== null) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'ExamPaper already exists');
        }

        $subject = $this->em->getRepository(Subject::class)->findOneById($form->subjectId);
        if ($subject === null) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Invalid subject_id');
        }

        if ($form->classes) {
            foreach ($form->classes as $class) {
                if (!ClassData::hasClass($class)) {
                    throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Invalid class ' . htmlentities($class));
                }
            }
        }

        $examPaper->name = $form->name;
        $examPaper->subjectId = $form->subjectId;
        $examPaper->subject = $subject;
        $this->em->persist($examPaper);

        if ($form->classes) {
            foreach ($form->classes as $class) {
                $cls = new ExamPaperClass();
                //$cls->examPaperId = $examPaper->id;
                $cls->classId = $class;
                $cls->examPaper = $examPaper;
                $this->em->persist($cls);
            }
        }

        $this->em->flush();
        return $examPaper;
    }

    public function update(int $id, ExamPaperCreateForm $form): ExamPaper {
        $examPaper = $this->em->getRepository(ExamPaper::class)->findOneById($id);

        if ($examPaper === null) {
            throw new HttpRestException(HttpStatus::$NOT_FOUND, 'ExamPaper not found');
        }

        if ($examPaper->status !== ExamPaperStatus::PREPARING) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'ExamPaper cannot be updated, as it is already freezed');
        }

        if ($form->classes) {
            foreach ($form->classes as $class) {
                if (!ClassData::hasClass($class)) {
                    throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Invalid class ' . htmlentities($class));
                }
            }
        }

        $query = $this->em->createQuery("SELECT c FROM dev\\suvera\\exms\\data\\entity\\ExamPaper c WHERE c.id != ?1 AND c.name = ?2 ");
        $query->setParameter(1, $id);
        $query->setParameter(2, $form->name);

        $result = $query->getResult();
        if (count($result) > 0) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'ExamPaper already exists with sane name');
        }

        if ($form->subjectId > 0) {
            $subject = $this->em->getRepository(Subject::class)->findOneById($form->subjectId);
            if ($subject === null) {
                throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Invalid subject_id');
            }
            $examPaper->subjectId = $form->subjectId;
            $examPaper->subject = $subject;
        }

        if ($form->classes) {
            foreach ($examPaper->classes as $cls) {
                $this->em->remove($cls);
            }
            foreach ($form->classes as $class) {
                $cls = new ExamPaperClass();
                $cls->examPaperId = $examPaper->id;
                $cls->classId = $class;
                $cls->examPaper = $examPaper;
                $this->em->persist($cls);
            }
        }

        $examPaper->name = $form->name;
        $this->em->persist($examPaper);

        $this->em->flush();
        return $examPaper;
    }

    public function getOne(int $id): ExamPaper {
        $examPaper = $this->em->getRepository(ExamPaper::class)->findOneById($id);
        if ($examPaper === null) {
            throw new HttpRestException(HttpStatus::$NOT_FOUND, 'ExamPaper not found');
        }
        $examPaper->classes = new MyCollection($examPaper->classes);
        return $examPaper;
    }

    public function getList(int $offset, int $limit): Paginator {

        if ($offset < 0) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Offset must be greater than zero');
        }

        if ($limit < 1) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Limit must be greater than zero');
        }

        $dql = "SELECT c FROM dev\\suvera\\exms\\data\\entity\\ExamPaper c ORDER BY c.id ASC";

        $query = $this->em->createQuery($dql)
            ->setHint(Paginator::HINT_ENABLE_DISTINCT, false)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $paginator = new Paginator($query, fetchJoinCollection: false);
        return $paginator;
    }

    public function addQuestions(int $paperId, ExamQuestionsForm $form): void {
        /** @var ExamPaper $examPaper */
        $examPaper = $this->em->getRepository(ExamPaper::class)->findOneById($paperId);
        if ($examPaper === null) {
            throw new HttpRestException(HttpStatus::$NOT_FOUND, 'ExamPaper not found');
        }
        if ($examPaper->status !== ExamPaperStatus::PREPARING) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'ExamPaper cannot be updated, as it is already freezed');
        }

        $totalTimeSecs = 0;
        foreach ($form->questions as $question) {
            $q = new ExamPaperQuestion();
            $q->examPaperId = $paperId;
            $q->examPaper = $examPaper;
            $q->question = $question->question;
            $q->timeSecs = $question->timeSeconds;
            $q->answer = $question->answer;
            $q->topic = $question->topic;
            $q->explanation = $question->explanation;
            $q->choiceA = $question->options->choiceA;
            $q->choiceB = $question->options->choiceB;
            $q->choiceC = $question->options->choiceC;
            $q->choiceD = $question->options->choiceD;
            $this->em->persist($q);

            $examPaper->totalQuestions++;
            $totalTimeSecs += $question->timeSeconds / 60;
        }
        $examPaper->totalTimeMins += ceil($totalTimeSecs / 60);
        $this->em->persist($examPaper);
        $this->em->flush();
    }

    public function getOneQuestion(int $paperId, int $id): ExamPaperQuestion {
        $examPaper = $this->em->getRepository(ExamPaper::class)->findOneById($paperId);
        if ($examPaper === null) {
            throw new HttpRestException(HttpStatus::$NOT_FOUND, 'ExamPaper not found');
        }
        if ($examPaper->status !== ExamPaperStatus::PREPARING) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'ExamPaper cannot be updated, as it is already freezed');
        }

        $question = $this->em->getRepository(ExamPaperQuestion::class)->findOneBy(['examPaperId' => $paperId, 'id' => $id]);
        if ($question === null) {
            throw new HttpRestException(HttpStatus::$NOT_FOUND, 'Question not found');
        }
        return $question;
    }

    public function deleteOneQuestion(int $paperId, int $id): void {
        $examPaper = $this->em->getRepository(ExamPaper::class)->findOneById($paperId);
        if ($examPaper === null) {
            throw new HttpRestException(HttpStatus::$NOT_FOUND, 'ExamPaper not found');
        }
        if ($examPaper->status !== ExamPaperStatus::PREPARING) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'ExamPaper cannot be updated, as it is already freezed');
        }

        /** @var ExamPaperQuestion $question */
        $question = $this->em->getRepository(ExamPaperQuestion::class)->findOneBy(['examPaperId' => $paperId, 'id' => $id]);
        if ($question === null) {
            throw new HttpRestException(HttpStatus::$NOT_FOUND, 'Question not found');
        }
        $this->em->remove($question);

        $examPaper->total_questions--;
        $examPaper->total_time_mins -= ceil($question->timeSecs / 60);
        $this->em->persist($examPaper);

        $this->em->flush();
    }

    public function getQuestions(int $paperId, int $offset, int $limit): Paginator {
        $dql = "SELECT c FROM dev\\suvera\\exms\\data\\entity\\ExamPaperQuestion c WHERE c.examPaperId = :paperId ORDER BY c.id ASC";

        $query = $this->em->createQuery($dql)
            ->setParameter('paperId', $paperId)
            ->setHint(Paginator::HINT_ENABLE_DISTINCT, false)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $paginator = new Paginator($query, fetchJoinCollection: false);
        return $paginator;
    }

    #[Transactional()]
    public function freezePaper(int $paperId): void {
        /** @var ExamPaper $examPaper */
        $examPaper = $this->em->getRepository(ExamPaper::class)->findOneById($paperId);
        if ($examPaper === null) {
            throw new HttpRestException(HttpStatus::$NOT_FOUND, 'ExamPaper not found');
        }
        if ($examPaper->status !== ExamPaperStatus::PREPARING) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'ExamPaper cannot be updated, as it is already freezed');
        }

        $classes = [];
        foreach ($examPaper->classes as $cls) {
            /** @var ExamPaperClass $cls */
            $classes[] = $cls->classId;
        }

        $examPaper->totalQuestions = $this->em->getRepository(ExamPaperQuestion::class)->count([
            'examPaperId' => $examPaper->id
        ]);

        $examPaper->totalQuestions = $this->em->getRepository(ExamPaperQuestion::class)->count([
            'examPaperId' => $examPaper->id
        ]);

        $dql = 'SELECT SUM(e.timeSecs) AS time_total FROM dev\\suvera\\exms\\data\\entity\\ExamPaperQuestion e WHERE e.examPaperId = ?1';
        $seconds = $this->em->createQuery($dql)
            ->setParameter(1, $examPaper->id)
            ->getSingleScalarResult();

        $examPaper->totalTimeMins = intval(1 + ceil($seconds / 60));

        $examPaper->status = ExamPaperStatus::FREEZED;
        $this->em->persist($examPaper);

        // $studentClasses = $this->em->getRepository(StudentClass::class)->findBy(['classId' => $classes]);
        // foreach ($studentClasses as $studentClass) {
        //     /** @var StudentClass $studentClass */
        //     $exam = new StudentExam();
        //     $exam->studentId = $studentClass->studentId;
        //     $exam->examPaperId = $paperId;
        //     $exam->totalQuestions = $examPaper->totalQuestions;
        //     $exam->totalTimeMins = $examPaper->totalTimeMins;
        //     $exam->score = 0;
        //     $this->em->persist($exam);
        // }

        $this->em->flush();
    }
}
