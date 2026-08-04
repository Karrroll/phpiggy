<?php
declare(strict_types=1);

namespace App\Services;

use Framework\Database;
use Framework\Exceptions\ValidationException;

//UserService -> interact with database
class UserService {
  //private Database $db -> constructor property promotion - add in constructor parameter list
  public function __construct(private Database $db) {
  }

  public function isEmailTaken(string $email) {
    $emailCount = $this->db->query("SELECT COUNT(*) FROM users WHERE email = :email", ['email' => $email])->count();
  
    if($emailCount > 0) {
      throw new ValidationException(['email' => 'Email taken']);
    }
  }

  public function create(array $formData) {
    $passwordHash= password_hash($formData['password'], PASSWORD_BCRYPT, ['cost' => 12]);
    
    $this->db->query(
      "INSERT INTO users(email, password, age, country, social_media_url)
      VALUES(:email, :password, :age, :country, :url)",
      [
        'email' => $formData['email'],
        'password' => $passwordHash,
        'age' => $formData['age'],
        'country' => $formData['country'],
        'url' => $formData['socialMediaURL']
      ]
    );
  }
}
