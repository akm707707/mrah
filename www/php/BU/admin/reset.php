<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require '../../../inc/functions.php'; //include 'coordinates.php'; // 
	
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if (	!isset( $_POST['key'] ) || empty( $_POST['key']	)		)   {
		$responseArray = array(	'id' => 'danger' , 'alert' => 'يوجد خطأ في رمز المنشأه');
	} elseif (	!isset( $_POST['mobile'] ) || empty( $_POST['mobile']	)		)   {
		$responseArray = array(	'id' => 'danger' , 'alert' => 'يوجد خطأ في رقم الجوال');
	} elseif (	!isset( $_POST['password'] ) || empty( $_POST['password']	)		)   {
		$responseArray = array(	'id' => 'danger' , 'alert' => 'يوجد خطأ في كلمة المرور');
	} else {
		
		$key 		= mysqli_real_escape_string($link, $_POST['key']);						$key		= a2e($key);
		$mobile 	= mysqli_real_escape_string($link, $_POST['mobile']);					$mobile 	= a2e($mobile);
		$password 	= mysqli_real_escape_string($link, $_POST['password']);					$password 	= a2e($password);		$hashed 	= md5($password);
		$date      	= time();
		
		$update = mysqli_query($link,"UPDATE `entities` SET `hashed`='$hashed', `password`='$password' WHERE `mobile`='$mobile' AND `id`='$key'" );
			
		if ($update) {	$responseArray = array('id'=>'success', 'alert'=>'تم تعيين كلمة المرور بنجاح'); } else { $responseArray = array('id'=>'danger', 'alert'=>'الرقم أو البريد الإلكتروني مسجل مسبقاُ');	}
	}										
} else {	$responseArray = array('id'=>'danger', 'alert'=>'خطأ في الخادم');	}
// if requested by AJAX request return JSON response
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>