<?php
/* This is a multiline
comment*/

//This is a Single Line Comment

//Creating Variables
$string = “Hello World”;
$int = 1;
$float = 2.2;
//Creating Variables with Bad Data
$garbage = $FLOAT + $int;
$garbage2 = $string + $int;

//Outputting Good Varibles
print “<h1>String</h1>”;
print $string;
print “<h1>Int</h1>”;
print $int;
print “<h1>Float</h1>”;
print $float;
//Ouputting Variables with Bad Data
print “<h1>Garbage (Capitalization Error)</h1>”;
print $garbage;
print “<h1>Garbage2 (Adding a String)</h1>”;
print $garbage2;
?>