<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		
session_start();
//print_r($_POST);	
$allresps = [];
if($_POST){
	$time = time();
	if ( !$_SESSION ) {
		$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $_SESSION');
		array_push($allresps,$responseArray);
	} else {
		if ( !isset($_SESSION['loggedin']) || $_SESSION["loggedin"] !== true )   {
			$responseArray = array('id' => 'danger', 'message' => 'لم تقم بتسجيل الدخول');
			array_push($allresps,$responseArray);
		} elseif ( !isset($_SESSION['userstatus']) || $_SESSION["userstatus"] != 1 )   {
			$responseArray = array('id' => 'danger', 'message' => 'لا تمتلك التصريح');
			array_push($allresps,$responseArray);
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

			if( !isset($_POST['bc']) || empty($_POST['bc']) )   {
				$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال الباركود');
				array_push($allresps,$responseArray);
			} elseif ( !is_numeric($_POST['bc'])  )   {
				$responseArray = array('id' => 'danger', 'message' => 'رقم الباركود يجب أن يحتوي على أرقام فقط');
				array_push($allresps,$responseArray);
			} elseif ( isset($_POST['quantity']) && !empty($_POST['quantity']) && !is_numeric($_POST['quantity'])  )   {
				$responseArray = array('id' => 'danger', 'message' => 'الكميه يجب أن تحتوي على أرقام فقط');
				array_push($allresps,$responseArray);
			} elseif ( isset($_POST['cost']) && !empty($_POST['cost']) && !is_numeric($_POST['cost'])  )   {
				$responseArray = array('id' => 'danger', 'message' => 'سعر التكلفه يجب أن تحتوي على أرقام فقط');
				array_push($allresps,$responseArray);
			} elseif ( isset($_POST['price']) && !empty($_POST['price']) && !is_numeric($_POST['price'])  )   {
				$responseArray = array('id' => 'danger', 'message' => 'سعر بيع التفريد يجب أن تحتوي على أرقام فقط');
				array_push($allresps,$responseArray);
			} elseif ( isset($_POST['wsprice']) && !empty($_POST['wsprice']) && !is_numeric($_POST['wsprice'])  )   {
				$responseArray = array('id' => 'danger', 'message' => 'سعر بيع الجمله يجب أن تحتوي على أرقام فقط');
				array_push($allresps,$responseArray);
			} else {
				
				$bc = mysqli_real_escape_string($link, $_POST['bc']);
				$name	 = mysqli_real_escape_string($link, $_POST['name']);
				$record0 = mysqli_query($link,"SELECT barcode FROM `ownbcdb` WHERE barcode = '$bc'   ");
				$record1 = mysqli_query($link,"SELECT bc FROM `inventory` WHERE `bc`='$bc' ");
				if(@mysqli_num_rows($record0) == 0){
					$responseArray = array('id' => 'danger', 'message' => 'الباركود غير مضاف لقاعدة البيانات');
					array_push($allresps,$responseArray);
				} elseif(@mysqli_num_rows($record1) == 0){
					$responseArray = array('id' => 'danger', 'message' => 'المنتج غير مضاف للمخزون');
					array_push($allresps,$responseArray);					
				} else {
					
					$bcfetcher = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `inventory` WHERE bc = '$bc' "), MYSQLI_ASSOC);
					$logqty = $bcfetcher['qty'];
					$logcost = $bcfetcher['cost'];
					$logsaletype = $bcfetcher['saletype'];
					$logprice = $bcfetcher['price'];
					$logwsprice = $bcfetcher['wsprice'];
					$logday = $bcfetcher['day'];
					$logmonth = $bcfetcher['month'];
					$logyear = $bcfetcher['year'];
					$lognumber = $bcfetcher['number'];
					$logletter = $bcfetcher['letter'];

					// Quantity Section
					if(isset($_POST['quantity']) && !empty($_POST['quantity']) )   {
						$qty = mysqli_real_escape_string($link, $_POST['quantity']);
						$update = mysqli_query($link,"UPDATE `inventory` SET `qty`='$qty' WHERE `bc`='$bc' ");
						if (!$update) { 
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $updateqty');	
							array_push($allresps,$responseArray);
						} else {
							$responseArray = array('id' => 'success', 'message' => 'تم تحديث الكميه بنجاح');
							array_push($allresps,$responseArray);
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','تم تحديث المخزون مباشرةً للباركود رقم $bc و وصف $name بتعديل الكميه من $logqty إلى $qty','$time' )");
						}

					}
					
					// Cost Section
					if(isset($_POST['cost']) && !empty($_POST['cost']) )   {
						$cost = mysqli_real_escape_string($link, $_POST['cost']);
						$update = mysqli_query($link,"UPDATE `inventory` SET `cost`='$cost' WHERE `bc`='$bc' ");
						if (!$update) { 
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $updatecost');	
							array_push($allresps,$responseArray);
						} else {
							$responseArray = array('id' => 'success', 'message' => 'تم تحديث التكلفه بنجاح');
							array_push($allresps,$responseArray);
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','تم تحديث المخزون مباشرةً للباركود رقم $bc و وصف $name بتعديل التكلفه من $logcost إلى $cost','$time' )");
						}

					}
					
					// Retail Price Section
					if(isset($_POST['price']) && !empty($_POST['price']) )   {
						$price = mysqli_real_escape_string($link, $_POST['price']);
						$update = mysqli_query($link,"UPDATE `inventory` SET `price`='$price' WHERE `bc`='$bc' ");
						if (!$update) { 
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $updateprice');	
							array_push($allresps,$responseArray);
						} else {
							$responseArray = array('id' => 'success', 'message' => 'تم تحديث سعر بيع التفريد بنجاح');
							array_push($allresps,$responseArray);
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','تم تحديث المخزون مباشرةً للباركود رقم $bc و وصف $name بتعديل سعر بيع التفريد من $logprice إلى $price','$time' )");
						}

					}
					
					// Wholesale Price Section
					if(isset($_POST['wsprice']) && !empty($_POST['wsprice']) )   {
						$wsprice = mysqli_real_escape_string($link, $_POST['wsprice']);
						if ( $logsaletype == 'retail' ) {
							$responseArray = array('id' => 'danger', 'message' => 'المنتج لا يباع كجمله');
							array_push($allresps,$responseArray);							
						} else {
							$update = mysqli_query($link,"UPDATE `inventory` SET `wsprice`='$wsprice' WHERE `bc`='$bc' ");
							if (!$update) { 
								$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $updatewsprice');	
								array_push($allresps,$responseArray);
							} else {
								$responseArray = array('id' => 'success', 'message' => 'تم تحديث سعر بيع الجمله بنجاح');
								array_push($allresps,$responseArray);
								$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','تم تحديث المخزون مباشرةً للباركود رقم $bc و وصف $name بتعديل سعر بيع الجمله من $logwsprice إلى $wsprice' )");
							}
						}

					}
					
					// ExpiryDate Section
					if(isset($_POST['day']) || isset($_POST['month']) || isset($_POST['year'])  )   {
						if(empty($_POST['day']) && empty($_POST['month']) && empty($_POST['year'])  )   {
							// Do nothing
						} else { 
							$expiryflag = 1;
							// Make sure expiry day, month and year all exists 
							if( !empty($_POST['day']))   {
								if( empty($_POST['month']) || empty($_POST['year']) )   { 	$expiryflag = 0;	}
							}
							if( !empty($_POST['month']))   {
								if( empty($_POST['day']) || empty($_POST['year']) )   {		$expiryflag = 0;	}
							}
							if( !empty($_POST['year']))   {
								if( empty($_POST['month']) || empty($_POST['day']) )   { 	$expiryflag = 0;	}
							}

							if( $expiryflag == 0 )   {
								$responseArray = array('id' => 'danger', 'message' => 'تاريخ الانتهاء غير مكتمل');
								array_push($allresps,$responseArray);
							} else {
							
								$day = mysqli_real_escape_string($link, $_POST['day']);
								$month = mysqli_real_escape_string($link, $_POST['month']);
								$year = mysqli_real_escape_string($link, $_POST['year']);
								$update = mysqli_query($link,"UPDATE `inventory` SET 
										`day`='$day',
										`month`='$month',
										`year`='$year' WHERE `bc`='$bc' ");
								if (!$update) { 
									$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $updateExpiryDate');	
									array_push($allresps,$responseArray);
								} else {
									$responseArray = array('id' => 'success', 'message' => 'تم تحديث تاريخ الانتهاء بنجاح');
									array_push($allresps,$responseArray);
									$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','تم تحديث المخزون مباشرةً للباركود رقم $bc و وصف $name بتعديل تاريخ الانتهاء من $logday-$logmonth-$logyear إلى $day-$month-$year ','$time' )");
								}
							}
						}

					}

					// Display Area Section
					if(isset($_POST['letter']) || isset($_POST['number'])  )   {
						if(empty($_POST['letter']) && empty($_POST['number'])  )   {
							// Do nothing
						} else { 
							$displayflag = 1;
							// Make sure expiry day, month and year all exists 
							if( !empty($_POST['letter']))   {
								if( empty($_POST['number'])  )   { 
									$displayflag = 0;
								}
							}
							if( !empty($_POST['number']))   {
								if( empty($_POST['letter'])  )   { 
									$displayflag = 0;
								}
							}

							if( $displayflag == 0 )   {
								$responseArray = array('id' => 'danger', 'message' => 'مكان العرض غير مكتمل');
								array_push($allresps,$responseArray);
							} else {
							
								$letter = mysqli_real_escape_string($link, $_POST['letter']);
								$number = mysqli_real_escape_string($link, $_POST['number']);
								$update = mysqli_query($link,"UPDATE `inventory` SET 
										`letter`='$letter',
										`number`='$number' WHERE `bc`='$bc' ");
								if (!$update) { 
									$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $updatedisplay');	
									array_push($allresps,$responseArray);
								} else {
									$responseArray = array('id' => 'success', 'message' => 'تم تحديث مكان العرض بنجاح');
									array_push($allresps,$responseArray);
									$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','inventory','تم تحديث المخزون مباشرةً للباركود رقم $bc و وصف $name بتعديل مكان التخزين من $lognumber $logletter إلى $number $letter','$time' )");
								}
							}
						}
					}					
				}
			}

		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم$_POST');
}
end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $encoded = json_encode($allresps);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>