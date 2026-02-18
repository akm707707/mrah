<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); /*Arabic*/	require '../inc/functions.php'; 
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// if ( $_SESSION["loggedin"] == true && $_SESSION["username"] ) {
/*				
				$_SESSION["name"] = $name;
				$_SESSION["username"] = $mobile;
				$_SESSION["identifier"] = $key;
				$_SESSION["userid"] = $userid;
				$_SESSION["userclearance"] = $clearance;
*/	
	if ( !$_SESSION ) {
		$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $_SESSION');
	} else {
		if ( !isset($_SESSION['loggedin']) || $_SESSION["loggedin"] !== true )   {
			$responseArray = array('id' => 'danger', 'message' => 'لم تقم بتسجيل الدخول');
		} elseif ( !isset($_SESSION['userstatus']) || $_SESSION["userstatus"] !== 1 )   {
			$responseArray = array('id' => 'danger', 'message' => 'لا تمتلك التصريح');
		} else {
			
			// $username = $_SESSION["username"];
			// $mobile = $_SESSION["mobile"];
			$identifier = $_SESSION["identifier"];
			$entity = mysqli_fetch_array(mysqli_query($link,"SELECT * FROM `entities` WHERE `id`='$identifier'"), MYSQLI_ASSOC);
			$id = $entity['id'];
			$Aname = $entity['Aname'];
			$Ename = $entity['Ename'];
			$crid = $entity['crid'];
			$taxid = $entity['taxid'];
			$vat = $entity['vat'];
			$fiscal = $entity['fiscal'];
			$username = $entity['username'];
			$mobile = $entity['mobile'];
			$email = $entity['email'];
			
			$entity = array('id' => $id, 'Aname' => $Aname, 'crid' => $crid, 'taxid' => $taxid, 'vat' => $vat, 'fiscal' => $fiscal, 'username' => $username, 'identifier' => $identifier);
			
			$contacts = mysqli_fetch_array(mysqli_query($link,"SELECT * FROM `contacts` WHERE `identifier`='$identifier'"), MYSQLI_ASSOC);
			$address = $contacts['address'];
			$website = $contacts['website'];
			$email = $contacts['email'];
			$mobile = $contacts['mobile'];
			$landline1 = $contacts['landline1'];
			$landline2 = $contacts['landline2'];
			$fax = $contacts['fax'];
			$twitter = $contacts['twitter'];
			$facebook = $contacts['facebook'];
			$instagram = $contacts['instagram'];
			$snapchat = $contacts['snapchat'];
			$whatsapp = $contacts['whatsapp'];
			$linkedin = $contacts['linkedin'];
			
			$contacts = array('address' => $address, 'website' => $website, 'email' => $email, 'mobile' => $mobile, 'landline1' => $landline1, 'landline2' => $landline2, 'fax' => $fax, 'twitter' => $twitter, 'facebook' => $facebook, 'instagram' => $instagram, 'snapchat' => $snapchat, 'whatsapp' => $whatsapp, 'linkedin' => $linkedin);

			$logo = '../../userdata/'.str_pad($identifier, 4, "0", STR_PAD_LEFT).'/cropped.png';
			if(!file_exists($logo)){
				$logo = '../../userdata/'.str_pad($identifier, 4, "0", STR_PAD_LEFT).'/cropped.jpg';
				if(!file_exists($logo)){
					$logo = '../../userdata/'.str_pad($identifier, 4, "0", STR_PAD_LEFT).'/cropped.jpeg';
					if(!file_exists($logo)){
						$logo = ''; $exist = 'no';
					}
				} else { $exist = 'yes'; }
			} else { $exist = 'yes'; }

			$responseArray = array('id' => 'success', 'entity' => $entity	, 'contacts' => $contacts , 'logo' => $logo , 'exist' => $exist );
		} else {
			$responseArray = array('id' => 'danger', 'message' => 'no tokens');
		}
	} 
	
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $POST');
}


if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>