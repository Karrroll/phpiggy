<?php
declare(strict_types=1);

namespace App\Services;

use Framework\Validator;

class ValidatorService {
  private Validator $validator;

  public function __construct() {
    $this->validator = new Validator();  //set validator property to a new instance of validator class
  }

  public function validateRegister(array $formData) {
    $this->validator->validate($formData);
  }
}
?>