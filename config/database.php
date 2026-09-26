<?php
declare(strict_types=1);

final class Database
{
    private static ?Database $instance = null;

    private PDO $connection;

    private function __construct(array $dbConfig)
    {
        $dataSourceName = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['name'],
            $dbConfig['charset']
        );

        $this->connection = new PDO($dataSourceName, $dbConfig['user'], $dbConfig['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        $this->connection->exec("SET time_zone = '" . date('P') . "'");
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database(Config::get('db'));
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    private function __clone()
    {
    }

    public function __wakeup(): void
    {
        throw new LogicException('The Database singleton cannot be unserialized.');
    }
}
