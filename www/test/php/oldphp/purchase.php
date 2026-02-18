<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		
session_start();
//print_r($_POST);	
$logtext = '';				// used for delete and add
$newinvlogtext = '';		
$updinvlogtext = '';		// used for delete and add
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
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `purchases` WHERE id = '$id' "), MYSQLI_ASSOC);
						$bc = $logrecord['bc'];
							$bcfetcher = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `ownbcdb` WHERE barcode = '$bc' OR FIND_IN_SET('$bc', wsbc) "), MYSQLI_ASSOC);
							$rbc = $bcfetcher['barcode'];	// for inventory use
							$logrdesc = $bcfetcher['description'];
							$logpas = $logrecord['pas'];
							$logvatablity	 = $logrecord['vatablity'];
								if ( $logvatablity == 'yes' ) { $logvatablitycode = ''; } else { $logvatablitycode = 'غير'; }
							$logqty = $logrecord['qty'];
							$logcost = $logrecord['cost'];	
							$logvat = $logrecord['vat'];	
							$logtotalcost = $logrecord['totalcost'];	
							$logsaletype = $logrecord['saletype'];	
							$logsupplier = $logrecord['supplier'];	
							$logbillid = $logrecord['billid'];	

							if ( $bc !== $bcfetcher['barcode'] ) { 	
								$wsbc = $bcfetcher['wsbc'];					  	$wsbcs = explode(",",$wsbc);
								$wsdesc = $bcfetcher['wsdescription'];		  	$wsdescs = explode(",",$wsdesc);
								$wsipb = $bcfetcher['wsitemsperbox'];			$wsipbs = explode(",",$wsipb);

								if ( count((array)$wsbcs) > 0 ) {
									for($i=0;$i<count((array)$wsbcs);$i++){
										if ( $bc == $wsbcs[$i] ) { 	
											$logwsdesc = $wsdescs[$i];
											$logwsipb = $wsipbs[$i];
											$logtext .= ' تم حذف مشتريات جمله للباركود رقم '.$bc.' بوصف '.$logwsdesc;
											$updinvlogtext .= ' تم حذف مشتريات جمله للباركود رقم '.$bc.' بوصف '.$logwsdesc;
											$updinvlogtext .= ' وبالتالي تم تحديث المخزون للباركود '.$rbc.' بوصف '.$logrdesc;
										}
									}
								}
							} else {
								$logtext .= ' تم حذف مشتريات تفريد للباركود رقم '.$rbc.' بوصف '.$logrdesc;
								$updinvlogtext .= ' تم حذف مشتريات تفريد للباركود رقم '.$rbc.' بوصف '.$logrdesc;
								$updinvlogtext .= ' وبالتالي تم تحديث المخزون لنفس المنتج ';

							}

							$logtext .= ' بكمية '.$logqty.' بتكلفه فرديه '.$logcost.' وضريبه قيمتها '.$logvat.' ';
							$logtext .= $logvatablitycode.' خاضع للضريبه وبتكلفه أجماليه قدرها '.$logtotalcost;
							if ( !empty($logsupplier) ) {	$logtext .= ' من المورد '.$logsupplier; }
							if ( !empty($logbillid) ) {	$logtext .= ' بفاتوره رقم '.$logbillid; }

					if(isset($_POST['restore']) && !empty($_POST['restore']) )   {	//$msg = $msg.'</br> restore is set and has value';
					// FETCH PURCHASE DATA
						$record1 = mysqli_query($link,"SELECT * FROM `purchases` WHERE `id`='$id' ");
						if(@mysqli_num_rows($record1) < 1){
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم No Purchase Found');
						} else {
							//$msg = $msg.'</br> purchase found';
							while($purchaseinfo = mysqli_fetch_array($record1, MYSQLI_ASSOC)){	
								$bc = $purchaseinfo['bc'];	
								$qty = $purchaseinfo['qty'];		if ( $logpas == 'ws' ) {	$qty = $qty * $logwsipb;	}
								$cost = $purchaseinfo['cost'];	
								$totalcost = $purchaseinfo['totalcost'];	
							}
							
							// FETCH INVENTORY DATA
							$record2 = mysqli_query($link,"SELECT * FROM `inventory` WHERE `bc`='$rbc' ");
							if(@mysqli_num_rows($record2) < 1){	
								$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم No inventory Found');
							} else {
								//$msg = $msg.'</br> inventory found';
								while($inventoryinfo = mysqli_fetch_array($record2, MYSQLI_ASSOC)){	
									$bc = $inventoryinfo['bc'];	
									$qty1 = $inventoryinfo['qty'];	
									$cost1 = $inventoryinfo['cost'];	
								}
								
								$qty0 = $qty1 - $qty;
								if ( $qty0 > 0 ) {	// To Avoid dividimg by zero
									$cost0 = ( ( $cost1 * $qty1 ) - $totalcost ) / $qty0;
								} else {
									$cost0 = 0;
								}
								$updinvlogtext .= ' بتغيير الكميه من '.$qty1.' إلى '.$qty0;
								if ( $cost1 != $cost0 ) {
									$updinvlogtext .= ' وتعديل التكلفه من '.$cost1.' إلى '.$cost0;
								} else {
									$updinvlogtext .= ' بنفس سعر التكلفه '.$cost1;
								}
								
								$update = mysqli_query($link,"UPDATE `inventory` SET 
								`qty`='$qty0',
								`cost`='$cost0'
								WHERE `bc`='$rbc'");

								if ( $update ) {
									//$msg = $msg.'</br> $update is successful';
								} else {
									$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $inventory_update');
								}

							}
						}
					}
					
					$delete = mysqli_query($link,"DELETE FROM purchases WHERE id = '$id' ");
					if (isset($delete)) {	
						$responseArray = array('id' => 'success', 'message' => 'تم حذف المنتج بنجاح');
						// $logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','purchase','تم حذف مشتريات لـ $logdesc باركود رقم $logbc كمية $logqty بتكلفه فرديه $logcost وضريبه $logvat  و حالة خضوع للضريبه $logvatablity واجمالي تكلفه $logtotalcost من المورد $logsupplier  ويباع ك $logsaletype برقم فاتوره $logbillid','$time' )");
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','purchase','$logtext','$time' )");
						if (isset($update) && $update) {	
							// $logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','تم تحديث المخزون عن طريق حذف مشتريات للباركود رقم $rbc بوصف $logdesc وتغيرت الكميه من $qty1 إلى $qty0 وتغيرت التكلفه الفرديه من $cost1 إلى $cost0','$time' )");
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','$updinvlogtext','$time' )");
						} 
					} else {
						// Delete failed thus restore inventory update
						$update2 = mysqli_query($link,"UPDATE `inventory` SET `qty`='$qty1', `cost`='$cost1' WHERE `bc`='$rbc'");
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات isset($delete)');
					}
				}
			} else {		// Insert new Purchase
				if(!isset($_POST['name']) || empty($_POST['name']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لا يوجد اسم للمنتج');
				} elseif ( !isset($_POST['bc']) || empty($_POST['bc']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال باركود للمنتج');
				} elseif ( !is_numeric($_POST['bc'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'رقم الباركود يجب أن يحتوي على أرقام فقط');
				} elseif ( !isset($_POST['vatablity']) || empty($_POST['vatablity']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بتحديد ما اذا كان المنتج خاضع للضريبه');
				} elseif ( !isset($_POST['quantity']) || empty($_POST['quantity']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال كمية المنتج');
				} elseif ( !is_numeric($_POST['quantity'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'الكميه يجب أن تحتوي على أرقام فقط');
				} elseif ( !isset($_POST['cost']) || empty($_POST['cost']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال التكلفة الفرديه للمنتج');
				} elseif ( !is_numeric($_POST['cost'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'التكلفه الفرديه يجب أن تحتوي على أرقام فقط');
				} elseif ( !isset($_POST['purchasevat']) || empty($_POST['purchasevat']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال قيمة الضريبه للمنتج');
				} elseif ( !is_numeric($_POST['purchasevat'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'قيمة الضريبه يجب أن تحتوي على أرقام فقط');
				} elseif ( !isset($_POST['totalcost']) || empty($_POST['totalcost']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال التكلفة الاجماليه');
				} elseif ( !is_numeric($_POST['totalcost'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'التكلفه الكليه يجب أن تحتوي على أرقام فقط');
				} elseif ( !isset($_POST['producttype']) || empty($_POST['producttype']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم.  لم يتم تحديد نوع المنتج');
				} elseif ( !empty($_POST['lastbill']) && !is_numeric($_POST['lastbill']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'رقم الفاتوره يجب أن يحتوي على أرقام فقط');
				} else {

					$name = mysqli_real_escape_string($link, $_POST['name']);
					$producttype = mysqli_real_escape_string($link, $_POST['producttype']);
					$bc = mysqli_real_escape_string($link, $_POST['bc']);
					$vatablity = mysqli_real_escape_string($link, $_POST['vatablity']);
						if ( $vatablity == 'yes' ) { $vatablitylog = ''; } else { $vatablitylog = 'غير'; }			/// for log reporting
					$qty = mysqli_real_escape_string($link, $_POST['quantity']);
					$cost = mysqli_real_escape_string($link, $_POST['cost']);
					
					if ( $producttype == 'ws' ) {		// if purchase addded as wholesale
						$pas = 'ws';	
						$purchasedas = 'كجمله';	
						if(!isset($_POST['wsquantity']) || empty($_POST['wsquantity']) )   {
							$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال كمية الجمله');
						} elseif ( !is_numeric($_POST['wsquantity'])  )   {
							$responseArray = array('id' => 'danger', 'message' => 'كمية الجمله يجب أن تحتوي على أرقام فقط');
						} elseif (!isset($_POST['wscost']) || empty($_POST['wscost']) )   {
							$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال كلفة الجمله');
						} elseif ( !is_numeric($_POST['wscost'])  )   {
							$responseArray = array('id' => 'danger', 'message' => 'كلفة الجمله يجب أن تحتوي على أرقام فقط');
						} elseif (!isset($_POST['itemsperbox']) || empty($_POST['itemsperbox']) )   {
							$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال عدد وحدات التفريد بالجمله');
						} elseif ( !is_numeric($_POST['itemsperbox'])  )   {
							$responseArray = array('id' => 'danger', 'message' => 'عدد وحدات التفريد بالجمله يجب أن يحتوي على أرقام فقط');
						} else {
							$wsbc = $bc;
							$wsquantity = mysqli_real_escape_string($link, $_POST['wsquantity']);
							$wscost = mysqli_real_escape_string($link, $_POST['wscost']);
							$itemsperbox = mysqli_real_escape_string($link, $_POST['itemsperbox']);
							
							// Create new variables quantity and cost to switch between reatil and ws
							$quantity = $wsquantity;
							$purchasecost = $wscost;
						}
					}

					if ( $producttype == 'retail' ) { // if purchase addded as retail
						$pas = 'retail';
						$purchasedas = 'كتفريد';	

						// Create new variables quantity and cost to switch between reatil and ws
						$quantity = $qty;
						$purchasecost = $cost;
					} 

					$vat = mysqli_real_escape_string($link, $_POST['purchasevat']);
					$totalcost = mysqli_real_escape_string($link, $_POST['totalcost']);

					$logtext .= 'تم إضافة مشتريات '.$purchasedas.' للباركود رقم '.$bc.' بوصف '.$name.' '.$vatablitylog.' خاضع للضريبه وكمية '.$quantity.' وتكلفه فرديه بقيمة '.$purchasecost.' وتكلفه اجماليه '.$totalcost;
					$newinvlogtext .= $vatablitylog.' خاضع للضريبه وكمية '.$qty.' وتكلفه فرديه بقيمة '.$cost;
					
					
					if(isset($_POST['supplier']) && !empty($_POST['supplier']))   {
						$supplier = mysqli_real_escape_string($link, $_POST['supplier']);
						$logtext .= ' من المورد '.$supplier;
					} else { 	$supplier = '';	 }

					// chech if bill uploaded and exists
					if(isset($_POST['lastbill']) && !empty($_POST['lastbill']))   {
						$billid = mysqli_real_escape_string($link, $_POST['lastbill']);
						// add leading zeroes (6 digits)
						$billidwithleadingzeroes = str_pad($billid, 6, '0', STR_PAD_LEFT);
						$identifierdwithleadingzeroes = str_pad($identifier, 4, '0', STR_PAD_LEFT);
						$bill = "../userdata/".$identifierdwithleadingzeroes."/Pbills".$billidwithleadingzeroes.".*";	
						if ( !glob("../userdata/".$identifierdwithleadingzeroes."/Pbills/".$billidwithleadingzeroes.".*") )  {
							$responseArray = array('id' => 'danger', 'message' => 'لا توجد فاتورة شراء بهذا الرقم'); 				goto end;
						}
						$logtext .= ' برقم فاتورة شراء '.$billid;
					} else {
						$billid = mysqli_real_escape_string($link, $_POST['lastbill']);
					}
					
					// For INVENTORY
					$rbc = mysqli_real_escape_string($link, $_POST['rbc']);
					// Get Description for logging purposes
					$record = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE `barcode`='$rbc' ");
					if(@mysqli_num_rows($record) > 0){
						while($barcodeinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							$logdesc = $barcodeinfo['description'];
						}
					}
					// For logging purposes
					if ( $rbc == $bc ) {	// purchase added as retail
						$newinvlogtext = 'تم إضافة مخزون جديد بإضافة مشتريات للباركود رقم '.$rbc.' ووصف '.$logdesc.$newinvlogtext;
						$updinvlogtext = 'تم تحديث المخزون  بإضافة مشتريات للباركود رقم '.$rbc.' ووصف '.$logdesc;
					} else {				// purchase added as wholesale
						$newinvlogtext = 'تم إضافة مشتريات للباركود رقم '.$bc.' ووصف '.$name.' وبالتالي تم إضافة مخزون جديد للباركود رقم '.$rbc.' ووصف '.$logdesc.$newinvlogtext;
						$updinvlogtext = 'تم إضافة مشتريات للباركود رقم '.$bc.' ووصف '.$name.' وبالتالي تم تحديث المخزون للباركود رقم '.$rbc.' ووصف '.$logdesc;
					}

					// Check if barcode exists in database or not 
					$record = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE `barcode`='$bc' OR FIND_IN_SET('$bc', wsbc) ");
					if(@mysqli_num_rows($record) == 0){
						$responseArray = array('id' => 'danger', 'message' => 'الباركود غير مضاف لقاعدة البيانات الخاصه بك');
					} else {
						if(isset($_POST['retail']) && !empty($_POST['retail']))   {
							if(!isset($_POST['price']) || empty($_POST['price']) )   {
								$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال سعر بيع التفريد'); 				goto end;
							} elseif ( !is_numeric($_POST['price'])  )   {
								$responseArray = array('id' => 'danger', 'message' => 'سعر بيع التفريد يجب أن يحتوي على أرقام فقط');		goto end;
							} elseif( !empty($_POST['discount']) && !is_numeric($_POST['discount']) )   {
								$responseArray = array('id' => 'danger', 'message' => 'يجب أن يحتوي قيمة خصم التفريد على أرقام فقط');	goto end;
							}

								$saletype = mysqli_real_escape_string($link, $_POST['retail']);
								$price = mysqli_real_escape_string($link, $_POST['price']);
								// $logtext .= '<br>وبسعر '.$price;
								$newinvlogtext .= '<br>يباع كتفريد بسعر '.$price;
								if ( isset($_POST['discount']) && !empty($_POST['discount']) ) {
									$disc = mysqli_real_escape_string($link, $_POST['discount']);
									// $logtext .= ' وبخصم اختياري بقيمة '.$disc;
									$newinvlogtext .= ' وبخصم اختياري بقيمة '.$disc;
								} else {
									$disc = '';
								}
						} else {
							// Product is not sold as Retail, thus nullifing variable for INSERT 
							$price = '';
							$disc = '';
						} 

						if(isset($_POST['wholesale']) && !empty($_POST['wholesale']))   {
							if(!isset($_POST['itemsperbox']) || empty($_POST['itemsperbox']) )   {
								$responseArray = array('id' => 'danger', 'message' => 'لم بإ\خال عدد وحدات التفريد لمنتج الجمله');				goto end;
							} elseif ( !is_numeric($_POST['itemsperbox'])  )   {
								$responseArray = array('id' => 'danger', 'message' => 'عدد وحدات التفريد في الجمله يجب أن تحتوي على أرقام فقط');		goto end;
							} elseif(!isset($_POST['wsprice']) || empty($_POST['wsprice']) )   {
								$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال سعر بيع منتج الجمله');					goto end;
							} elseif ( !is_numeric($_POST['wsprice'])  )   {
								$responseArray = array('id' => 'danger', 'message' => 'سعر بيع الجمله يجب أن يحتوي على أرقام فقط');				goto end;
							} else {
								$saletype = mysqli_real_escape_string($link, $_POST['wholesale']);
								$wsprice = mysqli_real_escape_string($link, $_POST['wsprice']);
								// $logtext .= ' يباع جمله للباركود '.$wsbc;
								$newinvlogtext .= ' ويباع جمله للباركود '.$wsbc;
								// $logtext .= '  بسعر '.$wsprice;
								$newinvlogtext .= '  بسعر '.$wsprice;
							}
						} else {
							$wsbc = '';
							$wsprice = '';
							$itemsperbox = '';
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
						
						if( !empty($_POST['day']) && !empty($_POST['month']) && !empty($_POST['year']) )   {
							// $logtext .= '<br>وبتاريخ إنتهاء '.$year.'/'.$month.'/'.$day;
							$newinvlogtext .= '<br>وبتاريخ إنتهاء '.$year.'/'.$month.'/'.$day;
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

						if( !empty($_POST['letter']) && !empty($_POST['number'])  )   {
							// $logtext .= ' وبمكان عرض '.$letter.'-'.$number;
							$newinvlogtext .= ' وبمكان عرض '.$letter.'-'.$number;
						}
					
						// Add record to purchases 
						$ins1 = mysqli_query($link,"INSERT INTO `purchases`( 
						`id`,
						`pas`,
						`bc`,
						`vatablity`,
						`qty`,
						`cost`,
						`vat`,
						`totalcost`,
						`saletype`,
						`supplier`,
						`billid`,
						`timeadded`
						)VALUES( 
						NULL,
						'$pas',
						'$bc',
						'$vatablity',
						'$quantity',
						'$purchasecost',
						'$vat',
						'$totalcost',
						'$saletype',
						'$supplier',
						'$billid',
						'$time'
						)");
						
						
						if ($ins1) {
							// $logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','purchase','تم إضافة مشتريات $purchasedas للباركود رقم $bc ووصف $name خاضع لضريبه $vatablity وكمية $quantity وتكلفه فرديه بقيمة $purchasecost وتكلفه اجماليه $totalcost يباع ك $saletype من المورد $supplier وبرقم فاتوره $billid','$time' )");
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','purchase','$logtext','$time' )");
						// Add or Update record to inventory 
							// Check if BC exists in inventory Table, if yes, update cost and quantities
							$record = mysqli_query($link,"SELECT * FROM `inventory` WHERE `bc`='$rbc' ");
							if(@mysqli_num_rows($record) > 0){
								// Fetch existing data
								while($productinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
									$id0 = $productinfo['id'];
									$bc0 = $productinfo['bc'];
									$vatablity0 = $productinfo['vatablity'];
									$qty0 = $productinfo['qty'];
									$minqty0 = $productinfo['minqty'];
									$cost0 = $productinfo['cost'];
									$saletype0 = $productinfo['saletype'];
									$price0 = $productinfo['price'];
									$disc0 = $productinfo['disc'];
									$wsbc0 = $productinfo['wsbc'];
									$itemsperbox0 = $productinfo['itemsperbox'];
									$wsprice0 = $productinfo['wsprice'];
									$day0 = $productinfo['day'];
									$month0 = $productinfo['month'];
									$year0 = $productinfo['year'];
									$number0 = $productinfo['number'];
									$letter0 = $productinfo['letter'];
									$timeadded0 = $productinfo['timeadded'];
									$timeedited0 = $productinfo['timeedited'];
								}
								
								$vatablity1 = $vatablity;
								// For logging purposes
								if ( $vatablity !== $vatablity0 ) { 
									if ( $vatablity == 'yes' ) { $vatablitylog = ''; } else { $vatablitylog = 'غير'; }
									$updinvlogtext .= $vatablitylog.' خاضع للضريبه ';
								}
								// $saletype1 = $saletype;
								
								// Need to checked agsain
								if ( $producttype == 'retail' && $saletype0 == 'Both' ) {				// if purchase addded as retail
									{ $saletype1 = $saletype0; } 										// existing saletype is Both so stick to it
								} elseif ( $producttype == 'retail' && $saletype0 == 'wholesale' ) {
									$saletype1 = 'Both'; 												// existing saletype is WS so sell both
								} elseif ( $producttype == 'ws' && $saletype0 == 'Both' ) { // if purchase addded as retail
									{ $saletype1 = $saletype0; } 	// existing saletype is Both so stick to it
								} elseif ( $producttype == 'ws' && $saletype0 == 'retail' ) {
									$saletype1 = 'Both'; 	// existing saletype is WS so sell both
								} else {
									$saletype1 = $saletype;
								}

								
								// check if salestype is identical or different AND convert quantities

								$qty1 = $qty + $qty0;
								$cost1 = ( ( $qty0 * $cost0 ) + ( $qty * $cost ) ) / ( $qty0 + $qty );
								$cost1 = round($cost1, 2);
								//For logging Purposes
								$updinvlogtext .= ' وتم تحديث الكميه من '.$qty0.' إلى '.$qty1;
								if ( $cost1 !== $cost0 ) {
									$updinvlogtext .= ' و تحديث التكلفه من '.$cost0.' إلى '.$cost1;
								} else {
									$updinvlogtext .= ' بنفس سعر التكلفه '.$cost0;
								}

								
								if ( $saletype == 'retail' || $saletype == 'Both' ) {
									$price1 = $price;
									$disc1 = $disc;
									//For logging Purposes
									$updinvlogtext .= '<br>';
									if ( $price1 !== $price0 ) {
										$updinvlogtext .= 'و تحديث سعر بيع التفريد من '.$price0.' إلى '.$price1;
									} else {
										$updinvlogtext .= ' بنقس سعر بيع التفريد '.$price0;
									}
									if ( $disc1 !== $disc0 ) {
										$updinvlogtext .= ' و تحديث قيمة الخصم المتاح من '.$disc0.' إلى '.$disc1;
									} else {
										if ( !empty($disc0) ) { $updinvlogtext .= ' بنفس قيمة الخصم المتاح '.$disc0; }
									}
									
								} else {
									$price1 = '';
									$disc1 = '';
								}
								
								if ( $saletype == 'wholesale' || $saletype == 'Both' ) {
									// if empty wsbc 
									if ( $wsbc0 == '' || $wsbc0 == NULL || empty($wsbc0) ) {	// if empty just add new value
										$wsbc1 = $wsbc;
										$itemsperbox1 = $itemsperbox;
										$wsprice1 = $wsprice;
									} else {	// not empty
										$wsbcs = explode(",",$wsbc0);
										$itemsperboxs = explode(",",$itemsperbox0);
										$wsprices = explode(",",$wsprice0);
										
										if (in_array($wsbc, $wsbcs)) { 	// check if it exists then do nothing
											$index = array_search($wsbc,$wsbcs);	// Get index # of barcode to update price and ipb
											$itemsperboxs[$index] = $itemsperbox;
											$wsprices[$index] = $wsprice;
										} else { 						// doesnt exist thus add it
											$wsbcs[] = $wsbc;					
											$itemsperboxs[] = $itemsperbox;		
											$wsprices[] = $wsprice;				
										}
										$wsbc = implode(",",$wsbcs);					$wsbc1 = $wsbc;
										$itemsperbox = implode(",",$itemsperboxs);		$itemsperbox1 = $itemsperbox;
										$wsprice = implode(",",$wsprices);				$wsprice1 = $wsprice;	
									}
									//For logging Purposes
									$updinvlogtext .= ' ويباع كجمله للباركود '.$wsbc.' ووصف '.$name.' بسعر '.$wsprice;
								} else {	// keep old data
									$wsbc1 = $wsbc0;
									$itemsperbox1 = $itemsperbox0;
									$wsprice1 = $wsprice0;	
								}
								
								// check if minimum quantity exists to update or leave it as it is 
								if ( empty($minqty) ) { $minqty1 = $minqty0; } else { $minqty1 = $minqty; }

								// if old products still exists in inventory, expity date wont change
								if ( $qty0 > 0 ) {
									if ( !empty($day0) && !empty($month0) && !empty($year0) ) {	
										//$msg = $msg.'</br>(لم يتم تحديث تاريخ الانتهاء لوجود منتجات سابقه بتاريخ مختلف)';
										$day1 = $day0;
										$month1 = $month0;
										$year1 = $year0;
										$updinvlogtext .= ' مع بقاء تاريخ الانتهاء نظرا لوجود مخزون سابق '.$year0.'/'.$month0.'/'.$day0;
									} else {	
										$day1 = $day;
										$month1 = $month;
										$year1 = $year;
										if ( $day !== '' ) {
											$updinvlogtext .= '<br>وبتاريخ إنتهاء '.$year.'/'.$month.'/'.$day;
										}
									}
								} else {
									$day1 = $day;
									$month1 = $month;
									$year1 = $year;
									if ( $day !== '' ) {
										$updinvlogtext .= '<br>وبتاريخ إنتهاء '.$year.'/'.$month.'/'.$day;
									}
								}
								
								// check display place if they have been assigned or leave me 
								if ( empty($number) ) { $number1 = $number0; } else { $number1 = $number; }
								if ( empty($letter) ) { 
									$letter1 = $letter0;
									$updinvlogtext .= ' وبقاء مكان العرض '.$letter0.'-'.$number0;
								} else { 
									$letter1 = $letter;
									$updinvlogtext .= ' وبمكان عرض '.$letter.'-'.$number;
								}
														
								$update2 = mysqli_query($link,"UPDATE `inventory` SET 
								`vatablity`='$vatablity1',
								`qty`='$qty1',
								`minqty`='$minqty1',
								`cost`='$cost1',
								`saletype`='$saletype1',
								`price`='$price1',
								`disc`='$disc1',
								`wsbc`='$wsbc1',
								`itemsperbox`='$itemsperbox1',
								`wsprice`='$wsprice1',
								`day`='$day1',
								`month`='$month1',
								`year`='$year1',
								`number`='$number1',
								`letter`='$letter1',
								`timeedited`='$time'
								WHERE `id`='$id0'");
								
								if ($update2) {
									// $logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','تم تحديث المخزون عن طريق إضافة مشتريات للباركود رقم $bc بوصف $logdesc وتغيير حالة الخضوع للضريبه من $vatablity0 إلى $vatablity1 والكميه من $qty0 إلى $qty1 والتكلفه الفرديه من $cost0 إلى $cost1 وطريقة البيع من $saletype0 إلى $saletype1 وسعر بيع التفريد من $price0 إلى $price1 وقيمة الخصم المتاح من $disc0 إلى $disc1 وباركود الجمله من $wsbc0 إلى $wsbc1  وسعر بيع الجمله من $wsprice0 إلى $wsprice1 وعدد حبات التفريد في الجمله من $itemsperbox0 إلى $itemsperbox1 وتاريخ الانتهاء من $day0-$month0-$year0 إلى $day1-$month1-$year1 ومكان التخزين من $number0 $letter0 إلى $number1 $letter1','$time' )");
									$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','$updinvlogtext','$time' )");

									$responseArray = array('id' => 'success', 'message' => 'تم تحديث المنتج بنجاح'  );
								} else {
									$responseArray = array('id' => 'danger', 'message' => 'خطأ في إدخال البيانات $update2');
									// delete purchase if inventory update didnt succeed
									$delete2 = mysqli_query($link,"DELETE FROM purchases ORDER BY id DESC LIMIT 1");

								}
							// Add new inventory	
							} else {
								//Add new to inventory
								$ins2 = mysqli_query($link,"INSERT INTO `inventory`(
								`id`,
								`bc`,
								`vatablity`,
								`qty`,
								`minqty`,
								`cost`,
								`saletype`,
								`price`,
								`disc`,
								`wsbc`,
								`itemsperbox`,
								`wsprice`,
								`day`,
								`month`,
								`year`,
								`number`,
								`letter`,
								`timeadded`,
								`timeedited`
								)VALUES( 
								NULL,
								'$rbc',
								'$vatablity',
								'$qty',
								'$minqty',
								'$cost',
								'$saletype',
								'$price',
								'$disc',
								'$wsbc',
								'$itemsperbox',
								'$wsprice',
								'$day',
								'$month',
								'$year',
								'$number',
								'$letter',
								'$time',
								NULL	
								)");		

								if ($ins2) {
									$responseArray = array('id' => 'success', 'message' => 'تم إضافة منتج جديد بنجاح'  );
									// $logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','تم إضافة مخزون جديد بإضافة مشتريات للباركود رقم $rbc بوصف $logdesc و حالة ضريبيه $vatablity والكميه $qty و التكلفه الفرديه $cost وطريقة البيع $saletype وسعر بيع التفريد $price وقيمة الخصم المتاح $disc وباركود الجمله $wsbc وسعر بيع الجمله $wsprice وعدد حبات التفريد في الجمله $itemsperbox وتاريخ الانتهاء $day/$month/$year ومكان التخزين $number $letter','$time' )");
									$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','$newinvlogtext','$time' )");
								} else {
									$responseArray = array('id' => 'danger', 'message' => 'خطأ في إدخال البيانات $ins2');
									// delete purchase if inventory input didnt succeed
									$delete2 = mysqli_query($link,"DELETE FROM purchases ORDER BY id DESC LIMIT 1");
								}	
							}
							// Add Purchase id to pbills pool
							if ( isset($update2) || isset($ins2) ) {
								if ( !empty($billid) ) {
									// Get last Purchases id 
									$finder = mysqli_query($link,"SELECT id FROM `purchases` ORDER BY `id` DESC LIMIT 1");
									if(@mysqli_num_rows($finder) > 0){ 
										while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ $lastpurchaseid = $idfinder['id']; } 
									}

									$record = mysqli_query($link,"SELECT pids FROM `pbills` WHERE `id`='$billid' ");
									if(@mysqli_num_rows($record) > 0){
										while($pbillsinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){	
											$pids = $pbillsinfo['pids'];	
											$pids = explode(",",$pids);			// Convert to array
											$pids[] = $lastpurchaseid;			// Add to array
											$pids = array_filter($pids);		// tidy array
											$pids = implode(",",$pids);			// Convert to string
											$update3 = mysqli_query($link,"UPDATE `pbills` SET `pids`='$pids' WHERE `id`='$billid' ");
										}
									}
								}
							}
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في إدخال البيانات $ins1');
						}
					}
				}
			}
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم No POST');
}
end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>