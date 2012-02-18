<?

function notes() {
  $notes = func_get_args();
  $html = array();
  
  foreach ($notes as $note) {
    if (strpos($note,'/') !== false ) {
      $split_notes = explode('/', $note);
      array_walk($split_notes,'trim');
      array_walk($split_notes,'notes');
      
      $html[] = implode('/', $split_notes);
    } else {
      $strlen = strlen($note)
      if ($strlen > 2 || $strlen == 0)
        continue;
      
      $mod = substr($note, -1);
      if ($strlen == 2) {
        switch ($mod) {
          case 'b':
            $mod = sup('b');
            break;
          case '#':
            $mod = sup('#');
            break;
          default:
            $mod = '';
            break;
        }
      }
      
      $note = substr($note, 0, 1);
      if (!preg_match('/[A-F]/i', $note))
        $note = '';
      
      if (empty($note) && empty($mod))
        continue;
      
      $html[] = o($note, $mod);
    }
  }
  
  return implode(', ', $html);
}