<?php
include __DIR__ .'/src/Framework/Database.php';

use Framework\Database;

$db = new Database(
  'mysql',
  ['host' => 'localhost', 'dbname' => 'phpiggy'],
  'root',
  ''
);

try {
  $db->connection->beginTransaction();

  $db->connection->query("INSERT INTO products VALUES(NULL, 'Gloves')");
  $db->connection->query("INSERT INTO products VALUES(NULL, 'Hats')");
  
  //var_dump($db->connection->lastInsertId());

  $stmt = $db->connection->query("SELECT * FROM products");
  //var_dump($stmt->rowCount());
  var_dump($stmt->fetchAll(PDO::FETCH_ASSOC));

  $db->connection->commit();
} catch(Exception $error) {
  if($db->connection->inTransaction()) {
    $db->connection->rollBack();
  }

  echo "Transaction failed.";
}