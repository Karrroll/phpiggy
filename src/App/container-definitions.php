<?php
declare(strict_types=1);

namespace App;

use Framework\{TemplateEngine, Database, Container};
use App\Config\Paths;
use App\Services\{ValidatorService, UserService, TransactionService, ReceiptService};

//Return associative array definitions that tell the container how to create class instances
return [
  //associative array: 'key' => 'value', ...
  //e.g.
  // key: TemplateEngine::class = full class name e.g. Framework\TemplateEngine
  // =>: operator assigns a value to a key
  // A value return a new TemplateEngine object and pass Paths::VIEW to its constructor
  // value: fn() => new TemplateEngine(Paths::VIEW) = anonymous function like:
  //  function () {
  //    return new TemplateEngine(Paths::VIEW);
  //  }

  //Anonymous functions will be executed when they are called, NOT when they are defined
  TemplateEngine::class => fn() => new TemplateEngine(Paths::VIEW),
  ValidatorService::class => fn() => new ValidatorService(),
  Database::class => fn() => new Database(
    $_ENV['DB_DRIVER'],
    ['host' => $_ENV['DB_HOST'], 'dbname' => $_ENV['DB_NAME']],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS']
  ),
  UserService::class => function(Container $container) {
    $db = $container->get(Database::class);

    return new UserService($db);
  },
  TransactionService::class => function(Container $container) {
    $db = $container->get(Database::class);

    return new TransactionService($db);
  },
  ReceiptService::class => function(Container $container) {
    $db = $container->get(Database::class);

    return new ReceiptService($db);
  }
];
?>