<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		
session_start();
//print_r($_POST);	
if($_POST){
	$time = time();
	$log = '';
	$updatelog = '';
	$suplog = '';
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
			// //$pass = $_SESSION["pass"];
			// $mrah = $_SESSION["mrah"];
			// $mrahid = $_SESSION["mrahid"];
			// $mrahname = $_SESSION["mrahname"];

			if(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'edit' )   {
				if (!isset($_POST['name']) || empty($_POST['name']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'الاسم مطلوب');
				} elseif (!isset($_POST['mobile']) || empty($_POST['mobile']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'رقم الجوال مطلوب');
				} elseif( !is_numeric($_POST['mobile']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'رقم الجوال يجب أن يحتوى على أرقام فقط');
				} elseif (strlen($_POST['mobile']) < 10) {
					$responseArray = array('id' => 'danger', 'message' => 'رقم الجوال يجب أن يكون 10 أرقام على الأقل');
				// } elseif(!isset($_POST['email']) || empty($_POST['email']) )   {
					// $responseArray = array('id' => 'danger', 'message' => 'البريد الالكتروني مطلوب');
				// } elseif ( !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
					// $responseArray = array('id' => 'danger', 'message' => 'صيفة البريد الالكتروني خاطئة');
				} else {
					$name = mysqli_real_escape_string($link, $_POST['name']);
					$mobile = mysqli_real_escape_string($link, $_POST['mobile']);		$mobile = ltrim($mobile, '0');	// remove leading zero
					// $email = mysqli_real_escape_string($link, $_POST['email']);
					
					// check if user exists
					$record = mysqli_query($link,"SELECT * FROM `users` WHERE id = '$userid'");
					if(@mysqli_num_rows($record) > 0){
						while($info = mysqli_fetch_array($record, MYSQLI_ASSOC)){ 							
							$existingname = $info['name'];
							$existingmobile = $info['mobile'];		
							// $existingemail = $info['email'];
						}

						// if ( $name == $existingname && $mobile == $existingmobile && $email == $existingemail ) {
						if ( $name == $existingname && $mobile == $existingmobile ) {
							$responseArray = array('id' => 'danger', 'message' => 'جميع البيانات الشخصية مطابقه للسابق');		goto end;
						} 
						
						// check if email exists with another user
						$record = mysqli_query($link,"SELECT * FROM `users` WHERE mobile = '$mobile' AND id <> $userid");
						if(@mysqli_num_rows($record) > 0){
							$responseArray = array('id' => 'danger', 'message' => 'رقم الجوال مضاف مسبقاً');		goto end;
							// $responseArray = array('id' => 'danger', 'message' => 'البريد الالكتروني مضاف مسبقاً');		goto end;
						}

						$log .= 'تم تحديث البيانات الشخصة ';
						if ( $existingname !== $name ) {
							$log .= ' بنغيير الاسم من '.$existingname;
							$log .= ' إلى '.$name;
						} 

						if ( $mobile !== $existingmobile ) {
							$log .= ' وتغيير الجوال  من '.$existingmobile;
							$log .= ' إلى '.$mobile;
						} 

						// if ( $email !== $existingemail ) {
							// $log .= ' وتغيير البريد الالكتروني  من '.$existingemail;
							// $log .= ' إلى '.$email;
						// } 
						
						// $update = mysqli_query($link,"UPDATE `users` SET `name`='$name', `mobile`='$mobile', `email`='$email' WHERE `id`='$userid'");
						$update = mysqli_query($link,"UPDATE `users` SET `name`='$name', `mobile`='$mobile' WHERE `id`='$userid'");
						
						if ($update) {	
							$responseArray = array('id' => 'success', 'message' => 'تم التحديث بنجاح' );
							$_SESSION["name"] = $name;
							$_SESSION["mobile"] = $mobile;
							// $_SESSION["email"] = $email;
							
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','0','users','تحديث البيانات الشخصة','$log','$time' )");
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $update faild');
						}
						
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على المستخدم المطلوب');	
					}
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'marketvalue' )   {
				if (!isset($_POST['0']) || empty($_POST['0']) || !is_numeric($_POST['0']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'القيمه السوقيه لعمر أقل من شهر يجب تحتوي على أرقام ولاتترك فارغه');
				} elseif (!isset($_POST['1']) || empty($_POST['1']) || !is_numeric($_POST['1']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'القيمه السوقيه لعمر شهر الى شهرين يجب تحتوي على أرقام ولاتترك فارغه');
				} elseif (!isset($_POST['2']) || empty($_POST['2']) || !is_numeric($_POST['2']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'القيمه السوقيه لعمر شهرين إلى ثلاث أشهر يجب تحتوي على أرقام ولاتترك فارغه');
				} elseif (!isset($_POST['3']) || empty($_POST['3']) || !is_numeric($_POST['3']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'القيمه السوقيه لعمر ثلاث إلى أربع أشهر يجب تحتوي على أرقام ولاتترك فارغه');
				} elseif (!isset($_POST['4']) || empty($_POST['4']) || !is_numeric($_POST['4']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'القيمه السوقيه لعمر أربع إلى خمس أشهر يجب تحتوي على أرقام ولاتترك فارغه');
				} elseif (!isset($_POST['5']) || empty($_POST['5']) || !is_numeric($_POST['5']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'القيمه السوقيه لعمر خمس إلى ستة أشهر يجب تحتوي على أرقام ولاتترك فارغه');
				} elseif (!isset($_POST['6']) || empty($_POST['6']) || !is_numeric($_POST['6']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'القيمه السوقيه لعمر ستة أشهر إلى سنه يجب تحتوي على أرقام ولاتترك فارغه');
				} elseif (!isset($_POST['7']) || empty($_POST['7']) || !is_numeric($_POST['7']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'القيمه السوقيه لعمر سنه إلى سنتبن يجب تحتوي على أرقام ولاتترك فارغه');
				} elseif (!isset($_POST['8']) || empty($_POST['8']) || !is_numeric($_POST['8']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'القيمه السوقيه لعمر سنتين إلى ثلاث سنوات يجب تحتوي على أرقام ولاتترك فارغه');
				} elseif (!isset($_POST['9']) || empty($_POST['9']) || !is_numeric($_POST['9']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'القيمه السوقيه لعمر ثلاث إلى أربع سنوات يجب تحتوي على أرقام ولاتترك فارغه');
				} elseif (!isset($_POST['10']) || empty($_POST['10']) || !is_numeric($_POST['10']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'القيمه السوقيه لعمر أربع إلى خمس سنوات يجب تحتوي على أرقام ولاتترك فارغه');
				} elseif (!isset($_POST['11']) || empty($_POST['11']) || !is_numeric($_POST['11']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'القيمه السوقيه لعمر أكبر من خمس سنوات يجب تحتوي على أرقام ولاتترك فارغه');
				} else {
					array_pop($_POST);	// removes key key
					$_POST = implode(",",$_POST);			// convert to string
					
					// check if water exists in water table
					$record = mysqli_query($link,"SELECT * FROM `users` WHERE id = '$userid'");
					if(@mysqli_num_rows($record) > 0){
						$log .= 'تم تحديث القيمة السوقية للأغنام';
						$update = mysqli_query($link,"UPDATE `users` SET `marketvalue`='$_POST' WHERE `id`='$userid'");
						
						if ($update) {	
							$responseArray = array('id' => 'success', 'message' => 'تم التحديث بنجاح' );
							$marketvalue = explode(",",$_POST);	// convert to array
							$_SESSION["marketvalue"] = $marketvalue;

							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','0','users','تحديث القيمة السوقية للأغنام','$log','$time' )");
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $update faild');
						}
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على المستخدم المطلوب');	
					}
				}	
			}
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($waternum) && $waternum > 0 ){	for($a=0;$a<$waternum;$a++){ array_push($Totalwaterinfos,${'waterinfo'.$a}); }	array_push($responseArray,$Totalwaterinfos); }
	if ( isset($suppliernum) && $suppliernum > 0 ){	for($a=0;$a<$suppliernum;$a++){ array_push($Totalsupplierinfos,${'supplierinfo'.$a}); }	array_push($responseArray,$Totalsupplierinfos); }
	if ( isset($purchasenum) && $purchasenum > 0 ){	for($a=0;$a<$purchasenum;$a++){ array_push($Totalpurchaseinfos,${'purchaseinfo'.$a}); }	array_push($responseArray,$Totalpurchaseinfos); }
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message

?>

