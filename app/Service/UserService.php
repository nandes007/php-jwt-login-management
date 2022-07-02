<?php

namespace NandesSimanjuntak\Belajar\PHP\MVC\Service;

use Exception;
use NandesSimanjuntak\Belajar\PHP\MVC\Exception\ValidationException;
use NandesSimanjuntak\Belajar\PHP\MVC\Config\Database;
use NandesSimanjuntak\Belajar\PHP\MVC\Domain\User;
use NandesSimanjuntak\Belajar\PHP\MVC\Model\UserLoginRequest;
use NandesSimanjuntak\Belajar\PHP\MVC\Model\UserLoginResponse;
use NandesSimanjuntak\Belajar\PHP\MVC\Model\UserPasswordUpdateRequest;
use NandesSimanjuntak\Belajar\PHP\MVC\Model\UserPasswordUpdateResponse;
use NandesSimanjuntak\Belajar\PHP\MVC\Model\UserProfileUpdateRequest;
use NandesSimanjuntak\Belajar\PHP\MVC\Model\UserProfileUpdateResponse;
use NandesSimanjuntak\Belajar\PHP\MVC\Model\UserRegisterRequest;
use NandesSimanjuntak\Belajar\PHP\MVC\Model\UserRegisterResponse;
use NandesSimanjuntak\Belajar\PHP\MVC\Repository\UserRepository;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(UserRegisterRequest $request): UserRegisterResponse
    {
        $this->validateUserRegistrationRequest($request);

        try {
            Database::beginTransaction();
            $user = $this->userRepository->findById($request->id);
            if ($user != null) {
                throw new ValidationException('User with id ' . $request->id . ' already exists');
            }

            $user = new User();
            $user->id = $request->id;
            $user->name = $request->name;
            $user->password = password_hash($request->password, PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $response = new UserRegisterResponse();
            $response->user = $user;

            Database::commitTransaction();
            return $response;
        } catch (Exception $exception) {
            Database::rollbackTransaction();
            throw new ValidationException($exception->getMessage());
        }
        
        

    }

    private function validateUserRegistrationRequest(UserRegisterRequest $request): void
    {
        if ($request->id == null || $request->name == null || $request->password == null || trim($request->id) == '' || trim($request->name) == '' || trim($request->password) == '') {
            throw new ValidationException('Id, name, and password are required');
        }
    }

    public function login(UserLoginRequest $request): UserLoginResponse
    {
        $this->validationUserLoginRequest($request);

        $user = $this->userRepository->findById($request->id);
        if ($user == null) {
            throw new ValidationException("Id or password is wrong");
        }

        if (password_verify($request->password, $user->password)) {
            $response = new UserLoginResponse();
            $response->user = $user;
            return $response;
        } else {
            throw new ValidationException("Id or password is wrong");
        }
    }

    private function validationUserLoginRequest(UserLoginRequest $request): void
    {
        if ($request->id == null || $request->password == null || trim($request->id) == '' || trim($request->password) == '') {
            throw new ValidationException('Id and password are required');
        }
    }

    public function updateProfile(UserProfileUpdateRequest $request): UserProfileUpdateResponse
    {
        $this->validateUserProfileUpdateRequest($request);

        try {
            Database::beginTransaction();

            $user = $this->userRepository->findById($request->id);
            if ($user == null) {
                throw new ValidationException('User with id ' . $request->id . ' not found');
            }
            
            $user->name = $request->name;
            $this->userRepository->update($user);

            Database::commitTransaction();

            $response = new UserProfileUpdateResponse();
            $response->user = $user;
            return $response;
        } catch(Exception $e) {
            Database::rollbackTransaction();
            throw $e;
        }
    }

    private function validateUserProfileUpdateRequest(UserProfileUpdateRequest $request): void
    {
        if ($request->id == null || $request->name == null || trim($request->id) == '' || trim($request->name) == '') {
            throw new ValidationException('Id, Name are required');
        }
    }

    public function updatePassword(UserPasswordUpdateRequest $request): UserPasswordUpdateResponse
    {
        $this->validateUserPasswordUpdateRequest($request);

        try {
            Database::beginTransaction();

            $user = $this->userRepository->findById($request->id);
            if ($user == null) {
                throw new ValidationException('User with id ' . $request->id . ' not found');
            }

            if (!password_verify($request->oldPassword, $user->password)) {
                throw new ValidationException('Old password is wrong');
            }

            $user->password = password_hash($request->newPassword, PASSWORD_BCRYPT);
            $this->userRepository->update($user);

            Database::commitTransaction();

            $response = new UserPasswordUpdateResponse();
            $response->user = $user;
            return $response;
        } catch(Exception $e) {
            Database::rollbackTransaction();
            throw $e;
        }
    }

    private function validateUserPasswordUpdateRequest(UserPasswordUpdateRequest $request)
    {
        if ($request->id == null || $request->oldPassword == null || $request->newPassword == null || trim($request->id) == '' || trim($request->oldPassword) == '' || trim($request->newPassword) == '') {
            throw new ValidationException('Id, Old Passowrd, and New Password are required');
        }
    }
}