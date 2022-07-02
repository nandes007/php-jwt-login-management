<?php

namespace NandesSimanjuntak\Belajar\PHP\MVC\Middleware {

    require_once __DIR__ . '/../Helper/helper.php';

    use NandesSimanjuntak\Belajar\PHP\MVC\Config\Database;
    use NandesSimanjuntak\Belajar\PHP\MVC\Domain\Session;
    use NandesSimanjuntak\Belajar\PHP\MVC\Domain\User;
    use NandesSimanjuntak\Belajar\PHP\MVC\Repository\SessionRepository;
    use NandesSimanjuntak\Belajar\PHP\MVC\Repository\UserRepository;
    use NandesSimanjuntak\Belajar\PHP\MVC\Service\SessionService;
    use PHPUnit\Framework\TestCase;
    
    class MustNotLoginMiddlewareTest extends TestCase
    {
        private MustNotLoginMiddleware $middleware;
        private SessionRepository $sessionRepository;
        private UserRepository $userRepository;

        protected function setUp(): void
        {
            $this->middleware = new MustNotLoginMiddleware();
            putenv("mode=test");

            $this->sessionRepository = new SessionRepository(Database::getConnection());
            $this->userRepository = new UserRepository(Database::getConnection());

            $this->sessionRepository->deleteAll();
            $this->userRepository->deleteAll();
        }

        public function testBeforeGuest()
        {
            $this->middleware->before();
            $this->expectOutputString("");
        }

        public function testBeforeLoginUser()
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

            $this->middleware->before();
            $this->expectOutputRegex("[Location: /]");
        }
    }

}
