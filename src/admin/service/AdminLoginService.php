<?php

declare(strict_types=1);

namespace dev\suvera\exms\admin\service;

use dev\suvera\exms\data\entity\Admin;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\Service;
use Doctrine\ORM\EntityManager;

#[Service]
class AdminLoginService {

    #[Autowired()]
    private EntityManager $em;

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
