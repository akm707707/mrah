<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		
session_start();
//print_r($_POST);	
if($_POST){
	$time = time();
	if ( !$_SESSION ) {
		$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $_SESSION');
	} else {
		if ( !isset($_SESSION['loggedin']) || $_SESSION["loggedin"] !== true )   {
			$responseArray = array('id' => 'danger', 'message' => 'لم تقم بتسجيل الدخول');
		} elseif ( !isset($_SESSION['userstatus']) || $_SESSION["userstatus"] != 1 )   {
			$responseArray = array('id' => 'danger', 'message' => 'لا تمتلك التصريح');
		} else {			
			$identifier = $_SESSION["identifier"];
			$entity = mysqli_fetch_array(mysqli_query($link,"SELECT * FROM `entities` WHERE `id`='$identifier'"), MYSQLI_ASSOC);
			$Aname = $entity['Aname'];
			$Ename = $entity['Ename'];
			$crid = $entity['crid'];
			$taxid = $entity['taxid'];
			$vat = $entity['vat'];
			$fiscal = $entity['fiscal'];
			$username = $_SESSION['username'];
			$userid = $_SESSION['userid'];
			$clearance = $_SESSION['userclearance'];
			$empname = $_SESSION['name'];
		
			 
			$record = mysqli_query($link,"SELECT * FROM `logs` WHERE `eid`='$identifier' ");
			$lognum = mysqli_num_rows($record);
			
			if(@mysqli_num_rows($record) > 0){									$z = 0;				$responseArray = [];			$Totalloginfos = [];	
				while($loginfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
					$id = $loginfo['id'];
					$name = $loginfo['name'];
					$section = $loginfo['section'];
					$info = $loginfo['info'];
					$timeadded = $loginfo['timeadded'];											$timeadded = Time_Passed(date($loginfo['timeadded']),'time');

				${'loginfo'.$z} = array('id' => $id,'name' => $name,'section' => $section,'info' => $info,'timeadded' => $timeadded );	
				$z++;
			}
			$responseArray = array('id' => 'success', 'lognum' => $lognum);
			} else { 																				
				$responseArray = array('id' => 'success', 'message' => 'no log exists');
			}
			
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($lognum) && $lognum > 0 ){	for($a=0;$a<$lognum;$a++){ array_push($Totalloginfos,${'loginfo'.$a}); }	array_push($responseArray,$Totalloginfos); }
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>