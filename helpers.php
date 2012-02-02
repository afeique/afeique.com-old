<?php

function array_trim(array &$array) {
  foreach ($array as $key => $value) {
    if (empty($value))
      unset($array[$key]);
    else
      break;
  }
  
  foreach (array_reverse($array, $preserve_keys=1) as $key => $value) {
    if (empty($value))
      unset($array[$key]);
    else
      break;
  }
} 

?>