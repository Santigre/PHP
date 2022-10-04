<?php

$inventory = array(
	'orange' => 3,
	'blue' => 5,
	'green' => 7,
	'red' => 12,
	'white' => 8,
	'teal' => 1
);
//print everything in the array as is
print_r($inventory);

print "<br>";
// ksort sorts the keys alphabeticly
ksort($inventory);
//prints sorted array
print_r($inventory);

print "<br>";
// krsort sorts in reverse alphabetic order
krsort($inventory);

print_r($inventory);

?>