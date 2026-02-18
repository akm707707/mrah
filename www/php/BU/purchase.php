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
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `purchases` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logbc = $logrecord['bc'];
							$bcfetcher = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `ownbcdb` WHERE barcode = '$logbc' "), MYSQLI_ASSOC);
							$logdesc = $bcfetcher['description'];
						$logqty = $logrecord['qty'];
						$logcost = $logrecord['cost'];	
						$logvat = $logrecord['vat'];	
						$logvatablity	 = $logrecord['vatablity'];	
						$logtotalcost = $logrecord['totalcost'];	
						$logsupplier = $logrecord['supplier'];	
						$logsaletype = $logrecord['saletype'];	
						
					if(isset($_POST['restore']) && !empty($_POST['restore']) )   {	//$msg = $msg.'</br> restore is set and has value';
					// FETCH PURCHASE DATA
						$record1 = mysqli_query($link,"SELECT * FROM `purchases` WHERE `id`='$id' ");
						if(@mysqli_num_rows($record1) < 1){
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم No Purchase Found');
						} else {
							//$msg = $msg.'</br> purchase found';
							while($purchaseinfo = mysqli_fetch_array($record1, MYSQLI_ASSOC)){	
								$bc = $purchaseinfo['bc'];	
								$qty = $purchaseinfo['qty'];	
								$cost = $purchaseinfo['cost'];	
								$totalcost = $purchaseinfo['totalcost'];	
							}
							
							// FETCH INVENTORY DATA
							$record2 = mysqli_query($link,"SELECT * FROM `inventory` WHERE `bc`='$bc' ");
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
								$cost0 = ( ( $cost1 * $qty1 ) - $totalcost ) / $qty0;
								
								$update = mysqli_query($link,"UPDATE `inventory` SET 
								`qty`='$qty0',
								`cost`='$cost0'
								WHERE `bc`='$bc'");

								if ( $update ) {
									//$msg = $msg.'</br> $update is successful';
								} else {
									$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $id');
								}

							}
						}
					}
					
					$delete = mysqli_query($link,"DELETE FROM purchases WHERE id = '$id' ");
					if (isset($delete)) {	
						$responseArray = array('id' => 'success', 'message' => 'تم حذف المنتج بنجاح');
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','purchase','تم حذف مشتريات لـ $logdesc باركود رقم $logbc كمية $logqty بتكلفه فرديه $logcost وضريبه $logvat  وحاله الخضوع للضريبه $logvatablity واجمالي تكلفه $logtotalcost من المورد $logsupplier  ويباع ك $logsaletype','$time' )");
						if (isset($update) && $update) {	
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','تم تحديث المخزون عن طريق حذف مشتريات للباركود رقم $logbc بوصف $logdesc وتغيرت الكميه من $qty1 إلى $qty0 وتغيرت التكلفه الفرديه من $cost1 إلى $cost0','$time' )");
						} 
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات isset($delete)');
					}
				}
			} else {		// Insert new Purchase
				if(!isset($_POST['name']) || empty($_POST['name']) )   {
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
				} elseif(!isset($_POST['totalcost']) || empty($_POST['totalcost']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال التكلفة الاجماليه');
				} elseif ( !is_numeric($_POST['totalcost'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'التكلفه الكليه يجب أن تحتوي على أرقام فقط');
				} else {
					$name = mysqli_real_escape_string($link, $_POST['name']);
					$bc = mysqli_real_escape_string($link, $_POST['bc']);
					// Check if barcode exists in database or not 
					$record = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE `barcode`='$bc' ");
					if(@mysqli_num_rows($record) == 0){
						$responseArray = array('id' => 'danger', 'message' => 'الباركود غير مضاف لقاعدة البيانات الخاصه بك');
					} else {
							$bcfetcher = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `ownbcdb` WHERE barcode = '$bc' "), MYSQLI_ASSOC);
							$logdesc = $bcfetcher['description'];
						$vatablity = mysqli_real_escape_string($link, $_POST['vatablity']);
						$qty = mysqli_real_escape_string($link, $_POST['quantity']);
						$vat = mysqli_real_escape_string($link, $_POST['purchasevat']);
						$cost = mysqli_real_escape_string($link, $_POST['cost']);
						$totalcost = mysqli_real_escape_string($link, $_POST['totalcost']);
						$billid = mysqli_real_escape_string($link, $_POST['lastbill']);
						// $eachcost = mysqli_real_escape_string($link, $_POST['eachcost']);
						// With tax
						// $cost = $totalcost / $qty;				$cost = round($cost, 2); 


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
							$disc = mysqli_real_escape_string($link, $_POST['discount']);
						} else {
							// Product is not sold as Retail, thus nullifing variable for INSERT 
							$price = '';
							$disc = '';
						} 

						if(isset($_POST['wholesale']) && !empty($_POST['wholesale']))   {
							if(!isset($_POST['wsname']) || empty($_POST['wsname']) )   {
								$responseArray = array('id' => 'danger', 'message' => 'لا يوجد اسم للجمله');							goto end;
							} elseif(!isset($_POST['wsbc']) || empty($_POST['wsbc']) )   {
								$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال باركود للمنتج الجمله');					goto end;
							} elseif ( !is_numeric($_POST['wsbc'])  )   {
								$responseArray = array('id' => 'danger', 'message' => 'باركود منتج الجمله يجب أن يحتوي على أرقام فقط');			goto end;
							} elseif(!isset($_POST['itemsperbox']) || empty($_POST['itemsperbox']) )   {
								$responseArray = array('id' => 'danger', 'message' => 'لم بإ\خال عدد وحدات التفريد لمنتج الجمله');				goto end;
							} elseif ( !is_numeric($_POST['itemsperbox'])  )   {
								$responseArray = array('id' => 'danger', 'message' => 'عدد وحدات التفريد في الجمله يجب أن تحتوي على أرقام فقط');		goto end;
							} elseif(!isset($_POST['wsprice']) || empty($_POST['wsprice']) )   {
								$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال سعر بيع منتج الجمله');					goto end;
							} elseif ( !is_numeric($_POST['wsprice'])  )   {
								$responseArray = array('id' => 'danger', 'message' => 'سعر بيع الجمله يجب أن يحتوي على أرقام فقط');				goto end;
							} else {
								$wsname = mysqli_real_escape_string($link, $_POST['wsname']);
								$wsbc = mysqli_real_escape_string($link, $_POST['wsbc']);
								// Check if barcode exists in database or not 
								$record2 = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE `barcode`='$wsbc' ");
								if(@mysqli_num_rows($record2) > 0){
									$responseArray = array('id' => 'danger', 'message' => 'باركزد الجمله مضاف كـ باركود تفريد');	goto end;
								} else {
									// check if wsbc exists within retail barcode detail
									while($productinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){ $existingwsbc = $productinfo['wsbc']; }
									//check if retail barcode has a different wsbc
									if ( !empty($existingwsbc) && $existingwsbc != $wsbc ) {
									$responseArray = array('id' => 'danger', 'message' => 'باركود الجمله المدخل غير متطابق مع فاعدة البيانات. يلزم تعديله من قاعدة بيانات الباركود'); goto end;
									}

									if (  empty($existingwsbc) ) {
										$update1 = mysqli_query($link,"UPDATE `ownbcdb` SET `wsbc`='$wsbc', `wsdescription`='$wsname' WHERE `barcode`='$bc'");
										if (!$update1) {	
											$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات$update1');	goto end;
											$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','purchase','تم إضافة باركود الجمله $wsbc بوصف $wsname عن طريق إضافة مشتريات للباركود رقم  $logbc بوصف $logdesc','$time' )");
										}								
									}
									
									$saletype = mysqli_real_escape_string($link, $_POST['wholesale']);
									$itemsperbox = mysqli_real_escape_string($link, $_POST['itemsperbox']);
									$wsprice = mysqli_real_escape_string($link, $_POST['wsprice']);
								}
							}
						} else {
							$wsbc = '';
							$itemsperbox = '';
							$wsprice = '';
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
					
						if(isset($_POST['supplier']) && !empty($_POST['supplier']))   {
							$supplier = mysqli_real_escape_string($link, $_POST['supplier']);
							// $finder = mysqli_query($link,"SELECT * FROM `suppliers` WHERE `suppliername`='$supplier'");
							// if(@mysqli_num_rows($finder) > 0){ 
								// while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ 
									// $supplierid = $idfinder['id']; break;
								// } 
							// }
						} else { $supplier = '';	 }

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

						
						// Add record to purchases 
						$ins1 = mysqli_query($link,"INSERT INTO `purchases`( 
						`id`,
						`bc`,
						`vatablity`,
						`qty`,
						`cost`,
						`vat`,
						`totalcost`,
						`saletype`,
						`supplier`,
						`billid`,
						`timeadded`,
						`buid`
						)VALUES( 
						NULL,
						'$bc',
						'$vatablity',
						'$qty',
						'$cost',
						'$vat',
						'$totalcost',
						'$saletype',
						'$supplier',
						'$billid',
						'$time',
						NULL	
						)");
						
						
						if ($ins1) {
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','purchase','تم إضافة مشتريات للباركود رقم $bc ووصف $logdesc خاضع لضريبه $vatablity وكمية $qty وتكلفه فرديه بقيمة $cost وتكلفه اجماليه $totalcost يباع ك $saletype من المورد $supplier وبرقم فاتوره $billid','$time' )");
						// Add or Update record to inventory 
							// Check if BC exists in inventory Table, if yes, update cost and quantities
							$record = mysqli_query($link,"SELECT * FROM `inventory` WHERE `bc`='$bc' ");
							if(@mysqli_num_rows($record) > 0){
								// Fetch existing data
								while($productinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
									$id0 = $productinfo['id'];
									$bc0 = $productinfo['bc'];
									$vatablity0 = $productinfo['vatablity'];
									$qty0 = $productinfo['qty'];
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
								$saletype1 = $saletype;
								
								// check if salestype is identical or different AND convert quantities

								$qty1 = $qty + $qty0;
								$cost1 = ( ( $qty0 * $cost0 ) + ( $qty * $cost ) ) / ( $qty0 + $qty );
								$cost1 = round($cost1, 2);    
								
								if ( $saletype == 'retail' || $saletype == 'Both' ) {
									$price1 = $price;
									$disc1 = $disc;
								} else {
									$price1 = NULL;
									$disc1 = NULL;
								}
								
								if ( $saletype == 'wholesale' || $saletype == 'Both' ) {
									$wsbc1 = $wsbc;
									$itemsperbox1 = $itemsperbox;
									$wsprice1 = $wsprice;
								} else {
									$wsbc1 = $wsbc0;
									$itemsperbox1 = $itemsperbox0;
									// $wsprice1 = $wsprice0;
									// $wsbc1 = NULL;
									// $itemsperbox1 = NULL;
									$wsprice1 = NULL;
								}
								
								// if old products still exists in inventory, expity date wont change
								if ( $qty0 > 0 ) {
									if ( !empty($day0) && !empty($month0) && !empty($year0) ) {	
										//$msg = $msg.'</br>(لم يتم تحديث تاريخ الانتهاء لوجود منتجات سابقه بتاريخ مختلف)';
										$day1 = $day0;
										$month1 = $month0;
										$year1 = $year0;
									} else {	
										$day1 = $day;
										$month1 = $month;
										$year1 = $year;
									}
								} else {
									$day1 = $day;
									$month1 = $month;
									$year1 = $year;
								}
								
								// check display place if they have been assigned or leave me 
								if ( empty($number) ) { $number1 = $number0; } else { $number1 = $number; }
								if ( empty($letter) ) { $letter1 = $letter0; } else { $letter1 = $letter; }
														
								$update2 = mysqli_query($link,"UPDATE `inventory` SET 
								`vatablity`='$vatablity1',
								`qty`='$qty1',
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
									// $logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','تم تحديث المخزون للباركود رقم $bc بوصف $logdesc  والكميه من $qty0 إلى $qty1  والتكلفه الفرديه من $cost0 إلى $cost1 وطريقة البيع من $saletype0  إلى $saletype1  وسعر البيع من $price0 إلى $price1','$time' )");
									$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','تم تحديث المخزون عن طريق إضافة مشتريات للباركود رقم $bc بوصف $logdesc وتغيير حالة الخضوع للضريبه من $vatablity0 إلى $vatablity1 والكميه من $qty0 إلى $qty1 والتكلفه الفرديه من $cost0 إلى $cost1 وطريقة البيع من $saletype0 إلى $saletype1 وسعر بيع التفريد من $price0 إلى $price1 وقيمة الخصم المتاح من $disc0 إلى $disc1 وباركود الجمله من $wsbc0 إلى $wsbc1  وسعر بيع الجمله من $wsprice0 إلى $wsprice1 وعدد حبات التفريد في الجمله من $itemsperbox0 إلى $itemsperbox1 وتاريخ الانتهاء من $day0-$month0-$year0 إلى $day1-$month1-$year1 ومكان التخزين من $number0 $letter0 إلى $number1 $letter1','$time' )");
									// back up old data into buinventory
									$ins3 = mysqli_query($link,"INSERT INTO `buinventory`(
									`id`,
									`bc`,
									`vatablity`,
									`qty`,
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
									`timeedited`,
									`invid`
									)VALUES( 
									NULL,
									'$bc0',
									'$vatablity0',
									'$qty0',
									'$cost0',
									'$saletype0',
									'$price0',
									'$disc0',
									'$wsbc0',
									'$itemsperbox0',
									'$wsprice0',
									'$day0',
									'$month0',
									'$year0',
									'$number0',
									'$letter0',
									'$timeadded0',
									'$timeedited0',
									'$id0'	
									)");		
									
									//get back row idd 
									$buidrow = mysqli_query($link,"SELECT * FROM `buinventory` ORDER BY id DESC LIMIT 1");
									if(@mysqli_num_rows($buidrow) > 0){
										// Fetch existing data
										while($productinfo = mysqli_fetch_array($buidrow, MYSQLI_ASSOC)){
											$buid = $productinfo['id'];
										}
									}

									//get last purchase idd 
									$lastpurchaseidrow = mysqli_query($link,"SELECT * FROM `purchases` ORDER BY id DESC LIMIT 1");
									if(@mysqli_num_rows($lastpurchaseidrow) > 0){
										// Fetch existing data
										while($productinfo = mysqli_fetch_array($lastpurchaseidrow, MYSQLI_ASSOC)){
											$lastpurchaseid = $productinfo['id'];
										}
									}
									
									$update3 = mysqli_query($link,"UPDATE `purchases` SET `buid`='$buid' WHERE `id`='$lastpurchaseid'");
									
									// $responseArray = array('id' => 'success', 'message' => 'تم تحديث المنتج بنجاح'.$msg.$id0  );
									$responseArray = array('id' => 'success', 'message' => 'تم تحديث المنتج بنجاح'  );
								} else {
									$responseArray = array('id' => 'danger', 'message' => 'خطأ في إدخال البيانات $update3');
								}
							// Add new inventory	
							} else {
								// nullify price andd sicount if sold as wholesale
								if (	$saletype =='wholesale' ) {	
									$price = NULL;
									$disc = NULL;
								}

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
								'$bc',
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
									$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','تم تحديث المخزون بإضافة مخزون جديد عن طريق إضافة مشتريات للباركود رقم $bc بوصف $logdesc و حالة الخضوع للضريبه من $vatablity والكميه $qty و التكلفه الفرديه $cost وطريقة البيع $saletype وسعر بيع التفريد $price وقيمة الخصم المتاح $disc وباركود الجمله $wsbc  وسعر بيع الجمله $wsprice وعدد حبات التغريد في الجمله $itemsperbox وتاريخ الانتهاء $day/$month/$year ومكان التخزين $number $letter','$time' )");
								} else {
									$responseArray = array('id' => 'danger', 'message' => 'خطأ في إدخال البيانات $ins2');
									//delete $ins1
									$delete2 = mysqli_query($link,"DELETE FROM purchases ORDER BY id DESC LIMIT 1");
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