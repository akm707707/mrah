<?php header('Content-type: application/json');		header('Content-Type: text/html; charset=utf-8'); 	
header('cache-control: no-cache'); 					require '../../inc/functions.php'; 
// print_r($_POST);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
} else {
	if(isset($_POST['mobile']) && !empty($_POST['mobile']))   {
		$mobile   = mysqli_real_escape_string($link, $_POST['mobile']);
		$entity = mysqli_fetch_array(mysqli_query($link,"SELECT * FROM `entities` WHERE `mobile`='$mobile' "));			$identifier = $entity['id'];		
		$responseArray = array('id' => 'success', 'message' => 'تم الاستعلاد بنجاح', 'identifier' => $identifier);
	} else {
		$responseArray = array('id' => 'danger', 'message' => 'من فضلك حاول في وقت لاحق');
	}
}
// if requested by AJAX request return JSON response
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
}
?>