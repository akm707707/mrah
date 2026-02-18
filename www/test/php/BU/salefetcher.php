<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		//print_r($_POST);	
if($_POST){
	$time = time();
	if($_POST['bc']){
		// $Arrays = [];		
		// $Catdata = [];		
		$bc = mysqli_real_escape_string($link, $_POST['bc']);
		$record0 = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE `barcode`='$bc' OR `wsbc`='$bc'");
		if(@mysqli_num_rows($record0) < 1){
			$responseArray = array('id' => 'danger', 'message' => 'المنتج غير معرف لقاعدة البيانات الخاصة بك' );	
		} else {	
			//Fetch barcode
			while($barcodeinfo = mysqli_fetch_array($record0, MYSQLI_ASSOC)){
					$category = $barcodeinfo['category'];
					$description = $barcodeinfo['description'];
					$wsdescription = $barcodeinfo['wsdescription'];
			}
			
			$record1 = mysqli_query($link,"SELECT * FROM `inventory` WHERE `bc`='$bc' OR `wsbc`='$bc'");
			if(@mysqli_num_rows($record1) < 1){
				$responseArray = array('id' => 'danger', 'message' => 'المنتج غير مضاف في المحزون' );	
			} else {	
				while($inventory = mysqli_fetch_array($record1, MYSQLI_ASSOC)){
					$id = $inventory['id'];
					$rbc = $inventory['bc'];
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
					
					// Check if barcode is retail or wholesale
					if ( $bc == $rbc ) { $soldas = 'R'; }
					if ( $bc == $wsbc ) { $soldas = 'WS'; }
					
					$inventorydata = array('id' => $id,'category' => $category,'description' => $description,'wsdescription' => $wsdescription,'rbc' => $rbc,'vatablity' => $vatablity, 'qty' => $qty, 'cost' => $cost, 'saletype' => $saletype,'price' => $price,'disc' => $disc,'wsbc' => $wsbc ,'itemsperbox' => $itemsperbox ,'wsprice' => $wsprice ,'day' => $day ,'month' => $month ,'year' => $year ,'number' => $number ,'letter' => $letter,'soldas' => $soldas,'timeedited' => $timeedited,'timeadded' => $timeadded	);	
				}

				if ( $bc == $rbc &&  $saletype == 'wholesale' ) { 
					$responseArray = array('id' => 'danger', 'message' => 'المنتج لا يباع كتفريد');
				} elseif ( $bc == $wsbc &&  $saletype == 'retail' ) { 
					$responseArray = array('id' => 'danger', 'message' => 'المنتج لا يباع كجمله');
				} elseif ( $qty < 1 ) { 
					$responseArray = array('id' => 'danger', 'message' => 'نفذ المخزون');
				} else {
					$responseArray = array('id' => 'success', 'message' => $inventorydata );
				}
				
				// if ( $qty > 0 ) { 
					// $responseArray = array('id' => 'success', 'message' => $inventorydata );
				// } else {
					// $responseArray = array('id' => 'danger', 'message' => 'نفذ المخزون');
				// }
				
			}
		}
	} else {
		$responseArray = array('id' => 'danger', 'message' => 'من فضلك حاول في وقت لاحق $bc');
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'من فضلك حاول في وقت لاحق');
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>