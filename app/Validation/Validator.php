<?php

namespace LoginScript\Validation;

use LoginScript\Validation\Exception\ValidatorException;

use \ReflectionMethod;

class Validator {

  protected $failed = [];
  protected $error_messages;

  public function __construct(array $error_messages = []) {
    if (empty($error_messages)) {
      throw new ValidatorException('Error messages missing');
    }

    $this->error_messages = $error_messages;
  }

  public function validate(array $data, array $rules) {
    $items = $rules;
    $rules = [];

    $failed = [];

    foreach ($items as $item => $rules) {
      foreach ($rules as $rule => $rule_value) {
        $value = $data[$item];

        if (!method_exists($this, $rule)) {
          throw new ValidatorException('Invalid/Unknown validation rule');
        }

        $rule_method = new ReflectionMethod(get_class($this), $rule);

        if ($rule_method->getNumberOfRequiredParameters() > 1) {
          if ( $rule == 'required_if_filled' && !$this->$rule($value, $data[$rule_value]) ) {
            $failed[$rule_value][] = $rule;
          }

          if (!$this->$rule($value, $rule_value)) {
            $failed[$item][] = $rule;
          }
        } else {
          if (!$rule_value) {
            throw new ValidatorException('Invalid/Unknown validation rule value');
          }

          if ($rule == 'optional') {
            if ($this->$rule($value)) {
              break;
            }
          } else if (!$this->$rule($value)) {
            $failed[$item][] = $rule;
          }
        }
      }
    }

    $this->failed = $failed;

    return $this;
  }

  public function passed() : bool {
    return empty($this->failed);
  }

  public function getErrors() : array {
    $error_messages = $this->error_messages;
    $errors = [];

    foreach ($this->failed as $field => $rules) {
      foreach ($rules as $rule) {
        if ($rule == 'optional') {
          continue;
        }

        if (empty($error_messages[$field][$rule])) {
          throw new ValidatorException('Invalid/Unknown error message');
        }

        $errors[$field][] = $error_messages[$field][$rule];
      }
    }

    return $errors;
  }

  public function getError(string $field, string $rule) : string {
    $error = $this->getErrors()[$field][$rule];

    return !empty($error) ? $error : '';
  }

  protected function empty(string $value) : bool {
    return empty($value);
  }

  protected function required(string $value) : bool {
    return !$this->empty($value);
  }

  protected function required_if_filled(string $value, string $field_value) : bool {
    return !$this->empty($field_value) && !$this->empty($value);
  }

  protected function optional(string $value) : bool {
    return $this->empty($value);
  }

  protected function minlength(string $value, int $min) : bool {
    return strlen($value) >= $min;
  }

  protected function maxlength(string $value, int $max) : bool {
    return strlen($value) <= $max;
  }

  protected function email(string $value) : bool {
    return (filter_var($value, FILTER_VALIDATE_EMAIL) !== false);
  }

}
