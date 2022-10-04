<?php

$shirts = array('long sleeve', 't shirt', 'sweater');
$bottoms = array('pants', 'shorts', 'skirt');
$types = array('top' => $shirts, 'bottom' => $bottoms);

print "{$types['top'][2]}";
print "(@types['bottom'][1])"
?>