<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); /*Arabic*/	
require '../inc/functions.php'; 
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
		
			$MAXDEBT = 1000;
			// FETCH EMPLOYEES IDS
			// $record = mysqli_query($link,"SELECT id FROM `users` ");
			// $userids = [];
			// if(@mysqli_num_rows($record) > 0){	
				// while($userinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
					// $userid = $userinfo['id'];					array_push($userids,$userid); 
				// }
			// }
			// $userids = implode(",",$userids);
			if(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'temphide' )   {
				if(isset($_POST['id']) && !empty($_POST['id']))	{
					$id = mysqli_real_escape_string($link, $_POST['id']);	
					$record = mysqli_query($link,"SELECT * FROM `notification` WHERE `id`='$id' AND status = '1' ");
					if(@mysqli_num_rows($record) > 0){	
						while($notificationinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							$notid = $notificationinfo['id'];					
							$nottemphide = $notificationinfo['temphide'];			$nottemphide = explode(",",$nottemphide);				
							$nottemphidetime = $notificationinfo['temphidetime'];	$nottemphidetime = explode(",",$nottemphidetime);				
							
							if ( !in_array($userid, $nottemphide)){		// if id is not in temphide
								array_push($nottemphide,$userid);					
								$nottemphide = array_filter($nottemphide);
								$nottemphide = implode(",",$nottemphide);	
								array_push($nottemphidetime,$time); 				
								$nottemphidetime = array_filter($nottemphidetime);
								$nottemphidetime = implode(",",$nottemphidetime);

								$update = mysqli_query($link,"UPDATE `notification` SET 
								`temphide`= '$nottemphide',`temphidetime`= '$nottemphidetime',`timeedited`= '$time' WHERE `id`='$id'");
								if ( $update ) {
									$responseArray = array('id' => 'success', 'message' => 'تم إخفاء التنبيه بنجاح' );
								} else {
									$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $update' );
								}
							} else {									// id is in temphide already
								$responseArray = array('id' => 'warning', 'message' => 'userid already in temphide' );
							}
						}
					} else {
						$responseArray = array('id' => 'warning', 'message' => 'لم يتم العثور على نتائج' );
					}
				} else {
					$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على $id' );	
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'permhide' )   {
				if(isset($_POST['id']) && !empty($_POST['id']))	{
					$id = mysqli_real_escape_string($link, $_POST['id']);	
					$record = mysqli_query($link,"SELECT * FROM `notification` WHERE `id`='$id' AND status = '1' ");
					if(@mysqli_num_rows($record) > 0){	
						while($notificationinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							$notid = $notificationinfo['id'];					
							$notpermhide = $notificationinfo['permhide'];			$notpermhide = explode(",",$notpermhide);				
							
							if ( !in_array($userid, $notpermhide)){		// if id is not in permhide
								array_push($notpermhide,$userid);					
								$notpermhide = array_filter($notpermhide);
								$notpermhide = implode(",",$notpermhide);	
								$update = mysqli_query($link,"UPDATE `notification` SET 
								`permhide`= '$notpermhide' WHERE `id`='$id'");
								if ( $update ) {
									$responseArray = array('id' => 'success', 'message' => 'تم حذف التنبيه بنجاح' );
								} else {
									$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $update' );
								}
							} else {									// id is in permhide already
								$responseArray = array('id' => 'warning', 'message' => 'userid already in permhide' );
							}
						}
					} else {
						$responseArray = array('id' => 'warning', 'message' => 'لم يتم العثور على نتائج' );
					}
				} else {
					$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على $id' );	
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'notreset' )   {
				$record = mysqli_query($link,"SELECT * FROM `notification` WHERE ( `status`= 1 AND FIND_IN_SET('$userid', temphide) ) OR ( `status`= 1 AND FIND_IN_SET('$userid', permhide) )");
				$notnum = mysqli_num_rows($record);
				if(@mysqli_num_rows($record) > 0){	
					$z = 0;					
					$Totalnotinfo = [];	
					while($notificationinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
						$notid = $notificationinfo['id'];					
						$nottemphide = $notificationinfo['temphide'];			$nottemphide = explode(",",$nottemphide);				
						$nottemphidetime = $notificationinfo['temphidetime'];	$nottemphidetime = explode(",",$nottemphidetime);				
						$notpermhide = $notificationinfo['permhide'];			$notpermhide = explode(",",$notpermhide);				

						if ( in_array($userid, $nottemphide)){		// if id is not in temphide
							$index = '';
							$index = array_search($userid,$nottemphide);	
							unset($nottemphide[$index]);	unset($nottemphidetime[$index]);
							$nottemphide = array_filter($nottemphide);			$nottemphide = implode(",",$nottemphide);	
							$nottemphidetime = array_filter($nottemphidetime);	$nottemphidetime = implode(",",$nottemphidetime);
							$update = mysqli_query($link,"UPDATE `notification` SET 
							`temphide`= '$nottemphide',`temphidetime`= '$nottemphidetime',`timeedited`= '$time' WHERE `id`='$notid'");
						}

						if ( in_array($userid, $notpermhide)){		// if id is not in temphide
							$index = '';
							$index = array_search($userid,$notpermhide);	
							unset($notpermhide[$index]);
							$notpermhide = array_filter($notpermhide);
							$notpermhide = implode(",",$notpermhide);
							$update = mysqli_query($link,"UPDATE `notification` SET 
							`permhide`= '$notpermhide',`timeedited`= '$time' WHERE `id`='$notid'");
						}
						
						$opsid = $notificationinfo['opsid'];
						$code = $notificationinfo['code'];		
						$message = $notificationinfo['message'];
						$timeadded = Time_Passed(date($notificationinfo['timeadded']),'time');
						${'notinfo'.$z} = array('id' => $notid,'opsid' => $opsid,'code' => $code,'message' => $message,'timeadded' => $timeadded );	
						$z++;
					}
					$responseArray = array('id' => 'success', 'message' => 'تم إستعادة التنبيهات بنجاح' , 'notnum' => $notnum );
				} else {
					$responseArray = array('id' => 'warning', 'message' => 'لايوجد تنبيهات مستعاده' , 'notnum' => $notnum );
				}
			} else {
	// NOTIFICATION SECTION			
	// Customer Section
				$record = mysqli_query($link,"SELECT id,name,balance FROM `customers` WHERE `balance` >= $MAXDEBT ");
				$customerids = [];		$customernames = [];	$customerbalances = [];
				if(@mysqli_num_rows($record) > 0){	
					while($customersinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
						$cusid = $customersinfo['id'];					array_push($customerids,$cusid); 
						$name = $customersinfo['name'];					array_push($customernames,$name); 
						$balance = $customersinfo['balance'];			array_push($customerbalances,$balance); 
					}
				}
		// Find Existing Customers in Notification To DELETE	
				$record = mysqli_query($link,"SELECT * FROM `notification` WHERE code = 'MAXDEBT' AND status = '1' ");
				if(@mysqli_num_rows($record) > 0){	
					while($notificationinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
						$notid = $notificationinfo['id'];					
						$notopsid = $notificationinfo['opsid'];
						if ( !in_array($notopsid, $customerids)){		// Remove Customers in Notification who no longer maxe out in debt
							$update = mysqli_query($link,"UPDATE `notification` SET 
							`status`='0', `temphide`='', `permhide`='', `timeclosed`='$time' WHERE `id`='$notid'");	// Shut down notification
						} else {										// if user exists remove it from arrays to be inserted
							$index = array_search($notopsid,$customerids);	
							unset($customerids[$index]);	unset($customernames[$index]);	unset($customerbalances[$index]);	
						}
					}
				}
		// INSERT INTO NOTIFICATION
				for($a=0;$a<count($customerids);$a++){
					$ins = mysqli_query($link,"INSERT INTO `notification`( 
					`id`,`opsid`,`code`,`message`,`temphide`,`permhide`,`temphidetime`,`status`,`timeadded`,`timeedited`,`timeclosed`
					) VALUES ( 
					NULL,'$customerids[$a]','MAXDEBT',
					'العميل $customernames[$a] وصل إلى السقف الأعلى للدين $MAXDEBT ريال وعليه سداد $customerbalances[$a] ريال',
					'','','',1,'$time','',''
					)");
				}
	// INVENTORY SECTION
				$record = mysqli_query($link,"SELECT * FROM `inventory` ");
				// $customerids = [];		$customernames = [];	$customerbalances = [];
				$inventoryids = [];		$inventorycodes = [];	$inventorybcs = [];	
				if(@mysqli_num_rows($record) > 0){	
					while($invinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
						$invid = $invinfo['id'];
						$invbc = $invinfo['bc'];
						$invqty = $invinfo['qty'];
						$invminqty = $invinfo['minqty'];
						$invcost = $invinfo['cost'];
						$invprice = $invinfo['price'];
						$invdisc = $invinfo['disc'];
						$invwsbc = $invinfo['wsbc'];							$invwsbc = explode(",",$invwsbc);
						$invipb = $invinfo['itemsperbox'];						$invipb = explode(",",$invipb);
						$invwsprice = $invinfo['wsprice'];						$invwsprice = explode(",",$invwsprice);
						$invday = $invinfo['day'];
						$invmonth = $invinfo['month'];
						$invyear = $invinfo['year'];
						$expirydate = $invmonth.'/'.$invday.'/'.$invyear;		// American Standard M/D/Y
						$expirydate = strtotime($expirydate);			

						if ( (float)$invqty <= (float)$invminqty ) {
							array_push($inventoryids,$invid); 	array_push($inventorybcs,$invbc); 	array_push($inventorycodes,'LOWINV'); 
						}
						if ( ( (float)$invprice - (float)$invdisc ) <= $invcost ) {
							array_push($inventoryids,$invid); 	array_push($inventorybcs,$invbc); 	array_push($inventorycodes,'LOWRPRICE'); 
						}
						for($k=0;$k<count((array)$invwsprice);$k++){
							if ( (float)$invwsprice[$k] <= ( (float)$invipb[$k] * (float)$invcost ) ) { 
								array_push($inventoryids,$invid); 	array_push($inventorybcs,$invbc); 	array_push($inventorycodes,'LOWWSPRICE'); 
							}
						}
						if ( $time > $expirydate ) {								// EXPIRED
							array_push($inventoryids,$invid); 		array_push($inventorybcs,$invbc); 	array_push($inventorycodes,'EXPIRED'); 
						} elseif ( $time+432000 > $expirydate ) {					// EXPIRING in 5 DAYS
							array_push($inventoryids,$invid); 		array_push($inventorybcs,$invbc); 	array_push($inventorycodes,'EXPIRING'); 
						}
					}
				}
		// Find Existing Inventory in Notification To DELETE	
				$record = mysqli_query($link,"SELECT * FROM `notification` WHERE ( `code`='LOWINV' OR `code`='LOWRPRICE' OR `code`='LOWWSPRICE' OR `code`='EXPIRED' OR `code`='EXPIRING' ) AND status = '1' ");
				if(@mysqli_num_rows($record) > 0){	
					while($notificationinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
						$notid = $notificationinfo['id'];					
						$notopsid = $notificationinfo['opsid'];
						$notcode = $notificationinfo['code'];
						if ( !in_array($notopsid, $inventoryids)){		// Remove Customers in Notification who no longer maxe out in debt
							$update = mysqli_query($link,"UPDATE `notification` SET 
							`status`='0', `temphide`='', `permhide`='', `timeclosed`='$time' WHERE `id`='$notid'");	// Shut down notification
						} else {										// if user exists remove it from arrays to be inserted
							for($k=0;$k<count((array)$inventoryids);$k++){
								if ( $notopsid == $inventoryids[$k] && $notcode == $inventorycodes[$k] ) { 
									unset($inventoryids[$k]);	unset($inventorycodes[$k]);	unset($inventorybcs[$k]);	
								}
							}
							$inventoryids = array_values($inventoryids);	
							$inventorycodes = array_values($inventorycodes);	
							$inventorybcs = array_values($inventorybcs);	
						}
					}
				}
		// INSERT INTO NOTIFICATION
				for($a=0;$a<count($inventoryids);$a++){
					$bcfetcher = mysqli_fetch_array(mysqli_query($link," SELECT description FROM `ownbcdb` WHERE barcode = '$inventorybcs[$a]' "), MYSQLI_ASSOC);
					$bcdesc = $bcfetcher['description'];
					if ( $inventorycodes[$a] == 'LOWINV' ) { $invmessage = 'المنتج  '.$bcdesc.' برقم باركود  '.$inventorybcs[$a].'  على وشك النفاذ';}
					if ( $inventorycodes[$a] == 'LOWRPRICE' ) { $invmessage = 'سعر منتج التفريد  '.$bcdesc.' برقم باركود  '.$inventorybcs[$a].'  أقل أو مساوي للتكللفه';}
					if ( $inventorycodes[$a] == 'LOWWSPRICE' ) { $invmessage = 'سعر منتج الجمله لـ  '.$bcdesc.' برقم باركود  '.$inventorybcs[$a].'  أقل أو مساوي للتكللفه';}
					if ( $inventorycodes[$a] == 'EXPIRED' ) { $invmessage = 'المنتج  '.$bcdesc.' برقم باركود  '.$inventorybcs[$a].'انتهى تاريخ صلاحيته';	}
					if ( $inventorycodes[$a] == 'EXPIRING' ) { 	$invmessage = 'المنتج  '.$bcdesc.' برقم باركود  '.$inventorybcs[$a].'على وشك إنتهاء صلاحيته';	}
					$ins = mysqli_query($link,"INSERT INTO `notification`( 
					`id`,`opsid`,`code`,`message`,`temphide`,`permhide`,`temphidetime`,`status`,`timeadded`,`timeedited`,`timeclosed`
					) VALUES ( 
					NULL,'$inventoryids[$a]','$inventorycodes[$a]',	'$invmessage', '','','',1,'$time','',''
					)");
				}
				$responseArray = array('id' => 'success', 'message' => 'تم تحديث التنبيهات' );
			}
///////////////////////
		}
	} 
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $POST');
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($notnum) && $notnum > 0 ){
		for($a=0;$a<$notnum;$a++){
			array_push($Totalnotinfo,${'notinfo'.$a}); 
		}	
		array_push($responseArray,$Totalnotinfo); 
	}
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>