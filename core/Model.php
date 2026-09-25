<?php
declare(strict_types=1);

abstract class Model
{
    protected static string $table = '';

    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    protected function run(string $sql, array $parameters = []): PDOStatement
    {
        $statement = $this->db->prepare($sql);

        foreach ($parameters as $placeholder => $value) {
            $parameterType = match (true) {
                is_int($value)  => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                $value === null => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
            $statement->bindValue(':' . ltrim((string) $placeholder, ':'), $value, $parameterType);
        }

        $statement->execute();
        return $statement;
    }

    protected function fetchAll(string $sql, array $parameters = []): array
    {
        return $this->run($sql, $parameters)->fetchAll();
    }

    protected function fetchOne(string $sql, array $parameters = []): ?array
    {
        $row = $this->run($sql, $parameters)->fetch();
        return $row === false ? null : $row;
    }

    protected function fetchValue(string $sql, array $parameters = []): mixed
    {
        $value = $this->run($sql, $parameters)->fetchColumn();
        return $value === false ? null : $value;
    }

    protected function insert(string $sql, array $parameters): int
    {
        $this->run($sql, $parameters);
        return (int) $this->db->lastInsertId();
    }

    protected function execute(string $sql, array $parameters = []): int
    {
        return $this->run($sql, $parameters)->rowCount();
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM `' . static::$table . '` WHERE id = :id', ['id' => $id]);
    }

    public function deleteById(int $id): bool
    {
        return $this->execute('DELETE FROM `' . static::$table . '` WHERE id = :id', ['id' => $id]) > 0;
    }

    public function countAll(): int
    {
        return (int) $this->fetchValue('SELECT COUNT(*) FROM `' . static::$table . '`');
    }

    public function beginTransaction(): void
    {
        $this->db->beginTransaction();
    }

    public function commit(): void
    {
        $this->db->commit();
    }

    public function rollBack(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }
}
