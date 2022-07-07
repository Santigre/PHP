<html>
<H1>PHP Example </h1>
<body>
	<form action="process.php" method = "post">
		Enter your name <input name="name" type="text">
		<input type="submit">
	</form>
	


</body>
<?PHP 
	$great = "Welcome";
	$name = "Santiago";
	$myva = "Variable";
	$number = 4;
	$number2 = 5;
	$sum = $number + $number2;
	$sum2 = $number + $number2; 
	$sum3 = $number * $number2; 
	$sum4 = $number / $number2; 
	
	$loggedIn = true;
	if ($loggedIn == true)
	{
		echo "Your Logged in!";
	}
	else{
		echo "You should log in!";
	}
	
	$people = array("Santiago", "Johanna", "Xochitl", "Leo");
	
	$numbers = array(1, 4, 7, 9);
	$sum5 = 0;
	
	foreach ($numbers as $number3){
		$sum5 = $sum5 + $number3;
	}
	
	echo $sum5;
	
	foreach ($people as $person){
		echo $person , ' ';
	}
	
	
	print "Hello world printed with php ";
	echo "Echo Hello World ";
	echo "$sum";
	echo $great, $name;
?>

</html>