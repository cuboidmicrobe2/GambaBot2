<?php

declare(strict_types=1);

namespace Database;

use Debug\Debug;
use Debug\MessageType;
use PDO;
use PDOException;
use SensitiveParameter;

final class PersistentConnection
{
    use Debug;

    private PDO $conn;

    public function __construct(
        public readonly string $connectionName,
        private readonly string $dsn,
        private readonly ?string $username = null,
        #[SensitiveParameter] private readonly ?string $password = null,
        private readonly ?array $options = null
    ) {
        $this->createConnection();
    }

    public function __destruct()
    {
        echo self::createUpdateMessage('', $this->connectionName.' was destroyed', MessageType::WARNING), PHP_EOL;
    }

    public static function connect(
        string $connectionName,
        string $dsn,
        ?string $username = null,
        ?string $password = null,
        ?array $options = null
    ): self {
        return new self($connectionName, $dsn, $username, $password, $options);
    }

    // public function __invoke(): PDO
    // {
    //     return $this->getConnection();
    // }

    public function getConnection(): PDO
    {
        $connectionStatus = $this->isConnected();
        if ($connectionStatus !== true) {
            echo self::createUpdateMessage('', $this->connectionName.' has lost connection, attempting to reconnect...', MessageType::WARNING), PHP_EOL;
            $this->createConnection();
            echo self::createUpdateMessage('', $this->connectionName.' connection successful!', MessageType::DEBUG), PHP_EOL;
        }

        return $this->conn;
    }

    private function createConnection(): void
    {
        try {
            $this->conn = PDO::connect($this->dsn, $this->username, $this->password, $this->options);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo self::createUpdateMessage('', $this->connectionName.' => '.$e->getMessage(), MessageType::WARNING), PHP_EOL;
            throw $e;
        }
    }

    private function isConnected(): true|string
    {
        try {
            $this->conn->getAttribute(PDO::ATTR_SERVER_INFO);

            return true;
        } catch (PDOException $e) {
            return $e->getCode();
        }
    }
}
