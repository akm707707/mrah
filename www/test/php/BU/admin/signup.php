<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require '../../../inc/functions.php'; //include 'coordinates.php'; // 
	
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if (	!isset( $_POST['name'] ) || empty( $_POST['name']	)		)   {
		$responseArray = array(	'id' => 'danger' , 'alert' => 'يوجد خطأ في إسم المنشأه');
	} elseif (	!isset( $_POST['mobile'] ) || empty( $_POST['mobile']	)		)   {
		$responseArray = array(	'id' => 'danger' , 'alert' => 'يوجد خطأ في رقم الجوال');
	// } elseif (	!isset( $_POST['email'] ) || empty( $_POST['email']	)		)   {
		// $responseArray = array(	'id' => 'danger' , 'alert' => 'يوجد خطأ في البريد الإلكتروني');
	} elseif (	!isset( $_POST['password'] ) || empty( $_POST['password']	)		)   {
		$responseArray = array(	'id' => 'danger' , 'alert' => 'يوجد خطأ في كلمة المرور');
	} else {
		
		$name 		= mysqli_real_escape_string($link, $_POST['name']);						$name		= a2e($name);
		$mobile 	= mysqli_real_escape_string($link, $_POST['mobile']);					$mobile 	= a2e($mobile);
		// $email 		= mysqli_real_escape_string($link, $_POST['email']);					$email 		= a2e($email);
		$password 	= mysqli_real_escape_string($link, $_POST['password']);					
		$password 	= a2e($password);		
		$hashed 	= md5($password);
		$date      	= time();
		
		$ins = mysqli_query($link,"INSERT INTO `entities`(`id`,`Aname`,`Ename`,`crid`,`taxid`,`vat`,`fiscal`,`username`,`mobile`,`email`,`hashed`,`password`,`time`,`status`)VALUES(NULL,'$name',NULL,NULL,'15',NULL,'$mobile','$mobile',NULL,'$hashed','$password','$date','Active')");
			
		if ($ins) {
			$record = mysqli_query($link,"SELECT * FROM `entities` WHERE mobile = '$mobile'");
				if(@mysqli_num_rows($record) > 0){																			
					// while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){
						$identifier = $record['id'] ;
					// }
				
					$responseArray = array('id'=>'success', 'message'=>'تم التسجيل بنجاح'); 
					session_start();
					$_SESSION["loggedin"] = true;
					$_SESSION["username"] = $mobile;
					$_SESSION["identifier"] = $identifier;
				} else {
					$responseArray = array('id'=>'danger', 'message'=>'خطأ في الخادم. تم التسجيل بنجاح ولكن فشل إستخراج المعرف');
				}
		} else { 
			$responseArray = array('id'=>'danger', 'message'=>'الرقم أو البريد الإلكتروني مسجل مسبقاُ');	
		}
	}										
} else {	$responseArray = array('id'=>'danger', 'message'=>'خطأ في الخادم');	}
// if requested by AJAX request return JSON response
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>