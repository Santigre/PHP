<?php

$file = file('names.txt');

foreach($file as $line_num => $line) {
    print "$line_num: $line</br>"; 
}

print "</br>";

sort($file);

foreach($file as $line_num => $line) {
    print "$line_num: $line</br>"; 
}

?>