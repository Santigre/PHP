<?php

$names = array('bob', 'tim', 'tom', 'shelly', 'julia');

foreach($names as $value) {
	print "$value </br>";
}
 
print "</br>";

sort($names);

foreach($names as $value) {
	print "$value </br>";
}
?> 