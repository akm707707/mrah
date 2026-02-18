<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		
session_start();
//print_r($_POST);	
if($_POST){
	$time = time();
	$log = '';
	$sheeparray = [];
	$eventarray = [];
	if ( !$_SESSION ) {
		$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $_SESSION');
	} else {
		if ( !isset($_SESSION['loggedin']) || $_SESSION["loggedin"] !== true )   {
			$responseArray = array('id' => 'danger', 'message' => 'لم تقم بتسجيل الدخول');
		} else {			
			$userid = $_SESSION["userid"];
			$name = $_SESSION["name"];
			$mobile = $_SESSION["mobile"];
			$email = $_SESSION["email"];
			//$pass = $_SESSION["pass"];
			// $mrah = $_SESSION["mrah"];
			// $mrahid = $_SESSION["mrahid"];
			// $mrahname = $_SESSION["mrahname"];

			$record = mysqli_query($link,"SELECT * FROM `logs` WHERE userid = '$userid' ORDER BY id DESC   ");
			$lognum = mysqli_num_rows($record);
			if(@mysqli_num_rows($record) > 0){									
				$z = 0;				$responseArray = [];			$Totalloginfos = [];	
				while($loginfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
					$id = $loginfo['id'];
					$userid = $loginfo['userid'];
						$record1 = mysqli_query($link,"SELECT * FROM `users` WHERE id = '$userid' ");
						if(@mysqli_num_rows($record1) > 0){
							while($recordinfo1 = mysqli_fetch_array($record1, MYSQLI_ASSOC)){		$name = $recordinfo1['name'];	}
						} else { $name = 'غير معروف'; }
					$mrahid = $loginfo['mrahid'];
						$record2 = mysqli_query($link,"SELECT * FROM `mrah` WHERE id = '$mrahid' ");
						if(@mysqli_num_rows($record2) > 0){
							while($recordinfo2 = mysqli_fetch_array($record2, MYSQLI_ASSOC)){		$mrahname = $recordinfo2['name'];	}
						} else { $mrahname = 'غير معروف'; }
					$category = $loginfo['category'];	$category = arabic($category);
					$title = $loginfo['title'];
					$details = $loginfo['details'];							
					$timeadded = Time_Passed(date($loginfo['time']),'time');

					${'loginfo'.$z} = array('id' => $id,'userid' => $userid,'name' => $name,'mrahid' => $mrahid,'mrahname' => $mrahname, 'category' => $category , 'title' => $title , 'details' =>$details ,'timeadded' => $timeadded );	
					$z++;
				}
			}
			$responseArray = array('id' => 'success', 'lognum' => $lognum);
			
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($lognum) && $lognum > 0 ){	for($a=0;$a<$lognum;$a++){ array_push($Totalloginfos,${'loginfo'.$a}); }	array_push($responseArray,$Totalloginfos); }
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message

?>

