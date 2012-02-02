<?php

require_once 'error.php';

define('STRING_VALIDATOR', 'string');
define('ARRAY_VALIDATOR', 'array');

class validate {
  protected $type;
  protected $what;
  
  protected $errors;
  
  protected function __construct($type, $what) {
    if (!is_string($type))
      throw error::expecting_string();
    
    if ($type != STRING_VALIDATOR && $type != ARRAY_VALIDATOR)
      throw validator_error::invalid_type();
    
    if ($type == STRING_VALIDATOR && !is_string($what))
      throw error::expecting_string();
    if ($type == ARRAY_VALIDATOR && !is_array($what))
      throw error::expecting_array();
    
    $this->type = $type;
    $this->what = $what;
    
    $this->errors = array();
  }
  
  public static function string($string) {
    if (!is_string($string))
      throw error::expecting_string();
    
    $validator = new validate(STRING_VALIDATOR, $string);
    
    return $validator;
  }
  
  public static function arryay(array $array) {
    $validator = new validate(VALIDATOR_TYPE_ARRAY, $array);
    
    return $validator;
  }
  
  public function min_length($length) {
    if (!is_int($length) || $length < 0)
      throw error::expecting_unsigned_int();
    
    if ($this->type == STRING_VALIDATOR) {
      if (strlen($this->what) < $length)
        $this->errors[] = 'minimum length: '.$length;
    } elseif ($this->type == ARRAY_VALIDATOR) {
      if (sizeof($this->what) < $length)
        $this->errors[] = 'need at least '.$length.' element'.($length > 1 ? 's' : '');
    }
    
    return $this;
  }
  
  public function max_length($length) {
    if (!is_int($length) || $length <= 0)
      throw error::expecting_unsigned_int_gt_zero();
    
    if ($this->type == STRING_VALIDATOR) {
      if (strlen($this->what) > $length)
        $this->errors[] = 'maximum length: '.$length;
    } elseif ($this->type == ARRAY_VALIDATOR) {
      if (sizeof($this->what) > $length)
        $this->errors[] = 'at most '.$length.' element'.($length > 1 ? 's' : '');
    }
    
    return $this;
  }
  
  public function trim() {
    if ($this->type == STRING_VALIDATOR)
      $this->what = trim($this->what);
    elseif ($this->type == ARRAY_VALIDATOR)
      array_trim($this->what);
    
    return $this;
  }
  
  public function not_empty() {
    $length = 1;
    
    if ($this->type == STRING_VALIDATOR) {
      if (strlen($this->what) < $length)
        $this->errors[] = 'cannot be empty';
    } elseif ($this->type == ARRAY_VALIDATOR) {
      if (sizeof($this->what) < $length)
        $this->errors[] = 'need at least one element';
    }
    
    return $this;
    
    return $this;
  }
  
  public function htmlify() {
    if ($this->type == STRING_VALIDATOR)
      $this->what = htmlentities($this->what);
    elseif ($this->type == ARRAY_VALIDATOR) {
      foreach ($this->what as $i => $thing) {
        $this->what[$i] = htmlentities($thing);
      }
    }
    
    return $this;
  }
  
  public function is_file($prepend, $append) {
    if ($this->type == STRING_VALIDATOR) {
      if (!is_file($prepend.$this->what.$append))
        $this->errors[] = 'not a file: '. $this->what.$append;
    } elseif ($this->type == ARRAY_VALIDATOR) {
      foreach ($this->what as $i => $thing) {
        if (!is_file($prepend.$this->what.$append))
          $this->errors[] = 'not a file: '. $this->what.$append;
      }
    }
    
    return $this;
  }
  
  public function is_dir($prepend='', $append='') {
    if ($this->type == STRING_VALIDATOR) {
      if (!is_dir($prepend.$this->what.$append))
        $this->errors[] = 'not a directory: '. $this->what.$append;
    } elseif ($this->type == ARRAY_VALIDATOR) {
      foreach ($this->what as $i => $thing) {
        if (!is_dir($prepend.$this->what.$append))
          $this->errors[] = 'not a directory: '. $this->what.$append;
      }
    }
  
    return $this;
  }
  
  public function trim_slashes() {
    if ($this->type == STRING_VALIDATOR) {
      $this->what = trim_slashes($this->what);
    } elseif ($this->type == ARRAY_VALIDATOR) {
      foreach ($this->what as $i => $thing)
        $this->what[$i] = trim_slashes($thing);
    }
    
    return $this;
  }
  
  public function go() {
    return implode(', ', $this->errors);
  }
  
  public function __toString() {
    return $this->go();
  }
}

class validator_error extends error {
  public static function invalid_type() {
    return self::e('invalid type for validator');
  }
}

?>