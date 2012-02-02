<?php

if ((int)date('Y') > 2011)
  $date = '2011 - '. date('Y');
else
  $date = '2011';

$content = l('footer')->_c('span-24 text-center')->__(
    "&copy; $date"
);