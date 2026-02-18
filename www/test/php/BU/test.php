<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		
// foreach($_POST as $item) {	echo array_search($item, $_POST);	echo ': ';	if ( is_array($item) ) { var_dump($item); } else { echo $item; }	echo '<br>';	}
$arr = ['0' => 'a', '1' => 'b', '2' => 'c'];
$arr2 = ['1','2','3'];
$arr = implode(",",$arr);
$arr2 = implode(",",$arr2);
$ins = mysqli_query($link,"INSERT INTO `test`( `id`,`text` )VALUES( NULL, '$arr' )");			

// $record = mysqli_query($link,"SELECT * FROM `test` WHERE id = 3 ");
// while($barcodeinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
	// $text = $barcodeinfo['text'];
// }

 // print_r (explode(":",$text));

?>