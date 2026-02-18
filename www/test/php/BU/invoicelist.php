<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		//print_r($_POST);	
if($_POST){
	$time = time();			$rs = '';
// DELETE BARCODE SECTION ////////////////////////////////////////////////////////////////////////////////////////////
	/*
	if(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'delete' )   {
		if(isset($_POST['id']) && !empty($_POST['id']) )   {
			$id = mysqli_real_escape_string($link, $_POST['id']);
			$delete = mysqli_query($link,"DELETE FROM ownbcdb WHERE id = $id ");
			if ($delete) {	
				$responseArray = array('id' => 'success', 'message' => 'تم حذف الباركود بنجاح');
			} else {
				$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات($delete');
			}
		} else {
			$responseArray = array('id' => 'danger', 'message' => 'لا توجد بيانات');
		}

// ADD BARCODE SECTION ////////////////////////////////////////////////////////////////////////////////////////////
	} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'add' )   {
		if (!isset($_POST['owndbdescription']) || empty($_POST['owndbdescription']) )   {
			$responseArray = array('id' => 'danger', 'message' => 'لايوجد وصف للمنتج');
		} elseif (!isset($_POST['productbarcode']) || empty($_POST['productbarcode']) )   {
			$responseArray = array('id' => 'danger', 'message' => 'لايوجد رقم للباركود');
		} else {
			$owndbdescription = mysqli_real_escape_string($link, $_POST['owndbdescription']);
			$productbarcode = mysqli_real_escape_string($link, $_POST['productbarcode']);
			$wsdescription = mysqli_real_escape_string($link, $_POST['wsdescription']);
			$wsbarcode = mysqli_real_escape_string($link, $_POST['wsbarcode']);
			
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
					$finder = mysqli_query($link,"SELECT * FROM `ownbcdb` ORDER BY `id` DESC");
					if(@mysqli_num_rows($finder) > 0){ while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ $id = $idfinder['id']; break;} }

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
		} else {
			$id = mysqli_real_escape_string($link, $_POST['id']);
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
					$responseArray = array('id' => 'success', 'message' => 'تم التحديث بنجاح');
					// if ($rs=='yes'){$responseArray['rs']='yes'; $responseArray['retailid'] = $retailid; } else { $responseArray['rs'] = 'no'; } 
				} else {
					$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $update');
				}
			}
		}
	} else {
	*/
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
// FILLING invoices ARRAY /////////////////////////////////////////////////////////////////////////////////////////
			$record = mysqli_query($link,"SELECT * FROM `cbills` ");
			$invnum = mysqli_num_rows($record);
			if(@mysqli_num_rows($record) > 0){
				$z = 0;					
				$Totalinvinfos = [];
				while($invinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
					
					$id = $invinfo['id'];
					$cid = $invinfo['cid'];
					
					$customer = mysqli_query($link,"SELECT * FROM `customers` WHERE id = '$cid' ");
					if(@mysqli_num_rows($customer) > 0){									
						while($costinfo = mysqli_fetch_array($customer, MYSQLI_ASSOC)){
							$name = $costinfo['name'];
						}					
					} else {
						$name = '';
					}

					$allitems = $invinfo['allitems'];					$allitems = explode(":",$allitems);
					$bcs = $invinfo['bcs'];								$bcs = explode(",",$bcs);
					
					$itemdescarr = [];				
					for($a=0;$a<count($bcs);$a++){
						$desc = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE barcode = '$bcs[$a]' OR wsbc = '$bcs[$a]'   ");
						if(@mysqli_num_rows($desc) > 0){									
							while($descinfo = mysqli_fetch_array($desc, MYSQLI_ASSOC)){
								$description = $descinfo['description'];		array_push($itemdescarr,$description);
							}					
						}
					}
					$soldas = $invinfo['soldas'];						$soldas = explode(",",$soldas);
					$qtys = $invinfo['qtys'];							$qtys = explode(",",$qtys);
					$ptunitprice = $invinfo['ptunitprice'];				$ptunitprice = explode(",",$ptunitprice);
					$vat = $invinfo['vat'];								$vat = explode(",",$vat);
					$discount = $invinfo['discount'];					$discount = explode(",",$discount);
					$itemprice = $invinfo['itemprice'];					$itemprice = explode(",",$itemprice);
					$totalvat = $invinfo['totalvat'];					$totalvat = explode(",",$totalvat);
					$totaldiscount = $invinfo['totaldiscount'];			$totaldiscount = explode(",",$totaldiscount);
					$totalprice = $invinfo['totalprice'];				$totalprice = explode(",",$totalprice);
					$ptype = $invinfo['ptype'];							$ptype = explode(",",$ptype);
					$cashier = $invinfo['cashier'];						$cashier = explode(",",$cashier);
					
					$timeadded = $invinfo['timeadded'];								
					$timeadded = Time_Passed(date($invinfo['timeadded']),'time');
					
					if ( !empty($invinfo['timeadded']) ) {
						$timeedited = Time_Passed(date($invinfo['timeadded']),'time');
					} else {
						$timeedited = '';
					}

				${'invinfo'.$z} = array('id' => $id,'name' => $name,'allitems' => $allitems,'bcs' => $bcs,'itemdesc' => $itemdescarr,'soldas' => $soldas,'qtys' => $qtys,'ptunitprice' => $ptunitprice,'vat' => $vat,'discount' => $discount,'itemprice' => $itemprice,'totalvat' => $totalvat,'totaldiscount' => $totaldiscount,'totalprice' => $totalprice,'ptype' => $ptype,'cashier' => $cashier,'timeadded' => $timeadded,'timeedited' => $timeedited );	
				$z++;
				}
			} 
			$responseArray = array('id' => 'success', 'invnum' => $invnum,);
		}
	//}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($invnum) && $invnum > 0 ){
		for($a=0;$a<$invnum;$a++){
			array_push($Totalinvinfos,${'invinfo'.$a}); 
		}	
		array_push($responseArray,$Totalinvinfos); 
	}
	// if ( isset($catnum) && $catnum > 0 ){
		// array_push($responseArray,$catarray); 	
	// }
	
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
	
} else {    echo $responseArray['message'];		}		// else just display the message
?>