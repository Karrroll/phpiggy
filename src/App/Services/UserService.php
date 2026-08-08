<?php
declare(strict_types=1);

namespace App\Services;

use Exception;
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

  public function login(array $formData) {
    $user = $this->db->query("SELECT * FROM users WHERE email = :email", ['email' => $formData['email']])->find();

    $passwordsMatch = password_verify($formData['password'], $user['password'] ?? '');

    if(!$user || !$passwordsMatch) {
      throw new ValidationException(['password' => ['Invalid credentials']]);
    }

    session_regenerate_id();
    
    $_SESSION['user'] = $user['id'];
  }
}
