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
		
			if(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'delete' )   {
				if(isset($_POST['id']) && !empty($_POST['id']) )   {
					$id = mysqli_real_escape_string($link, $_POST['id']);
						// for logging purposes
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `category` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logcategory = $logrecord['category'];
					$delete = mysqli_query($link,"DELETE FROM category WHERE id = $id ");
					if ($delete) {	
						$responseArray = array('id' => 'success', 'message' => 'تم حذف التصنيف بنجاح');
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','category','تم حذف التصنيف $logcategory','$time' )");
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات');
					}
				} else {
					$responseArray = array('id' => 'danger', 'message' => 'لا توجد بيانات');
				}
			} elseif (isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'update' )   {
				if(!isset($_POST['id']) && empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم يتم تحديد معرف ID');
				} elseif (!isset($_POST['categoryname']) && empty($_POST['categoryname']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال تصنيف');
				} else {
					$id = mysqli_real_escape_string($link, $_POST['id']);
						// for logging purposes
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `category` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logcategory = $logrecord['category'];
					$categoryname = mysqli_real_escape_string($link, $_POST['categoryname']);
					
					// check if category already exist
					$record = mysqli_query($link,"SELECT * FROM `category` WHERE category = '$categoryname'   ");
					
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id' => 'warning', 'message' => 'التصنيف مضاف مسبقاً');		//goto end;	
					} else {
						$update = mysqli_query($link,"UPDATE `category` SET `category` = '$categoryname'	WHERE `id`='$id'");
						
						if ($update) {	
							$responseArray = array('id' => 'success', 'message' => 'تم تحديث التصنيف بنجاح');
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','category','تم تحديث التصنيف $logcategory إلى $categoryname','$time' )");
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات');
						}
					}
				}
			} elseif (isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'add' )   {
				if (!isset($_POST['categoryname']) || empty($_POST['categoryname']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'اسم التصنيف مطلوب');
				} else {
		///////////////////////////////////////////////////////////////////////////////////////
					$categoryname = mysqli_real_escape_string($link, $_POST['categoryname']);
						
					// check if category already exist
					$record = mysqli_query($link,"SELECT * FROM `category` WHERE category = '$categoryname'   ");
					
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id' => 'warning', 'message' => 'التصنيف مضاف مسبقاً');		//goto end;	
					} else {
						// $finder = mysqli_query($link,"SELECT * FROM `category` ORDER BY `id` DESC");
						// if(@mysqli_num_rows($finder) > 0){ 																				
							// while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){
								// $id = $idfinder['id'] + 1;;		break;
							// }
						// } else {	
							// $id = 1;	
						// }
						
						$ins = mysqli_query($link,"INSERT INTO `category`( `id`,`category`,`timeadded` )VALUES(	NULL,'$categoryname','$time' )");			
						
						$finder = mysqli_query($link,"SELECT * FROM `category` ORDER BY `id` DESC");
						if(@mysqli_num_rows($finder) > 0){ while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ $id = $idfinder['id']; break;} } else { $id = 1; }

						
						if ($ins) {	
							$responseArray = array('id' => 'success', 'message' => 'تم إضافة التصنيف بنجاح', 'lastid' => $id );
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','category','تم إضافة التصنيف $categoryname','$time' )");
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات');
						}
					}
		///////////////////////////////////////////////////////////////////////////////////////			
				}
			} else {
				if(isset($_POST['id']) && !empty($_POST['id']))	{	
					$id = mysqli_real_escape_string($link, $_POST['id']);	
					$record = mysqli_query($link,"SELECT * FROM `category` WHERE id = '$id'   ");
				} else { 
					$record = mysqli_query($link,"SELECT * FROM `category` ");
				}
					$categorynum = mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){									
					$z = 0;				$responseArray = [];			$Totalbarcodeinfos = [];	
						while($barcodeinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							$id = $barcodeinfo['id'];
							$category = $barcodeinfo['category'];
							$timeadded = $barcodeinfo['timeadded'];											
							$timeadded = Time_Passed(date($barcodeinfo['timeadded']),'time');

						${'barcodeinfo'.$z} = array('id' => $id,'category' => $category,'timeadded' => $timeadded );	
						$z++;
						}
					}
					$responseArray = array('id' => 'success', 'categorynum' => $categorynum);
					// $responseArray = array('id' => 'success', 'categorynum' => $categorynum);
					// } else { 																				
						// $responseArray = array('id' => 'danger', 'message' => 'no barcode exists');
					// }
			}
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($categorynum) && $categorynum > 0 ){	for($a=0;$a<$categorynum;$a++){ array_push($Totalbarcodeinfos,${'barcodeinfo'.$a}); }	array_push($responseArray,$Totalbarcodeinfos); }
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>