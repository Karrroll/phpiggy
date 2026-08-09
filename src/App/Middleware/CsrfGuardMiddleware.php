<?php
declare(strict_types=1);

namespace App\Middleware;

use Framework\Contracts\MiddlewareInterface;

class CsrfGuardMiddleware implements MiddlewareInterface {
  public function process(callable $next) {
    $requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);  //grab HTTP method
    //HTTP method to validate:
    //POST - creating data
    //PATH - updating data
    //DELETE - delete existing data
    $validMethods = ['POST', 'PATH', 'DELETE'];

    //check if current method is one of the $validMethods
    if(!in_array($requestMethod, $validMethods)) {
      $next();
      return;
    }

    //check if csrf token from form matches with the session token
    if($_SESSION['token'] !== $_POST['token']) {
      redirectTo('/');
    }

    //unset the session token to generate new one for the next form submission
    unset($_SESSION['token']);
    $next();
  }
}