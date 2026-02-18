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
		// DELETE customer SECTION ////////////////////////////////////////////////////////////////////////////////////////////
			if(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'delete' )   {
				if(isset($_POST['id']) && !empty($_POST['id']) )   {
					$id = mysqli_real_escape_string($link, $_POST['id']);
						// for logging purposes
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `customers` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logname = $logrecord['name'];
						$logmobile = $logrecord['mobile'];
						$logbalance = $logrecord['balance'];	
						$logtransactions = $logrecord['transactions'];	
						
					$delete = mysqli_query($link,"DELETE FROM customers WHERE id = $id ");
					if ($delete) {	
						$responseArray = array('id' => 'success', 'message' => 'تم حذف العميل بنجاح');
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','customer','تم حذف عميل باسم $logname ورقم جوال $logmobile ورصيد $logbalance وعدد تعاملات $logtransactions ','$time' )");
						
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات($delete');
					}
				} else {
					$responseArray = array('id' => 'danger', 'message' => 'لا توجد بيانات');
				}

		// ADD customer SECTION ////////////////////////////////////////////////////////////////////////////////////////////
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'add' )   {
				if (!isset($_POST['customername']) || empty($_POST['customername']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال اسم العميل');
				} elseif( !empty($_POST['customermobile']) && !is_numeric($_POST['customermobile']) )   {
						$responseArray = array('id' => 'danger', 'message' => 'يجب أن يحتوي رفم الجوال على أرقام فقط');	
				} else {
					$name = mysqli_real_escape_string($link, $_POST['customername']);
					if(isset($_POST['customermobile']) && !empty($_POST['customermobile']))	{
						$mobile = mysqli_real_escape_string($link, $_POST['customermobile']);				
					} else {
						$mobile = '';
					}

					$record = mysqli_query($link,"SELECT * FROM `customers` WHERE name = '$name'   ");
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id' => 'danger', 'message' => 'العميل مضاف مسبقاً');		goto end;	
					} else {
						$randnum = rand(0,9999);
						$pass = str_pad($randnum, 4, '0', STR_PAD_LEFT);

						$ins = mysqli_query($link,"INSERT INTO `customers`( `id`, `name`, `mobile`, `password`, `balance`, `transactions`, `timeadded`, `timeedited` )VALUES( NULL,'$name','$mobile','$pass', 0, 0,'$time', NULL )");
						if ($ins) {
							$finder = mysqli_query($link,"SELECT * FROM `customers` ORDER BY `id` DESC LIMIT 1");
							if(@mysqli_num_rows($finder) > 0){ while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ $id = $idfinder['id']; break;} } else { $id = 1; }
							// retreive user data
							$record = mysqli_query($link,"SELECT * FROM `customers` WHERE id = '$id'   ");
							if(@mysqli_num_rows($record) > 0){
								while($customersinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
									$id = $customersinfo['id'];
									$name = $customersinfo['name'];
									$mobile = $customersinfo['mobile'];
									$password = $customersinfo['password'];
									$balance = $customersinfo['balance'];
									$transactions = $customersinfo['transactions'];
									$timeadded = Time_Passed(date($customersinfo['timeadded']),'time');
									
									if(isset($_POST['timeedited']) && !empty($_POST['timeedited']))	{
										$timeedited = Time_Passed(date($customersinfo['timeedited']),'time');
									} else {
										$timeedited = '';
									}

									$customerdata = array('id' => $id,'name' => $name,'mobile' => $mobile,'password' => $password,'balance' => $balance,'transactions' => $transactions,'timeadded' => $timeadded,'timeedited' => $timeedited );	
								}
							}
							$responseArray = array('id' => 'success', 'message' => 'تمت الإضافه بنجاح', 'customerdata' => $customerdata);
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','customer','تم إضافة عميل باسم $name ورقم جوال $mobile','$time' )");
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات$ins (Add)');
						}
					}
				}
				
		// UPDATE customer SECTION ////////////////////////////////////////////////////////////////////////////////////////////		
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'update' )   {	
				if(!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $id');
				} elseif (!isset($_POST['customername']) || empty($_POST['customername']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال اسم العميل');
				// } elseif (!isset($_POST['customerbalance']) || empty($_POST['customerbalance']) )   {
					// $responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال رصيد العميل');
				// } elseif ( !is_numeric($_POST['customerbalance']) )   {
					// $responseArray = array('id' => 'danger', 'message' => 'يجب أن يحتوي رصيد العميل على أرقام فقط');	
				// } elseif (!isset($_POST['customertransactions']) || empty($_POST['customertransactions']) )   {
					// $responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال عدد تعاملات العميل');
				// } elseif ( !is_numeric($_POST['customertransactions']) )   {
					// $responseArray = array('id' => 'danger', 'message' => 'يجب أن يحتوي عدد تعاملات العميل على أرقام فقط');	
				} elseif( !empty($_POST['customermobile']) && !is_numeric($_POST['customermobile']) )   {
						$responseArray = array('id' => 'danger', 'message' => 'يجب أن يحتوي رفم الجوال على أرقام فقط');	
				} else {
					$id = mysqli_real_escape_string($link, $_POST['id']);
						// for logging purposes
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `customers` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logname = $logrecord['name'];
						$logmobile = $logrecord['mobile'];
						$logbalance = $logrecord['balance'];	
						$logtransactions = $logrecord['transactions'];
						
					$name = mysqli_real_escape_string($link, $_POST['customername']);
					
					if(isset($_POST['customermobile']) && !empty($_POST['customermobile']))	{
						$mobile = mysqli_real_escape_string($link, $_POST['customermobile']);				
					} else {
						$mobile = $logmobile; // Dont Change
					}
					
					$balance = mysqli_real_escape_string($link, $_POST['customerbalance']);
					$transactions = mysqli_real_escape_string($link, $_POST['customertransactions']);

					// Updating Section
					$update = mysqli_query($link,"UPDATE `customers` SET `name`='$name', `mobile`='$mobile', `balance`='$balance', `transactions`='$transactions' , `timeedited`='$time'	WHERE `id`='$id'");

					if ($update) {	
						$responseArray = array('id' => 'success', 'message' => 'تم التحديث بنجاح');
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','customer','تم تحديث بيانات عميل باسم $logname ورقم جوال $logmobile و رصيد $logbalance وعدد تعاملات $logtransactions  إلى اسم $name وجوال $mobile و رصيد $balance  وتعاملات $transactions','$time' )");
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $update');
					}
				}
		// Fetch customer Purchases SECTION /////////////////////////////////////////////////////////////////////////////////		
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'fetch' )   {	
				if(!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $id');
				} else {
					$id = mysqli_real_escape_string($link, $_POST['id']);	
					$record = mysqli_query($link,"SELECT * FROM `cbills` WHERE cid = '$id' ");
					$invnum = mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){
						$z = 0;					
						$Totalinvinfos = [];
						while($invinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							
							$id = $invinfo['id'];
							$allitems = $invinfo['allitems'];					$allitems = explode(":",$allitems);
							$bcs = $invinfo['bcs'];								$bcs = explode(",",$bcs);
							
							$itemdescarr = [];				
							for($a=0;$a<count($bcs);$a++){
								$desc = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE barcode = '$bcs[$a]' OR FIND_IN_SET('$bcs[$a]', wsbc)");
								if(@mysqli_num_rows($desc) > 0){									
									while($descinfo = mysqli_fetch_array($desc, MYSQLI_ASSOC)){
										if ( $bcs[$a] == $descinfo['barcode'] ) {
											$description = $descinfo['description'];		array_push($itemdescarr,$description);
										} else {
											$wsbcs = $descinfo['wsbc'];			$wsbcs = explode(",",$wsbcs);
											$wsdescs = $descinfo['wsdescription'];		$wsdescs = explode(",",$wsdescs);
											for($k=0;$k<count((array)$wsbcs);$k++){
												if ( $bcs[$a] == $wsbcs[$k] ) { array_push($itemdescarr,$wsdescs[$k]);	}
											}
										}
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

						${'invinfo'.$z} = array('id' => $id,'allitems' => $allitems,'bcs' => $bcs,'itemdesc' => $itemdescarr,'soldas' => $soldas,'qtys' => $qtys,'ptunitprice' => $ptunitprice,'vat' => $vat,'discount' => $discount,'itemprice' => $itemprice,'totalvat' => $totalvat,'totaldiscount' => $totaldiscount,'totalprice' => $totalprice,'ptype' => $ptype,'cashier' => $cashier,'timeadded' => $timeadded,'timeedited' => $timeedited );	
						$z++;
						}
					}

					$responseArray = array('id' => 'success', 'message' => 'تم جلب البيانات بنجاح', 'invnum' => $invnum);
				}
		// Customer Pay Debt SECTION /////////////////////////////////////////////////////////////////////////////////		
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'purchaseupdate' )   {	
				if(!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $id');
				} elseif (!isset($_POST['paymenttype']) || empty($_POST['paymenttype']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'يجب اختيار آلية السداد');
				} else {
					$id = mysqli_real_escape_string($link, $_POST['id']);	
					$paymenttype = mysqli_real_escape_string($link, $_POST['paymenttype']);

					// Updating Section
					$update = mysqli_query($link,"UPDATE `cbills` SET `ptype`='$paymenttype', `timeedited`='$time'	WHERE `id`='$id'");

					if ($update) {
					// Update customer stats if any
						// fetch customer id from cbills
						$cidfetch = mysqli_fetch_array(mysqli_query($link," SELECT cid FROM `cbills` WHERE id = '$id' "), MYSQLI_ASSOC);
						$cusid = $cidfetch['cid'];
						$totalprice = $cidfetch['totalprice'];
						// fetch customer data
						$cusrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `customers` WHERE id = '$cid'"), MYSQLI_ASSOC);
						$cusbalance = $cusrecord['balance'];	

						$cusbalance = $cusbalance - $totalprice;
						$update2 = mysqli_query($link,"UPDATE `customers` SET `balance`='$cusbalance' WHERE `id`='$cusid'");

						$responseArray = array('id' => 'success', 'message' => 'تم التحديث بنجاح');
///////////////////
						$record = mysqli_query($link,"SELECT * FROM `cbills` WHERE `id`='$id' ");
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

								$ptype = $invinfo['ptype'];
								$totalprice = $invinfo['totalprice'];

							${'invinfo'.$z} = array('ptype' => $ptype );	
							$z++;
							}
						} 
////////////////////
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','customer','قام العميل $name بسداد مديونية الفاتورة رقم  $id بقيمة  $totalprice ريال','$time' )");
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $update');
					}



				}
			} else {
		// FETCH SPECIFIC customer SECTION ///////////////////////////////////////////////////////////////////////////////////////		
				if(isset($_POST['id']) && !empty($_POST['id']))	{										// for editing
					$id = mysqli_real_escape_string($link, $_POST['id']);	
					$record = mysqli_query($link,"SELECT * FROM `customers` WHERE id = '$id'   ");
				} else {																				// for all retreival
					$record = mysqli_query($link,"SELECT * FROM `customers` ");
				}
				
				$customernum = mysqli_num_rows($record);
				if(@mysqli_num_rows($record) > 0){	
					$z = 0;					
					$Totalcustomerinfos = [];	
					while($customersinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
						$id = $customersinfo['id'];
						$name = $customersinfo['name'];
						$mobile = $customersinfo['mobile'];		
						$password = $customersinfo['password'];
						$balance = $customersinfo['balance'];
						$transactions = $customersinfo['transactions'];
						$timeadded = Time_Passed(date($customersinfo['timeadded']),'time');
						
						if(isset($_POST['timeedited']) && !empty($_POST['timeedited']))	{
							$timeedited = Time_Passed(date($customersinfo['timeedited']),'time');
						} else {
							$timeedited = '';
						}

						${'customerinfo'.$z} = array('id' => $id,'name' => $name,'mobile' => $mobile,'password' => $password,'balance' => $balance,'transactions' => $transactions,'timeadded' => $timeadded,'timeedited' => $timeedited );	
						$z++;
					}
				} else {
					$responseArray = array('id' => 'danger', 'message' => 'no customer exists');
				}
					 
				$responseArray = array('id' => 'success', 'customernum' => $customernum);
				
			}
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($customernum) && $customernum > 0 ){
		for($a=0;$a<$customernum;$a++){
			array_push($Totalcustomerinfos,${'customerinfo'.$a}); 
		}	
		array_push($responseArray,$Totalcustomerinfos); 
	}

	if ( isset($invnum) && $invnum > 0 ){
		for($a=0;$a<$invnum;$a++){
			array_push($Totalinvinfos,${'invinfo'.$a}); 
		}	
		array_push($responseArray,$Totalinvinfos); 
	}

	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
	
} else {    echo $responseArray['message'];		}		// else just display the message
?>