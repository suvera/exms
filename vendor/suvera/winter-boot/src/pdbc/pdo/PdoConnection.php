<?php
declare(strict_types=1);

namespace dev\winterframework\pdbc\pdo;

use dev\winterframework\pdbc\CallableStatement;
use dev\winterframework\pdbc\ex\CannotGetConnectionException;
use dev\winterframework\pdbc\ex\SQLException;
use dev\winterframework\pdbc\PreparedStatement;
use dev\winterframework\pdbc\ResultSet;
use dev\winterframework\pdbc\Statement;
use dev\winterframework\pdbc\support\AbstractConnection;
use dev\winterframework\pdbc\support\DatabaseMetaData;
use dev\winterframework\txn\Savepoint;
use PDO;
use Throwable;

class PdoConnection extends AbstractConnection {
    private ?PDO $pdo = null;

    /*
    private static array $supportedDrivers = [
        'pgsql',
        'sqlite',
        'mysql',
        'sybase',
        'mssql',
        'dblib',
        'cubrid',
        'firebird',
        'ibm',
        'informix',
        'sqlsrv',
        'oci',
        'odbc',
    ];
    */

    public function __construct(
        private string $dsn,
        private ?string $username = null,
        private ?string $password = null,
        private array $options = []
    ) {
        $this->doConnect();
    }

    public function getPdo(): PDO {
        $this->assertConnectionOpen();
        return $this->pdo;
    }

    public function reConnect(): void {
        $this->doConnect();
    }

    private function doConnect() {
        if (isset($this->options['idleTimeout'])) {
            $this->idleTimeout = $this->options['idleTimeout'];
            unset($this->options['idleTimeout']);
        }
        $this->lastAccessTime = time();
        $this->lastIdleCheck = time();

        try {
            $this->pdo = new PDO($this->dsn, $this->username, $this->password, $this->options);
        } catch (Throwable $e) {
            throw new CannotGetConnectionException('Could not connect to datasource', 0, $e);
        }
    }

    private function assertConnectionOpen(): void {
        $this->lastAccessTime = time();
        if (!isset($this->pdo)) {
            $this->reConnect();
        }
    }

    /**
     * --------------------------
     * Implemented Methods
     */
    public function close($safe = false): void {
        if ($this->pdo) {
            self::logInfo('PDO Connection Closed -  safe ' . $safe);
        }
        $this->pdo = null;
    }

    public function isClosed(): bool {
        return is_null($this->pdo);
    }

    public function setSchema(string $schema): void {
        throw new SQLException('Driver does not support this function ' . __METHOD__);
    }

    public function getDriverType(): mixed {
        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    public function getSchema(): string {
        $this->assertConnectionOpen();

        return match ($this->getDriverType()) {
            'oci' => $this->username,
            'mysql' => $this->pdo->query('select database()')->fetchColumn(),
            default => '',
        };
    }

    public function createStatement(
        int $resultSetType = ResultSet::TYPE_FORWARD_ONLY
    ): Statement {
        $this->assertConnectionOpen();

        $stmt = new PdoQueryStatement($this);
        $stmt->setResultSetType($resultSetType);
        return $stmt;
    }

    public function prepareStatement(
        string $sql,
        int $autoGeneratedKeys = Statement::NO_GENERATED_KEYS,
        array $columnIdxOrNameOrs = [],
        int $resultSetType = ResultSet::TYPE_FORWARD_ONLY
    ): PreparedStatement {
        $this->assertConnectionOpen();

        $stmt = new PdoPreparedStatement($this, $sql);
        $stmt->setResultSetType($resultSetType);
        return $stmt;
    }

    public function prepareCall(
        string $sql,
        int $resultSetType = ResultSet::TYPE_FORWARD_ONLY
    ): CallableStatement {
        $this->assertConnectionOpen();

        $stmt = new PdoCallableStatement($this, $sql);
        $stmt->setResultSetType($resultSetType);
        return $stmt;
    }

    public function getMetaData(): DatabaseMetaData {
        // TODO:
        return new DatabaseMetaData();
    }

    public function beginTransaction(): void {
        $this->assertConnectionOpen();

        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        }
    }

    public function commit(): void {
        $this->assertConnectionOpen();

        if ($this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    }

    public function rollback(Savepoint $savepoint = null): void {
        $this->assertConnectionOpen();
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    public function setSavepoint(string $name = null): Savepoint {
        throw new SQLException('Driver does not support this function ' . __METHOD__);
    }

    public function releaseSavepoint(Savepoint $savepoint): void {
        throw new SQLException('Driver does not support this function ' . __METHOD__);
    }

    public function setClientInfo(array $keyPair): void {
        throw new SQLException('Driver does not support this function ' . __METHOD__);
    }

    public function setClientInfoValue(string $name, string $value): void {
        throw new SQLException('Driver does not support this function ' . __METHOD__);
    }

    public function getClientInfo(): array {
        throw new SQLException('Driver does not support this function ' . __METHOD__);
    }

    public function getClientInfoValue(string $name): string {
        throw new SQLException('Driver does not support this function ' . __METHOD__);
    }

    public function isSavepointAllowed(): bool {
        return false;
    }

    public function getRowPreFetch(): int {
        if (isset($this->options[PDO::ATTR_PREFETCH]) && is_numeric($this->options[PDO::ATTR_PREFETCH])) {
            return intval($this->options[PDO::ATTR_PREFETCH]);
        }
        return 0;
    }

}