<?php

$file = 'file.html';
$time = time();
$info = "Interation: $time</br>";

file_put_contents($file, $info, FILE_APPEND);

print "Script Ran at: $time";

?>