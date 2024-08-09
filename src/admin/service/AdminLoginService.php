<?php

declare(strict_types=1);

namespace dev\suvera\exms\admin\service;

use dev\suvera\exms\data\entity\Admin;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\Service;
use Doctrine\ORM\EntityManager;
use dev\suvera\exms\utils\session\Handler;
use dev\winterframework\exception\HttpRestException;
use dev\winterframework\web\http\HttpRequest;
use dev\winterframework\web\http\HttpStatus;

#[Service]
class AdminLoginService {
    public const SESSION_NAME = 'exms_admin_sess_id';
    public const SESSION_TYPE = 1;
    public const SESSION_EXPIRY_SECS = 1600;

    public const MAX_FAILED_ATTEMPTS = 10;
    public const LOCK_TIME = '-20 minutes';

    #[Autowired]
    private EntityManager $em;

    private Admin $admin;

    public function getEncryptKey(): string {
        return 'Feuxoph2Ue8ru8he';
    }

    public function getAdmin(): Admin {
        if (isset($this->admin)) {
            return $this->admin;
        }
        $this->admin = new Admin();
        $this->admin->__unserialize($_SESSION['admin']);
        return $this->admin;
    }

    protected function sessionStart(string $username): void {
        $sessionHanlder = new Handler(
            $this->em,
            self::SESSION_NAME,
            self::SESSION_TYPE,
            $this->getEncryptKey(),
            self::SESSION_EXPIRY_SECS,
            $username
        );

        if (!$sessionHanlder->sessionStart('')) {
            throw new HttpRestException(HttpStatus::$INTERNAL_SERVER_ERROR, 'Failed to start session');
        }
    }

    public function loginInitSession(string $username, string $password): ?Admin {
        $admin = $this->verify($username, $password);

        if ($admin === null) {
            return null;
        }
        $this->sessionStart($username);

        $_SESSION['admin'] = $admin->jsonSerialize();

        return $admin;
    }

    public function sessionLogout(HttpRequest $request): void {
        $this->sessionStart('');
        unset($_SESSION['admin']);
        session_destroy();
    }

    public function sessionLogin(HttpRequest $request): ?Admin {
        $this->sessionStart('');

        if (!isset($_SESSION['admin'])) {
            return null;
        }

        $data = $_SESSION['admin'];
        if (!isset($data['id']) && !isset($data['username'])) {
            return null;
        }

        $admin = new Admin($data);
        $admin->__unserialize($data);

        return $admin;
    }

    public function verify(string $username, string $password): ?Admin {
        /** @var Admin|null $admin */
        $admin = $this->em->getRepository(Admin::class)->findOneByUsername($username);

        if ($admin === null) {
            return null;
        }

        if (!password_verify($password, $admin->getPassword())) {
            return null;
        }

        return $admin;
    }
}
