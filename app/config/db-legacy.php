<?php
// Adapter kecil untuk controller lama yang masih memakai sintaks mysqli.
final class LegacyDbResult
{
    public function __construct(private array $rows) {}
    public function num_rows(): int { return count($this->rows); }
    private int $i = 0;
    public function fetch_assoc(): ?array { return $this->rows[$this->i++] ?? null; }
    public function fetch(): ?array { return $this->fetch_assoc(); }
}

final class LegacyDbStmt
{
    private ?PDOStatement $stmt = null;
    private array $refs = [];
    public int $insert_id = 0;
    public int $affected_rows = 0;
    public string $error = '';

    public function __construct(private PDO $pdo, private string $sql) {}
    public function bind_param(string $types, &...$args): bool { $this->refs = $args; return true; }
    public function execute(): bool
    {
        try {
            $this->stmt = $this->pdo->prepare($this->sql);
            $this->stmt->execute(array_values($this->refs));
            $this->insert_id = (int)$this->pdo->lastInsertId();
            $this->affected_rows = $this->stmt->rowCount();
            return true;
        } catch (Throwable $e) { $this->error = $e->getMessage(); return false; }
    }
    public function get_result(): LegacyDbResult
    {
        return new LegacyDbResult($this->stmt ? $this->stmt->fetchAll(PDO::FETCH_ASSOC) : []);
    }
    public function close(): void {}
}

final class LegacyDb
{
    public int $insert_id = 0;
    public int $affected_rows = 0;
    public string $error = '';
    public function __construct(private PDO $pdo) {}
    public function query(string $sql): LegacyDbResult|false
    {
        try {
            $st = $this->pdo->query($sql);
            $this->affected_rows = $st->rowCount();
            $this->insert_id = (int)$this->pdo->lastInsertId();
            return new LegacyDbResult($st->fetchAll(PDO::FETCH_ASSOC));
        } catch (Throwable $e) { $this->error = $e->getMessage(); return false; }
    }
    public function prepare(string $sql): LegacyDbStmt { return new LegacyDbStmt($this->pdo, $sql); }
    public function begin_transaction(): void { $this->pdo->beginTransaction(); }
    public function commit(): void { $this->pdo->commit(); }
    public function rollback(): void { if ($this->pdo->inTransaction()) $this->pdo->rollBack(); }
    public function real_escape_string(string $value): string
    { return substr((string)$this->pdo->quote($value), 1, -1); }
}
