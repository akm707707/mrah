<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		
session_start();
//print_r($_POST);
$logtext = '';
if($_POST){
	$time = time();			$rs = '';
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
		// DELETE BARCODE SECTION ////////////////////////////////////////////////////////////////////////////////////////////
			if(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'delete' )   {
				if(isset($_POST['id']) && !empty($_POST['id']) )   {
					$id = mysqli_real_escape_string($link, $_POST['id']);
						// for logging purposes
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `ownbcdb` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logbc = $logrecord['barcode'];
						$logbcdesc = $logrecord['description'];
							$logtext .= 'تم حذف باركود رقم '.$logbc.' بوصف '.$logbcdesc.' ';
						$logcat = $logrecord['category'];
							if ( !empty($logcat) ) { $logtext .= 'وتصنيف '.$logcat.' '; }
						$logwsbc = $logrecord['wsbc'];
						$logwsbcdesc = $logrecord['wsdescription'];
						$logwsipb = $logrecord['wsitemsperbox'];
							if ( !empty($logwsbc) && !empty($logwsbcdesc) && !empty($logwsipb) ) { 
								$logtext .= 'وباركود جمله '.$logwsbc.' ووصف باركود جمله '.$logwsbcdesc.' وعدد حبات تفريد '.$logwsipb;
							}

					$delete = mysqli_query($link,"DELETE FROM ownbcdb WHERE id = $id ");
					if ($delete) {	
						$responseArray = array('id' => 'success', 'message' => 'تم حذف الباركود بنجاح');
						// $logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','barcode','تم حذف باركود رقم $logbc بوصف $logbcdesc وتصنيف $logcat وباركود جمله $logwsbc ووصف باركود جمله $logwsbcdesc وعدد حبات تفريد $logwsipb','$time' )");
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','barcode','$logtext','$time' )");
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات($delete');
					}
				} else {
					$responseArray = array('id' => 'danger', 'message' => 'لا توجد بيانات');
				}

		// ADD BARCODE SECTION ////////////////////////////////////////////////////////////////////////////////////////////
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'add' )   {
				if (!isset($_POST['productbarcode']) || empty($_POST['productbarcode']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لايوجد رقم للباركود');
				} elseif ( !is_numeric($_POST['productbarcode'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'رقم الباركود يجب أن يحتوي على أرقام فقط');
				} elseif (!isset($_POST['owndbdescription']) || empty($_POST['owndbdescription']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لايوجد وصف للمنتج');
				// } elseif( !empty($_POST['wsbarcode']) && !is_numeric($_POST['wsbarcode']) )   {
					// $responseArray = array('id' => 'danger', 'message' => 'رقم باركود الجمله يجب أن يحتوي على أرقام فقط');
				} else {
					$owndbdescription = mysqli_real_escape_string($link, $_POST['owndbdescription']);
					$productbarcode = mysqli_real_escape_string($link, $_POST['productbarcode']);
					$logtext .= 'تم إضافة باركود رقم '.$productbarcode.' بوصف '.$owndbdescription.' ';

					// DEFINE CATEGORY 		/////////////////////////////////////////////////////////////////////
					if(isset($_POST['category']) && !empty($_POST['category']))	{
						$category = mysqli_real_escape_string($link, $_POST['category']);
						$logtext .= 'وتصنيف '.$category.' ';

					} else { 
						$category = ''; 
					}


					$record = mysqli_query($link,"SELECT barcode FROM `ownbcdb` WHERE barcode = 'productbarcode' ");
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id'=>'danger','message'=>'لباركود مضاف مسبقاً لمنتج آخر'); goto end;	
					}

					
					$wsbcs = array_slice($_POST['wsbarcode'],0, -1);		// start from 0 and exclude last one (empty)
					$wsdescs = array_slice($_POST['wsdescription'],0, -1);
					$wsipbs = array_slice($_POST['itemsperbox'],0, -1);
					
					$wsbcs = array_filter($wsbcs);
					for($i=0;$i<count((array)$wsbcs);$i++){
					// for($i=0;$i<count($wsbcs);$i++){
						if ( !empty( $wsbcs[$i] ) && !is_numeric( $wsbcs[$i]) )   {
							$responseArray = array('id' => 'danger', 'message' => 'رقم باركود الجمله يجب أن يحتوي على أرقام فقط'); goto end;
						}
						if ( !empty( $wsipbs[$i] ) && !is_numeric( $wsipbs[$i]) )   {
							$responseArray = array('id' => 'danger', 'message' => 'عدد التفريد في الجمله يجب أن يحتوي على أرقام فقط'); goto end;
						}
						if ( empty( $wsbcs[$i] ) || empty( $wsipbs[$i] ) || empty( $wsdescs[$i] ) )   {
							if ( empty( $wsbcs[$i] ) ) {
								$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال رقم لباركود الجمله'); goto end;
							}
							if ( empty( $wsdescs[$i] ) ) {
								$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال وصف لباركود الجمله '.$wsbcs[$i]); goto end;
							}
							if ( empty( $wsipbs[$i] ) ) {
								$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال عدد التفريد لباركود الجمله '.$wsbcs[$i]); goto end;
							}
						}

						$record = mysqli_query($link,"SELECT wsbc FROM `ownbcdb` WHERE FIND_IN_SET('$wsbcs[$i]', wsbc)");
						if(@mysqli_num_rows($record) > 0){
							$responseArray = array('id'=>'danger','message'=>'باركود الجمله مضاف مسبقاً '.$wsbcs[$i]);	goto end;	
						}
						$record = mysqli_query($link,"SELECT barcode FROM `ownbcdb` WHERE barcode = '$wsbcs[$i]'   ");
						if(@mysqli_num_rows($record) > 0){
							$responseArray = array('id'=>'danger','message'=>'باركود الجمله مضاف مسبقاً كتفريد '.$wsbcs[$i]); goto end;	
						}
					} 

					
					// if ( count($wsbcs) > 0 ) {
					if ( count((array)$wsbcs) > 0 ) {
						// for($i=0;$i<count($wsbcs);$i++){
						for($i=0;$i<count((array)$wsbcs);$i++){
							$wsbc = implode(",",$wsbcs);
							$wsdesc = implode(",",$wsdescs);
							$wsipb = implode(",",$wsipbs);
						}
						$logtext .= 'وباركود جمله '.$wsbc.' ووصف باركود جمله '.$wsdesc.' وعدد حبات تفريد '.$wsipb;
					} else {
						$wsbc = '';
						$wsdesc = '';
						$wsipb = '';
					}
					
					$record = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE barcode = '$productbarcode'   ");
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id' => 'danger', 'message' => 'المنتج معرف مسبقاً');		goto end;	
					} else {	// BARCODE DOESNT EXIST /////////////////////////////////////////////////////////////////////
						
						$ins = mysqli_query($link,"INSERT INTO `ownbcdb`( `id`, `barcode`, `category`, `description`, `wsbc`, `wsdescription`, `wsitemsperbox`, `timeadded` )VALUES( NULL,'$productbarcode','$category', '$owndbdescription','$wsbc','$wsdesc','$wsipb', '$time' )");
						if ($ins) {
							// $logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','barcode','تم إضافة باركود رقم $productbarcode بوصف $owndbdescription وتصنيف $category وباركود جمله $wsbc ووصف باركود جمله $wsdesc وعدد حبات تفريد $wsipb','$time' )");
							
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','barcode','$logtext','$time' )");
							
							// Get las input 
							$record = mysqli_query($link,"SELECT * FROM `ownbcdb` ORDER BY `id` DESC LIMIT 1");
							
							if(@mysqli_num_rows($record) > 0){									
								while($barcodeinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
								  $id = $barcodeinfo['id'];
								  $barcode = $barcodeinfo['barcode'];
								  $category = $barcodeinfo['category'];
								  $description = $barcodeinfo['description'];
								  $wsdescription= $barcodeinfo['wsdescription'];  $wsdescription = explode(",",$wsdescription);
								  $wsbc = $barcodeinfo['wsbc'];						$wsbc = explode(",",$wsbc);
								  $wsipb = $barcodeinfo['wsitemsperbox'];				$wsipb = explode(",",$wsipb);
								  $timeadded = $barcodeinfo['timeadded'];											
								  $timeadded = Time_Passed(date($barcodeinfo['timeadded']),'time');

								  $barcodedata = array('id' => $id,'description' => $description,'barcode' => $barcode,'category' => $category,'wsipb' => $wsipb,'wsdescription' => $wsdescription,'wsbc' => $wsbc,'timeadded' => $timeadded );	
								}
								$responseArray = array('id' => 'success','message' => 'تمت الإضافه بنجاح', 'barcodedata' => $barcodedata);
							} else {
								$responseArray = array('id' => 'danger', 'message' => 'no barcode exists');
							}
							// $responseArray = array('id' => 'success', 'message' => 'تمت الإضافه بنجاح', 'lastid' => $id);
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات$ins');
						}
					}
				}
		// UPDATE BARCODE SECTION ////////////////////////////////////////////////////////////////////////////////////////////		
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'update' )   {
				if(!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $id');
				} elseif (!isset($_POST['owndbdescription']) || empty($_POST['owndbdescription']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لايوجد وصف للمنتج');
				} elseif (!isset($_POST['productbarcode']) || empty($_POST['productbarcode']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لايوجد رقم للباركود');
				} elseif ( !is_numeric($_POST['productbarcode'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'رقم الباركود يجب أن يحتوي على أرقام فقط');
				// } elseif( !empty($_POST['wsbarcode']) && !is_numeric($_POST['wsbarcode']) )   {
					// $responseArray = array('id' => 'danger', 'message' => 'رقم باركود الجمله يجب أن يحتوي على أرقام فقط');
				} else {
					$id = mysqli_real_escape_string($link, $_POST['id']);
						// for logging purposes
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `ownbcdb` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logbc = $logrecord['barcode'];
						$logbcdesc = $logrecord['description'];
							$logtext .= 'تم تحديث باركود رقم '.$logbc.' بوصف '.$logbcdesc.' ';
						$logcat = $logrecord['category'];
							if ( !empty($logcat) ) { $logtext .= 'وتصنيف '.$logcat.' '; }
						$logwsbc = $logrecord['wsbc'];						
						$logwsbcdesc = $logrecord['wsdescription'];
						$logwsipb = $logrecord['wsitemsperbox'];
							if ( !empty($logwsbc) && !empty($logwsbcdesc) && !empty($logwsipb) ) { 
								$logtext .= 'وباركود جمله '.$logwsbc.' ووصف باركود جمله '.$logwsbcdesc.' وعدد حبات تفريد '.$logwsipb;
							}
						
					$owndbdescription = mysqli_real_escape_string($link, $_POST['owndbdescription']);
					$productbarcode = mysqli_real_escape_string($link, $_POST['productbarcode']);
						$logtext .= ' إلى <br>باركود رقم '.$productbarcode.' بوصف '.$owndbdescription.' ';

					// DEFINE CATEGORY 		/////////////////////////////////////////////////////////////////////
					if(isset($_POST['category']) && !empty($_POST['category']))	{
						$category = mysqli_real_escape_string($link, $_POST['category']);
						$logtext .= 'وتصنيف '.$category.' ';
					} else { 
						$category = ''; 
					}

					$wsbcs = array_slice($_POST['wsbarcode'],0, -1);		// start from 0 and exclude last one (empty)
					$wsdescs = array_slice($_POST['wsdescription'],0, -1);
					$wsipbs = array_slice($_POST['itemsperbox'],0, -1);

					$wsbcs = array_filter($wsbcs);
					for($i=0;$i<count((array)$wsbcs);$i++){
					// for($i=0;$i<count($wsbcs);$i++){
						if ( !empty( $wsbcs[$i] ) && !is_numeric( $wsbcs[$i]) )   {
							$responseArray = array('id' => 'danger', 'message' => 'رقم باركود الجمله يجب أن يحتوي على أرقام فقط'); goto end;
						}
						if ( !empty( $wsipbs[$i] ) && !is_numeric( $wsipbs[$i]) )   {
							$responseArray = array('id' => 'danger', 'message' => 'عدد التفريد في الجمله يجب أن يحتوي على أرقام فقط'); goto end;
						}
						if ( empty( $wsbcs[$i] ) || empty( $wsipbs[$i] ) || empty( $wsdescs[$i] ) )   {
							if ( empty( $wsbcs[$i] ) ) {
								$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال رقم لباركود الجمله'); goto end;
							}
							if ( empty( $wsdescs[$i] ) ) {
								$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال وصف لباركود الجمله '.$wsbcs[$i]); goto end;
							}
							if ( empty( $wsipbs[$i] ) ) {
								$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال عدد التفريد لباركود الجمله '.$wsbcs[$i]); goto end;
							}
						}

						$record = mysqli_query($link,"SELECT wsbc FROM `ownbcdb` WHERE FIND_IN_SET('$wsbcs[$i]', wsbc) AND id != '$id'");
						if(@mysqli_num_rows($record) > 0){
							$responseArray = array('id'=>'danger','message'=>'باركود الجمله مضاف مسبقاً '.$wsbcs[$i]);	goto end;	
						}
						$record = mysqli_query($link,"SELECT barcode FROM `ownbcdb` WHERE barcode = '$wsbcs[$i]' AND id != '$id'");
						if(@mysqli_num_rows($record) > 0){
							$responseArray = array('id'=>'danger','message'=>'باركود الجمله مضاف مسبقاً كتفريد '.$wsbcs[$i]); goto end;	
						}
					}

					// if ( count($wsbcs) > 0 ) {
					if ( count((array)$wsbcs) > 0 ) {
						// for($i=0;$i<count($wsbcs);$i++){
						for($i=0;$i<count((array)$wsbcs);$i++){
							$wsbc = implode(",",$wsbcs);
							$wsdesc = implode(",",$wsdescs);
							$wsipb = implode(",",$wsipbs);
						}
						$logtext .= 'وباركود جمله '.$wsbc.' ووصف باركود جمله '.$wsdesc.' وعدد حبات تفريد '.$wsipb;
					} else {
						$wsbc = '';
						$wsdesc = '';
						$wsipb = '';
					}

		////////////////////////////
					$record = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE barcode = '$productbarcode' AND id != '$id'  ");
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id' => 'danger', 'message' => 'الباركود مضاف مسبقاً');		goto end;	
					} else {
						// Updating Section
						$update = mysqli_query($link,"UPDATE `ownbcdb` SET `barcode`='$productbarcode', `description`='$owndbdescription', `category`='$category', `wsbc`='$wsbc' , `wsdescription`='$wsdesc' , `wsitemsperbox`='$wsipb'	WHERE `id`='$id'");

						if ($update) {	
							// $logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','barcode','تم تحديث باركود رقم $logbc بوصف $logbcdesc وتصنيف $logcat وباركود جمله $logwsbc ووصف باركود جمله $logwsbcdesc وعدد تفريد $logwsipb إلى<br>باركود رقم $productbarcode بوصف $owndbdescription وتصنيف $category وباركود جمله $wsbc ووصف باركود جمله $wsdesc وعدد تفريد $wsipb','$time' )");
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','barcode','$logtext','$time' )");
							
							// Get las input 
							$record = mysqli_query($link,"SELECT * FROM `ownbcdb` ORDER BY `id` DESC LIMIT 1");
							
							if(@mysqli_num_rows($record) > 0){									
								while($barcodeinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
								  $id = $barcodeinfo['id'];
								  $barcode = $barcodeinfo['barcode'];
								  $category = $barcodeinfo['category'];
								  $description = $barcodeinfo['description'];
								  $wsdescription= $barcodeinfo['wsdescription'];  $wsdescription = explode(",",$wsdescription);
								  $wsbc = $barcodeinfo['wsbc'];					  $wsbc = explode(",",$wsbc);
								  $wsipb = $barcodeinfo['wsitemsperbox'];		  $wsipb = explode(",",$wsipb);
								  $timeadded = $barcodeinfo['timeadded'];											
								  $timeadded = Time_Passed(date($barcodeinfo['timeadded']),'time');

								  $barcodedata = array('id' => $id,'description' => $description,'barcode' => $barcode,'category' => $category,'wsipb' => $wsipb,'wsdescription' => $wsdescription,'wsbc' => $wsbc,'timeadded' => $timeadded );	
								}
								$responseArray = array('id' => 'success','message' => 'تمت التحديث بنجاح', 'barcodedata' => $barcodedata);
							} else {
								$responseArray = array('id' => 'danger', 'message' => 'no barcode exists');
							}							
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $update');
						}
					}
				}
			} else {
		// FETCH SPECIFIC BARCODE SECTION ///////////////////////////////////////////////////////////////////////////////////////		
				if(isset($_POST['id']) && !empty($_POST['id']))	{
					$id = mysqli_real_escape_string($link, $_POST['id']);	
					$record = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE id = '$id'   ");
					
					if(@mysqli_num_rows($record) > 0){									
						while($barcodeinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							$id = $barcodeinfo['id'];
							$barcode = $barcodeinfo['barcode'];
							$category = $barcodeinfo['category'];
							$description = $barcodeinfo['description'];
							// To Be Checked //////////////////////////////////////////////////
							$wsdescription = $barcodeinfo['wsdescription'];		$wsdescription = explode(",",$wsdescription);
							$wsbc = $barcodeinfo['wsbc'];						$wsbc = explode(",",$wsbc);
							$wsipb = $barcodeinfo['wsitemsperbox'];				$wsipb = explode(",",$wsipb);
							//////////////////////////////////////////////////
							$timeadded = $barcodeinfo['timeadded'];											
							$timeadded = Time_Passed(date($barcodeinfo['timeadded']),'time');

							$barcodedata = array('id' => $id,'description' => $description,'barcode' => $barcode,'category' => $category,'wsdescription' => $wsdescription,'wsbc' => $wsbc,'wsipb' => $wsipb,'timeadded' => $timeadded );	
						}
						$responseArray = array('id' => 'success', 'barcodedata' => $barcodedata);
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'no barcode exists');
					}
				} else {
		// FETCH BARCODES AND CATEGORIES SECTION //////////////////////////////////////////////////////////////////////////			
					// $record = mysqli_query($link,"SELECT * FROM `ownbcdb` ");
		// FILLING CATEGORY ARRAY /////////////////////////////////////////////////////////////////////////////////////////
					$catrecord = mysqli_query($link,"SELECT * FROM `category` ");
					$catnum = mysqli_num_rows($catrecord);
					if(@mysqli_num_rows($catrecord) > 0){
						$catarray = [];	
						while($catinfo = mysqli_fetch_array($catrecord, MYSQLI_ASSOC)){
							$category = $catinfo['category'];
							array_push($catarray,$category);
						}
					}
		// FILLING BARCODES ARRAY /////////////////////////////////////////////////////////////////////////////////////////
					// $record = mysqli_query($link,"SELECT * FROM `ownbcdb` LIMIT 10");
					$record = mysqli_query($link,"SELECT * FROM `ownbcdb` ");
					$barcodenum = mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){
						$z = 0;					
						$Totalbarcodeinfos = [];	
						while($barcodeinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							$id = $barcodeinfo['id'];
							$barcode = $barcodeinfo['barcode'];
							$category = $barcodeinfo['category'];
							$description = $barcodeinfo['description'];
							$wsdescription = $barcodeinfo['wsdescription'];		$wsdescription = explode(",",$wsdescription);
							$wsbc = $barcodeinfo['wsbc'];						$wsbc = explode(",",$wsbc);
							$wsipb = $barcodeinfo['wsitemsperbox'];				$wsipb = explode(",",$wsipb);
							$timeadded = $barcodeinfo['timeadded'];								
							$timeadded = Time_Passed(date($barcodeinfo['timeadded']),'time');

						${'barcodeinfo'.$z} = array('id' => $id,'description' => $description,'barcode' => $barcode,'category' => $category,'wsdescription' => $wsdescription,'wsbc' => $wsbc,'wsipb' => $wsipb,'timeadded' => $timeadded );	
						$z++;
						}
					} 
					$responseArray = array('id' => 'success', 'barcodenum' => $barcodenum, 'catnum' => $catnum);
				}
			}
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($barcodenum) && $barcodenum > 0 ){
		for($a=0;$a<$barcodenum;$a++){
			array_push($Totalbarcodeinfos,${'barcodeinfo'.$a}); 
		}	
		array_push($responseArray,$Totalbarcodeinfos); 
	}
	if ( isset($catnum) && $catnum > 0 ){
		array_push($responseArray,$catarray); 	
	}
	
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
	
} else {    echo $responseArray['message'];		}		// else just display the message
?>