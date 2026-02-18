<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		
session_start();
//print_r($_POST);	
if($_POST){
	$time = time();
	$msg = '';
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
			
			/// DELETE PURCHASE SECTION
			if(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'delete' )   {
				if(!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $id');
				} else {
					$id = mysqli_real_escape_string($link, $_POST['id']);
						// for logging purposes
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `inventory` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logbc = $logrecord['bc'];
							$bcfetcher = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `ownbcdb` WHERE barcode = '$logbc' "), MYSQLI_ASSOC);
							$logdesc = $bcfetcher['description'];
						$logvatablity = $logrecord['vatablity'];
						$logqty = $logrecord['qty'];	
						$logcost = $logrecord['cost'];	
						$logsaletype = $logrecord['saletype'];	
						$logprice = $logrecord['price'];	
						$logdisc = $logrecord['disc'];	
						$logwsbc = $logrecord['wsbc'];	
						$logitemsperbox = $logrecord['itemsperbox'];	
						$logwsprice = $logrecord['wsprice'];
						$logday = $logrecord['day'];
						$logmonth = $logrecord['month'];
						$logyear = $logrecord['year'];
						$lognumber = $logrecord['number'];
						$logletter = $logrecord['letter'];
						
					$delete = mysqli_query($link,"DELETE FROM inventory WHERE id = '$id' ");
					if (isset($delete)) {	
						$responseArray = array('id' => 'success', 'message' => 'تم حذف المخزون بنجاح');
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','تم حذف مخزون باركود رقم $logbc ووصف $logdesc حالة خضوع ضريبي $logvatablity بكمية $logqty وبتكلفه فرديه $logcost يباع ك $logsaletype بسعر $logprice وبتخفيض $logdisc وباركود جمله $logwsbc وعدد تفريد للجمله $logitemsperbox وسعر بيع جمله $logwsprice وتاريخ انتهاء $logday-$logmonth-$logyear ومكان تخزين $logletter-$lognumber','$time' )");
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات isset($delete)');
					}
				}
			// UPDATE INVENTORY SECTION ////////////////////////////////////////////////////////////////////////////////////////////		
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'update' )   {
				if(!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $id');
				} elseif(!isset($_POST['name']) || empty($_POST['name']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لا يوجد اسم للمنتج');
				} elseif(!isset($_POST['bc']) || empty($_POST['bc']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال باركود للمنتج');
				} elseif ( !is_numeric($_POST['bc'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'رقم الباركود يجب أن يحتوي على أرقام فقط');
				} elseif(!isset($_POST['vatablity']) || empty($_POST['vatablity']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بتحديد ما اذا كان المنتج خاضع للضريبه');
				} elseif(!isset($_POST['quantity']) || empty($_POST['quantity']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال كمية المنتج');
				} elseif ( !is_numeric($_POST['quantity'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'الكميه يجب أن تحتوي على أرقام فقط');
				} elseif(!isset($_POST['cost']) || empty($_POST['cost']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال التكلفة الفرديه للمنتج');
				} elseif ( !is_numeric($_POST['cost'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'التكلفه الفرديه يجب أن تحتوي على أرقام فقط');
				} else {
					$id = mysqli_real_escape_string($link, $_POST['id']);
					$name = mysqli_real_escape_string($link, $_POST['name']);
					$bc = mysqli_real_escape_string($link, $_POST['bc']);
					$vatablity = mysqli_real_escape_string($link, $_POST['vatablity']);
					$qty = mysqli_real_escape_string($link, $_POST['quantity']);
					$cost = mysqli_real_escape_string($link, $_POST['cost']);

					$record = mysqli_query($link,"SELECT * FROM `inventory` WHERE bc = '$bc' AND id != '$id'  ");
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id' => 'danger', 'message' => 'الباركود مضاف لمنتج اخر في المخزون');		goto end;	
					}

					$record = mysqli_query($link,"SELECT * FROM `inventory` WHERE `bc`='$bc' ");
					if(@mysqli_num_rows($record) < 1){
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على المخزون');	goto end;
					} else {						
						// for logging purposes
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `inventory` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logbc = $logrecord['bc'];
							$bcfetcher = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `ownbcdb` WHERE barcode = '$logbc' "), MYSQLI_ASSOC);
							$logdesc = $bcfetcher['description'];
						$logvatablity = $logrecord['vatablity'];
						$logqty = $logrecord['qty'];	
						$logminqty = $logrecord['minqty'];	
						$logcost = $logrecord['cost'];	
						$logsaletype = $logrecord['saletype'];	
						$logprice = $logrecord['price'];	
						$logdisc = $logrecord['disc'];	
						$logwsbc = $logrecord['wsbc'];	
						$logitemsperbox = $logrecord['itemsperbox'];	
						$logwsprice = $logrecord['wsprice'];
						$logday = $logrecord['day'];
						$logmonth = $logrecord['month'];
						$logyear = $logrecord['year'];
						$lognumber = $logrecord['number'];
						$logletter = $logrecord['letter'];

						if(isset($_POST['retail']) && !empty($_POST['retail']))   {
							if(!isset($_POST['price']) || empty($_POST['price']) )   {
								$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال سعر البيع'); 				goto end;
							} elseif ( !is_numeric($_POST['price'])  )   {
								$responseArray = array('id' => 'danger', 'message' => 'سعر البيع يجب أن يحتوي على أرقام فقط');		goto end;
							} elseif( !empty($_POST['discount']) && !is_numeric($_POST['discount']) )   {
								$responseArray = array('id' => 'danger', 'message' => 'يجب أن يحتوي قيمة خصم التفريد على أرقام فقط');	goto end;
							}
							
							$saletype = mysqli_real_escape_string($link, $_POST['retail']);
							$price = mysqli_real_escape_string($link, $_POST['price']);
							if ( isset($_POST['discount']) ) {
								$disc = mysqli_real_escape_string($link, $_POST['discount']);
							} else {
								$disc = '';
							}
						} else {
							// Product is not sold as Retail, thus nullifing variable for INSERT 
							$price = '';
							$disc = '';
						}

						if(isset($_POST['wholesale']) && !empty($_POST['wholesale']))   {
							$saletype = mysqli_real_escape_string($link, $_POST['wholesale']);
							$wsbcs = array_slice($_POST['wsbarcode'],0, -1);		// start from 0 and exclude last one (empty)
							$wsdescs = array_slice($_POST['wsdescription'],0, -1);
							$wsprices = array_slice($_POST['wsprice'],0, -1);
							$wsipb = [];  
							
							$wsbcs = array_filter($wsbcs);
							for($i=0;$i<count((array)$wsbcs);$i++){
							// for($i=0;$i<count($wsbcs);$i++){
								if ( !empty( $wsbcs[$i] ) && !is_numeric( $wsbcs[$i]) )   {
									$responseArray = array('id' => 'danger', 'message' => 'رقم باركود الجمله يجب أن يحتوي على أرقام فقط'); goto end;
								}
								if ( !empty( $wsprices[$i] ) && !is_numeric( $wsprices[$i]) )   {
									$responseArray = array('id' => 'danger', 'message' => 'عدد التفريد في الجمله يجب أن يحتوي على أرقام فقط'); goto end;
								}
								if ( empty( $wsbcs[$i] ) || empty( $wsprices[$i] ) || empty( $wsdescs[$i] ) )   {
									if ( empty( $wsbcs[$i] ) ) {
										$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال رقم لباركود الجمله'); goto end;
									}
									if ( empty( $wsdescs[$i] ) ) {
										$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال وصف لباركود الجمله '.$wsbcs[$i]); goto end;
									}
									if ( empty( $wsprices[$i] ) ) {
										$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال  كلفة الجمله '.$wsbcs[$i]); goto end;
									}
								}

								$record = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE barcode = '$bc' AND FIND_IN_SET('$wsbcs[$i]', wsbc) ");
								if(@mysqli_num_rows($record) == 0){
									$responseArray = array('id'=>'danger','message'=>'باركود الجمله '.$wsbcs[$i].' غير مضاف لباركود التفريد');	goto end;	
								} else {
									while($ipbfetcher = mysqli_fetch_array($record, MYSQLI_ASSOC)){
										$wsbarcodes = $ipbfetcher['wsbc'];				$wsbarcodes = explode(",",$wsbarcodes);
										$wsitemsperbox = $ipbfetcher['wsitemsperbox'];	$wsitemsperbox = explode(",",$wsitemsperbox);
										for($k=0;$k<count((array)$wsbarcodes);$k++){
											if ( $wsbcs[$i] == $wsbarcodes[$k] ) { array_push($wsipb,$wsitemsperbox[$k]);	}
										}
									}
								}
								$record = mysqli_query($link,"SELECT barcode FROM `ownbcdb` WHERE barcode = '$wsbcs[$i]'   ");
								if(@mysqli_num_rows($record) > 0){
									$responseArray = array('id'=>'danger','message'=>'باركود الجمله مضاف مسبقاً كتفريد '.$wsbcs[$i]); goto end;	
								}
								
							}
							if ( count((array)$wsbcs) > 0 ) {
								$wsbc = implode(",",$wsbcs);
								$wsdesc = implode(",",$wsdescs);
								$wsprice = implode(",",$wsprices);
								$wsipb = implode(",",$wsipb);
							} else {
								$wsbc = '';
								$wsdesc = '';
								$wsprice = '';
								$wsipb = '';
							}

							
						} else {
							// Product is not sold as wholesale, thus nullifing variable for INSERT 
							$wsbc = '';
							$wsdesc = '';
							$wsprice = '';
							$wsipb = '';
						}

						if(isset($_POST['wholesale']) && !empty($_POST['wholesale']) && isset($_POST['retail']) && !empty($_POST['retail']) )   {	
							$saletype = 'Both';	
						}

						if(isset($_POST['minqty']) && !empty($_POST['minqty']))   {
							$minqty = mysqli_real_escape_string($link, $_POST['minqty']);
						} else { $minqty = '10'; }
						
						if(isset($_POST['day']) && !empty($_POST['day']))   {
							$day = mysqli_real_escape_string($link, $_POST['day']);
						} else { $day = ''; }
						if(isset($_POST['month']) && !empty($_POST['month']))   {
							$month = mysqli_real_escape_string($link, $_POST['month']);
						} else { $month = ''; }
						if(isset($_POST['year']) && !empty($_POST['year']))   {
							$year = mysqli_real_escape_string($link, $_POST['year']);
						} else { $year = ''; }
						if(isset($_POST['number']) && !empty($_POST['number']))   {
							$number = mysqli_real_escape_string($link, $_POST['number']);
						} else { $number = ''; }
						if(isset($_POST['letter']) && !empty($_POST['letter']))   {
							$letter = mysqli_real_escape_string($link, $_POST['letter']);
						} else { $letter = ''; }

						// Make sure expiry day, month and year all exists 
						if( !empty($_POST['day']))   {
							if( empty($_POST['month']) || empty($_POST['year']) )   { 
								$responseArray = array('id' => 'danger', 'message' => 'تاريخ الانتهاء غير مكتمل');	goto end;
							}
						}
						if( !empty($_POST['month']))   {
							if( empty($_POST['day']) || empty($_POST['year']) )   { 
								$responseArray = array('id' => 'danger', 'message' => 'تاريخ الانتهاء غير مكتمل');	goto end;
							}
						}
						if( !empty($_POST['year']))   {
							if( empty($_POST['month']) || empty($_POST['day']) )   { 
								$responseArray = array('id' => 'danger', 'message' => 'تاريخ الانتهاء غير مكتمل');	goto end;
							}
						}
						
						// Make sure display code is fully present
						if( !empty($_POST['letter']))   {
							if( empty($_POST['number'])  )   { 
								$responseArray = array('id' => 'danger', 'message' => 'مكان العرض غير مكتمل');	goto end;
							}
						}
						if( !empty($_POST['number']))   {
							if( empty($_POST['letter'])  )   { 
								$responseArray = array('id' => 'danger', 'message' => 'مكان العرض غير مكتمل');	goto end;
							}
						}
						
						// Update Inventory
						$update2 = mysqli_query($link,"UPDATE `inventory` SET 
						`bc`='$bc',
						`vatablity`='$vatablity',
						`qty`='$qty',
						`minqty`='$minqty',
						`cost`='$cost',
						`saletype`='$saletype',
						`price`='$price',
						`disc`='$disc',
						`wsbc`='$wsbc',
						`itemsperbox`='$wsipb',
						`wsprice`='$wsprice',
						`day`='$day',
						`month`='$month',
						`year`='$year',
						`number`='$number',
						`letter`='$letter',
						`timeedited`='$time'
						WHERE `id`='$id'");
						
						if ($update2) {
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','تم تعديل المخزون مباشرة للباركود $logbc بوصف $logdesc وحالة خضوع للضريبه $logvatablity و كميه $logqty وأدنى كميه $logminqty وبتكلفه $logcost وطريقة بيع $logsaletype وسعر بيع تفريد $logprice وخصم $logdisc وباركود جمله $logwsbc وعدد وحدات تفريد للجمله $logitemsperbox وسعر بيع جمله $logwsprice وتاريخ انتهاء $logday-$logmonth-$logyear ومكان تخزين $logletter-$lognumber إلى باركود $bc بوصف $name وحالة خضوع للضريبه $vatablity و كميه $qty وأدنى كميه $minqty وبتكلفه $cost وطريقة بيع $saletype وسعر بيع تفريد $price وخصم $disc وباركود جمله $wsbc وعدد وحدات تفريد للجمله $wsipb وسعر بيع جمله $wsprice وتاريخ انتهاء $day-$month-$year ومكان تخزين $letter-$number','$time' )");
///////////////////////////////////////////
							//Fetch Inventory to update Html table
							$record = mysqli_query($link,"SELECT * FROM `inventory` WHERE `id` = $id ");
							
							$inventorynum = mysqli_num_rows($record);
							if(@mysqli_num_rows($record) > 0){									
								$z = 0;				
								$responseArray = [];			
								$Totalinventorys = [];	
								
								while($inventory = mysqli_fetch_array($record, MYSQLI_ASSOC)){
									$id = $inventory['id'];
									$bc = $inventory['bc'];
										$record2 = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE `barcode` = $bc ");
										if(@mysqli_num_rows($record2) > 0){
											while($barcode = mysqli_fetch_array($record2, MYSQLI_ASSOC)){
												$description = $barcode['description'];
												$ipb = '';
											}
										}
									$vatablity = $inventory['vatablity'];
									$qty = $inventory['qty'];
									$minqty = $inventory['minqty'];
									$cost = $inventory['cost'];
									$saletype = $inventory['saletype'];
									$price = $inventory['price'];
									$disc  = $inventory['disc'];
									$iwsbc = $inventory['wsbc'];							$iwsbc = explode(",",$iwsbc);
									$wsdescription = []; 
										for($i=0;$i<count((array)$iwsbc);$i++){
											$record3 = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE FIND_IN_SET('$iwsbc[$i]', wsbc) ");
											if(@mysqli_num_rows($record3) > 0){
												while($barcode = mysqli_fetch_array($record3, MYSQLI_ASSOC)){
													$wsbc = $barcode['wsbc'];				$wsbc = explode(",",$wsbc);
													$wsdesc = $barcode['wsdescription'];	$wsdesc = explode(",",$wsdesc);
													
													for($k=0;$k<count((array)$wsbc);$k++){
														if ( $wsbc[$k] == $iwsbc[$i] ) { 
															$wsdescription[] = $wsdesc[$k];
														}
													}
												}
											}							
										}
										// Barcode name fetcher Section
										$wsdescription = array_filter($wsdescription); // remove empty values
										$wsdescription = array_unique($wsdescription); // remove duplicate values
										$wsdescription = array_values($wsdescription); // renumber array keys 

									$itemsperbox = $inventory['itemsperbox'];				$itemsperbox = explode(",",$itemsperbox);
									$wsprice = $inventory['wsprice'];						$wsprice = explode(",",$wsprice);
									$day = $inventory['day'];
									$month = $inventory['month'];
									$year = $inventory['year'];
									$number = $inventory['number'];
									$letter = $inventory['letter'];				
									$timeedited = $inventory['timeedited'];				
									$timeadded = Time_Passed(date($inventory['timeadded']),'time');

									if ( !is_null($timeedited) ) { 
										$timeedited = Time_Passed(date($inventory['timeedited']),'time'); 
									} else {	$timeedited = ''; }	
								
									${'inventory'.$z} = array('id' => $id,'bc' => $bc,'description' => $description,'vatablity' => $vatablity,'qty' => $qty,'minqty' => $minqty,'cost' => $cost,'saletype' => $saletype,'price' => $price,'disc' => $disc,'wsbc' => $iwsbc,'wsdesc' => $wsdescription,'itemsperbox' => $itemsperbox,'wsprice' => $wsprice,'day' => $day,'month' => $month,'year' => $year,'number' => $number,'letter' => $letter,'timeadded' => $timeadded,'timeedited' => $timeedited );	
									$z++;
								}
								
								$responseArray = array('id' => 'success', 'message' => 'تم تحديث  المخزون بنجاح'.$msg, 'inventorynum' => $inventorynum  );
							}
//////////////////////////////////////////							
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في إدخال البيانات $update2');
						}
					}
				}
			// END OF UPDATE INVENTORY SECTION /////////////////////////////////////////////////////////
			/// FETCH INVENTORY DATA	
			} else {
				if(isset($_POST['id']) && !empty($_POST['id']))	{	
					$id = mysqli_real_escape_string($link, $_POST['id']);	
					$record = mysqli_query($link,"SELECT * FROM `inventory` WHERE id = $id   ");
				} else {
					$record = mysqli_query($link,"SELECT * FROM `inventory` ");
				}
				
				$inventorynum = mysqli_num_rows($record);
				if(@mysqli_num_rows($record) > 0){									
					$z = 0;				
					$responseArray = [];			
					// $bcArray1 = [];				// store BC to fetch names
					// $bcArray2 = [];				// store BC to fetch names
					$Totalinventorys = [];	
					
					while($inventory = mysqli_fetch_array($record, MYSQLI_ASSOC)){
						$id = $inventory['id'];
						$bc = $inventory['bc'];
							$record2 = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE `barcode` = $bc ");
							if(@mysqli_num_rows($record2) > 0){
								while($barcode = mysqli_fetch_array($record2, MYSQLI_ASSOC)){
									$description = $barcode['description'];
									$wsbc = $barcode['wsbc'];				$wsbc = explode(",",$wsbc);		// to be used next iteration
									$wsdesc = $barcode['wsdescription'];	$wsdesc = explode(",",$wsdesc); // to be used next iteration

									$ipb = '';
								}
							}
						$vatablity = $inventory['vatablity'];
						$qty = $inventory['qty'];
						$minqty = $inventory['minqty'];
						$cost = $inventory['cost'];
						$saletype = $inventory['saletype'];
						$price = $inventory['price'];
						$disc  = $inventory['disc'];
						$iwsbc = $inventory['wsbc'];							$iwsbc = explode(",",$iwsbc);
						$wsdescription = []; 
							for($i=0;$i<count((array)$iwsbc);$i++){
								for($k=0;$k<count((array)$wsbc);$k++){
									if ( $wsbc[$k] == $iwsbc[$i] ) { 
										$wsdescription[] = $wsdesc[$k];
									}
								}
							}
							// Barcode name fetcher Section
							$wsdescription = array_filter($wsdescription); // remove empty values
							$wsdescription = array_unique($wsdescription); // remove duplicate values
							$wsdescription = array_values($wsdescription); // renumber array keys 

						$itemsperbox = $inventory['itemsperbox'];				$itemsperbox = explode(",",$itemsperbox);
						$wsprice = $inventory['wsprice'];						$wsprice = explode(",",$wsprice);
						$day = $inventory['day'];
						$month = $inventory['month'];
						$year = $inventory['year'];
						$number = $inventory['number'];
						$letter = $inventory['letter'];				
						$timeedited = $inventory['timeedited'];				
						$timeadded = Time_Passed(date($inventory['timeadded']),'time');

						if ( !is_null($timeedited) ) { 
							$timeedited = Time_Passed(date($inventory['timeedited']),'time'); 
						} else {	$timeedited = ''; }	
					
						// store BC to fetch names
						// $bcArray1[] = $bc;
						// $bcArray1[] = $wsbc;

						${'inventory'.$z} = array('id' => $id,'bc' => $bc,'description' => $description,'vatablity' => $vatablity,'qty' => $qty,'minqty' => $minqty,'cost' => $cost,'saletype' => $saletype,'price' => $price,'disc' => $disc,'wsbc' => $iwsbc,'wsdesc' => $wsdescription,'itemsperbox' => $itemsperbox,'wsprice' => $wsprice,'day' => $day,'month' => $month,'year' => $year,'number' => $number,'letter' => $letter,'timeadded' => $timeadded,'timeedited' => $timeedited,'bcwsbc' => $wsbc,'bcwsdesc' => $wsdesc );	
						$z++;
					}
					
					$responseArray = array('id' => 'success', 'message' => 'تم التحديث بنجاح', 'inventorynum' => $inventorynum);
					
					
				} else { 																				
					$responseArray = array('id' => 'danger', 'inventorynum' => $inventorynum);
				}
			}
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $POST');
}

end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	
	if(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'delete' )   {
	// } elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'update' )   {
	} else {
	
		// if ( isset($bcArray1) && count($bcArray1) > 0 ){	
			// for($a=0;$a<count($bcArray1);$a++){ 
				// needed in case some barcode doesnt exists in data bases count will be different 
				// if ( isset(${'barcodes'.$a}) ) {
					// array_push($bcArray2,${'barcodes'.$a}); 
				// }
			// }
			// $bcnum = count($bcArray2);
			// $responseArray["bcnum"] = $bcnum;
			// array_push($responseArray,$bcArray2); 
		// }
		
		if ( isset($inventorynum) && $inventorynum > 0 ){	
			for($a=0;$a<$inventorynum;$a++){ 
				array_push($Totalinventorys,${'inventory'.$a}); 
			}	
			array_push($responseArray,$Totalinventorys); 
		}
	}
	// array_push($responseArray,$bcArray1);
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message

?>