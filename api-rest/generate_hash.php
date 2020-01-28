<?php
  $time = time();
  echo 'Time: ' . $time . PHP_EOL . 'Hash: ' . sha1($argv[1] . $time . 'Clave secreta! No la digas a nadie') . PHP_EOL;
?>