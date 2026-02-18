<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); /*Arabic*/	
require '../inc/functions.php'; 
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if ( !$_SESSION ) {
		$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $_SESSION');
	} else {
		if ( !isset($_SESSION['loggedin']) || $_SESSION["loggedin"] !== true )   {
			$responseArray = array('id' => 'danger', 'message' => 'لم تقم بتسجيل الدخول');
		} elseif ( !isset($_SESSION['userstatus']) || $_SESSION["userstatus"] != 1 )   {
			$responseArray = array('id' => 'danger', 'message' => 'لا تمتلك التصريح');
		} elseif (!isset($_POST['color']) || empty($_POST['color']) )   {
			$responseArray = array('id' => 'danger', 'message' => 'no color submitted');
		} else {
			$color = mysqli_real_escape_string($link, $_POST['color']);
			$identifier = $_SESSION["identifier"];
			$userid = $_SESSION['userid'];
			
			$update = mysqli_query($link,"UPDATE `users` SET `color`='$color' WHERE `id`='$userid' AND `eid`='$identifier' ");
			
			if ($update) {
				$responseArray = array('id' => 'success', 'message' => 'update succeded');
			} else {
				$responseArray = array('id' => 'danger', 'message' => 'update failed');
			}

		}
	} 
	
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $POST');
}


if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>