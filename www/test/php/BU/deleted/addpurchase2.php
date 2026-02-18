<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		//print_r($_POST);	
// TO HANDLE RETAIL ONLY PURCHASES
if($_POST){
	$time = time();
	$msg = '';
	if(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'delete' )   {
		if(isset($_POST['id']) && !empty($_POST['id']) )   {
			$id = mysqli_real_escape_string($link, $_POST['id']);
			$delete = mysqli_query($link,"DELETE FROM purchases WHERE id = '$id' ");
			if (isset($delete)) {	
				$responseArray = array('id' => 'success', 'message' => 'تم حذف المنتج بنجاح');
			} else {
				$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات isset($delete)');
			}
		} else {
			$responseArray = array('id' => 'danger', 'message' => 'لا بوجد معرف للمنتج');
		}
	} else {
		if(!isset($_POST['productname']) || empty($_POST['productname']) )   {
			$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال اسم للمنتج');
		} elseif(!isset($_POST['wsbc']) || empty($_POST['wsbc']) )   {
			$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال باركود للمنتج');
		} elseif(!isset($_POST['vatablity']) || empty($_POST['vatablity']) )   {
			$responseArray = array('id' => 'danger', 'message' => 'لم تقم بتحديد ما اذا كان المنتج خاضع للضريبه');
		} elseif(!isset($_POST['wsqty']) || empty($_POST['wsqty']) )   {
			$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال كمية المنتج');
		} elseif(!isset($_POST['totalcost']) || empty($_POST['totalcost']) )   {
			$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال التكلفة الاجماليه');
		} else {
			$productname = mysqli_real_escape_string($link, $_POST['productname']);
			$wsbc = mysqli_real_escape_string($link, $_POST['wsbc']);
			$vatablity = mysqli_real_escape_string($link, $_POST['vatablity']);
			$wsqty = mysqli_real_escape_string($link, $_POST['wsqty']);
			$purchasevat = mysqli_real_escape_string($link, $_POST['purchasevat']);
			$totalcost = mysqli_real_escape_string($link, $_POST['totalcost']);
			$billid = mysqli_real_escape_string($link, $_POST['lastbill']);
			// $eachcost = mysqli_real_escape_string($link, $_POST['eachcost']);
			// With tax
			$eachcost = $totalcost / $wsqty;
			$eachcost = round($eachcost, 2); 

			if(isset($_POST['wholesale']) && !empty($_POST['wholesale']))   {
				// if(!isset($_POST['wsprice']) || empty($_POST['wsprice']) )   {
					// $responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال سعر بيع الجمله'); goto end;
				// }
				// $saletype = mysqli_real_escape_string($link, $_POST['wholesale']);
				// $wsprice = mysqli_real_escape_string($link, $_POST['wsprice']);
				// $wsdisc = mysqli_real_escape_string($link, $_POST['wsdisc']);
			}	else {
				$wsprice = 0;
				$wsdisc = 0;
			}

			if(isset($_POST['retail']) && !empty($_POST['retail']))   {
				// if(!isset($_POST['retailproductname']) || empty($_POST['retailproductname']) )   {
					// $responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال اسم لمنتج التفريد'); goto end;
				if(!isset($_POST['rprice']) || empty($_POST['rprice']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال سعر بيع التفريد'); goto end;
				// } elseif(!isset($_POST['rbc']) || empty($_POST['rbc']) )   {
					// $responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال باركود منتج التفريد'); goto end;
				// } elseif(!isset($_POST['itemsperbox']) || empty($_POST['itemsperbox']) )   {
					// $responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال عدد الحبات في الكرتون'); goto end;
				// } elseif(!isset($_POST['rprice']) || empty($_POST['rprice']) )   {
					// $responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال سعر بيع التفريد'); goto end;
				}
				$saletype = mysqli_real_escape_string($link, $_POST['retail']);
				$retailproductname = $productname;
				$rbc = $wsbc;
				$itemsperbox = 1;
				$rqty = $wsqty;
				$rcost = $eachcost;
				$rprice = mysqli_real_escape_string($link, $_POST['rprice']);
				$rdisc = mysqli_real_escape_string($link, $_POST['rdisc']);
			}  
			// {
				// $retailproductname = '';		// needs to be overwritten to not delete it if it exists
				// $rbc = '';						// needs to be overwritten to not delete it if it exists
				// $itemsperbox = '';				// needs to be overwritten to not delete it if it exists
				// $rqty = 0;
				// $rcost = 0;
				// $rprice = 0;
				// $rdisc = 0;
			// }
			
			// if(isset($_POST['wholesale']) && !empty($_POST['wholesale']) && isset($_POST['retail']) && !empty($_POST['retail']) )   {	$saletype = 'Both';	}

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

			// Add record to purchases 
			$ins1 = mysqli_query($link,"INSERT INTO `purchases`( 
			`id`,
			`wsbc`,
			`vatablity`,
			`wsqty`,
			`eachcost`,
			`purchasevat`,
			`totalcost`,
			`saletype`,
			`rbc`,
			`itemsperbox`,
			`rqty`,
			`rcost`,
			`supplier`,
			`billid`,
			`timeadded`,
			`buid`
			)VALUES( 
			NULL,
			'',
			'$vatablity',
			'',
			'',
			'$purchasevat',
			'$totalcost',
			'$saletype',
			'$rbc',
			'$itemsperbox',
			'$rqty',
			'$rcost',
			'$supplier',
			'$billid',
			'$time',
			NULL	
			)");		
			
			
			if ($ins1) {
			// Add or Update record to inventory 
				// Check if BC exists in inventory Table 
				$record = mysqli_query($link,"SELECT * FROM `inventory` WHERE `wsbc`='$wsbc' OR `rbc`='$rbc'");
				if(@mysqli_num_rows($record) > 0){
					// Fetch existing data
					while($productinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
						$id0 = $productinfo['id'];
						$wsbc0 = $productinfo['wsbc'];
						$vatablity0 = $productinfo['vatablity'];
						$wsqty0 = $productinfo['wsqty'];
						$eachcost0 = $productinfo['eachcost'];
						// $purchasevat0 = $productinfo['purchasevat'];
						// $totalcost0 = $productinfo['totalcost'];
						$saletype0 = $productinfo['saletype'];
						$wsprice0 = $productinfo['wsprice'];
						$wsdisc0 = $productinfo['wsdisc'];
						$rbc0 = $productinfo['rbc'];
						$itemsperbox0 = $productinfo['itemsperbox'];
						$rqty0 = $productinfo['rqty'];
						$rcost0 = $productinfo['rcost'];
						$rprice0 = $productinfo['rprice'];
						$rdisc0 = $productinfo['rdisc'];
						$day0 = $productinfo['day'];
						$month0 = $productinfo['month'];
						$year0 = $productinfo['year'];
						$number0 = $productinfo['number'];
						$letter0 = $productinfo['letter'];
						// $supplier0 = $productinfo['supplier'];
						$timeadded0 = $productinfo['timeadded'];
						$timeedited0 = $productinfo['timeedited'];
					}
					
					$vatablity1 = $vatablity;
					$saletype1 = $saletype;
					

					// check if salestype is identical or different AND convert quantities

						
					if ( $saletype0 == 'Both' && $saletype == 'retail' ) {
						// Convert WS to Retail
						$wsqty1 = 0;
						$rqty1 = $rqty0 + $rqty;
						
						$eachcost1 = ( ( $wsqty0 * $eachcost0 ) + ( $wsqty * $eachcost ) ) / ( $wsqty0 + $wsqty );
							$eachcost1 = round($eachcost1, 2);    
						$wsprice1 = 0;
						$wsdisc1 = 0;
						$rcost1 = ( ( $rqty0 * $rcost0 ) + ( $rqty * $rcost ) ) / ( $rqty0 + $rqty );
							$rcost1 = round($rcost1, 2);
						$rprice1 = $rprice;
						$rdisc1 = $rdisc;
						$rbc1 = $rbc;
													
					} else if ( $saletype0 == 'wholesale' && $saletype == 'retail' ) {	
						// convert WS to R
						$wsqty1 = 0;
						$wsprice1 = 0;
						$wsdisc1 = 0;

						$rqty1 = ( $wsqty0 * $itemsperbox0 ) + $rqty;
						
						$eachcost1 = ( ( $wsqty0 * $eachcost0 ) + ( $wsqty * $eachcost ) ) / ( $wsqty0 + $wsqty );
							$eachcost1 = round($eachcost1, 2);    
						// $rcost1 = $eachcost1 / $itemsperbox0;
						$rcost1 = ( ( $wsqty0 * $eachcost0 ) + ( $rqty * $rcost ) ) / ( ( $wsqty0 * $itemsperbox0 ) + $wsqty );
							$rcost1 = round($rcost1, 2);
						$rprice1 = $rprice;
						$rdisc1 = $rdisc;
						$rbc1 = $rbc;

					} else if ( $saletype0 == 'retail' && $saletype == 'retail' ) {	
						$wsqty1 = 0;
						$rqty1 = $rqty + $rqty0;
						$wsqty1 = 0;
						$eachcost1 = 0;
						$wsprice1 = 0;
						$wsdisc1 = 0;
						$rcost1 = ( ( $rqty0 * $rcost0 ) + ( $rqty * $rcost ) ) / ( $rqty0 + $rqty );
							$rcost1 = round($rcost1, 2);
						$rprice1 = $rprice;
						$rdisc1 = $rdisc;
						
						$rbc1 = $rbc0;
					}
					

					
					// if old products still exists in inventory, expity date wont change
					if ( $wsqty0 > 0 && $rqty0 > 0 ) {
						$day1 = $day0;
						$month1 = $month0;
						$year1 = $year0;
						$msg = $msg.'</br>(لم يتم تحديث تاريخ الانتهاء لوجود منتجات سابقه بتاريخ مختلف)';
					} else {
						$day1 = $day;
						$month1 = $month;
						$year1 = $year;
					}
					
					// check display place if they have been assigned or leave me 
					if ( empty($number) ) { $number1 = $number0; } else { $number1 = $number; }
					if ( empty($letter) ) { $letter1 = $letter0; } else { $letter1 = $letter; }
					if ( empty($itemsperbox) ) { $itemsperbox1 = $itemsperbox0; } else { $itemsperbox1 = $itemsperbox; }

					$timeadded1 = $timeadded0;
					$timeedited1 = $time;
					
					if (	$saletype = 'retail' ) {	
						// if (	$wsqty0 > 0 ) { $wsqty1 = $wsqty0; } else { $wsqty1 = 0; }
						// if (	$eachcost0 > 0 ) { $eachcost1 = $eachcost0; } else { $eachcost1 = 0; }
						if (	$wsbc0 > 0 ) { $wsbc1 = $wsbc0; } else { $wsbc1 = ''; }
						if (	$itemsperbox0 > 0 ) { $itemsperbox1 = $itemsperbox0; } else { $wsbc1 = ''; }
						$wsqty1 = 0;
						$eachcost1 = 0;
					}
					
					$update = mysqli_query($link,"UPDATE `inventory` SET 
					`vatablity`='$vatablity1',
					`wsqty`='$wsqty1',
					`eachcost`='$eachcost1',
					`saletype`='$saletype1',
					`wsprice`='$wsprice1',
					`wsdisc`='$wsdisc1',
					`rbc`='$rbc1',
					`itemsperbox`='$itemsperbox1',
					`rqty`='$rqty1',
					`rcost`='$rcost1',
					`rprice`='$rprice1',
					`rdisc`='$rdisc1',
					`day`='$day1',
					`month`='$month1',
					`year`='$year1',
					`number`='$number1',
					`letter`='$letter1',
					`timeedited`='$time'
					WHERE `id`='$id0'");
					
					if ($update) {
						
						// back up old data into buinventory
						$ins3 = mysqli_query($link,"INSERT INTO `buinventory`(
						`id`,
						`wsbc`,
						`vatablity`,
						`wsqty`,
						`eachcost`,
						`saletype`,
						`wsprice`,
						`wsdisc`,
						`rbc`,
						`itemsperbox`,
						`rqty`,
						`rcost`,
						`rprice`,
						`rdisc`,
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
						'$wsbc0',
						'$vatablity0',
						'$wsqty0',
						'$eachcost0',
						'$saletype0',
						'$wsprice0',
						'$wsdisc0',
						'$rbc0',
						'$itemsperbox0',
						'$rqty0',
						'$rcost0',
						'$rprice0',
						'$rdisc0',
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
						
						$update = mysqli_query($link,"UPDATE `purchases` SET `buid`='$buid' WHERE `id`='$lastpurchaseid'");
						
						$responseArray = array('id' => 'success', 'message' => 'تم تحديث المنتج بنجاح'.$msg.$id0  );
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في إدخال البيانات $update');
					}
					
				} else {
					// You might need to zeros ws quantity (maybe eachcost)
					if (	$saletype = 'retail' ) {	
						$wsqty = 0;
						$eachcost = 0;
						$wsbc = '';
					}

					//Add new to inventory
					$ins2 = mysqli_query($link,"INSERT INTO `inventory`(
					`id`,
					`wsbc`,
					`vatablity`,
					`wsqty`,
					`eachcost`,
					`saletype`,
					`wsprice`,
					`wsdisc`,
					`rbc`,
					`itemsperbox`,
					`rqty`,
					`rcost`,
					`rprice`,
					`rdisc`,
					`day`,
					`month`,
					`year`,
					`number`,
					`letter`,
					`timeadded`,
					`timeedited`
					)VALUES( 
					NULL,
					'$wsbc',
					'$vatablity',
					'$wsqty',
					'$eachcost',
					'$saletype',
					'$wsprice',
					'$wsdisc',
					'$rbc',
					'$itemsperbox',
					'$rqty',
					'$rcost',
					'$rprice',
					'$rdisc',
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
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في إدخال البيانات $ins2');
						//delete $ins1
						$delete = mysqli_query($link,"DELETE FROM purchases ORDER BY id DESC LIMIT 1");
					}	
				}
					
			} else {
				$responseArray = array('id' => 'danger', 'message' => 'خطأ في إدخال البيانات $ins1');
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