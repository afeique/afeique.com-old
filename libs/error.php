<?php

/**
 * error class contains only static functions that
 * return Exception objects for throwing
 * 
 * its primary purpose is to simply store the most
 * common errors in a single place and make throwing
 * an Exception/error more succinct
 * 
 */
class error {
  protected static function e($msg) {
    if (!is_string($msg))
      throw error::expecting_string();
    
    return new Exception($msg);
  }
  
  public static function expecting_string() {
    return self::e('expecting string');
  }
  
  public static function expecting_int() {
    return self::e('expecting int');
  }
  
  public static function expecting_unsigned_int() {
    return self::e('expecting unsigned int');
  }
  
  public static function expecting_unsigned_int_gt_zero() {
    return self::e('expecting unsigned int greater than zero');
  }
  
  public static function expecting_scalar() {
    return self::e('expecting scalar');
  }
  
  public static function expecting_array() {
    return self::e('expecting array');
  }
  
  public static function expecting_renderable() {
    return self::e('expecting something renderable');
  }
}