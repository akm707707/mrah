<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		
session_start();
//print_r($_POST);	
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
						$logcat = $logrecord['category'];
						$logwsbc = $logrecord['wsbc'];
						$logwsbcdesc = $logrecord['wsdescription'];
					$delete = mysqli_query($link,"DELETE FROM ownbcdb WHERE id = $id ");
					if ($delete) {	
						$responseArray = array('id' => 'success', 'message' => 'تم حذف الباركود بنجاح');
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','barcode','تم حذف باركود رقم $logbc بوصف $logbcdesc وتصنيف $logcat وباركود جمله $logwsbc ووصف باركود جمله $logwsbcdesc','$time' )");
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
				} elseif( !empty($_POST['wsbarcode']) && !is_numeric($_POST['wsbarcode']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'رقم باركود الجمله يجب أن يحتوي على أرقام فقط');
				} else {
					$owndbdescription = mysqli_real_escape_string($link, $_POST['owndbdescription']);
					$productbarcode = mysqli_real_escape_string($link, $_POST['productbarcode']);

					if(isset($_POST['wsdescription']) && !empty($_POST['wsdescription']))	{
						$wsdescription = mysqli_real_escape_string($link, $_POST['wsdescription']); 
					} else { 	$wsdescription = ''; }

					if(isset($_POST['wsbarcode']) && !empty($_POST['wsbarcode']))	{
						$wsbarcode = mysqli_real_escape_string($link, $_POST['wsbarcode']); 
					} else { 	$wsbarcode = ''; 	}

					// $wsdescription = mysqli_real_escape_string($link, $_POST['wsdescription']);
					// $wsbarcode = mysqli_real_escape_string($link, $_POST['wsbarcode']);
					
					$record = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE barcode = '$productbarcode'   ");
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id' => 'danger', 'message' => 'المنتج معرف مسبقاً');		goto end;	
					} else {
						if ( !empty($wsbarcode) ) {
							$record = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE barcode = '$wsbarcode'   ");
							if(@mysqli_num_rows($record) > 0){
								$responseArray = array('id' => 'danger', 'message' => 'باركود الجمله مضاف كباركود تفريد');		goto end;	
							} 
							$record = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE wsbc = '$wsbarcode'   ");
							if(@mysqli_num_rows($record) > 0){
								$responseArray = array('id' => 'danger', 'message' => 'باركود الجمله مضاف لمنتج آخر');		goto end;	
							}
							if ( empty($wsdescription) ) {
								$responseArray = array('id' => 'danger', 'message' => 'لا يوجد وصف لباركود الجمله');		goto end;	
							}
						} else {
								$wsbarcode = NULL;
						}
			// BARCODE DOESNT EXIST ////////////////////////////////////////////////////////////////////////////////////////////
				// DEFINE CATEGORY ////////////////////////////////////////////////////////////////////////////////////////////
						if(isset($_POST['category']) && !empty($_POST['category']))	{
							$category = mysqli_real_escape_string($link, $_POST['category']); 
						} else { 
							$category = ''; 
						}
						
						$ins = mysqli_query($link,"INSERT INTO `ownbcdb`( `id`, `barcode`, `category`, `description`, `wsbc`, `wsdescription`, `timeadded` )VALUES( NULL,'$productbarcode','$category', '$owndbdescription', '$wsbarcode','$wsdescription', '$time' )");
						if ($ins) {
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','barcode','تم إضافة باركود رقم $productbarcode بوصف $owndbdescription وتصنيف $category وباركود جمله $wsbarcode ووصف باركود جمله $wsdescription','$time' )");
							// $finder = mysqli_query($link,"SELECT * FROM `ownbcdb` ORDER BY `id` DESC");
							// if(@mysqli_num_rows($finder) > 0){ while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ $id = $idfinder['id']; break;} }
							
							$finder = mysqli_query($link,"SELECT * FROM `ownbcdb` ORDER BY `id` DESC");
							if(@mysqli_num_rows($finder) > 0){ while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ $id = $idfinder['id']; break;} } else { $id = 1; }

							$responseArray = array('id' => 'success', 'message' => 'تمت الإضافه بنجاح', 'lastid' => $id);
							// if ($rs=='yes'){$responseArray['rs']='yes'; $responseArray['retailid'] = $retailid; } else { $responseArray['rs'] = 'no'; } 
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
				} elseif( !empty($_POST['wsbarcode']) && !is_numeric($_POST['wsbarcode']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'رقم باركود الجمله يجب أن يحتوي على أرقام فقط');
				} else {
					$id = mysqli_real_escape_string($link, $_POST['id']);
						// for logging purposes
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `ownbcdb` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logbc = $logrecord['barcode'];
						$logbcdesc = $logrecord['description'];
						$logcat = $logrecord['category'];
						$logwsbc = $logrecord['wsbc'];
						$logwsbcdesc = $logrecord['wsdescription'];
					$owndbdescription = mysqli_real_escape_string($link, $_POST['owndbdescription']);
					$productbarcode = mysqli_real_escape_string($link, $_POST['productbarcode']);
					$wsdescription = mysqli_real_escape_string($link, $_POST['wsdescription']);
					$wsbarcode = mysqli_real_escape_string($link, $_POST['wsbarcode']);
					
					if(isset($_POST['category']) && !empty($_POST['category']))	{
						$category = mysqli_real_escape_string($link, $_POST['category']); 
					} else { 
						$category = ''; 
					}
		////////////////////////////
					$record = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE barcode = '$productbarcode' AND id != '$id'  ");
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id' => 'danger', 'message' => 'الباركود مضاف مسبقاً');		goto end;	
					} else {
						
						if ( !empty($wsbarcode) ) {
							$record = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE barcode = '$wsbarcode'   ");
							if(@mysqli_num_rows($record) > 0){
								$responseArray = array('id' => 'danger', 'message' => 'باركود الجمله مضاف كباركود تفريد');		goto end;	
							} 
							$record = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE wsbc = '$wsbarcode'   ");
							if(@mysqli_num_rows($record) > 0){
								$responseArray = array('id' => 'danger', 'message' => 'باركود الجمله مضاف لمنتج آخر');		goto end;	
							}
							if ( empty($wsdescription) ) {
								$responseArray = array('id' => 'danger', 'message' => 'لا يوجد وصف لباركود الجمله');		goto end;	
							}
						} else {
								$wsbarcode = NULL;
						}
		////////////////////////////
						// Updating Section
						$update = mysqli_query($link,"UPDATE `ownbcdb` SET `barcode`='$productbarcode', `description`='$owndbdescription', `category`='$category', `wsbc`='$wsbarcode' , `wsdescription`='$wsdescription'	WHERE `id`='$id'");

						if ($update) {	
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','barcode','تم تحديث باركود رقم $logbc بوصف $logbcdesc وتصنيف $logcat وباركود جمله $logwsbc ووصف باركود جمله $logwsbcdesc إلى باركود رقم $productbarcode بوصف $owndbdescription وتصنيف $category وباركود جمله $wsbarcode ووصف باركود جمله $wsdescription','$time' )");
							$responseArray = array('id' => 'success', 'message' => 'تم التحديث بنجاح');
							// if ($rs=='yes'){$responseArray['rs']='yes'; $responseArray['retailid'] = $retailid; } else { $responseArray['rs'] = 'no'; } 
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
							$wsbc = $barcodeinfo['wsbc'];
							$wsdescription = $barcodeinfo['wsdescription'];
							$timeadded = $barcodeinfo['timeadded'];											$timeadded = Time_Passed(date($barcodeinfo['timeadded']),'time');

							// if(isset($wsbc) && !empty($wsbc))	{
								// $childrecord = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE barcode = '$wsbc' ");
								// if(@mysqli_num_rows($childrecord) > 0){
									// $wsbcinfo = mysqli_fetch_array($childrecord, MYSQLI_ASSOC);
									// $wsbc = $wsbcinfo['barcode'];
									// $childdescription = $wsbcinfo['description'];	
								// } 
							// } else {
								// $wsbc = '';
								// $childdescription = '';
							// }
							$barcodedata = array('id' => $id,'description' => $description,'barcode' => $barcode,'category' => $category,'wsdescription' => $wsdescription,'wsbc' => $wsbc );	
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
							$wsbc = $barcodeinfo['wsbc'];
							$wsdescription = $barcodeinfo['wsdescription'];
							$timeadded = $barcodeinfo['timeadded'];								
							$timeadded = Time_Passed(date($barcodeinfo['timeadded']),'time');

						${'barcodeinfo'.$z} = array('id' => $id,'description' => $description,'barcode' => $barcode,'category' => $category,'wsdescription' => $wsdescription,'wsbc' => $wsbc,'timeadded' => $timeadded );	
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