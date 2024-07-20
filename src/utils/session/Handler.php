<?php

declare(strict_types=1);

namespace dev\suvera\exms\utils\session;

use dev\suvera\exms\data\entity\Session;
use dev\suvera\exms\utils\Utility;
use dev\winterframework\util\log\Wlf4p;
use Doctrine\ORM\EntityManager;

class Handler implements \SessionHandlerInterface, \SessionIdInterface {
    use Wlf4p;
    protected bool $started = false;
    protected string $tableName = 'sessions';
    protected string $idColumn = 'session_id';
    protected string $expiresColumn = 'session_expires';

    // construtor

    public function __construct(
        protected EntityManager $em,
        protected string $sessionName,
        protected int $sessionType,
        protected string $encryptKey,
        protected int $sessionExpirySecs,
        protected string $username
    ) {
    }

    public function create_sid(): string {
        return bin2hex(random_bytes(mt_rand(24, 32)));
    }

    public function sessionStart(string $sessionId): bool {
        if ($this->started) {
            return $this->started;
        }
        if (session_name($this->sessionName) === false) {
            return false;
        }
        if (strlen($sessionId) > 0 && preg_match('/^[\-,a-zA-Z0-9]{1,128}$/', $sessionId)) {
            if (session_id($sessionId) === false) {
                return false;
            }
        }
        if (!session_set_save_handler($this, true)) {
            return false;
        }
        $this->started = session_start();
        return $this->started;
    }

    public function open(string $savePath, string $sessionName): bool {
        self::logDebug("Session opened");
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read(string $id): string|false {
        $session = $this->em->getRepository(Session::class)->findOneById($id);
        if ($session === null || $session->getType() !== $this->sessionType) {
            //self::logInfo("Session not found: " . $id);
            return '';
        }

        return Utility::decrypt($session->getData(), $this->encryptKey);
    }

    public function write(string $id, string $data): bool {
        $expires = (new \DateTime('now'))->add(new \DateInterval('PT' . $this->sessionExpirySecs . 'S'));
        $session = $this->em->getRepository(Session::class)->findOneById($id);
        if ($session === null) {
            $session = new Session();
            $session->setId($id);
            $session->setUsername($this->username);
            $session->setType($this->sessionType);
        }

        $session->setData(Utility::encrypt($data, $this->encryptKey));
        $session->setExpires($expires);
        $this->em->persist($session);
        $this->em->flush();
        self::logDebug("Session written");
        return true;
    }

    public function destroy(string $id): bool {
        $this->em->getConnection()->delete($this->tableName, [$this->idColumn => $id]);
        return true;
    }

    public function gc(int $maxlifetime): int|false {
        $this->em->getConnection()->executeStatement('delete from ' . $this->tableName . ' where ' . $this->expiresColumn
            . ' < ?', [(new \DateTime('now'))->format('Y-m-d H:i:s')]);
        return true;
    }
}
