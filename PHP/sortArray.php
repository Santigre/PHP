<?php

$names = array('bob','sue','ted','ralph','mark','fred','marge');

//prints all item in array as is
print_r($names);

print "<br>";
//sorts all items in array in alphabetical order
sort($names);
//prints the sorted array
print_r($names);

print "<br>";
//sorts the array in revers alphabetical order
rsort($names);
//prints the revers sorted array
print_r($names);

?>