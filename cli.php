<?php
include __DIR__ .'/src/Framework/Database.php';
include __DIR__ ."/vendor/autoload.php";

use Framework\Database;
use Dotenv\Dotenv;
use App\Config\Paths;

//createImmutable - Create a new immutable dotenv instance
$dotenv = Dotenv::createImmutable(Paths::ROOT);

//load() - Read and load environment file(s).
$dotenv->load();

$db = new Database(
  $_ENV['DB_DRIVER'],
  ['host' => $_ENV['DB_HOST'], 'dbname' => $_ENV['DB_NAME']],
  $_ENV['DB_USER'],
  $_ENV['DB_PASS']
);

$sqlFile = file_get_contents("./database.sql");
$db->query($sqlFile);   //Database method to SQL query