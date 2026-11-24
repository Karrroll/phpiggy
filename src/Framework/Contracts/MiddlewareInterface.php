<?php
declare(strict_types=1);

namespace Framework\Contracts;

// Every class that implements this interface must include all the methods and constants defined in it
interface MiddlewareInterface {
  // A callable is a PHP variable type that stores a value that can be called like a function.
  // $next can store a function names, an anonymous function or an object method.
  // e.g. $next = 'showName' or $next = fn() => $middlewareInstance->process($action) etc.
  // showName or anonymous finction iare NOT functions they are just a function name stored as a string
  // The $next is executed when $next() is called
  public function process(callable $next);
}