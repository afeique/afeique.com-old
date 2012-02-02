<?php

require 'container.php';

/**
 * tag! you're it!
 *
 */
class tag extends container {
  protected $name;
  protected $self_closing;
  protected $attributes;
  protected $class;

  /**
   *
   * @param string $name
   * @param array $specifications
   */
  public function __construct($name, $self_closing=0) {
    if (!is_string($name))
      throw error::expecting_string();
    
    // instantiates: $this->content = array();
    parent::__construct();

    // instantiates remaining attributes
    $this->name = $name;
    $this->self_closing = $self_closing ? 1 : 0;
    $this->attributes = array();
    $this->class = array();
  }

  /**
   * Set the specified html $attribute.
   * 
   * Returns $this for chaining.
   * 
   */
  public function _($attribute, $value=null) {
    if (!is_string($attribute))
      throw error::expecting_string();
    
    if (!isset($value))
      $value = $attribute;
    elseif (!is_string($value))
      throw error::expecting_string();
      
    
    $this->attributes[$attribute] = $value;
    
    return $this;
  }
  
  /**
   * Shortcut for setting html attribute 'id'
   * 
   * Returns $this for chaining.
   * 
   */
  public function _i($id) {
    return $this->_('id', $id);
  }
  
  /**
   * Shortcut for setting html attribute 'href'
   * 
   * Returns $this for chaining.
   * 
   */
  public function _h($href) {
    return $this->_('href', $href);
  }
  
  /**
   * Shortcut for setting html attribute 'for'
   *
   * Returns $this for chaining.
   *
   */
  public function _f($for) {
    return $this->_('for', $for);
  }
  
  /**
   * Shortcut for setting html attribute 'action'
   *
   * Returns $this for chaining.
   *
   */
  public function _a($action) {
    return $this->_('action', BASE_URL.$action);
  }
  
  /**
   * Shortcut for setting html attribute 'method'
   *
   * Returns $this for chaining.
   *
   */
  public function _m($method) {
    return $this->_('method', $method);
  }
  
  /**
   * Shortcut for setting html attribute 'name'
   *
   * Returns $this for chaining.
   *
   */
  public function _n($name) {
    return $this->_('name', $name);
  }
  
  /**
   * Shortcut for setting html attribute 'type'
   *
   * Returns $this for chaining.
   *
   */
  public function _t($type) {
    return $this->_('type', $type);
  }
  
  /**
   * Shortcut for setting html attribute 'value'
   *
   * Returns $this for chaining.
   *
   */
  public function _v($value) {
    return $this->_('value', $value);
  }
  
  /**
   * Shortcut for setting html attribute 'style'
   *
   * Returns $this for chaining.
   *
   */
  public function _s($value) {
    return $this->_('style', $value);
  }

  /**
   * Adds the given class(es) to the internal
   * array.
   *
   * Explodes by ' ' if multiple classes are
   * provided.
   *
   * Returns 0 if a non-string class
   * name is given. Returns $this for
   * chaining.
   *
   * @param string $class
   */
  public function _c($class) {
    if (!is_string($class))
      throw error::expecting_string();

    $tmp = explode(' ', $class);
    foreach ($tmp as $class_name)
      $this->class[$class_name] = 1;

    return $this;
  }
  
  public function __toString() {
    // start opening tag
    $html = '<'. $this->name;

    // render attributes if applicable
    foreach ($this->attributes as $attribute => $value) {
      $html .= " $attribute=\"$value\"";
    }

    // if there is a class attribute, render that
    if (!empty($this->class))
      $html .= ' class="'.implode(' ', array_keys($this->class)).'"';

    // for a self-closing tag, close without rendering content
    if ($this->self_closing) {
      $html .= ' />';
    } else {
      // close the opening tag
      $html .= '>';
      	
      // render any content
      $html .= parent::__toString();
      	
      // close the tag
      $html .= '</'.$this->name.'>';
    }

    return $html;
  }
}

?>