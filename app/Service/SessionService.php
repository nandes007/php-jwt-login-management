<?php

namespace NandesSimanjuntak\Belajar\PHP\MVC\Service;

use Exception;
use NandesSimanjuntak\Belajar\PHP\MVC\Domain\Session;
use NandesSimanjuntak\Belajar\PHP\MVC\Domain\User;
use NandesSimanjuntak\Belajar\PHP\MVC\Repository\SessionRepository;
use NandesSimanjuntak\Belajar\PHP\MVC\Repository\UserRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class SessionService
{
    public static $COOKIE_NAME = "X-PZN-SESSION";
    public static $SECRET_KEY = "asldnlasndlnwqoioenONLSNFLDNSLNLDNSLlnfldsnfldnslfknslkdfnlksdnlfnsdlfnlsdkn";

    private SessionRepository $sessionRepository;
    private UserRepository $userRepository;

    public function __construct(SessionRepository $sessionRepository, UserRepository $userRepository)
    {
        $this->sessionRepository = $sessionRepository;
        $this->userRepository = $userRepository;
    }
   
    public function create(string $userId): Session
    {
        $payload = [
            "userId" => $userId,
            "createdAt" => time(),
            "updatedAt" => time()
        ];
        $jwt = JWT::encode($payload, SessionService::$SECRET_KEY, "HS256");
        $session = new Session();
        $session->id = uniqid();
        $session->userId = $userId;
        $session->_token = $jwt;

        $this->sessionRepository->save($session);

        setcookie(self::$COOKIE_NAME, $session->id, time() + (60 * 60 * 24 * 30), "/"); // waktu: detik, menit, jam, hari
        
        return $session;
    }

    public function destroy()
    {
        $sessionId = $_COOKIE[self::$COOKIE_NAME] ?? '';
        $this->sessionRepository->deleteById($sessionId);

        setcookie(self::$COOKIE_NAME, '', 1, "/"); // for reset cookie
    }

    public function current(): ?User
    {
        $sessionId = $_COOKIE[self::$COOKIE_NAME] ?? '';

        $session = $this->sessionRepository->findById($sessionId);
        if ($session == null) {
            return null;
        }

        try {
            JWT::decode($session->_token, new Key(SessionService::$SECRET_KEY, 'HS256'));
        } catch (Exception $e) {
            return null;
        }

        return $this->userRepository->findById($session->userId);
    }

}