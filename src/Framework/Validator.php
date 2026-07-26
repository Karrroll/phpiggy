<?php
declare(strict_types=1);

namespace Framework;

use Framework\Contracts\RuleInterface;
use Framework\Exceptions\ValidationException;

class Validator {
  private array $rules = [];

  public function add(string $alias, RuleInterface $rule) {
    $this->rules[$alias] = $rule;
  }

  public function validate(array $formData, array $fields) {
    $errors = [];

    foreach($fields as $fieldName => $rules) {     //single form input with rules array
      foreach($rules as $rule) {                  //single rule from array rules
        $ruleParams = [];
        //statement only for special custom params like "min:18" - otherwise $ruleParams is empty
        if(str_contains($rule, ':')) {
          [$rule, $ruleParams] = explode(':', $rule);
          $ruleParams = explode(',', $ruleParams);
        }
        
        $ruleValidator = $this->rules[$rule];     //assign rule to variable

        if($ruleValidator->validate($formData, $fieldName, $ruleParams)) {
          continue;
        }

        $errors[$fieldName][] = $ruleValidator->getMessage($formData, $fieldName, $ruleParams);
      }
    }

    if(count($errors)) {
      throw new ValidationException($errors);
    }
  }
}