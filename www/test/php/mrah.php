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
		} else {			
			$userid = $_SESSION["userid"];
			$name = $_SESSION["name"];
			$mobile = $_SESSION["mobile"];
			$email = $_SESSION["email"];
			//$pass = $_SESSION["pass"];
			// $mrah = $_SESSION["mrah"];
			
			if(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'selector' )   {
				if (!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'معرف المراح مطلوب');
				} else {
					$mrahid = mysqli_real_escape_string($link, $_POST['id']);

					$record = mysqli_query($link,"SELECT * FROM `mrah` WHERE id = '$mrahid'  ");
					if(@mysqli_num_rows($record) < 1){
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على المراح');		goto end;	
					} else {
						while($mrahfetcher = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							$mrahuserid = $mrahfetcher['userid'];
							$mrahname = $mrahfetcher['name'];
							$profit = $mrahfetcher['profit'];
						}
						if ( $userid == $mrahuserid ) {
							$_SESSION["mrahid"] = $mrahid;
							$_SESSION["mrahname"] = $mrahname;
							$_SESSION["profit"] = $profit;
							$responseArray = array('id' => 'success', 'message' => 'جاري تحويلك لصفحة المراح', 'data' => $mrahname );
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'المراح لا يخص المستخدم');
						}
					}
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'delete' )   {
				if (!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'معرف المراح مطلوب');
				} else {
					$mrahid = mysqli_real_escape_string($link, $_POST['id']);

					$record = mysqli_query($link,"SELECT * FROM `mrah` WHERE id = '$mrahid'  ");
					if(@mysqli_num_rows($record) < 1){
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على المراح');		goto end;	
					} else {
						while($mrahfetcher = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							$existingmrahname = $mrahfetcher['name'];
						}
						$delete = mysqli_query($link,"DELETE FROM `mrah` WHERE id = '$mrahid' ");
						
						$record = mysqli_query($link,"SELECT * FROM `mrah` WHERE userid = '$userid'  ");
						if(@mysqli_num_rows($record) == 0 ){		$_SESSION["mrahid"] = null; 	$_SESSION["mrahname"] = null;	}

						if (isset($delete)) {	
							$responseArray = array('id' => 'success', 'message' => 'تم حذف المراح بنجاح');
							$log = 'تم حذف مراح '.$existingmrahname.' بمعرف رقم '.$mrahid;
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','mrah','حذف مراح','$log','$time' )");

						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات isset($delete)');
						}
					}
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'edit' )   {
				if (!isset($_POST['mrahname']) || empty($_POST['mrahname']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'اسم المراح مطلوب');
				} elseif(!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'معرف المراح مطلوب');
				} else {
					$mrahname = mysqli_real_escape_string($link, $_POST['mrahname']);
					$mrahid = mysqli_real_escape_string($link, $_POST['id']);
					
					$mrahfinder = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `mrah` WHERE id = '$mrahid' "), MYSQLI_ASSOC);
					$existingmrahname = $mrahfinder['name'];
					
					if ( $mrahname == $existingmrahname ) {
						$responseArray = array('id' => 'danger', 'message' => 'اسم المراح الجديد مطابق للسابق');
					} else {
						$record = mysqli_query($link,"SELECT * FROM `mrah` WHERE name = '$mrahname'  ");
						if(@mysqli_num_rows($record) > 0){
							$responseArray = array('id' => 'danger', 'message' => 'اسم المراح مضاف مسبقاً');		goto end;	
						} else {
							$update = mysqli_query($link,"UPDATE `mrah` SET `name`='$mrahname' WHERE `id`='$mrahid'");
							if ($update) {	
								$responseArray = array('id' => 'success', 'message' => 'تم تعديل اسم المراح بنجاح', 'data' => $mrahname );
								$log = 'تم تعديل وصف مراح بإسم '.$existingmrahname.' إلى '.$mrahname.' بمعرف رقم '.$mrahid;
								$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','mrah','تعديل اسم مراح','$log','$time' )");

							} else {
								$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات');
							}
						}
					}
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'profit' )   {
				if (!isset($_POST['mrahid']) || empty($_POST['mrahid']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'معرف المراح مطلوب');
				} elseif(!isset($_POST['mrahname']) || empty($_POST['mrahname']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'معرف المراح مطلوب');
				} elseif(!isset($_POST['profit']) ) {
					$responseArray = array('id' => 'danger', 'message' => 'قيمة الأرباخ السابقه مطلوبه');
				} elseif(!is_numeric($_POST['profit']) ) {
					$responseArray = array('id' => 'danger', 'message' => 'فيمة الأرباح لابد أن تتكون من أرقام فقط');
				} else {
					$mrahid = mysqli_real_escape_string($link, $_POST['mrahid']);
					$mrahname = mysqli_real_escape_string($link, $_POST['mrahname']);
					$profit = mysqli_real_escape_string($link, $_POST['profit']);
										
					$record = mysqli_query($link,"SELECT * FROM `mrah` WHERE id = '$mrahid'  ");
					if(@mysqli_num_rows($record) < 1){
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العصور على المراح');		goto end;	
					} else {
						$update = mysqli_query($link,"UPDATE `mrah` SET `profit`='$profit' WHERE `id`='$mrahid'");
						if ($update) {	
							$responseArray = array('id' => 'success', 'message' => 'تم التحديث بنجاح' );
							$log = 'تم إضافة أرباح سابقه بقيمة قدرها '.$profit.' ريال لمراح '.$mrahname.' بمعرف رقم '.$mrahid;
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','mrah','إضافة أرباح سابقه','$log','$time' )");
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات');
						}
					}
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'expense' )   {
				if (!isset($_POST['mrahid']) || empty($_POST['mrahid']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'معرف المراح مطلوب');
				} elseif(!isset($_POST['mrahname']) || empty($_POST['mrahname']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'معرف المراح مطلوب');
				} elseif(!isset($_POST['expense']) ) {
					$responseArray = array('id' => 'danger', 'message' => 'قيمة التكاليف السابقه مطلوبه');
				} elseif(!is_numeric($_POST['expense']) ) {
					$responseArray = array('id' => 'danger', 'message' => 'فيمة التكاليف لابد أن تتكون من أرقام فقط');
				} else {
					$mrahid = mysqli_real_escape_string($link, $_POST['mrahid']);
					$mrahname = mysqli_real_escape_string($link, $_POST['mrahname']);
					$expense = mysqli_real_escape_string($link, $_POST['expense']);
										
					$record = mysqli_query($link,"SELECT * FROM `mrah` WHERE id = '$mrahid'  ");
					if(@mysqli_num_rows($record) < 1){
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العصور على المراح');		goto end;	
					} else {
						$update = mysqli_query($link,"UPDATE `mrah` SET `expense`='$expense' WHERE `id`='$mrahid'");
						if ($update) {	
							$responseArray = array('id' => 'success', 'message' => 'تم التحديث بنجاح' );
							$log = 'تم إضافة تكاليف سابقه بقيمة قدرها '.$expense.' ريال لمراح '.$mrahname.' بمعرف رقم '.$mrahid;
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','mrah','إضافة تكاليف سابقه','$log','$time' )");
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات');
						}
					}
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'add' )   {
				if (!isset($_POST['mrahname']) || empty($_POST['mrahname']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'اسم المراح مطلوب');
				} else {
					$mrahname = mysqli_real_escape_string($link, $_POST['mrahname']);

					$record = mysqli_query($link,"SELECT * FROM `mrah` WHERE name = '$mrahname'  ");
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id' => 'danger', 'message' => 'اسم المراح مضاف مسبقاً');		goto end;	
					} else {
						$ins = mysqli_query($link,"INSERT INTO `mrah`( `id`,`userid`,`name`,`time`,`profit`,`expense` )VALUES(	NULL,'$userid','$mrahname','$time',0,0 )");			
						$finder = mysqli_query($link,"SELECT * FROM `mrah` ORDER BY `id` DESC LIMIT 1");
						if(@mysqli_num_rows($finder) > 0){ 
							while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ 
								$newid = $idfinder['id']; 
								$newuserid = $idfinder['userid']; 
								$newname = $idfinder['name']; 
								$newtime = $idfinder['time']; break;
							}
						} else { $newid = 1; }
						if ($ins) {	
							$responseArray = array('id' => 'success', 'message' => 'تم إضافة المراح بنجاح', 'newid' => $newid );
							$log = 'تم إضافة مراح بإسم '.$mrahname;
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$newid','mrah','إضافة مراح','$log','$time' )");

							
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات');
						}
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
	// if ( isset($categorynum) && $categorynum > 0 ){	for($a=0;$a<$categorynum;$a++){ array_push($Totalbarcodeinfos,${'barcodeinfo'.$a}); }	array_push($responseArray,$Totalbarcodeinfos); }
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>