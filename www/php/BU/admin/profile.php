<?php header('Content-type: application/json');		header('Content-Type: text/html; charset=utf-8'); 	
header('cache-control: no-cache'); 					require '../inc/functions.php'; 
session_start();
//print_r($_POST);	
$allresps = [];
if ( $_SERVER["REQUEST_METHOD"] != "POST") {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');						array_push($allresps,$responseArray);
} else {	//Aname=&crid=&taxid=&fiscalday=
	$time = time();
	if ( !$_SESSION ) {
		$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $_SESSION');			array_push($allresps,$responseArray);
	} else {
		if ( !isset($_SESSION['loggedin']) || $_SESSION["loggedin"] !== true )   {
			$responseArray = array('id' => 'danger', 'message' => 'لم تقم بتسجيل الدخول');			array_push($allresps,$responseArray);
		} elseif ( !isset($_SESSION['userstatus']) || $_SESSION["userstatus"] != 1 )   {
			$responseArray = array('id' => 'danger', 'message' => 'الحساب غير نشط');				array_push($allresps,$responseArray);
		} elseif ( !isset($_SESSION['userclearance']) || empty($_SESSION["userclearance"])  )   {
			$responseArray = array('id' => 'danger', 'message' => 'لايمكن الوصول للتصاريح');				array_push($allresps,$responseArray);
		// } elseif ( !str_contains($_SESSION["userclearance"], 'setting')  )   {
			// $responseArray = array('id' => 'danger', 'message' => 'العمليه غير مصرحه');				array_push($allresps,$responseArray);
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
			
			if(isset($_POST['fetch']) && !empty($_POST['fetch']) )   {

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
				
				$responseArray = array('id' => 'success', 'entity' => $entity );		array_push($allresps,$responseArray);
			
			} else {

				$logrecord = mysqli_fetch_array(mysqli_query($link,"SELECT * FROM `entities` WHERE `id`='$identifier'"), MYSQLI_ASSOC);
				$logid = $logrecord['id'];
				$logAname = $logrecord['Aname'];
				$logEname = $logrecord['Ename'];
				$logcrid = $logrecord['crid'];
				$logtaxid = $logrecord['taxid'];
				$logvat = $logrecord['vat'];
				$logfiscal = $logrecord['fiscal'];
				$logusername = $logrecord['username'];
				$logmobile = $logrecord['mobile'];
				$logemail = $logrecord['email'];

				if( isset($_POST['Aname']) )   {
					if( empty($_POST['Aname']) )   {
						$responseArray = array('id' => 'danger', 'message' => 'يلزم إدخال إسم المنشأه');		array_push($allresps,$responseArray);
					} else {
						$Aname = mysqli_real_escape_string($link, $_POST['Aname']);
						// echo $Aname;
						$record = mysqli_query($link,"SELECT * FROM `entities` WHERE id = '$identifier'   ");
						// echo mysqli_num_rows($record);
						if(@mysqli_num_rows($record) > 0){
							$record = mysqli_fetch_array($record, MYSQLI_ASSOC);
							  if ($Aname != $record['Aname']) {
								  $update = mysqli_query($link,"UPDATE `entities` SET `Aname` = '$Aname'	WHERE `id`='$identifier'");
									if ($update) {	
										$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','setting','تم تحديث اسم المنشأه من $logAname إلى $Aname','$time' )");
										$responseArray = array('id' => 'success', 'message' => 'تم تحديث الاسم التجاري بنجاح');
										array_push($allresps,$responseArray);
									} else {
										$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ أثناء عملية التحديث');
										array_push($allresps,$responseArray);
									}
							  } else { 
								$responseArray = array('id' => 'warning', 'message' => 'الاسم التجاري مطابق للسابق'); 
								array_push($allresps,$responseArray);
							  }
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على أي بيانات');
							array_push($allresps,$responseArray);
						}
					}
				}

				// if(isset($_POST['crid']) && !empty($_POST['crid']) )   {
				if( isset($_POST['crid']) )   {
					if( empty($_POST['crid']) )   {
						$responseArray = array('id' => 'danger', 'message' => 'يلزم إدخال رقم السجل التجاري');	
						array_push($allresps,$responseArray);
					} elseif ( !is_numeric($_POST['crid']) )   {
						$responseArray = array('id' => 'danger', 'message' => 'يجب أن يحتوي السجل التجاري على أرقام فقط');	
						array_push($allresps,$responseArray);
					} else {
						$crid = mysqli_real_escape_string($link, $_POST['crid']);
						// echo $Aname;
						$record = mysqli_query($link,"SELECT * FROM `entities` WHERE id = '$identifier'   ");
						// echo mysqli_num_rows($record);
						if(@mysqli_num_rows($record) > 0){
							$record = mysqli_fetch_array($record, MYSQLI_ASSOC);
							  if ($crid != $record['crid']) {
								  $update = mysqli_query($link,"UPDATE `entities` SET `crid` = '$crid'	WHERE `id`='$identifier'");
									if ($update) {	
										$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','setting','تم تحديث رقم السجل التجاري من $logcrid إلى $crid','$time' )");
										$responseArray = array('id' => 'success', 'message' => 'تم تحديث رفم السجل التجاري بنجاح');
										array_push($allresps,$responseArray);
									} else {
										$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ أثناء عملية التحديث');
										array_push($allresps,$responseArray);
									}
							  } else { 
								$responseArray = array('id' => 'warning', 'message' => 'رفم السجل التجاري مطابق للسابق'); 
								array_push($allresps,$responseArray);
							  }
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على أي بيانات');
							array_push($allresps,$responseArray);
						}
					}
				}
				
				// if(isset($_POST['taxid']) && !empty($_POST['taxid']) )   {
				if( isset($_POST['taxid']) )   {
					if( !empty($_POST['taxid']) && !is_numeric($_POST['taxid']) )   {
						$responseArray = array('id' => 'danger', 'message' => 'يجب أن يحتوي الرقم الضريبي على أرقام فقط');	
						array_push($allresps,$responseArray);
					} else {
						$taxid = mysqli_real_escape_string($link, $_POST['taxid']);
						// echo $Aname;
						$record = mysqli_query($link,"SELECT * FROM `entities` WHERE id = '$identifier'   ");
						// echo mysqli_num_rows($record);
						if(@mysqli_num_rows($record) > 0){
							$record = mysqli_fetch_array($record, MYSQLI_ASSOC);
							  if ($taxid != $record['taxid']) {
								  $update = mysqli_query($link,"UPDATE `entities` SET `taxid` = '$taxid'	WHERE `id`='$identifier'");
										if ($update) {	
											$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','setting','تم تحديث الرقم الضريبي من $logtaxid إلى $taxid','$time' )");
										$responseArray = array('id' => 'success', 'message' => 'تم تحديث الرقم الضريبي بنجاح');
										array_push($allresps,$responseArray);
									} else {
										$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ أثناء عملية التحديث');
										array_push($allresps,$responseArray);
									}
							  } else { 
								$responseArray = array('id' => 'warning', 'message' => 'الرقم الضريبي مطابق للسابق'); 
								array_push($allresps,$responseArray);
							  }
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على أي بيانات');
							array_push($allresps,$responseArray);
						}
					}
				}
				
				// if(isset($_POST['vat']) && !empty($_POST['vat']) )   {
				if( isset($_POST['vat']) )   {
					if( !empty($_POST['vat']) && !is_numeric($_POST['vat']) )   {
						$responseArray = array('id' => 'danger', 'message' => 'يجب أن تحتوي نسبة الضريبه على أرقام فقط');	
						array_push($allresps,$responseArray);
					} elseif ( empty($_POST['vat']) )   {
						$vat = 0;
					} else {	
						$vat = mysqli_real_escape_string($link, $_POST['vat']);
					}
					// echo $Aname;
					$record = mysqli_query($link,"SELECT * FROM `entities` WHERE id = '$identifier'   ");
					// echo mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){
						$record = mysqli_fetch_array($record, MYSQLI_ASSOC);
						  if ($vat != $record['vat']) {
							  $update = mysqli_query($link,"UPDATE `entities` SET `vat` = '$vat'	WHERE `id`='$identifier'");
								if ($update) {	
									$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','setting','تم تحديث نسبة الضريبه من $logvat إلى $vat','$time' )");
									$responseArray = array('id' => 'success', 'message' => 'تم تحديث النسبه الضريبيه  بنجاح');
									array_push($allresps,$responseArray);
								} else {
									$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ أثناء عملية التحديث');
									array_push($allresps,$responseArray);
								}
						  } else { 
							$responseArray = array('id' => 'warning', 'message' => 'النسبه الضريبيه مطابقه للسابق'); 
							array_push($allresps,$responseArray);
						  }
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على أي بيانات');
						array_push($allresps,$responseArray);
					}
				}
				
				// if(isset($_POST['fiscal']) && !empty($_POST['fiscal']) )   {
				if( isset($_POST['fiscal']) )   {
					$fiscal = mysqli_real_escape_string($link, $_POST['fiscal']);
					// echo $Aname;
					$record = mysqli_query($link,"SELECT * FROM `entities` WHERE id = '$identifier'   ");
					// echo mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){
						$record = mysqli_fetch_array($record, MYSQLI_ASSOC);
						  if ($fiscal != $record['fiscal']) {
							  $update = mysqli_query($link,"UPDATE `entities` SET `fiscal` = '$fiscal'	WHERE `id`='$identifier'");
								if ($update) {	
									$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','setting','تم تحديث بداية السنه الماليه من $logfiscal إلى $fiscal','$time' )");
									$responseArray = array('id' => 'success', 'message' => 'تم تحديث بداية السنة المالية بنجاح');
									array_push($allresps,$responseArray);
								} else {
									$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ أثناء عملية التحديث');
									array_push($allresps,$responseArray);
								}
						  } else { 
							$responseArray = array('id' => 'warning', 'message' => 'بداية السنة المالية مطابق للسابق'); 
							array_push($allresps,$responseArray);
						  }
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على أي بيانات');
						array_push($allresps,$responseArray);
					}
				}
		
			}
		}
	}
}
// if requested by AJAX request return JSON response
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $encoded = json_encode($allresps);		header('Content-Type: application/json');	echo $encoded;
}
?>
