<?php
declare(strict_types=1);

namespace Framework;

class App {
  //declare App properties
  private Router $router;
  private Container $container;

  //?string $containerDefinitionsPath = NULL - meaans can be NULL or string
  public function __construct(?string $containerDefinitionsPath = NULL) {
    
  //Create objects and assign them to properties
    $this->router = new Router();
    $this->container = new Container();

    //If a definitions file path is provided, load the definitions and pass them to the Container definitions array.
    if($containerDefinitionsPath) {
      $containerDefinitions = include $containerDefinitionsPath;
      $this->container->addDefinitions($containerDefinitions);
    }
  }

  public function run() {
    //get URL path e.g. "/", "/about", "/transaction"
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    //get request method
    $method = $_SERVER['REQUEST_METHOD'];

    $this->router->dispatch($path, $method, $this->container);
  }

  //method to add/register a new GET route in the Router class.
  public function get(string $path, array $controller) : App {
  // access to Router add method   
  $this->router->add('GET', $path, $controller);

  //return current App object - helps with method chaining - allow call multiple methods one after another
    return $this;
  }

  //method to add/register a new POST route in the Router class.
  public function post(string $path, array $controller) : App {
    $this->router->add('POST', $path, $controller);

    return $this;
  }

  //Pass the middleware class name ($middleware) to the Router's addMiddleware() method 
  public function addMiddleware(string $middleware) {
    $this->router->addMiddleware($middleware);
  }

  public function add(string $middleware) {
    $this->router->addRouteMiddleware($middleware);
  }

  public function delete(string $path, array $controller) : App {
    $this->router->add('DELETE', $path, $controller);

    return $this;
  }

  public function setErrorHandler(array $controller) {
    $this->router->setErrorHandler($controller);
  }
}