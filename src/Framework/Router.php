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
      // preg_match($pattern, $myURL, $resultArray ) allows matching dynamic URL paths,
      // e.g. /transaction/5/, /transaction/25/ against a route like /transaction/{id}/
      if (!preg_match("#^{$route['regexPath']}$#", $path, $paramValues) || $route['method'] !== $method) {
        continue;
      }

      // If pattern is matched, otherwise skip to next $route

      // Remove first array element (in this case first element is full matched path, next are extracted parameter values)
      // e.g 0=> [transaction/25/] 1=>[25]
      array_shift($paramValues);
      
      // Find PARAMETER NAMES in the route path like 'user', 'transaction'
      // e.g. transaction/{transaction} => $paramKeys store: [0]=>{transaction} (full regex value) [1]=>'transaction' (result) 
      preg_match_all('#{([^/]+)}#', $route['path'], $paramKeys);
      // Store name of parameter like 'transaction', 'user'
      $paramKeys = $paramKeys[1];

      // Create associative array with name and value e.g ['transaction' => 25]  
      $params = array_combine($paramKeys, $paramValues);

      // $class -> name of the controller class
      // $function -> name of the controller method
      [$class, $function] = $route['controller'];

      // Instantiate an object of controller $class. e.g. $class = "App\Controllers\HomeController"
      $controllerInstance = $container ? $container->resolve($class) : new $class;
 
      // Create an anonymous function. The function is NOT executed yet
      // {$function} contains the method name as a string like 'home', 'registerViev'
      // $params contains method parameters
      $action = fn() => $controllerInstance->{$function}($params);

      // Add new middleware and registered middlewares. Store them in $allMiddleaware
      // First add to $allMiddleware a new middleware
      // Execution order is reversed because each middleware wraps the previous action
      // So the new middleware will execute last
      $allMiddleware = [...$route['middlewares'], ...$this->middlewares];

      // Loop through all middelware clases
      foreach($allMiddleware as $middleware) {
        // Create an instance of the current middleware class
        $middlewareInstance = $container ? $container->resolve($middleware) : new $middleware;
        // Wrap the previous $action (controller instance) inside the current middleware
        // When $action() is called, the middleware's process() method will execute
        // The previous $action is passed to process() as the $next callable
        // This creates a middleware pipeline, with the controller anonymous function at the end
        $action = fn() => $middlewareInstance->process($action);
      }

      // Start executing the pipeline
      // This eventually runs all middleware and then the controller (if each middleware calls $next())
      $action();

      // Finish dispatching because a matching route has been found and executed
      return;
    }

    $this->dispatchNotFound($container);  //if route not found
  }

  //Add middleware class name to middlewares aray
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
