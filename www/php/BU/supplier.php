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
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `suppliers` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logsupname = $logrecord['suppliername'];
						$logsupmobile = $logrecord['suppliermobile'];
						$logsuplandline1 = $logrecord['supplierlandline1'];
						$logsuplandline2 = $logrecord['supplierlandline2'];
						$logsupwebsite = $logrecord['supplierwebsite'];
						$logsupemail = $logrecord['supplieremail'];
					$delete = mysqli_query($link,"DELETE FROM suppliers WHERE id = $id ");
					if ($delete) {	
						$responseArray = array('id' => 'success', 'message' => 'تم حذف المورد بنجاح');
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','supplier','فام بحذف مورد بأسم $logsupname ورقم جوال $logsupmobile وهاتف1 $logsuplandline1 وهاتف2 $logsuplandline2 وموقع الكتروني $logsupwebsite  وايميل $logsupemail','$time' )");
					} else {
						$responseArray = array('id' => 'danger', 'message' => '1خطأ في قاعدة البيانات');
					}
				} else {
					$responseArray = array('id' => 'danger', 'message' => 'لا توجد بيانات');
				}
			} elseif (isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'update' )   {
				if(!isset($_POST['id']) && empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم يتم تحديد معرف ID');
				} elseif (!isset($_POST['suppliername']) && empty($_POST['suppliername']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال اسم المورد');
				} elseif( !empty($_POST['suppliermobile']) && !is_numeric($_POST['suppliermobile']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'رقم الجوال يجب أن يحتوي على أرقام فقط');		
				} elseif( !empty($_POST['supplierlandline1']) && !is_numeric($_POST['supplierlandline1']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'رقم الهاتف1 يجب أن يحتوي على أرقام فقط');		
				} elseif( !empty($_POST['supplierlandline2']) && !is_numeric($_POST['supplierlandline2']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'رقم الهاتف2 يجب أن يحتوي على أرقام فقط');		
				} else {
					$id = mysqli_real_escape_string($link, $_POST['id']);
						// for logging purposes
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `suppliers` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logsupname = $logrecord['suppliername'];
						$logsupmobile = $logrecord['suppliermobile'];
						$logsuplandline1 = $logrecord['supplierlandline1'];
						$logsuplandline2 = $logrecord['supplierlandline2'];
						$logsupwebsite = $logrecord['supplierwebsite'];
						$logsupemail = $logrecord['supplieremail'];
					$suppliername = mysqli_real_escape_string($link, $_POST['suppliername']);
					$suppliermobile = mysqli_real_escape_string($link, $_POST['suppliermobile']);
					$supplierlandline1 = mysqli_real_escape_string($link, $_POST['supplierlandline1']);
					$supplierlandline2 = mysqli_real_escape_string($link, $_POST['supplierlandline2']);	
					$supplierwebsite = mysqli_real_escape_string($link, $_POST['supplierwebsite']);		
					$supplieremail = mysqli_real_escape_string($link, $_POST['supplieremail']);
					
					// check if category already exist
					$record = mysqli_query($link,"SELECT * FROM `suppliers` WHERE `suppliername`='$suppliername' AND `id` != '$id' ");
					
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id' => 'danger', 'message' => 'اسم المورد مضاف مسبقاً');
					} else {
						$update = mysqli_query($link,"UPDATE `suppliers` SET `suppliername`='$suppliername' , `suppliermobile`='$suppliermobile' , `supplierlandline1`='$supplierlandline1' , `supplierlandline2`='$supplierlandline2' , `supplierwebsite`='$supplierwebsite' , `supplieremail`='$supplieremail' WHERE `id`='$id'");
						
						if ($update) {	
							$responseArray = array('id' => 'success', 'message' => 'تم تحديث المورد بنجاح');
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','supplier','تم تحديث اسم المورد من $logsupname  إلى $suppliername  وجوال من $logsupmobile  إلى $suppliermobile  وهاتف1 من $logsuplandline1  إلى $supplierlandline1   وهاتف2 من $logsuplandline2  إلى $supplierlandline2  وموقع الكترونيي من $logsupwebsite  إلى $supplierwebsite  وايميل من $logsupemail  إلى $supplieremail  ','$time' )");
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $update');
						}
					}
				}
			} elseif (isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'add' )   {
				if (!isset($_POST['suppliername']) || empty($_POST['suppliername']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'اسم المورد  مطلوب');
				} elseif( !empty($_POST['suppliermobile']) && !is_numeric($_POST['suppliermobile']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'رقم الجوال يجب أن يحتوي على أرقام فقط');		
				} elseif( !empty($_POST['supplierlandline1']) && !is_numeric($_POST['supplierlandline1']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'رقم الهاتف1 يجب أن يحتوي على أرقام فقط');		
				} elseif( !empty($_POST['supplierlandline2']) && !is_numeric($_POST['supplierlandline2']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'رقم الهاتف2 يجب أن يحتوي على أرقام فقط');		
				} else {
		///////////////////////////////////////////////////////////////////////////////////////
					$suppliername = mysqli_real_escape_string($link, $_POST['suppliername']);
					$suppliermobile = mysqli_real_escape_string($link, $_POST['suppliermobile']);
					$supplierlandline1 = mysqli_real_escape_string($link, $_POST['supplierlandline1']);
					$supplierlandline2 = mysqli_real_escape_string($link, $_POST['supplierlandline2']);	
					$supplierwebsite = mysqli_real_escape_string($link, $_POST['supplierwebsite']);		
					$supplieremail = mysqli_real_escape_string($link, $_POST['supplieremail']);
					// check if category already exist
					$record = mysqli_query($link,"SELECT * FROM `suppliers` WHERE suppliername = '$suppliername'   ");
					
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id' => 'warning', 'message' => 'المورد مضاف مسبقاً');
					} else {
						// $finder = mysqli_query($link,"SELECT * FROM `suppliers` ORDER BY `id` DESC");
						// if(@mysqli_num_rows($finder) > 0){ 																				
							// while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){
								// $id = $idfinder['id'] + 1;;		break;
							// }
						// } else {	
							// $id = 1;	
						// }
						

						
						$ins = mysqli_query($link,"INSERT INTO `suppliers`( `id`, `suppliername`, `suppliermobile`, `supplierlandline1`, `supplierlandline2`, `supplierwebsite`, `supplieremail`, `transnum`, `timeadded` )VALUES( NULL,'$suppliername','$suppliermobile','$supplierlandline1','$supplierlandline2','$supplierwebsite','$supplieremail','0', '$time' )");

						$finder = mysqli_query($link,"SELECT * FROM `suppliers` ORDER BY `id` DESC");
						if(@mysqli_num_rows($finder) > 0){ while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ $id = $idfinder['id']; break;} } else { $id = 1; }
						
						if ($ins) {	
							$responseArray = array('id' => 'success', 'message' => 'تم إضافة المورد بنجاح', 'lastid' => $id);
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','supplier','فام بإضافة مورد بأسم $suppliername ورقم جوال $suppliermobile وهاتف1 $supplierlandline1 وهاتف2 $supplierlandline2 وموقع الكتروني $supplierwebsite  وايميل $supplieremail ','$time' )");
						} else {
							$responseArray = array('id' => 'danger', 'message' => '2خطأ في قاعدة البيانات');
						}
					}
		///////////////////////////////////////////////////////////////////////////////////////			
				}
			} else {
				if(isset($_POST['id']) && !empty($_POST['id']))	{	
					$id = mysqli_real_escape_string($link, $_POST['id']);	
					$record = mysqli_query($link,"SELECT * FROM `suppliers` WHERE id = '$id'   ");
				} else { 
					$record = mysqli_query($link,"SELECT * FROM `suppliers` ");
				}
					$suppliernum = mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){			$z = 0;			$responseArray = [];	$Totalsuppliers = [];	
						while($supplier = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							$id = $supplier['id'];		
							$suppliername = $supplier['suppliername'];		
							$suppliermobile = $supplier['suppliermobile'];		
							$supplierlandline1 = $supplier['supplierlandline1'];		
							$supplierlandline2 = $supplier['supplierlandline2'];		
							$supplierwebsite = $supplier['supplierwebsite'];		
							$supplieremail = $supplier['supplieremail'];		
							$transactionsnumber = $supplier['transnum'];		
							$timeadded = Time_Passed(date($supplier['timeadded']),'time');			

							${'supplier'.$z} = array('id' => $id,'suppliername' => $suppliername,'suppliermobile' => $suppliermobile,'supplierlandline1' => $supplierlandline1,'supplierlandline2' => $supplierlandline2,'supplierwebsite' => $supplierwebsite,'supplieremail' => $supplieremail,'timeadded' => $timeadded,'transactionsnumber' => $transactionsnumber);	
							$z++;
						}
					// $responseArray = array('id' => 'success', 'suppliernum' => $suppliernum);
					// } else { 																				
						// $responseArray = array('id' => 'danger', 'message' => 'no supplier exists');
					// }
					}
					$responseArray = array('id' => 'success', 'suppliernum' => $suppliernum);
			}
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($suppliernum) && $suppliernum > 0 ){	for($a=0;$a<$suppliernum;$a++){ array_push($Totalsuppliers,${'supplier'.$a}); }	array_push($responseArray,$Totalsuppliers); }
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>