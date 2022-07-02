<?php

namespace NandesSimanjuntak\Belajar\PHP\MVC\Middleware;

use NandesSimanjuntak\Belajar\PHP\MVC\App\View;
use NandesSimanjuntak\Belajar\PHP\MVC\Config\Database;
use NandesSimanjuntak\Belajar\PHP\MVC\Repository\SessionRepository;
use NandesSimanjuntak\Belajar\PHP\MVC\Repository\UserRepository;
use NandesSimanjuntak\Belajar\PHP\MVC\Service\SessionService;

class MustLoginMiddleware implements Middleware
{
    private SessionService $sessionService;

    public function __construct()
    {
        $sessionRepository = new SessionRepository(Database::getConnection());
        $userRepository = new UserRepository(Database::getConnection());
        $this->sessionService = new SessionService($sessionRepository, $userRepository);
    }

    public function before(): void
    {
        $user = $this->sessionService->current();
        if ($user == null) {
            View::redirect("/users/login");
        }
    }

}