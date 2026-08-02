<?php
declare(strict_types=1);

namespace App;

use Framework\{TemplateEngine, Database};
use App\Config\Paths;
use App\Services\ValidatorService;

return [
  TemplateEngine::class => fn() => new TemplateEngine(Paths::VIEW),
  ValidatorService::class => fn() => new ValidatorService(),
  Database::class => fn() => new Database(
    $_ENV['DB_DRIVER'],
    ['host' => $_ENV['DB_HOST'], 'dbname' => $_ENV['DB_NAME']],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS']
  )
];
?>