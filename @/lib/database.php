<?php

class Database
{
  private static ?Database $instance = null;

  public static function getInstance(): Database
  {
    return self::$instance;
  }

  public static function init(Env $env): void
  {
    if (self::$instance != null) {
      return;
    }

    self::$instance = new self($env);
  }

  private PDO $pdo;

  private function __construct(Env $env)
  {
    $this->pdo = new PDO(
      $env->dbDsn,
      $env->dbUser,
      $env->dbPassword,
      [
        PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
      ]
    );
  }

  private function executeAndGetStatement(string $query, ?array $params = null): PDOStatement
  {
    $statement = $this->pdo->prepare($query);
    $statement->execute($params);

    return $statement;
  }

  public function execute(string $query, ?array $params = null)
  {
    $this->executeAndGetStatement($query, $params);
  }

  public function get(string $query, ?array $params = null): object | false
  {
    $statement = $this->executeAndGetStatement($query, $params);
    $result = $statement->fetch();

    return $result;
  }

  public function getAll(string $query, ?array $params = null): array
  {
    $statement = $this->executeAndGetStatement($query, $params);
    $result = $statement->fetchAll();

    return $result;
  }

  public function exists(string $query, ?array $params = null): bool
  {
    $result = $this->get($query, $params);
    return $result !== false;
  }

  public function lastInsertId()
  {
    return $this->pdo->lastInsertId();
  }
}
