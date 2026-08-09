<?php
declare(strict_types=1);

namespace App\Middleware;

use Framework\Contracts\MiddlewareInterface;

class GuestOnlyMiddleware implements MiddlewareInterface {
  public function process(callable $next) {
    //redirect to home page if user is logged in
    if(!empty($_SESSION['user'])) {
      redirectTo('/');
    }

    $next();
  }
}