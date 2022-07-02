<?php

namespace NandesSimanjuntak\Belajar\PHP\MVC\Service;

require_once __DIR__ . '/../Helper/helper.php';

use NandesSimanjuntak\Belajar\PHP\MVC\Config\Database;
use NandesSimanjuntak\Belajar\PHP\MVC\Domain\Session;
use NandesSimanjuntak\Belajar\PHP\MVC\Domain\User;
use NandesSimanjuntak\Belajar\PHP\MVC\Repository\SessionRepository;
use NandesSimanjuntak\Belajar\PHP\MVC\Repository\UserRepository;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;

class SessionServiceTest extends TestCase
{
    private SessionService $sessionService;
    private SessionRepository $sessionRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->sessionRepository = new SessionRepository(Database::getConnection());
        $this->userRepository = new UserRepository(Database::getConnection());
        $this->sessionService = new SessionService($this->sessionRepository, $this->userRepository);

        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteAll();

        $user = new User();
        $user->id = "nandes";
        $user->name = "Nandes";
        $user->password = "nandes";
        $this->userRepository->save($user);
    }

    public function testCreate()
    {
        $session = $this->sessionService->create("nandes");

        $this->expectOutputRegex("[X-PZN-SESSION: $session->id]");

        $result = $this->sessionRepository->findById($session->id);

        self::assertEquals("nandes", $result->userId);
    }

    public function testDestroy()
    {
        $session = new Session();
        $session->id = uniqid();
        $session->userId = "nandes";

        $this->sessionRepository->save($session);

        $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

        $this->sessionService->destroy();

        $this->expectOutputRegex("[X-PZN-SESSION: ]");

        $result = $this->sessionRepository->findById($session->id);
        self::assertNull($result);
    }

    public function testCurrent()
    {
        $session = new Session();
        $session->id = uniqid();
        $session->userId = "nandes";

        $this->sessionRepository->save($session);

        $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

        $user = $this->sessionService->current();

        assertEquals($session->userId, $user->id);
    }
}