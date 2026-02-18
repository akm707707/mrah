<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php';
session_start();
//print_r($_POST);	

if($_POST){
	$Arrays = [];		
	$Catdata = [];		
	
	if( !isset($_POST['bc'] ) || empty($_POST['bc']))	{
		$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإدخال باركود المنتج');
	} elseif ( !is_numeric($_POST['bc'])  )   {
		$responseArray = array('id' => 'danger', 'message' => 'رقم الباركود يجب أن يحتوي على أرقام فقط');
	} else {
		
		$bc = mysqli_real_escape_string($link, $_POST['bc']);

		if(isset($_POST['db']) && !empty($_POST['db']) && $_POST['db'] == 'own' )   {
			$record = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE `barcode`='$bc' OR FIND_IN_SET('$bc', wsbc) ");
			// $record = mysqli_query($link,"SELECT * FROM `opurchases` WHERE FIND_IN_SET(1, amount)");
		} else { 
			$record = mysqli_query($link,"SELECT * FROM `bcdb` WHERE `barcode`='$bc'");
		}
		
		$catrecord = mysqli_query($link,"SELECT * FROM `category`");
		while($catinfo = mysqli_fetch_array($catrecord, MYSQLI_ASSOC)){
			$Cat = $catinfo['category'];
			array_push($Catdata,$Cat);
		}
		
		if(@mysqli_num_rows($record) > 0){
			
			while($info = mysqli_fetch_array($record, MYSQLI_ASSOC)){
				if( $_POST['db'] == 'own' )   {
					$barcode = $info['barcode'];	
					$category = $info['category'];	
					$itemDescription = $info['description'];	
					
					$wsbc = $info['wsbc'];				$wsbc = array_slice($wsbc,0);
					$wsdesc = $info['wsdescription'];	$wsdesc = array_slice($wsdesc,0);
					$wsipb = $info['wsitemsperbox'];	$wsipb = array_slice($wsipb,0);
					
					if ( $barcode == $bc ) { $producttype = 'retail'; }
					for($i=0;$i<count($wsbcs);$i++){
						if ( $wsbcs[$i] == $bc ) { 
							$producttype = 'ws'; 
							$wsbc = $wsbc[$i];
							$wsdescription = $wsdesc[$i];
							$wsipb = $wsipb[$i];
						}
					}
					$data = array('barcode' => $barcode,'category' => $category,'itemDescription' => $itemDescription,'wsbc' => $wsbc,'wsdescription' => $wsdescription,'wsipb' => $wsipb,'producttype' => $producttype);	
				}
				if( $_POST['db'] == 'universal' )   {
					$barcode = $info['barcode'];	
					$itemDescription = $info['itemDescription'];	
					$brandname = $info['brandName'];
					$tradename = $info['tradeName'];
					$itemWeight = $info['itemWeight'];
					$unitNameAr = $info['unitNameAr'];
					$companyName = $info['companyName'];
					$data = array('barcode' => $barcode,'itemDescription' => $itemDescription,'brandname' => $brandname,'tradename' => $tradename,'itemWeight' => $itemWeight.' '.$unitNameAr,'companyName' => $companyName);	
				}
				array_push($Arrays,$data);
			}
			
			if( $_POST['db'] == 'own' )   {
			// Check if the same barcode has been purchased before to fetch it is data 
				$record = mysqli_query($link,"SELECT * FROM `purchases` WHERE `bc`='$bc'");
				$purchasenum = mysqli_num_rows($record);
				if(@mysqli_num_rows($record) > 0){
					$z = 0;	
					$Totalpurchaseinfo = [];	
					while($purchaseinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
						$cost = $purchaseinfo['cost'];	
						$supplier = $purchaseinfo['supplier'];	
						$timeadded = Time_Passed(date($purchaseinfo['timeadded']),'time');
						${'purchaseinfo'.$z} = array('cost' => $cost,'supplier' => $supplier,'timeadded' => $timeadded );	
						$z++;
					}
				}
			// Check if the same barcode exists in inventory and fetch its data to auto fill prices
				$record = mysqli_query($link,"SELECT * FROM `inventory` WHERE `bc`='$bc'");
				if(@mysqli_num_rows($record) > 0){
					while($inventoryinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
						$vatablity = $inventoryinfo['vatablity'];	
						$minqty = $inventoryinfo['minqty'];	
						$saletype = $inventoryinfo['saletype'];	
						$price = $inventoryinfo['price'];	
						$disc = $inventoryinfo['disc'];	
						$itemsperbox = $inventoryinfo['itemsperbox'];	
						$wsprice = $inventoryinfo['wsprice'];	
						$day = $inventoryinfo['day'];	
						$month = $inventoryinfo['month'];	
						$year = $inventoryinfo['year'];	
						$number = $inventoryinfo['number'];	
						$letter = $inventoryinfo['letter'];	
						$inventorydata = array('vatablity' => $vatablity,'minqty' => $minqty, 'saletype' => $saletype,'price' => $price,'disc' => $disc ,'itemsperbox' => $itemsperbox ,'wsprice' => $wsprice ,'day' => $day ,'month' => $month ,'year' => $year ,'number' => $number ,'letter' => $letter	);	
					}
				} else {
					$inventorydata = '';
				}
				$responseArray = array('id' => 'success', 'message' => $Arrays, 'catdata' => $Catdata , 'inventorydata' => $inventorydata, 'purchasenum' => $purchasenum );			
			}
			// Recently Added
			if( $_POST['db'] == 'universal' )   {
				$responseArray = array('id' => 'success', 'message' => $Arrays, 'catdata' => $Catdata );			
			}
		} else {	
			if( $_POST['db'] == 'own' )   { $errmsg = 'المنتج غير معرف جاري البحث في قاعدة البيانات الشامله'; } else { $errmsg = 'المنتج غير معرف'; }
			$responseArray = array('id' => 'danger', 'message' => $errmsg , 'catdata' => $Catdata );
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'من فضلك حاول في وقت لاحق');
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($purchasenum) && $purchasenum > 0 ){
		for($a=0;$a<$purchasenum;$a++){
			array_push($Totalpurchaseinfo,${'purchaseinfo'.$a}); 
		}	
		array_push($responseArray,$Totalpurchaseinfo); 
	}
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>