<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); /*Arabic*/	
require 'inc/functions.php'; 
session_start();

		$dataarr = mysqli_query($link,"SELECT * FROM `users` WHERE `id`='111' " );
		$data = mysqli_fetch_array($dataarr);
		if ( mysqli_num_rows ( $dataarr ) > 0 ) {
			$userid = $data['id'];
			$name = $data['name'];
			$mobile = $data['mobile'];
			$email = $data['email'];
			// $pass = $data['pass'];
			// $mrah = $data['mrah'];
			$marketvalue = $data['marketvalue'];
			$timecreated = $data['time'];

			// if ( $password == $pass ) {
			$_SESSION["loggedin"] = true;
			$_SESSION["userid"] = $userid;
			$_SESSION["name"] = $name;
			$_SESSION["mobile"] = $mobile;
			$_SESSION["email"] = $email;
			// $_SESSION["pass"] = $pass;
			// $_SESSION["mrah"] = $mrah;
			$_SESSION["time"] = $timecreated;
			if (isset($marketvalue) && !empty($marketvalue) )   {
				$marketvalue = explode(",",$marketvalue);	// convert to array
			}
			$_SESSION["marketvalue"] = $marketvalue;
			
			$finder = mysqli_query($link,"SELECT * FROM `mrah` WHERE userid = '$userid' ");
			$z = 0;
			if(@mysqli_num_rows($finder) > 0){ 
				while($mfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ 
					$mid = $mfinder['id']; 
					$mname = $mfinder['name'];
					$profit = $mfinder['profit'];
					$expense = $mfinder['expense'];
					$mrahdata = array('mid' => $mid,'mname' => $mname,'profit' => $profit,'expense' => $expense );
					$mrah[] = $mrahdata;
					$z++;
				}
			} else { $mrah = ''; }
			
			if ( mysqli_num_rows($finder) == 1 ) {
				$_SESSION["mrahid"] = $mid;
				$_SESSION["mrahname"] = $mname;
				$_SESSION["profit"] = $profit;
				$_SESSION["expense"] = $expense;
			}

			
			if ( isset($_SESSION['mrahid']) && !empty($_SESSION['mrahid']) && isset($_SESSION['mrahname']) && !empty($_SESSION['mrahname']) ) {
				$data = array('userid' => $userid, 'name' => $name, 'mobile' => $mobile, 'email' => $email, 'mrah' => $mrah,'marketvalue' => $marketvalue,'time' => $timecreated,'mrahid' => $_SESSION['mrahid'],'mrahname' => $_SESSION['mrahname'],'profit' => $_SESSION['profit'],'expense' => $_SESSION['expense']);

			} else {
				$data = array('userid' => $userid, 'name' => $name, 'mobile' => $mobile, 'email' => $email, 'mrah' => $mrah,'marketvalue' => $marketvalue,'time' => $timecreated);
			}
			$responseArray = array('id' => 'success', 'data' => $data );

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