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
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `capex` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logdesc = $logrecord['description'];
						$logcost = $logrecord['amount'];
					$delete = mysqli_query($link,"DELETE FROM capex WHERE id = $id ");
					if ($delete) {	
						$responseArray = array('id' => 'success', 'message' => 'تم حذف الدفعه بنجاح');
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','capex','تم حذف $logdesc بقيمة $logcost','$time' )");
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات');
					}
				} else {
					$responseArray = array('id' => 'danger', 'message' => 'لا توجد بيانات');
				}
			} elseif (isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'update' )   {
				if(!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم يتم تحديد معرف ID');
				} elseif (!isset($_POST['description']) || empty($_POST['description'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'وصف التكلفه مطلوب');
				} elseif (!isset($_POST['amount']) || empty($_POST['amount'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'قيمة التكلفه مطلوبه');
				} elseif ( !is_numeric($_POST['amount'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'قيمة التكلفه يجب أن تحتوي على أرقام فقط');
				} else {
					$id = mysqli_real_escape_string($link, $_POST['id']);
						// for logging purposes
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `capex` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logdesc = $logrecord['description'];
						$logcost = $logrecord['amount'];

					$description = mysqli_real_escape_string($link, $_POST['description']);
					$amount = mysqli_real_escape_string($link, $_POST['amount']);

					// check if opex already exist
					$record = mysqli_query($link,"SELECT * FROM `capex` WHERE description = '$description' AND id != '$id'  ");
					
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id' => 'warning', 'message' => 'التكلفه مضافه مسبقاً');		goto end;	
					} else {
						$update = mysqli_query($link,"UPDATE `capex` SET `amount` = '$amount',`description` = '$description'	WHERE `id`='$id'");
					
						if ($update) {	
							$responseArray = array('id' => 'success', 'message' => 'تم تحديث التكلفه بنجاح');
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','capex','تم تحديث $logdesc بقيمة $logcost إلى $description بقيمة $amount','$time' )");
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات');
						}
					}
				}
			} elseif (isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'add' )   {
				if (!isset($_POST['description']) || empty($_POST['description']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'وصف التكلفه مطلوب');
				} elseif (!isset($_POST['amount']) || empty($_POST['amount']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'قيمة التكلفه مطلوبه');
				} elseif ( !is_numeric($_POST['amount'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'قيمة التكلفه يجب أن تحتوي على أرقام فقط');
				} else {
					$description = mysqli_real_escape_string($link, $_POST['description']);
					$amount = mysqli_real_escape_string($link, $_POST['amount']);

					// check if opex already exist
					$record = mysqli_query($link,"SELECT * FROM `capex` WHERE description = '$description' ");
					
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id' => 'warning', 'message' => 'التكلفه مضافه مسبقاً');		goto end;	
					} else {

						$ins = mysqli_query($link,"INSERT INTO `capex`( `id`,`description`,`amount`,`timeadded` )VALUES(	NULL,'$description','$amount','$time' )");
					
						// $finder = mysqli_query($link,"SELECT * FROM `capex` ORDER BY `id` DESC LIMIT 1");
						// if(@mysqli_num_rows($finder) > 0){ 																				
							// while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){
								// $id = $idfinder['id'] ;		break;
							// }
						// }
						
						$finder = mysqli_query($link,"SELECT * FROM `capex` ORDER BY `id` DESC LIMIT 1");
						if(@mysqli_num_rows($finder) > 0){ while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ $id = $idfinder['id']; break;} } else { $id = 1; }

						
						if ($ins) {	
							$responseArray = array('id' => 'success', 'message' => 'تم إضافة التكلفه بنجاح', 'lastid' => $id );
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','capex','تم إضافة $description بقيمة $amount','$time' )");

						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات');
						}
					}
				}
			} else {
				if(isset($_POST['id']) && !empty($_POST['id']))	{	
					$id = mysqli_real_escape_string($link, $_POST['id']);	
					$record = mysqli_query($link,"SELECT * FROM `capex` WHERE id = '$id'   ");
				} else { 
					$record = mysqli_query($link,"SELECT * FROM `capex` ");
				}
					$capexnum = mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){									
					$z = 0;				
					$responseArray = [];			
					$Total = [];	
						while($info = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							$id = $info['id'];
							$description = $info['description'];
							$amount = $info['amount'];
							$timeadded = $info['timeadded'];											
							$timeadded = Time_Passed(date($info['timeadded']),'time');

						${'info'.$z} = array('id' => $id,'description' => $description,'amount' => $amount,'timeadded' => $timeadded );	
						$z++;
						}
					// $responseArray = array('id' => 'success', 'capexnum' => $capexnum);
					// } else { 																				
						// $responseArray = array('id' => 'danger', 'message' => 'no barcode exists');
					}
					$responseArray = array('id' => 'success', 'capexnum' => $capexnum);

			}
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($capexnum) && $capexnum > 0 ){	for($a=0;$a<$capexnum;$a++){ array_push($Total,${'info'.$a}); }	array_push($responseArray,$Total); }
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>