<?php
declare(strict_types=1);

namespace dev\winterframework\pdbc\oci;

use dev\winterframework\pdbc\ex\SQLFeatureNotSupportedException;
use dev\winterframework\pdbc\support\AbstractStatement;

class OciQueryStatement extends AbstractStatement {
    private mixed $stmt = null;
    private ?OciResultSet $resultSet = null;
    private array $generatedKeys = [];
    private int $commitMode;

    public function __construct(
        protected OciConnection $connection
    ) {
        $this->commitMode = $this->connection->getCommitMode();

        parent::__construct();
    }

    public function __destruct() {
        $this->close();
    }

    public function getStatement(): mixed {
        return $this->stmt;
    }

    public function getConnection(): OciConnection {
        return $this->connection;
    }

    public function close(): void {
        if (isset($this->stmt)) {
            oci_free_statement($this->stmt);
        }
        $this->stmt = null;
        $this->reset();
    }

    private function reset(): void {
        $this->resultSet = null;
        $this->generatedKeys = [];
    }

    public function isClosed(): bool {
        return !isset($this->stmt);
    }

    public function executeQuery(string $sql): OciResultSet {
        $this->close();

        $this->stmt = oci_parse($this->connection->getOci(), $sql);
        if ($this->getConnection()->getRowPreFetch() > 0) {
            oci_set_prefetch($this->stmt, $this->getConnection()->getRowPreFetch());
        }
        oci_execute($this->stmt, $this->commitMode);

        $this->resultSet = new OciResultSet($this, $this->cursorName);
        $this->generatedKeys = [];
        return $this->resultSet;
    }

    public function execute(
        string $sql,
        int $autoGeneratedKeys = self::NO_GENERATED_KEYS,
        array $columnIdxOrNames = []
    ): bool {
        $this->close();
        $this->reset();

        $this->stmt = oci_parse($this->connection->getOci(), $sql);
        if ($this->getConnection()->getRowPreFetch() > 0) {
            oci_set_prefetch($this->stmt, $this->getConnection()->getRowPreFetch());
        }
        oci_execute($this->stmt, $this->commitMode);

        if ($autoGeneratedKeys == self::RETURN_GENERATED_KEYS) {
            $this->loadAutoGeneratedKeys($columnIdxOrNames);
        }
        $this->resultSet = new OciResultSet($this, $this->cursorName);
        return true;
    }

    public function executeUpdate(
        string $sql,
        int $autoGeneratedKeys = self::NO_GENERATED_KEYS,
        array $columnIdxOrNames = []
    ): int {
        $this->close();
        $this->reset();

        $this->stmt = oci_parse($this->connection->getOci(), $sql);
        oci_execute($this->stmt, $this->commitMode);

        $count = oci_num_rows($this->stmt);
        if ($autoGeneratedKeys == self::RETURN_GENERATED_KEYS) {
            $this->loadAutoGeneratedKeys($columnIdxOrNames);
        }

        $this->close();
        return $count;
    }

    private function loadAutoGeneratedKeys(array $columnIdxOrNames): void {
        // TODO:
    }

    public function executeBatch(): array {
        throw new SQLFeatureNotSupportedException('executeBatch is not supported');
    }

    public function getResultSet(): ?OciResultSet {
        return $this->resultSet;
    }

    public function getGeneratedKeys(): array {
        return $this->generatedKeys;
    }


}