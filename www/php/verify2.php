<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); /*Arabic*/	
require 'inc/functions.php'; 
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$time = time();
	if ( !$_SESSION ) {
		$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $_SESSION');
	} else {
		if ( !isset($_SESSION['loggedin']) || $_SESSION["loggedin"] !== true )   {
			$responseArray = array('id' => 'danger', 'message' => 'لم تقم بتسجيل الدخول');
		// } elseif ( !isset($_SESSION['userstatus']) || $_SESSION["userstatus"] != 1 )   {
			// $responseArray = array('id' => 'danger', 'message' => 'لا تمتلك التصريح');
		} else {			

			$userid = $_SESSION["userid"];
			$name = $_SESSION["name"];
			$mobile = $_SESSION["mobile"];
			$email = $_SESSION["email"];
			// //$pass = $_SESSION["pass"];
			$marketvalue = $_SESSION["marketvalue"];

			$finder = mysqli_query($link,"SELECT * FROM `mrah` WHERE userid = '$userid' ");
			$z = 0;
			if(@mysqli_num_rows($finder) > 0){ 
				while($mfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ 
					$mid = $mfinder['id']; 
					$mname = $mfinder['name'];
					$profit = $mfinder['profit'];
					$mrahdata = array('mid' => $mid,'mname' => $mname,'profit' => $profit );
					$mrah[] = $mrahdata;
					$z++;
				}
			} else { $mrah = ''; }
			
			if ( mysqli_num_rows($finder) == 1 ) {
				$_SESSION["mrahid"] = $mid;
				$_SESSION["mrahname"] = $mname;
				$_SESSION["profit"] = $profit;
			}
			// $user = mysqli_fetch_array(mysqli_query($link,"SELECT * FROM `users` WHERE `id`='$userid' "), MYSQLI_ASSOC);
			
			
			if ( isset($_SESSION['mrahid']) && !empty($_SESSION['mrahid']) && isset($_SESSION['mrahname']) && !empty($_SESSION['mrahname']) ) {
				$data = array('userid' => $userid, 'name' => $name, 'mobile' => $mobile, 'email' => $email, 'mrah' => $mrah,'marketvalue' => $marketvalue,'time' => $time,'mrahid' => $_SESSION['mrahid'],'mrahname' => $_SESSION['mrahname'],'profit' => $_SESSION['profit']);

			} else {
				$data = array('userid' => $userid, 'name' => $name, 'mobile' => $mobile, 'email' => $email, 'mrah' => $mrah,'marketvalue' => $marketvalue,'time' => $time);
			}
			$responseArray = array('id' => 'success', 'data' => $data );
		}
	} 
	
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $POST');
}


if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	// if ( isset($notnum) && $notnum > 0 ){
		// for($a=0;$a<$notnum;$a++){
			// array_push($Totalnotinfo,${'notinfo'.$a}); 
		// }	
		// array_push($responseArray,$Totalnotinfo); 
	// }
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>