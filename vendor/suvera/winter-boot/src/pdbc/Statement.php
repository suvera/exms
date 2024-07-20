<?php
declare(strict_types=1);

namespace dev\winterframework\pdbc;

interface Statement {
    const CLOSE_CURRENT_RESULT = 1;
    const KEEP_CURRENT_RESULT = 2;
    const CLOSE_ALL_RESULTS = 3;
    const SUCCESS_NO_INFO = -2;
    const EXECUTE_FAILED = -3;
    const RETURN_GENERATED_KEYS = 1;
    const NO_GENERATED_KEYS = 2;

    public function getConnection(): Connection;

    /**
     * Close the statement
     */
    public function close(): void;

    public function isClosed(): bool;

    public function closeOnCompletion(bool $closeOnCompletion): void;

    public function isCloseOnCompletion(): bool;

    /**
     * Cursor related
     */
    public function getQueryTimeout(): int;

    public function setQueryTimeout(int $queryTimeout): void;

    public function setCursorName(string $cursor): void;

    public function getResultSetType(): int;

    public function setFetchDirection(int $fetchDirection): void;

    public function getFetchDirection(): int;

    /**
     * Gives the PDBC driver a hint as to the number of rows that should be
     * fetched from the database when more rows are needed for ResultSet
     * objects generated by this Statement.
     * @param int $fetchSize
     */
    public function setFetchSize(int $fetchSize): void;

    public function getFetchSize(): int;

    /**
     * Sets the limit for the maximum number of rows that any ResultSet object
     * generated by this Statement object can contain to the given number.
     * @param int $max
     */
    public function setMaxRows(int $max): void;

    public function getMaxRows(): int;


    /**
     * ------------------------
     * Execute queries
     *
     * @param string $sql
     * @return ResultSet
     */
    public function executeQuery(string $sql): ResultSet;

    public function execute(
        string $sql,
        int $autoGeneratedKeys = self::NO_GENERATED_KEYS,
        array $columnIdxOrNames = []
    ): bool;

    /**
     * --------------------------
     * Getters
     */
    public function getResultSet(): ?ResultSet;

    public function getGeneratedKeys(): array;


    public function executeUpdate(
        string $sql,
        int $autoGeneratedKeys = self::NO_GENERATED_KEYS,
        array $columnIdxOrNames = []
    ): int;


    /**
     * -----------------------
     * Batch commands
     *
     * @param string $sql
     */
    public function addBatch(string $sql): void;

    public function executeBatch(): array;


}