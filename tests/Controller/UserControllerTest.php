<?php

namespace NandesSimanjuntak\Belajar\PHP\MVC\Controller {

    require_once __DIR__ . '/../Helper/helper.php';

    use NandesSimanjuntak\Belajar\PHP\MVC\Config\Database;
    use NandesSimanjuntak\Belajar\PHP\MVC\Domain\Session;
    use NandesSimanjuntak\Belajar\PHP\MVC\Domain\User;
    use NandesSimanjuntak\Belajar\PHP\MVC\Repository\SessionRepository;
    use NandesSimanjuntak\Belajar\PHP\MVC\Repository\UserRepository;
    use NandesSimanjuntak\Belajar\PHP\MVC\Service\SessionService;
    use PHPUnit\Framework\TestCase;

    class UserControllerTest extends TestCase
    {
        private UserController $userController;
        private UserRepository $userRepository;
        private SessionRepository $sessionRepository;

        protected function setUp(): void
        {
            $this->userController = new UserController();

            $this->sessionRepository = new SessionRepository(Database::getConnection());
            $this->sessionRepository->deleteAll();

            $this->userRepository = new UserRepository(Database::getConnection());
            $this->userRepository->deleteAll();

            putenv("mode=test");
        }

        public function testRegister()
        {
            $this->userController->register();

            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Register new User]");
        }

        public function testPostRegisterSuccess()
        {
            $_POST['id'] = 'nandes';
            $_POST['name'] = 'Nandes';
            $_POST['password'] = 'nandes';

            $this->userController->postRegister();

            $this->expectOutputRegex("[Location: /users/login]");
        }

        public function testPostRegisterValidationError()
        {
            $_POST['id'] = '';
            $_POST['name'] = 'Nandes';
            $_POST['password'] = 'nandes';

            $this->userController->postRegister();

            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Register new User]");
            $this->expectOutputRegex("[Id, name, and password are required]");
        }

        public function testPostRegisterDuplicate()
        {
            $user = new  User();
            $user->id = '1';
            $user->name = 'Nandes';
            $user->password = '123';

            $this->userRepository->save($user);

            $_POST['id'] = '1';
            $_POST['name'] = 'Nandes';
            $_POST['password'] = '123';

            $this->userController->postRegister();

            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Register new User]");
            $this->expectOutputRegex("[User with id 1 already exists]");
        }

        public function testLogin()
        {
            $this->userController->login();

            $this->expectOutputRegex("[Login user]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
        }

        public function testLoginSuccess()
        {
            $user = new  User();
            $user->id = 'nandes';
            $user->name = 'Nandes';
            $user->password = password_hash("nandes", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $_POST['id'] = 'nandes';
            $_POST['password'] = 'nandes';

            $this->userController->postLogin();

            $this->expectOutputRegex("[Location: /]");
            $this->expectOutputRegex("[X-PZN-SESSION: ]");
        }

        public function testLoginValidationError()
        {
            $_POST['id'] = '';
            $_POST['password'] = '';
            $this->userController->postLogin();

            $this->expectOutputRegex("[Login user]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id and password are required]");
        }

        public function testLoginUserNotFound()
        {
            $_POST['id'] = 'Not Found';
            $_POST['password'] = 'Not Found';
            $this->userController->postLogin();

            $this->expectOutputRegex("[Login user]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id or password is wrong]");
        }

        public function testLoginWrongPassword()
        {
            $user = new  User();
            $user->id = 'nandes';
            $user->name = 'Nandes';
            $user->password = password_hash("nandes", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $_POST['id'] = 'nandes';
            $_POST['password'] = 'salah';
            $this->userController->postLogin();

            $this->expectOutputRegex("[Login user]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id or password is wrong]");
        }

        public function testLogout()
        {
            $user = new  User();
            $user->id = 'nandes';
            $user->name = 'Nandes';
            $user->password = password_hash("nandes", PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->logout();

            $this->expectOutputRegex("[Location: /]");
            $this->expectOutputRegex("[X-PZN-SESSION: ]");
        }

        public function testUpdateProfile()
        {
            $user = new  User();
            $user->id = 'nandes';
            $user->name = 'Nandes';
            $user->password = password_hash("nandes", PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->updateProfile();

            $this->expectOutputRegex("[Profile]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[nandes]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Nandes]");
        }

        public function testUpdateProfileSuccess()
        {
            $user = new  User();
            $user->id = 'nandes';
            $user->name = 'Nandes';
            $user->password = password_hash("nandes", PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['name'] = 'Budi';
            $this->userController->postUpdateProfile();

            $this->expectOutputRegex("[Location: /]");

            $result = $this->userRepository->findById("nandes");
            self::assertEquals("Budi", $result->name);
        }

        public function testPostUpdateProfileValidationError()
        {
            $user = new  User();
            $user->id = 'nandes';
            $user->name = 'Nandes';
            $user->password = password_hash("nandes", PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['name'] = '';
            $this->userController->postUpdateProfile();

            $this->expectOutputRegex("[Profile]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[nandes]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Id, Name are required]");
        }

        public function testUpdatePassword()
        {
            $user = new  User();
            $user->id = 'nandes';
            $user->name = 'Nandes';
            $user->password = password_hash("nandes", PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->updatePassword();

            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[nandes]");
        }

        public function testPostUpdatePasswordSuccess()
        {
            $user = new  User();
            $user->id = 'nandes';
            $user->name = 'Nandes';
            $user->password = password_hash("nandes", PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['oldPassword'] = 'nandes';
            $_POST['newPassword'] = 'budi';

            $this->userController->postUpdatePassword();

            $this->expectOutputRegex("[Location: /]");

            $result = $this->userRepository->findById($user->id);
            self::assertTrue(password_verify("budi", $result->password));
        }

        public function testPostUpdatePasswordValidationError()
        {
            $user = new  User();
            $user->id = 'nandes';
            $user->name = 'Nandes';
            $user->password = password_hash("nandes", PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['oldPassword'] = '';
            $_POST['newPassword'] = '';

            $this->userController->postUpdatePassword();

            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[nandes]");
            $this->expectOutputRegex("[Id, Old Passowrd, and New Password are required]");
        }

        public function testPostUpdatePasswordWrongOldPassword()
        {
            $user = new  User();
            $user->id = 'nandes';
            $user->name = 'Nandes';
            $user->password = password_hash("nandes", PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['oldPassword'] = 'salah';
            $_POST['newPassword'] = 'budi';

            $this->userController->postUpdatePassword();

            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[nandes]");
            $this->expectOutputRegex("[Old password is wrong]");
        }

    }
}