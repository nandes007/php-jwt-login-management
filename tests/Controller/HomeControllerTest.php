<?php

namespace NandesSimanjuntak\Belajar\PHP\MVC\Controller;

use NandesSimanjuntak\Belajar\PHP\MVC\Config\Database;
use NandesSimanjuntak\Belajar\PHP\MVC\Domain\Session;
use NandesSimanjuntak\Belajar\PHP\MVC\Domain\User;
use NandesSimanjuntak\Belajar\PHP\MVC\Repository\SessionRepository;
use NandesSimanjuntak\Belajar\PHP\MVC\Repository\UserRepository;
use NandesSimanjuntak\Belajar\PHP\MVC\Service\SessionService;
use PHPUnit\Framework\TestCase;

class HomeControllerTest extends TestCase
{
    private HomeController $homeController;
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;
    
    protected function setUp(): void
    {
        $this->homeController = new HomeController();
        $this->sessionRepository = new SessionRepository(Database::getConnection());
        $this->userRepository = new UserRepository(Database::getConnection());

        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteAll();
    }

    public function testGuest()
    {
        $this->homeController->index();

        $this->expectOutputRegex("[Login Management]");
    }

    public function testUserLogin()
    {
        $user = new User();
        $user->id = "nandes";
        $user->name = "Nandes";
        $user->password = "nandes";
        $this->userRepository->save($user);

        $session = new Session();
        $session->id = uniqid();
        $session->userId = $user->id;
        $this->sessionRepository->save($session);

        $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

        $this->homeController->index();

        $this->expectOutputRegex("[Hello Nandes]");
    }
}