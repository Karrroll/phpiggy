<?php
declare(strict_types=1);

namespace Framework;

use Framework\Contracts\RuleInterface;

class Validator {
  private array $rules = [];

  public function add(string $alias, RuleInterface $rule) {
    $this->rules[$alias] = $rule;
  }

  public function validate(array $formData, array $fields) {
    foreach($fields as $fieldName => $rules) {     //single form input with rules array
      foreach($rules as $rule) {                  //single rule from array rules
        $ruleValidator = $this->rules[$rule];     //assign rule to variable

        if($ruleValidator->validate($formData, $fieldName, [])) {
          continue;
        }

        echo "Error";
      }
    }
  }
}