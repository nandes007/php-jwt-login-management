<?php

namespace NandesSimanjuntak\Belajar\PHP\MVC\Service;

use NandesSimanjuntak\Belajar\PHP\MVC\Exception\ValidationException;
use NandesSimanjuntak\Belajar\PHP\MVC\Config\Database;
use NandesSimanjuntak\Belajar\PHP\MVC\Domain\User;
use NandesSimanjuntak\Belajar\PHP\MVC\Model\UserLoginRequest;
use NandesSimanjuntak\Belajar\PHP\MVC\Model\UserPasswordUpdateRequest;
use NandesSimanjuntak\Belajar\PHP\MVC\Model\UserProfileUpdateRequest;
use NandesSimanjuntak\Belajar\PHP\MVC\Model\UserRegisterRequest;
use NandesSimanjuntak\Belajar\PHP\MVC\Repository\SessionRepository;
use NandesSimanjuntak\Belajar\PHP\MVC\Repository\UserRepository;
use NandesSimanjuntak\Belajar\PHP\MVC\Service\UserService;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;

    protected function setUp(): void
    {
        $connection = Database::getConnection();
        $this->sessionRepository = new SessionRepository($connection);
        $this->userRepository = new UserRepository($connection);
        $this->userService = new UserService($this->userRepository);

        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteAll();
    }

    public function testRegisterSuccess()
    {
        $request = new UserRegisterRequest();
        $request->id = 'nandes';
        $request->name = 'Nandes';
        $request->password = '123';

        $response = $this->userService->register($request);

        $this->assertEquals('nandes', $response->user->id);
        $this->assertEquals('Nandes', $response->user->name);
        $this->assertNotEquals('123', $response->user->password);

        self::assertTrue(password_verify($request->password, $response->user->password));
    }

    public function testRegisterFailed()
    {
        $this->expectException(ValidationException::class);

        $request = new UserRegisterRequest();
        $request->id = '';
        $request->name = '';
        $request->password = '';
        
        $this->userService->register($request);
    }

    public function testRegisterDuplicateId()
    {
        $user = new User();
        $user->id = 'nandes';
        $user->name = 'Nandes';
        $user->password = '123';

        $this->userRepository->save($user);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('User with id nandes already exists');

        $request = new UserRegisterRequest();
        $request->id = 'nandes';
        $request->name = 'Nandes';
        $request->password = '123';

        $this->userService->register($request);

        $this->userService->register($request);
    }

    public function testLoginNotFound()
    {
        $this->expectException(ValidationException::class);

        $request = new UserLoginRequest();
        $request->id = "nandes";
        $request->password = "nandes";

        $this->userService->login($request);
    }

    public function testLoginWrongPassword()
    {
        $user = new User();
        $user->id = "nandes";
        $user->name = "Nandes";
        $user->password = password_hash("nandes", PASSWORD_BCRYPT);

        $this->expectException(ValidationException::class);

        $request = new UserLoginRequest();
        $request->id = "nandes";
        $request->password = "nandex";

        $this->userService->login($request);
    }

    public function testLoginSuccess()
    {
        $user = new User();
        $user->id = "nandes";
        $user->name = "Nandes";
        $user->password = password_hash("nandes", PASSWORD_BCRYPT);

        $this->expectException(ValidationException::class);

        $request = new UserLoginRequest();
        $request->id = "nandes";
        $request->password = "nandes";

        $response = $this->userService->login($request);

        self::assertEquals($request->id, $response->user->id);
        self::assertTrue(password_verify($request->password, $response->user->password));
    }

    public function testUpdateSuccess()
    {
        $user = new User();
        $user->id = "nandes";
        $user->name = "Nandes";
        $user->password = password_hash("nandes", PASSWORD_BCRYPT);
        $this->userRepository->save($user);

        $request = new UserProfileUpdateRequest();
        $request->id = "nandes";
        $request->name = "Budi";

        $this->userService->updateProfile($request);

        $result = $this->userRepository->findById($user->id);

        self::assertEquals($request->name, $result->name);
    }

    public function testUpdateValidationError()
    {
        $this->expectException(ValidationException::class);

        $request = new UserProfileUpdateRequest();
        $request->id = "";
        $request->name = "";

        $this->userService->updateProfile($request);
    }

    public function testUpdateNotFound()
    {
        $this->expectException(ValidationException::class);
        
        $request = new UserProfileUpdateRequest();
        $request->id = "nandes";
        $request->name = "Budi";

        $this->userService->updateProfile($request);
    }

    public function testUpdatePasswordSuccess()
    {
        $user = new User();
        $user->id = "nandes";
        $user->name = "Nandes";
        $user->password = password_hash("nandes", PASSWORD_BCRYPT);
        $this->userRepository->save($user);

        $request = new UserPasswordUpdateRequest();
        $request->id = "nandes";
        $request->oldPassword = "nandes";
        $request->newPassword = "nandex";

        $this->userService->updatePassword($request);

        $result = $this->userRepository->findById($user->id);
        self::assertTrue(password_verify($request->newPassword, $result->password));
    }

    public function testUpdatePasswordValidationError()
    {
        $this->expectException(ValidationException::class);

        $request = new UserPasswordUpdateRequest();
        $request->id = "nandes";
        $request->oldPassword = "";
        $request->newPassword = "";

        $this->userService->updatePassword($request);
    }

    public function testUpdatePasswordWrongOldPassword()
    {
        $this->expectException(ValidationException::class);
        $user = new User();
        $user->id = "nandes";
        $user->name = "Nandes";
        $user->password = password_hash("nandes", PASSWORD_BCRYPT);
        $this->userRepository->save($user);

        $request = new UserPasswordUpdateRequest();
        $request->id = "nandes";
        $request->oldPassword = "salah";
        $request->newPassword = "new";

        $this->userService->updatePassword($request);
    }

    public function testUpdatePasswordNotFound()
    {
        $this->expectException(ValidationException::class);

        $request = new UserPasswordUpdateRequest();
        $request->id = "nandes";
        $request->oldPassword = "salah";
        $request->newPassword = "new";

        $this->userService->updatePassword($request);
    }
}