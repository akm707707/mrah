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
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `inventoryedit` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logbc = $logrecord['bc'];
							$bcfetcher = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `ownbcdb` WHERE barcode = '$logbc' "), MYSQLI_ASSOC);
							$logdesc = $bcfetcher['description'];
						$logqty = $logrecord['qty'];	
						$logcost = $logrecord['cost'];	
						$logprice = $logrecord['price'];	
						$logtype = $logrecord['type'];	
						
					$record0 = mysqli_query($link,"SELECT * FROM `inventoryedit` WHERE `id`='$id' ");
					if(@mysqli_num_rows($record0) > 0){
						while ( $inveditinfo = mysqli_fetch_array( $record0, MYSQLI_ASSOC ) ){ 	
							$bc = $inveditinfo['bc'];	
							$qty = $inveditinfo['qty'];	
							$type = $inveditinfo['type'];					
						}
						
						$record1 = mysqli_query($link,"SELECT * FROM `inventory` WHERE `bc`='$bc' ");
						if(@mysqli_num_rows($record1) > 0){
							while ( $invinfo = mysqli_fetch_array( $record1, MYSQLI_ASSOC ) ){ 	
								$invqty = $invinfo['qty'];	
							}
							
							if( $type == 'returned') {		$newqty = $invqty - $qty;	}
							if( $type == 'expired') {		$newqty = $invqty + $qty;	}
							
							$update0 = mysqli_query($link,"UPDATE `inventory` SET `qty`='$newqty' WHERE `bc`='$bc' ");
							if (!$update0) { 
								$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات$update0');	goto end; 
							} else {
								if( $type == 'returned') {		
									$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','تم تحديث المخزون عن طريق حذف منتج معاد للباركود رقم $logbc و وصف $logdesc بتعديل الكميه من $invqty إلى $newqty','$time' )");
								}
								if( $type == 'expired') {		
									$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','تم تحديث المخزون عن طريق إستعادة منتج منتهي الصلاحيه للباركود رقم $logbc و وصف $logdesc بتعديل الكميه من $invqty إلى $newqty','$time' )");
								}
							}
						} 
					}
					
					$delete = mysqli_query($link,"DELETE FROM inventoryedit WHERE id = $id ");
					if ($delete) {	
						$responseArray = array('id' => 'success', 'message' => 'تم حذف التعديل بنجاح');
						if( $type == 'returned') {		
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventoryedit','تم تعديل المخزون بإزالة منتح معاد بكمية $qty للباركود رقم $logbc ووصف $logdesc','$time' )");
						}
						if( $type == 'expired') {		
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventoryedit','تم تعديل المخزون بإستعادة منتح منتهي الصلاحيه بكمية $qty للباركود رقم $logbc ووصف $logdesc','$time' )");
						}
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات');
					}
				} else {
					$responseArray = array('id' => 'danger', 'message' => 'لا توجد بيانات');
				}
			} elseif (isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'add' )   {
				if( !isset($_POST['bc'] ) || empty($_POST['bc']))	{
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال باركود المنتج');
				} elseif ( !is_numeric($_POST['bc'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'رقم الباركود يجب أن يحتوي على أرقام فقط');
				} elseif( !isset($_POST['type'] ) || empty($_POST['type']))	{
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإختيار نوع العمليه');
				} elseif( !isset($_POST['qty'] ) || empty($_POST['qty']))	{
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال الكميه');
				} elseif ( !is_numeric($_POST['qty'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'الكميه يجب أن تحتوي على أرقام فقط');
				} elseif ( !empty($_POST['price']) && !is_numeric($_POST['price']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'سعر البيع  يجب أن يحتوي على أرقام فقط');
				} else {
					$bc = mysqli_real_escape_string($link, $_POST['bc']);
					
					$record00 = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE `barcode`='$bc' ");
					// $record00 = mysqli_query($link," SELECT * FROM `ownbcdb` WHERE barcode = '$bc' "), 
					if(@mysqli_num_rows($record00) > 0){
					// for logging purposes
						while ( $bcfetcher = mysqli_fetch_array( $record00, MYSQLI_ASSOC ) ){ 	
							$logdesc = $bcfetcher['description'];
						}
					} else {	 $responseArray = array('id' => 'danger', 'message' => 'الباركود غير مضاف لقاعدة بياناتك');	goto end; }

						// $bcfetcher = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `ownbcdb` WHERE barcode = '$bc' "), MYSQLI_ASSOC);
						// $logdesc = $bcfetcher['description'];
					$qty = mysqli_real_escape_string($link, $_POST['qty']);
					$type = mysqli_real_escape_string($link, $_POST['type']);

					$record0 = mysqli_query($link,"SELECT * FROM `inventory` WHERE `bc`='$bc' ");
					if(@mysqli_num_rows($record0) > 0){
						while ( $invinfo = mysqli_fetch_array( $record0, MYSQLI_ASSOC ) ){ 	
							$invqty = $invinfo['qty'];	
							$invcost = $invinfo['cost'];	
							$invprice = $invinfo['price'];					
						}
					} else {	 $responseArray = array('id' => 'danger', 'message' => 'المنتج غير مضاف في المخزون');	goto end; }
					
					if( $type == 'returned') {
						if( !isset($_POST['price'] ) || empty($_POST['price']))	{
							$price = $invprice;
						} else {
							$price = mysqli_real_escape_string($link, $_POST['price']);
						}
						
						$newqty = $invqty + $qty;
						$update0 = mysqli_query($link,"UPDATE `inventory` SET `qty`='$newqty' WHERE `bc`='$bc' ");
						if (!$update0) { 
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات$update0');	goto end; 
						} else {
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','تم تحديث المخزون عن طريق أرجاع منتج بكمية $qty للباركود رقم $bc ووصف $logdesc ليصبح اجمالي الكميه $newqty','$time' )");
						}
						
						$ins0 = mysqli_query($link,"INSERT INTO `inventoryedit`( `id`,`bc`,`type`,`cost`,`price`,`qty`,`timeadded` )VALUES(	NULL,'$bc','$type',NULL,'$price','$qty','$time' )");

						if ($ins0) {
							$responseArray = array('id' => 'success', 'message' => 'تمت إستعادة المنتج بنجاح');
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventoryedit','تم تعديل المخزون بإرجاع كميه منتح وقدرها $qty للباركود رقم $bc ووصف $logdesc بيعت بسعر $price','$time' )");

						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات$ins0');
						}
					}
					
					if( $type == 'expired') {
						if ( $qty > $invqty ) {
							$responseArray = array('id' => 'danger', 'message' => 'الكميه المدخله أكبر من كمية المخزون');	goto end;
						} else {	
							$newqty = $invqty - $qty;
							$update1 = mysqli_query($link,"UPDATE `inventory` SET `qty`='$newqty' WHERE `bc`='$bc' ");
							if (!$update1) { 
								$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات$update1');	goto end; 
							} else {
								$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','تم تحديث المخزون عن طريق إزالة منتج منتهي الصلاحيه بكمية $qty للباركود رقم $bc ووصف $logdesc ليصبح اجمالي الكميه $newqty','$time' )");
	
							}
							
							$ins1 = mysqli_query($link,"INSERT INTO `inventoryedit`( `id`,`bc`,`type`,`cost`,`price`,`qty`,`timeadded` )VALUES(	NULL,'$bc','$type','$invcost',NULL,'$qty','$time' )");

							if ($ins1) {
								$responseArray = array('id' => 'success', 'message' => 'تمت إزالة المنتج بنجاح');
								$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventoryedit','تم تعديل المخزون بإزالة منتج منتهي الصلاحيه بكميه $qty للباركود رقم $bc ووصف $logdesc','$time' )");
							} else {
								$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات$ins0');
							}
						}	
					}
					
					if ( isset($ins0) || isset($ins1) ) {
						$finder = mysqli_query($link,"SELECT * FROM `inventoryedit` ORDER BY `id` DESC LIMIT 1");
						if(@mysqli_num_rows($finder) > 0){ 																				
							while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){	
								$lastid = $idfinder['id'];
								$bc = $idfinder['bc'];
								$type = $idfinder['type'];	
								$cost = $idfinder['cost'];
								$price = $idfinder['price'];	
								$qty = $idfinder['qty'];
								$timeadded = $idfinder['timeadded'];	
								$timeadded = Time_Passed(date($idfinder['timeadded']),'time');
							
							}
							$responseArray['lastid'] = $lastid;
							$responseArray['bc'] = $bc;
							$responseArray['type'] = $type;
							$responseArray['cost'] = $cost;
							$responseArray['price'] = $price;
							$responseArray['qty'] = $qty;
						}
						
					}
				}
			} else {
				$record = mysqli_query($link,"SELECT * FROM `inventoryedit` ");
					$inveditnum = mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){									
						$z = 0;				
						$bcarr= [];				// store BC to fetch names
						$bcarr2= [];				// store BC to fetch names
						$Totalinveditinfos = [];	
						while($inveditinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							$id = $inveditinfo['id'];
							$bc = $inveditinfo['bc'];
							$type = $inveditinfo['type'];	
							$cost = $inveditinfo['cost'];
							$price = $inveditinfo['price'];	
							$qty = $inveditinfo['qty'];
							$timeadded = $inveditinfo['timeadded'];	
							$timeadded = Time_Passed(date($inveditinfo['timeadded']),'time');
						
							// store BC to fetch names
							$bcarr[] = $bc;

							${'info'.$z} = array('id' => $id,'bc' => $bc,'type' => $type,'cost' => $cost,'price' => $price,'qty' => $qty,'timeadded' => $timeadded );	
							$z++;
						}
					
						$responseArray = array('id' => 'success', 'inveditnum' => $inveditnum);
					
						// Barcode name fetcher Section
						$bcarr = array_filter($bcarr); // remove empty values
						$bcarr = array_unique($bcarr); // remove duplicate values
						$bcarr = array_values($bcarr); // renumber array keys 
					
						for ($a = 0; $a < count($bcarr); $a++) {
							$record = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE `barcode` = $bcarr[$a] ");
							if(@mysqli_num_rows($record) > 0){					
								while($barcode = mysqli_fetch_array($record, MYSQLI_ASSOC)){
									$bc = $barcode['barcode'];
									$description = $barcode['description'];
								}
								${'barcodes'.$a} = array('bc' => $bc,'description' => $description );
							}
						}
					} else { 																				
						$responseArray = array('id' => 'danger', 'inveditnum' => $inveditnum);
					}
			}
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم$_POST');
}
end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($bcarr) && count($bcarr) > 0 ){	
		for($a=0;$a<count($bcarr);$a++){ 
			// needed in case some barcode doesnt exists in data bases count will be different 
			if ( isset(${'barcodes'.$a}) ) {
				array_push($bcarr2,${'barcodes'.$a}); 
			}
		}
		$bcnum = count($bcarr2);
		$responseArray["bcnum"] = $bcnum;
		array_push($responseArray,$bcarr2); 
	}
	
	if ( isset($inveditnum) && $inveditnum > 0 ){
		for($a=0;$a<$inveditnum;$a++){ 
			array_push($Totalinveditinfos,${'info'.$a}); 
		}	
		array_push($responseArray,$Totalinveditinfos); 
	}

	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>