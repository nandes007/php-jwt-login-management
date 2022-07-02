<?php

namespace NandesSimanjuntak\Belajar\PHP\MVC\Repository;

use PHPUnit\Framework\TestCase;
use NandesSimanjuntak\Belajar\PHP\MVC\Domain\User;
use NandesSimanjuntak\Belajar\PHP\MVC\Config\Database;

class UserRepositoryTest extends TestCase
{
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;

    protected function setUp(): void
    {
        $this->sessionRepository = new SessionRepository(Database::getConnection());
        $this->userRepository = new UserRepository(Database::getConnection());
        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteAll();
    }

    public function testSaveSuccess()
    {
        $user = new User();
        $user->id = 'nandes';
        $user->name = 'Nandes';
        $user->password = 'nandes';

        $this->userRepository->save($user);

        $result = $this->userRepository->findById($user->id);

        $this->assertEquals($user->id, $result->id);
        $this->assertEquals($user->name, $result->name);
        $this->assertEquals($user->password, $result->password);
    }

    public function testFindByIdNotFound()
    {
        $user = $this->userRepository->findById('notfound');
        self::assertNull($user);
    }

    public function testUpdate()
    {
        $user = new User();
        $user->id = 'nandes';
        $user->name = 'Nandes';
        $user->password = 'nandes';
        $this->userRepository->save($user);

        $user->name = "putra";
        $this->userRepository->update($user);

        $result = $this->userRepository->findById($user->id);

        $this->assertEquals($user->id, $result->id);
        $this->assertEquals($user->name, $result->name);
        $this->assertEquals($user->password, $result->password);
    }
}