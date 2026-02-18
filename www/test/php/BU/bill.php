<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		
// foreach($_POST as $item) {	echo array_search($item, $_POST);	echo ': ';	if ( is_array($item) ) { var_dump($item); } else { echo $item; }	echo '<br>';	}
session_start();
$Purchasesarray = [];		

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
			$empname = $_SESSION['name'];			if ( isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'initial' ) {					
			// Fetch customers
				$record = mysqli_query($link,"SELECT * FROM `customers`");
				$customernum = mysqli_num_rows($record);
				if(@mysqli_num_rows($record) > 0){
					$z = 0;					
					$Totalcustomers = [];	
					while($customersinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
						$id = $customersinfo['id'];
						$name = $customersinfo['name'];
						$mobile = $customersinfo['mobile'];				

						${'customerinfo'.$z} = array('id' => $id,'name' => $name,'mobile' => $mobile );	
						$z++;
					}
				}

			// Fetch Inventory Products
				$record = mysqli_query($link,"SELECT * FROM `Inventory`");
				$inventorynum = mysqli_num_rows($record);
				if(@mysqli_num_rows($record) > 0){
					$z = 0;					
					$Totalinventorys = [];
					$bcArray1 = [];				// store BC to fetch names
					while($inventorysinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
						$bc = $inventorysinfo['bc'];
						$wsbc = $inventorysinfo['wsbc'];
						// store BC to fetch names
						$bcArray1[] = $bc;
						$bcArray1[] = $wsbc;
						// ${'inventoryinfo'.$z} = array('id' => $id,'name' => $name,'mobile' => $mobile );	
						// $z++;
					}
					// Barcode name fetcher Section
					$bcArray1 = array_filter($bcArray1); // remove empty values
					$bcArray1 = array_unique($bcArray1); // remove duplicate values
					$bcArray1 = array_values($bcArray1); // renumber array keys 
					
					for ($a = 0; $a < count($bcArray1); $a++) {
						$record = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE `barcode` = $bcArray1[$a] ");
						if(@mysqli_num_rows($record) > 0){					
							while($barcode = mysqli_fetch_array($record, MYSQLI_ASSOC)){
								$bc = $barcode['barcode'];
								$description = $barcode['description'];
							}
							${'barcodes'.$a} = array('bc' => $bc,'description' => $description );
						} else {
							$record2 = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE `wsbc` = $bcArray1[$a] ");
							if(@mysqli_num_rows($record2) > 0){					
								while($barcode = mysqli_fetch_array($record2, MYSQLI_ASSOC)){
									$bc = $barcode['wsbc'];
									$description = $barcode['wsdescription'];
								}
								${'barcodes'.$a} = array('bc' => $bc,'description' => $description );
							}
							
						}
					}

				}
				
		/*	// Fetch Inventory
				$record = mysqli_query($link,"SELECT * FROM `inventory` ");
				$inventorynum = mysqli_num_rows($record);
				if(@mysqli_num_rows($record) > 0){									
					$z = 0;				
					$responseArray = [];			
					$bcArray1 = [];				// store BC to fetch names
					$bcArray2 = [];				// store BC to fetch names
					$Totalinventorys = [];	
					
					while($inventory = mysqli_fetch_array($record, MYSQLI_ASSOC)){
						$id = $inventory['id'];
						$bc = $inventory['bc'];
						$vatablity = $inventory['vatablity'];
						$qty = $inventory['qty'];
						$cost = $inventory['cost'];
						$saletype = $inventory['saletype'];
						$price = $inventory['price'];
						$disc  = $inventory['disc'];
						$wsbc = $inventory['wsbc'];
						$itemsperbox = $inventory['itemsperbox'];
						$wsprice = $inventory['wsprice'];
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
						$bcArray1[] = $bc;
						$bcArray1[] = $wsbc;

						${'inventory'.$z} = array('id' => $id,'bc' => $bc,'vatablity' => $vatablity,'qty' => $qty,'cost' => $cost,'saletype' => $saletype,'price' => $price,'disc' => $disc,'wsbc' => $wsbc,'itemsperbox' => $itemsperbox,'wsprice' => $wsprice,'day' => $day,'month' => $month,'year' => $year,'number' => $number,'letter' => $letter,'timeadded' => $timeadded,'timeedited' => $timeedited );	
						$z++;
					}
					
					$responseArray = array('id' => 'success', 'inventorynum' => $inventorynum);
					
					// Barcode name fetcher Section
					$bcArray1 = array_filter($bcArray1); // remove empty values
					$bcArray1 = array_unique($bcArray1); // remove duplicate values
					$bcArray1 = array_values($bcArray1); // renumber array keys 
				
					for ($a = 0; $a < count($bcArray1); $a++) {
						$record = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE `barcode` = $bcArray1[$a] ");
						if(@mysqli_num_rows($record) > 0){					
							while($barcode = mysqli_fetch_array($record, MYSQLI_ASSOC)){
								$bc = $barcode['barcode'];
								$description = $barcode['description'];
							}
							${'barcodes'.$a} = array('bc' => $bc,'description' => $description );
						} else {
							$record2 = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE `wsbc` = $bcArray1[$a] ");
							if(@mysqli_num_rows($record2) > 0){					
								while($barcode = mysqli_fetch_array($record2, MYSQLI_ASSOC)){
									$bc = $barcode['wsbc'];
									$description = $barcode['wsdescription'];
								}
								${'barcodes'.$a} = array('bc' => $bc,'description' => $description );
							}
							
						}
					}
					
					
				} 
		*/

				// Fetch Bill Number According to last number 
				$finder = mysqli_query($link,"SELECT * FROM `cbills` ORDER BY `id` DESC");
				if(@mysqli_num_rows($finder) > 0){ while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ $billid = $idfinder['id']+1;		break; } } else { $billid = 1; }

				// $finder = mysqli_query($link,"SELECT * FROM `cbills` ORDER BY `id` DESC");
				
				// if(@mysqli_num_rows($finder) > 0){ 
					// while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ 
						// $billid = $idfinder['id']+1;		break; 
					// }
				// } else {	// no bills found
					// $billid = 1;
				// }
				
				$responseArray = array('id' => 'success', 'customernum' => $customernum, 'billid' => $billid, 'Hdate' => HijriDate(2), 'Gdate' => $Gdate);
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
			array_push($Totalcustomers,${'customerinfo'.$a}); 
		}	
		array_push($responseArray,$Totalcustomers); 
	}
	
	$bcArray2 = [];				// store BC to fetch names
	if ( isset($bcArray1) && count($bcArray1) > 0 ){	
		for($a=0;$a<count($bcArray1);$a++){ 
			// needed in case some barcode doesnt exists in data bases count will be different 
			if ( isset(${'barcodes'.$a}) ) {
				array_push($bcArray2,${'barcodes'.$a}); 
			}
		}
		$bcnum = count($bcArray2);
		$responseArray["bcnum"] = $bcnum;
		array_push($responseArray,$bcArray2); 
	}

	
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>