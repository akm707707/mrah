<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		//print_r($_POST);	
if($_POST){
	if(isset($_POST['id']) && !empty($_POST['id']))	{	
		$id = mysqli_real_escape_string($link, $_POST['id']);	
		$record = mysqli_query($link,"SELECT * FROM `purchases` WHERE id = $id   ");
	} else {
		$record = mysqli_query($link,"SELECT * FROM `purchases` ");
	}
		$purchasenum = mysqli_num_rows($record);
		if(@mysqli_num_rows($record) > 0){									
			$z = 0;				
			$responseArray = [];			
			$bcArray1 = [];				// store BC to fetch names
			$bcArray2 = [];				// store BC to fetch names
			$Totalpurchases = [];	
			
			while($purchase = mysqli_fetch_array($record, MYSQLI_ASSOC)){
				$id = $purchase['id'];
				$bc = $purchase['bc'];
				$vatablity = $purchase['vatablity'];
				$qty = $purchase['qty'];
				$cost = $purchase['cost'];
				$vat = $purchase['vat'];
				$totalcost = $purchase['totalcost'];
				$saletype = $purchase['saletype'];
				$supplier = $purchase['supplier'];
				$billid = $purchase['billid'];
				// $buid = $purchase['buid'];
				$timeadded = Time_Passed(date($purchase['timeadded']),'time');

				// $supplierrecord = mysqli_query($link,"SELECT * FROM suppliers WHERE id = $supplierid ");
				// if(@mysqli_num_rows($supplierrecord = mysqli_query($link,"SELECT * FROM suppliers WHERE id = $supplierid ")) > 0){
					// $supplier = mysqli_fetch_array($supplierrecord, MYSQLI_ASSOC);
					// $suppliername = $supplier['suppliername'];
				// } else { $suppliername = ''; }
				
				// if ( !is_null($purchase['timeedited']) ) { 
					// $timeedited = Time_Passed(date($purchase['timeedited']),'time'); 
				// } else {	$timeedited = ''; }	
			
			
				// store BC to fetch names
				$bcArray1[] = $bc;
				// $bcArray1[] = $rbc;

				${'purchase'.$z} = array('id' => $id,'bc' => $bc,'vatablity' => $vatablity,'qty' => $qty,'cost' => $cost,'vat' => $vat,'totalcost' => $totalcost,'saletype' => $saletype,'supplier' => $supplier,'billid' => $billid,'timeadded' => $timeadded );	

				$z++;
			}
			
			$responseArray = array('id' => 'success', 'purchasenum' => $purchasenum);
			
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
				}
			}
			
			
		} else { 																				
			$responseArray = array('id' => 'danger', 'purchasenum' => $purchasenum);
		}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

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
	
	if ( isset($purchasenum) && $purchasenum > 0 ){	
		for($a=0;$a<$purchasenum;$a++){ 
			array_push($Totalpurchases,${'purchase'.$a}); 
		}	
		array_push($responseArray,$Totalpurchases); 
	}

	// array_push($responseArray,$bcArray1);
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>