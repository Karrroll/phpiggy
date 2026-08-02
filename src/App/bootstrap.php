<?php
declare(strict_types=1);

include __DIR__ ."/../../vendor/autoload.php";

use Framework\App;
use App\Config\Paths;
use Dotenv\Dotenv;

use function App\Config\{registerRoutes, registerMiddleware};

//createImmutable - Create a new immutable dotenv instance
$dotenv = Dotenv::createImmutable(Paths::ROOT);

//load() - Read and load environment file(s).
$dotenv->load();

$app = new App(Paths::SOURCE . "App/container-definitions.php");

registerRoutes($app);
registerMiddleware($app);

return $app;