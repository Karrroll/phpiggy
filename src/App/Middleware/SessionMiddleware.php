<?php
declare(strict_types=1);

namespace App\Middleware;

use Framework\Contracts\MiddlewareInterface;
use App\Exception\SessionException;

class SessionMiddleware implements MiddlewareInterface {
  public function process(callable $next) {
    if(session_status() === PHP_SESSION_ACTIVE) {           //Ensure the session is not started already
      throw new SessionException("Session already active.");
    }

    if(headers_sent()) {
      throw new SessionException("Headers already sent.");  //Ensure HTTP headers can still be sent before starting the session
    }

    session_set_cookie_params([
      //$_ENV['APP_ENV'] -> environment variable
      //prevents cookies from being sent over insecure (HTTP) connections
      'secure' => $_ENV['APP_ENV'] === 'production',
      //prevents JS from accessing cookie
      'httponly' => true,
      //prevents cookies from being sent with most cross-site requests
      'samesite' => 'lax'
    ]);
    
    session_start();

    $next();

    //session_write_close(); ???????????
  }
}