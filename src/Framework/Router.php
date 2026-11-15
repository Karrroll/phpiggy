<?php

declare(strict_types=1);

namespace Framework;

class Router
{
  private array $routes = [];
  private array $middlewares = [];
  private array $errorHandler;

  // method to add/register new route
  public function add(string $method, string $path, array $controller) {
    //convert into a regular expression
    $path = $this->normalizePath($path);
    
    // replace dynamic route parameters with a regex pattern
    // e.g. /transaction/{transaction}/ -> /transaction/5/ or /transaction/25/ etc
    $regexPath = preg_replace('#{[^/]+}#', '([^/]+)', $path);

    // add a new route (associative array - 'key' => value) to the routes array
    // [] in routes means ADD to array, without [] means overwrite array !!
    $this->routes[] = [
      'path' => $path,
      'method' => strtoupper($method),
      'controller' => $controller,
      'middlewares' => [],
      'regexPath' => $regexPath
    ];
  }

  private function normalizePath(string $path): string {
    $path = trim($path, '/');   //remove '/' from the begining and end
    $path = "/{$path}/";        //add '/' from the begining and end
    $path = preg_replace('#[/]{2,}#', '/', $path);  //replace multiple '//' with a single '/'

    return $path;
  }
 
  //dispatch means to execute or route a request to the appropriate handler based on the given information
  public function dispatch(string $path, string $method, ?Container $container = NULL) {
    $path = $this->normalizePath($path);
    $method = strtoupper($_POST['_METHOD'] ?? $method);

    //find a matching route among registered routes
    foreach ($this->routes as $route) {
      // preg_match() allows matching dynamic URL paths,
      // e.g. /transaction/5/, /transaction/25/ against a route like /transaction/{id}/
      if (!preg_match("#^{$route['regexPath']}$#", $path, $paramValues) || $route['method'] !== $method) {
        continue;
      }

      array_shift($paramValues);
      
      preg_match_all('#{([^/]+)}#', $route['path'], $paramKeys);
      $paramKeys = $paramKeys[1];

      $params = array_combine($paramKeys, $paramValues);

      // $class -> name of class
      // $function -> name of method
      [$class, $function] = $route['controller'];

      //instantiate an object of $class. e.g. $class = "App\Controllers\HomeController"
      $controllerInstance = $container ? $container->resolve($class) : new $class;
 
      //invoke a method. {$function} contains the method name as a string.
      $action = fn() => $controllerInstance->{$function}($params);

      $allMiddleware = [...$route['middlewares'], ...$this->middlewares];

      foreach($allMiddleware as $middleware) {
        $middlewareInstance = $container ? $container->resolve($middleware) : new $middleware;
        $action = fn() => $middlewareInstance->process($action);
      }

      $action();

      return;
    }

    $this->dispatchNotFound($container);  //if route not found
  }

  public function addMiddleware(string $middleware) {
    $this->middlewares[] = $middleware;
  }

  public function addRouteMiddleware(string $middleware) {
    $lastRouteKey = array_key_last($this->routes);
    $this->routes[$lastRouteKey]['middlewares'][] = $middleware;
  }

  public function setErrorHandler(array $controller) {
    $this->errorHandler = $controller;
  }

  public function dispatchNotFound(?Container $container) {
    [$class, $function] = $this->errorHandler;

    $controllerInstance = $container ? $container->resolve($class) : new $class;
    $action = fn() => $controllerInstance->$function();

    foreach($this->middlewares as $middleware) {
      $middlewareInstance = $container ? $container->resolve($middleware) : new $middleware;
      $action = fn() => $middlewareInstance->process($action);
    }
    $action();
  }
}
