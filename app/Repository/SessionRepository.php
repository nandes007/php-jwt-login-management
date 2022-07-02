<?php

namespace NandesSimanjuntak\Belajar\PHP\MVC\Repository;

use NandesSimanjuntak\Belajar\PHP\MVC\Domain\Session;
use PDO;

class SessionRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function save(Session $session): Session
    {
        $statement = $this->connection->prepare("INSERT INTO sessions(id, user_id, _token) VALUES (?, ?, ?)");
        $statement->execute([
            $session->id,
            $session->userId,
            $session->_token
        ]);
        return $session;
    }

    public function findById(string $id): ?Session
    {
        $statement = $this->connection->prepare("SELECT id, user_id, _token FROM sessions WHERE id = ?");
        $statement->execute([$id]);

        try {
            if ($row = $statement->fetch()) {
                $session = new Session();
                $session->id = $row['id'];
                $session->userId = $row['user_id'];
                $session->_token = $row['_token'];
                return $session;
            } else {
                return null;
            }
        } finally {
            $statement->closeCursor();
        }
    }

    public function deleteById(string $id): void
    {
        $this->connection->prepare("DELETE FROM sessions WHERE id = ?")->execute([$id]);
    }

    public function deleteAll(): void
    {
        $this->connection->exec("DELETE FROM sessions");
    }

}