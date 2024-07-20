<?php

declare(strict_types=1);

namespace dev\suvera\exms\student\service;

use dev\suvera\exms\data\entity\Student;
use dev\suvera\exms\utils\session\Handler;
use dev\winterframework\exception\HttpRestException;
use dev\winterframework\stereotype\Service;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\web\http\HttpRequest;
use dev\winterframework\web\http\HttpStatus;
use Doctrine\ORM\EntityManager;

#[Service]
class LoginService {
    public const SESSION_NAME = 'exms_student_sess_id';
    public const SESSION_TYPE = 1;
    public const SESSION_EXPIRY_SECS = 3600;

    public const MAX_FAILED_ATTEMPTS = 10;
    public const LOCK_TIME = '-1 hour';

    #[Autowired]
    private EntityManager $em;

    public function getEncryptKey(): string {
        return 'Cazeeho3Feix5chu';
    }

    public function verify(string $username, string $password): ?Student {
        /** @var Student|null $student */
        $student = $this->em->getRepository(Student::class)->findOneByUsername($username);

        if ($student === null) {
            return null;
        }

        if ($student->getFailedAttempts() >= self::MAX_FAILED_ATTEMPTS && $student->lastLogin > new \DateTime(self::LOCK_TIME)) {
            throw new HttpRestException(HttpStatus::$TOO_MANY_REQUESTS, 'Login failed too many times');
        }

        $student->lastLogin = new \DateTime('now');
        if (!password_verify($password, $student->getPassword())) {
            $this->em->getConnection()->executeStatement(
                'update ' . $this->em->getClassMetadata(Student::class)->getTableName() . ' set last_login = ?, failed_attempts = failed_attempts + 1 where id = ?',
                [$student->lastLogin->format('Y-m-d H:i:s'), $student->getId()]
            );
            $student->failedAttempts++;
            return null;
        }

        $student->failedAttempts = 0;
        $this->em->getConnection()->executeStatement(
            'update ' . $this->em->getClassMetadata(Student::class)->getTableName() . ' set last_login = ?, failed_attempts = 0 where id = ?',
            [$student->lastLogin->format('Y-m-d H:i:s'), $student->getId()]
        );

        return $student;
    }

    protected function sessionStart(string $sessionId, string $username): void {
        $sessionHanlder = new Handler(
            $this->em,
            LoginService::SESSION_NAME,
            LoginService::SESSION_TYPE,
            $this->getEncryptKey(),
            LoginService::SESSION_EXPIRY_SECS,
            $username
        );

        if (!$sessionHanlder->sessionStart($sessionId)) {
            throw new HttpRestException(HttpStatus::$INTERNAL_SERVER_ERROR, 'Failed to start session');
        }
    }

    public function loginInitSession(string $username, string $password): ?Student {
        $student = $this->verify($username, $password);

        if ($student === null) {
            return null;
        }
        $this->sessionStart('', $username);

        $_SESSION['student'] = $student->jsonSerialize();

        return $student;
    }

    public function sessionLogout(HttpRequest $request): void {
        $token = $request->getHeader('X-Auth-Token');
        $sessionId = ($token && isset($token[0])) ? $token[0] : '';
        if (strlen($sessionId) == 0 || !preg_match('/^[\-,a-zA-Z0-9]{1,128}$/', $sessionId)) {
            $sessionId = '';
        }

        $this->sessionStart($sessionId, '');
        unset($_SESSION['student']);
        session_destroy();
    }

    public function sessionLogin(HttpRequest $request): ?Student {
        $token = $request->getHeader('X-Auth-Token');
        $sessionId = ($token && isset($token[0])) ? $token[0] : '';
        if (strlen($sessionId) == 0 || !preg_match('/^[\-,a-zA-Z0-9]{1,128}$/', $sessionId)) {
            return null;
        }

        $this->sessionStart($sessionId, '');

        if (!isset($_SESSION['student'])) {
            return null;
        }

        $data = $_SESSION['student'];
        if (!isset($data['id']) && !isset($data['username'])) {
            return null;
        }

        $student = new Student($data);
        $student->__unserialize($data);

        return $student;
    }
}
