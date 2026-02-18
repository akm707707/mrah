<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		//print_r($_POST);	
if($_POST){
	$time = time();
	if($_POST['bc']){
		$bc = mysqli_real_escape_string($link, $_POST['bc']);
		$record0 = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE `barcode`='$bc' OR FIND_IN_SET('$bc', wsbc)");
		if(@mysqli_num_rows($record0) < 1){
			$responseArray = array('id' => 'danger', 'message' => 'المنتج غير معرف لقاعدة البيانات الخاصة بك' );	
		} else {	
			//Fetch barcode
			while($barcodeinfo = mysqli_fetch_array($record0, MYSQLI_ASSOC)){
				$dbbc = $barcodeinfo['barcode'];
				$category = $barcodeinfo['category'];
				$description = $barcodeinfo['description'];

				if ( $dbbc == $bc ) {	
					$soldas = 'R';
					// nullify wholsesale Parameters
					$wsdescription = '';		$wsbc = '';					$itemsperbox = '';					$wsprice = '';		$invitemsperbox = '';
				} else {
					$dbwsbcs= $barcodeinfo['wsbc'];  					$dbwsbcs = explode(",",$dbwsbcs);
					$dbwsdescs = $barcodeinfo['wsdescription'];			$dbwsdescs = explode(",",$dbwsdescs);
					$dbipbx = $barcodeinfo['wsitemsperbox'];			$dbipbx = explode(",",$dbipbx);

					if ( count((array)$dbwsbcs) > 0 ) { 
						for($k=0;$k<count((array)$dbwsbcs);$k++){
							if (  $bc == $dbwsbcs[$k] ) {
								$wsbc = $dbwsbcs[$k];
								$wsdescription = $dbwsdescs[$k];
								$itemsperbox = $dbipbx[$k];
								$soldas = 'WS'; 
							}
						}
					}
				}
			}
			
			$record1 = mysqli_query($link,"SELECT * FROM `inventory` WHERE `bc`='$bc' OR FIND_IN_SET('$bc', wsbc)");
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

					$invwsbc = $inventory['wsbc'];							$invwsbc = explode(",",$invwsbc);
					$invipb = $inventory['itemsperbox'];					$invipb = explode(",",$invipb);
					$invwsprices = $inventory['wsprice'];					$invwsprices = explode(",",$invwsprices);

					if ( count((array)$invwsbc) > 0 ) { 
						for($i=0;$i<count((array)$invwsbc);$i++){
							if (  $wsbc == $invwsbc[$i] ) {
								$wsprice = $invwsprices[$i];
								$invitemsperbox = $invipb[$i];
							}
						}
					}


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
					// if ( $bc == $rbc ) { $soldas = 'R'; }
					// if ( $bc == $wsbc ) { $soldas = 'WS'; }
					
					$inventorydata = array('id' => $id,'category' => $category,'description' => $description,'wsdescription' => $wsdescription,'rbc' => $rbc,'vatablity' => $vatablity, 'qty' => $qty, 'cost' => $cost, 'saletype' => $saletype,'price' => $price,'disc' => $disc,'wsbc' => $wsbc ,'itemsperbox' => $itemsperbox ,'wsprice' => $wsprice ,'day' => $day ,'month' => $month ,'year' => $year ,'number' => $number ,'letter' => $letter,'soldas' => $soldas,'timeedited' => $timeedited,'timeadded' => $timeadded	);	
				}

				if ( $itemsperbox !== $invitemsperbox ) {
					$responseArray = array('id' => 'danger', 'message' => 'عدد حبات التفريد في الجمله في المحزون غير متطابق مع قاعدة البيانات');
				} elseif ( $dbbc !== $rbc ) { 
					$responseArray = array('id' => 'danger', 'message' => 'باركود منتج التفريد في المخزون غير متطابق مع قاعدة البيانات');
				} elseif ( $bc == $rbc &&  $saletype == 'wholesale' ) { 
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