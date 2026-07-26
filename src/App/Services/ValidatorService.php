<?php
declare(strict_types=1);

namespace App\Services;

use Framework\Validator;
use Framework\Rules\{RequiredRule};

class ValidatorService {
  private Validator $validator;

  public function __construct() {
    $this->validator = new Validator();  //set validator property to a new instance of validator class

    $this->validator->add('required', new RequiredRule());
  }

  public function validateRegister(array $formData) {
    $this->validator->validate($formData, [
      //for each input form field add the required rule aliases
      'email' => ['required'],
      'age' => ['required'],
      'country' => ['required'],
      'socialMediaURL' => ['required'],
      'password' => ['required'],
      'confirmPassword' => ['required'],
      'terms' => ['required']
    ]);
  }
}
?>