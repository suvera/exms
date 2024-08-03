<?php

declare(strict_types=1);

namespace dev\suvera\exms\student\service;

use dev\suvera\exms\data\entity\ExamPaper;
use dev\suvera\exms\data\entity\ExamPaperQuestion;
use dev\suvera\exms\data\entity\StudentExam;
use dev\suvera\exms\data\entity\StudentExamAnswer;
use dev\suvera\exms\data\ExamPaperStatus;
use dev\suvera\exms\data\StudentExamStatus;
use dev\suvera\exms\utils\ListResponse;
use dev\winterframework\exception\HttpRestException;
use dev\winterframework\stereotype\Service;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\txn\stereotype\Transactional;
use dev\winterframework\web\http\HttpStatus;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManager;

#[Service]
class ExamService {

    #[Autowired]
    private EntityManager $em;

    #[Autowired]
    private StudentContext $studentCtx;

    public function getPendingExams(int $offset, int $limit): ListResponse {

        if ($offset < 0) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Offset must be greater than zero');
        }

        if ($limit < 1) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Limit must be greater than zero');
        }

        $studentClassIds = $this->studentCtx->getClassIds();
        if (empty($studentClassIds)) {
            $studentClassIds[] = '-1';
        }

        $columnsCount = ['count(distinct e.id)'];
        $columns = [
            'e.uuid',
            'e.name',
            'e.subject_id',
            's.name as subject_name',
            'e.total_questions',
            'e.total_time_mins',
            'c.class_id',
            'e.updated_at',
        ];
        $qb = $this->em->getConnection()->createQueryBuilder();
        $qb->select(...$columnsCount)
            ->from('exam_paper', 'e')
            ->innerJoin('e', 'exam_paper_class', 'c', 'e.id = c.exam_paper_id')
            ->innerJoin('e', 'subject', 's', 'e.subject_id = s.id')
            ->where('e.status = :status')->setParameter('status', ExamPaperStatus::FREEZED->value)
            ->andWhere('c.class_id in ( :classes )')->setParameter('classes', $studentClassIds, ArrayParameterType::STRING)
            ->andWhere('e.id not in (select exam_paper_id from student_exam where student_id = :student )')
            ->setParameter('student', $this->studentCtx->getStudentId())
            ->orderBy('e.updated_at', 'DESC');

        // echo $qb->getSQL();
        // print_r($qb->getParameters());
        // exit;
        $count = $qb->executeQuery()->fetchOne();
        if ($count === false) {
            $count = 0;
        }

        $qb->select(...$columns)
            ->distinct(true)
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        $rows = $qb->executeQuery()->fetchAllAssociative();

        $resp = new ListResponse();
        $resp->count = $count;
        $resp->data = $rows;
        $resp->limit = $limit;
        $resp->offset = $offset;

        return $resp;
    }

    public function getCompletedExams(int $offset, int $limit): ListResponse {

        if ($offset < 0) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Offset must be greater than zero');
        }

        if ($limit < 1) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Limit must be greater than zero');
        }

        $columnsCount = ['count(distinct e.id)'];
        $columns = [
            'e.uuid',
            'c.name',
            'c.subject_id',
            's.name as subject_name',
            'e.total_questions',
            'e.total_time_mins',
            'e.status',
            'e.score',
            'e.start_time',
            'e.end_time',
            'e.updated_at',
        ];
        $qb = $this->em->getConnection()->createQueryBuilder();
        $qb->select(...$columnsCount)
            ->from('student_exam', 'e')
            ->innerJoin('e', 'exam_paper', 'c', 'e.exam_paper_id = c.id')
            ->innerJoin('c', 'subject', 's', 'c.subject_id = s.id')
            ->andWhere('e.student_id = :student')
            ->setParameter('student', $this->studentCtx->getStudentId())
            ->orderBy('e.updated_at', 'DESC');

        // echo $qb->getSQL();
        // print_r($qb->getParameters());
        // exit;
        $count = $qb->executeQuery()->fetchOne();
        if ($count === false) {
            $count = 0;
        }

        $qb->select(...$columns)
            ->distinct(true)
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        $rows = $qb->executeQuery()->fetchAllAssociative();

        $resp = new ListResponse();
        $resp->count = $count;
        $resp->data = $rows;
        $resp->limit = $limit;
        $resp->offset = $offset;

        return $resp;
    }

    #[Transactional]
    public function beginExam(string $examUuid): StudentExam {
        /** @var ExamPaper $examPaper */
        $examPaper = $this->em->getRepository(ExamPaper::class)->findOneBy([
            'uuid' => $examUuid,
            'status' => ExamPaperStatus::FREEZED
        ]);
        if ($examPaper === null) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'ExamPaper does not exist');
        }

        $studentExam = $this->em->getRepository(StudentExam::class)->findOneBy([
            'examPaperId' => $examPaper->id,
            'studentId' => $this->studentCtx->getStudentId(),
        ]);
        if ($studentExam !== null) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Exam is already in ' . $studentExam->status->value . ' state');
        }

        $studentExam = new StudentExam();
        $studentExam->studentId = $this->studentCtx->getStudentId();
        $studentExam->examPaperId = $examPaper->id;
        $studentExam->status = StudentExamStatus::IN_PROGRESS;
        $studentExam->score = 0;
        $studentExam->totalQuestions = $examPaper->totalQuestions;
        $studentExam->totalTimeMins = $examPaper->totalTimeMins;
        if ($studentExam->totalTimeMins <= 0) {
            $studentExam->totalTimeMins = 60;
        }
        $studentExam->startTime = (new \DateTime('now'))->add(new \DateInterval('PT30S'));
        $studentExam->endTime = $studentExam->startTime->add(new \DateInterval('PT' . $studentExam->totalTimeMins . 'M'));

        $this->em->persist($studentExam);
        //$this->em->flush();

        return $studentExam;
    }

    protected function doFinishExam(StudentExam $studentExam): void {
        $studentExam->status = StudentExamStatus::COMPLETED;
        $studentExam->score = $this->em->getRepository(StudentExamAnswer::class)->count([
            'studentExamId' => $studentExam->id,
            'isCorrect' => true
        ]);
        $this->em->persist($studentExam);
        $this->em->flush();
    }

    public function finishExam(string $examUuid): StudentExam {
        $studentExam = $this->em->getRepository(StudentExam::class)->findOneBy([
            'uuid' => $examUuid,
            'studentId' => $this->studentCtx->getStudentId()
        ]);
        if ($studentExam === null) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Exam does not exist');
        }

        if ($studentExam->status != StudentExamStatus::COMPLETED) {
            $this->doFinishExam($studentExam);
        }

        return $studentExam;
    }

    public function getExam(string $examUuid): mixed {
        $studentExam = $this->em->getRepository(StudentExam::class)->findOneBy([
            'uuid' => $examUuid,
            'studentId' => $this->studentCtx->getStudentId()
        ]);
        if ($studentExam === null) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Exam does not exist');
        }

        if ($studentExam->endTime < (new \DateTime('now'))) {
            if ($studentExam->status != StudentExamStatus::COMPLETED) {
                $this->doFinishExam($studentExam);
            }
        }

        /** @var ExamPaper $examPaper */
        $examPaper = $this->em->getRepository(ExamPaper::class)->findOneBy([
            'id' => $studentExam->examPaperId
        ]);
        if ($examPaper === null) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'ExamPaper does not exist');
        }

        $questions = $this->em->getRepository(ExamPaperQuestion::class)->findBy([
            'examPaperId' => $examPaper->id
        ]);

        $answers = $this->em->getRepository(StudentExamAnswer::class)->findBy([
            'studentExamId' => $studentExam->id
        ]);

        $answersMap = [];
        foreach ($answers as $answer) {
            $answersMap[$answer->examPaperQuestionId] = $answer;
        }

        $paper = [
            'uuid' => $examPaper->uuid,
            'name' => $examPaper->name,
            'subject_id' => $examPaper->subject->id,
            'subject_name' => $examPaper->subject->name,
            'start_time' => $studentExam->startTime->format('Y-m-d H:i:s'),
            'end_time' => $studentExam->endTime->format('Y-m-d H:i:s'),
            'remaining_secs' => $studentExam->endTime->getTimestamp() - (new \DateTime('now'))->getTimestamp(),
            'total_time_mins' => $studentExam->totalTimeMins,
            'total_questions' => $studentExam->totalQuestions,
            'status' => $studentExam->status,
            'questions' => []
        ];
        if ($studentExam->status == StudentExamStatus::COMPLETED) {
            $paper['score'] = $studentExam->score;
        }
        foreach ($questions as $question) {
            /** @var ExamPaperQuestion $question */
            $a = [
                'id' => $question->id,
                'topic' => $question->topic,
                'question' => $question->question,
                'choiceA' => $question->choiceA,
                'choiceB' => $question->choiceB,
                'choiceC' => $question->choiceC,
                'choiceD' => $question->choiceD,
                'answer' => isset($answersMap[$question->id]) ? $answersMap[$question->id]->answer : '',
            ];
            if ($studentExam->status == StudentExamStatus::COMPLETED) {
                $a['correct_answer'] = $question->answer;
                $a['is_correct'] = isset($answersMap[$question->id]) ? $answersMap[$question->id]->isCorrect : false;
                $a['explanation'] = $question->explanation;
            }
            $paper['questions'][] = $a;
        }
        $data = [
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'message' => 'Exam Status',
            'status' => 200,
            'data' => $paper
        ];

        return $data;
    }

    public function updateQuestionAnswer(string $examUuid, int $questionId, string $answer): void {
        $answer = trim($answer);
        if ($answer === '') {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Answer can not be empty');
        }
        if (strlen($answer) > 1) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Answer can not be more than 1 character');
        }

        $studentExam = $this->em->getRepository(StudentExam::class)->findOneBy([
            'uuid' => $examUuid,
            'studentId' => $this->studentCtx->getStudentId()
        ]);
        if ($studentExam === null || $studentExam->status == StudentExamStatus::COMPLETED) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Exam does not exist or already completed');
        }

        if ($studentExam->endTime < (new \DateTime('now'))) {
            $this->doFinishExam($studentExam);
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Exam is already completed');
        }

        /** @var ExamPaper $examPaper */
        $examPaper = $this->em->getRepository(ExamPaper::class)->findOneBy([
            'id' => $studentExam->examPaperId
        ]);
        if ($examPaper === null) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'ExamPaper does not exist');
        }

        $question = $this->em->getRepository(ExamPaperQuestion::class)->findOneBy([
            'examPaperId' => $examPaper->id,
            'id' => $questionId
        ]);

        if ($question === null) {
            throw new HttpRestException(HttpStatus::$BAD_REQUEST, 'Question does not exist');
        }

        /** @var StudentExamAnswer $a */
        $a = $this->em->getRepository(StudentExamAnswer::class)->findOneBy([
            'studentExamId' => $studentExam->id,
            'examPaperQuestionId' => $question->id
        ]);

        if ($a === null) {
            $a = new StudentExamAnswer();
            $a->studentExamId = $studentExam->id;
            $a->examPaperQuestionId = $question->id;
        }
        $a->answer = $answer;
        $a->isCorrect = strtolower($question->answer) === strtolower($answer);
        $this->em->persist($a);
        $this->em->flush();
    }
}
