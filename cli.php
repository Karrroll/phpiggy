<?php
include __DIR__ .'/src/Framework/Database.php';

use Framework\Database;

$db = new Database(
  'mysql',
  ['host' => 'localhost', 'dbname' => 'phpiggy'],
  'root',
  ''
);

$sqlFile = file_get_contents("./database.sql");
$db->query($sqlFile);   //Database method to SQL query