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
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `opex` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logdesc = $logrecord['description'];
						$logcost = $logrecord['cost'];
						$logcycle = $logrecord['cycle'];	
						if ( $logcycle == 'monthly' ) { $logcycle = 'شهري'; } else { $logcycle = 'ستوي'; }	
						$logstart = Time_Passed(date($logrecord['start']),'time');
					$delete = mysqli_query($link,"DELETE FROM opex WHERE id = $id ");
					if ($delete) {	
						$responseArray = array('id' => 'success', 'message' => 'تم حذف التكلفه بنجاح');
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','opex','تم حذف $logdesc بقيمة $logcost بشكل $logcycle وتبدأ في $logstart','$time' )");
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات');
					}
				} else {
					$responseArray = array('id' => 'danger', 'message' => 'لا توجد بيانات');
				}
			} elseif (isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'update' )   {
				if(!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم يتم تحديد معرف ID');
				} elseif (!isset($_POST['description']) || empty($_POST['description']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'وصف التكلفه مطلوب');
				} elseif (!isset($_POST['cost']) || empty($_POST['cost']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'قيمة التكلفه مطلوبه');
				} elseif ( !is_numeric($_POST['cost'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'قيمة التكلفه يجب أن تحتوي على أرقام فقط');
				} elseif (!isset($_POST['month']) || empty($_POST['month']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'الشهر نطلوب');
				} elseif (!isset($_POST['year']) || empty($_POST['year']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'السنه مطلوبه');
				} else {
					$id = mysqli_real_escape_string($link, $_POST['id']);				
						// for logging purposes
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `opex` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logdesc = $logrecord['description'];
						$logcost = $logrecord['cost'];
						$logcycle = $logrecord['cycle'];	
						if ( $logcycle == 'monthly' ) { $logcycle = 'شهري'; } else { $logcycle = 'ستوي'; }	
						$logstart = Time_Passed(date($logrecord['start']),'time');

					$description = mysqli_real_escape_string($link, $_POST['description']);
					$cost = mysqli_real_escape_string($link, $_POST['cost']);
					$cycle = mysqli_real_escape_string($link, $_POST['cycle']);
					$month = mysqli_real_escape_string($link, $_POST['month']);
					$year = mysqli_real_escape_string($link, $_POST['year']);

					$cycledate = strftime("%F", strtotime($year."-".$month."-01"));		// Construct Date
					$start = strtotime($cycledate);										// Get unix
					if ($cycle == 'monthly') {	$next = date('Y-m-d', strtotime('+1 month', strtotime($cycledate))); $logcycle = 'شهري'; }
					if ($cycle == 'yearly') {	$next = date('Y-m-d', strtotime('+1 year', strtotime($cycledate))); $logcycle = 'سنوي'; }
					$end = strtotime($next) - 1;										// Get unix of the end (-1 deduct a second)

					// check if opex already exist
					$record = mysqli_query($link,"SELECT * FROM `opex` WHERE description = '$description' AND start = '$start' AND end = '$end' AND id != '$id'  ");
					
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id' => 'warning', 'message' => 'التكلفه مضافه مسبقاً');		goto end;	
					} else {

						$update = mysqli_query($link,"UPDATE `opex` SET `cycle` = '$cycle',`description` = '$description',`cost` = '$cost',`start` = '$start',`end` = '$end',`timeedited` = '$time'	WHERE `id`='$id'");
						
						if ($update) {
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','opex','تم تحديث $logdesc بقيمة $logcost بشكل $logcycle وتبدأ في $logstart إلى $description  بقيمة $cost بشكل $logcycle وتبدأ في $cycledate','$time' )");
							
							$finder = mysqli_query($link,"SELECT * FROM `opex` WHERE `id`='$id'");
							while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){	
								$id = $idfinder['id'];
								$cycle = $idfinder['cycle'];
								$description = $idfinder['description'];
								$cost = $idfinder['cost'];
								$start = $idfinder['start'];							
								// $start = Time_Passed_MY(date($start),'time');
								$start = Time_Passed(date($start),'time');
								$end = $idfinder['end'];								
								// $end = Time_Passed_MY(date($end),'time');
								$end = Time_Passed(date($end),'time');
								$timeadded = $idfinder['timeadded'];					$timeadded = Time_Passed(date($timeadded),'time');
								 // gmdate("Y-m-d\TH:i:s\Z", $timestamp);
								$timeedited = $idfinder['timeedited'];					
							}
							
							$newentry = array('id' => $id,'cycle' => $cycle,'description' => $description,'cost' => $cost,'start' => $start,'end' => $end,'timeadded' => $timeadded,'timeedited' => $timeedited );	

							$responseArray = array('id' => 'success', 'message' => 'تم تحديث التكلفه بنجاح');
							array_push($responseArray,$newentry);
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات');
						}
					}			
				}
			} elseif (isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'add' )   {
				if (!isset($_POST['description']) || empty($_POST['description']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'وصف التكلفه مطلوب');
				} elseif (!isset($_POST['cost']) || empty($_POST['cost']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'قيمة التكلفه مطلوبه');
				} elseif ( !is_numeric($_POST['cost'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'قيمة التكلفه يجب أن تحتوي على أرقام فقط');
				} elseif (!isset($_POST['month']) || empty($_POST['month']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'الشهر نطلوب');
				} elseif (!isset($_POST['year']) || empty($_POST['year']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'السنه مطلوبه');
				} else {
		///////////////////////////////////////////////////////////////////////////////////////
					$description = mysqli_real_escape_string($link, $_POST['description']);
					$cost = mysqli_real_escape_string($link, $_POST['cost']);
					$cycle = mysqli_real_escape_string($link, $_POST['cycle']);
					$month = mysqli_real_escape_string($link, $_POST['month']);
					$year = mysqli_real_escape_string($link, $_POST['year']);

					$cycledate = strftime("%F", strtotime($year."-".$month."-01"));		// Construct Date
					$start = strtotime($cycledate);										// Get unix
					if ($cycle == 'monthly') {	$next = date('Y-m-d', strtotime('+1 month', strtotime($cycledate))); 	$logcycle = 'شهري'; }
					if ($cycle == 'yearly') {	$next = date('Y-m-d', strtotime('+1 year', strtotime($cycledate)));		$logcycle = 'سنوي'; }
					$end = strtotime($next) - 1;										// Get unix of the end (-1 deduct a second)

					$eid = 16;	// to be removed later 

					// check if opex already exist
					$record = mysqli_query($link,"SELECT * FROM `opex` WHERE description = '$description' AND start = '$start' AND end = '$end'  ");
					
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id' => 'warning', 'message' => 'التكلفه مضافه مسبقاً');		goto end;	
					} else {

						$ins = mysqli_query($link,"INSERT INTO `opex`( `id`,`eid`,`cycle`,`description`,`cost`,`start`,`end`,`timeadded`,`timeedited` )VALUES(	NULL,'$eid','$cycle','$description','$cost','$start','$end','$time',NULL )");			
						
						if ($ins) {
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','opex','تم إضافة $description بقيمة $cost بشكل $logcycle وتبدأ في $cycledate','$time' )");

							$finder = mysqli_query($link,"SELECT * FROM `opex` ORDER BY `id` DESC LIMIT 1");
							while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){	
								$id = $idfinder['id'];
								$cycle = $idfinder['cycle'];
								$description = $idfinder['description'];
								$cost = $idfinder['cost'];
								$start = $idfinder['start'];							
								// $start = Time_Passed_MY(date($start),'time');
								$start = Time_Passed(date($start),'time');
								$end = $idfinder['end'];								
								// $end = Time_Passed_MY(date($end),'time');
								$end = Time_Passed(date($end),'time');
								$timeadded = $idfinder['timeadded'];					$timeadded = Time_Passed(date($timeadded),'time');
								 // gmdate("Y-m-d\TH:i:s\Z", $timestamp);
								$timeedited = $idfinder['timeedited'];					
							}
							
							$newentry = array('id' => $id,'cycle' => $cycle,'description' => $description,'cost' => $cost,'start' => $start,'end' => $end,'timeadded' => $timeadded,'timeedited' => $timeedited );	

							$responseArray = array('id' => 'success', 'message' => 'تم إضافة التكلفه بنجاح' );
							array_push($responseArray,$newentry);
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات');
						}
					}
		///////////////////////////////////////////////////////////////////////////////////////			
				}
			} else {
					if(isset($_POST['id']) && !empty($_POST['id']))	{	
					$id = mysqli_real_escape_string($link, $_POST['id']);	
					$record = mysqli_query($link,"SELECT * FROM `opex` WHERE id = '$id'   ");
				} else { 
					$record = mysqli_query($link,"SELECT * FROM `opex` ");
				}
					$opexnum = mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){	
					$z = 0;				
					$responseArray = [];			
					$Totalopexinfos = [];	
						while($opexinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							$id = $opexinfo['id'];
							$cycle = $opexinfo['cycle'];
							$description = $opexinfo['description'];
							$cost = $opexinfo['cost'];
							$start = $opexinfo['start'];							
							// $start = Time_Passed_MY(date($start),'time');
							$start = Time_Passed(date($start),'time');
							$end = $opexinfo['end'];								
							// $end = Time_Passed_MY(date($end),'time');
							$end = Time_Passed(date($end),'time');
							$timeadded = $opexinfo['timeadded'];					$timeadded = Time_Passed(date($timeadded),'time');
							 // gmdate("Y-m-d\TH:i:s\Z", $timestamp);
							$timeedited = $opexinfo['timeedited'];					
					
							if( !empty($timeedited))	{
								$timeedited = Time_Passed(date($opexinfo['timeedited']),'time');
							} else {
								$timeedited = '';
							}						


						${'opexinfo'.$z} = array('id' => $id,'cycle' => $cycle,'description' => $description,'cost' => $cost,'start' => $start,'end' => $end,'timeadded' => $timeadded,'timeedited' => $timeedited );	
						$z++;
						}
					// $responseArray = array('id' => 'success', 'opexnum' => $opexnum);
					// } else { 																				
						// $responseArray = array('id' => 'danger', 'message' => 'no barcode exists');
					// }
					}
					$responseArray = array('id' => 'success', 'opexnum' => $opexnum);
			}
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($opexnum) && $opexnum > 0 ){	for($a=0;$a<$opexnum;$a++){ array_push($Totalopexinfos,${'opexinfo'.$a}); }	array_push($responseArray,$Totalopexinfos); }
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>