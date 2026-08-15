<?php
declare(strict_types=1);

namespace Framework;

use PDO, PDOException, PDOStatement;

class Database {
  private PDO $connection;
  private PDOStatement $stmt;

  public function __construct(string $driver, array $config, string $username, string $password ) {
    //http_build_query() to convert array for string with ; separator between properties
    $config = http_build_query(data: $config, arg_separator: ';');

    $dsn = "{$driver}:{$config}";
    try {
      $this->connection = new PDO($dsn, $username, $password, [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    } catch(PDOException $e) {
      die("Unable to connect to database.");
    }
  }

  public function query(string $query, array $params = []): Database {
    $this->stmt = $this->connection->prepare($query);
    $this->stmt->execute($params);

    return $this;
  }

  public function count() {
    return $this->stmt->fetchColumn();
  }

  public function find() {
    //return single result from query as an array
    return $this->stmt->fetch();
  }

  public function id() {
    //return last added record id's to database
    return $this->connection->lastInsertId();
  }

  public function findAll() {
    //return results from query as an array
    return $this->stmt->fetchAll();
  }
}