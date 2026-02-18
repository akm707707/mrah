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
			
			$user = mysqli_fetch_array(mysqli_query($link,"SELECT * FROM `users` WHERE `eid`='$identifier' AND `id`='$userid' "), MYSQLI_ASSOC);
			$color = $user['color'];
			
			
			// $entity = array('id' => $id, 'Aname' => $Aname, 'crid' => $crid, 'taxid' => $taxid, 'vat' => $vat, 'fiscal' => $fiscal, 'username' => $username, 'identifier' => $identifier);
			$entity = array('Aname' => $Aname, 'crid' => $crid, 'taxid' => $taxid, 'vat' => $vat, 'fiscal' => $fiscal,'username' => $username,'userid' => $userid, 'identifier' => $identifier, 'clearance' => $clearance);
			
			$logo = glob('../../userdata/'.str_pad($identifier, 4, "0", STR_PAD_LEFT).'/cropped.*'); // Will find 2.txt, 2.php, 2.gif
			// if(!file_exists($logo[0])){
				// $logo = ''; $exist = 'no';
			// } else { $exist = 'yes'; }

			if ( $logo ) {
				if(!file_exists($logo[0])){
					$logo = ''; $exist = 'no';
				} else { $logo = $logo[0]; $exist = 'yes'; }
			} else {
				$logo = ''; $exist = 'no';
			} 


			$avatar = glob('../../userdata/'.str_pad($identifier, 4, "0", STR_PAD_LEFT).'/employee/'.str_pad($userid, 4, "0", STR_PAD_LEFT).'.*');
			
			if ( $avatar ) {
				if(!file_exists($avatar[0])){
					$avatar = ''; $avatarexist = 'no';
				} else { $avatar = $avatar[0]; $avatarexist = 'yes'; }
			} else {
				$avatar = ''; $avatarexist = 'no';
			} 

			$responseArray = array('id' => 'success', 'entity' => $entity, 'logo' => $logo, 'exist' => $exist, 'empname' => $empname, 'avatar' => $avatar, 'avatarexist' => $avatarexist, 'color' => $color );
		}
	} 
	
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $POST');
}


if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>