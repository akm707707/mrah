<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		//print_r($_POST);	
if($_POST){
	$time = time();
	$timeedited = Time_Passed(date($time),'time');
	if(!isset($_POST['id']) || empty($_POST['id']) )   {
		$responseArray = array('id' => 'danger', 'message' => 'id is missing');
	} elseif(!isset($_POST['productbarcode']) || empty($_POST['productbarcode']) )   {
		$responseArray = array('id' => 'danger', 'message' => 'productbarcode is missing');
	} elseif(!isset($_POST['vatablity']) || empty($_POST['vatablity']) )   {
		$responseArray = array('id' => 'danger', 'message' => 'vatability is not set');
	} elseif(!isset($_POST['productquantity']) || empty($_POST['productquantity']) )   {
		$responseArray = array('id' => 'danger', 'message' => 'productquantity is not set');
	} elseif(!isset($_POST['eachcost']) || empty($_POST['eachcost']) )   {
		$responseArray = array('id' => 'danger', 'message' => 'eachcost is not set');
	} elseif(!isset($_POST['finalcost']) || empty($_POST['finalcost']) )   {
		$responseArray = array('id' => 'danger', 'message' => 'finalcost is not set');
	} else {
		$productname = mysqli_real_escape_string($link, $_POST['productname']);
		$id = mysqli_real_escape_string($link, $_POST['id']);
		$productbarcode = mysqli_real_escape_string($link, $_POST['productbarcode']);
		$vatablity = mysqli_real_escape_string($link, $_POST['vatablity']);
		$productquantity = mysqli_real_escape_string($link, $_POST['productquantity']);
		$eachcost = mysqli_real_escape_string($link, $_POST['eachcost']);
		$purchasevat = mysqli_real_escape_string($link, $_POST['purchasevat']);
		$finalcost = mysqli_real_escape_string($link, $_POST['finalcost']);
		$suppliername = mysqli_real_escape_string($link, $_POST['supplier']);

		if(isset($_POST['supplier']) && !empty($_POST['supplier']))   {
			$supplier = mysqli_real_escape_string($link, $_POST['supplier']);		$supplierrecord = mysqli_query($link,"SELECT * FROM `suppliers` WHERE `suppliername`='$supplier'");
			if(@mysqli_num_rows($supplierrecord) > 0){ while($supplierinfo = mysqli_fetch_array($supplierrecord, MYSQLI_ASSOC)){ $supplierid = $supplierinfo['id']; }
			} else {
				$ins = mysqli_query($link,"INSERT INTO `suppliers`( `id`, `suppliername`, `suppliermobile`, `supplierlandline1`, `supplierlandline2`, `supplierwebsite`, `supplieremail`, `timeadded` )VALUES(	NULL,'$supplier',NULL,NULL,NULL,NULL,NULL,'$time' )");
				$supplierrecord = mysqli_query($link,"SELECT * FROM `suppliers` WHERE `suppliername`='$supplier'");
				if(@mysqli_num_rows($supplierrecord) > 0){ 	while($supplierinfo = mysqli_fetch_array($supplierrecord, MYSQLI_ASSOC)){ $supplierid = $supplierinfo['id']; }
				}
			}
		} else {		$supplierid = '';	}
			
		if(isset($_POST['wholesale']) && !empty($_POST['wholesale']))   {
			$saletype = mysqli_real_escape_string($link, $_POST['wholesale']);
			$wsprice = mysqli_real_escape_string($link, $_POST['wsprice']);
			$wsvat = mysqli_real_escape_string($link, $_POST['wsvat']);
			$wsprofitmargin = mysqli_real_escape_string($link, $_POST['wsprofitmargin']);
			$wsprofitpercentage = mysqli_real_escape_string($link, $_POST['wsprofitpercentage']);
			$wsmaxdiscounted = mysqli_real_escape_string($link, $_POST['wsmaxdiscounted']);
			$wsmaxdiscountpercentage = mysqli_real_escape_string($link, $_POST['wsmaxdiscountpercentage']);
		}

		if(isset($_POST['retail']) && !empty($_POST['retail']))   {
			$saletype = mysqli_real_escape_string($link, $_POST['retail']);
			$retailproductname = mysqli_real_escape_string($link, $_POST['retailproductname']);
			$retailproductbarcode = mysqli_real_escape_string($link, $_POST['retailproductbarcode']);
			$itemperunit = mysqli_real_escape_string($link, $_POST['rquantity']);
			$rquantity = $itemperunit * $productquantity;
			$rcost = mysqli_real_escape_string($link, $_POST['rcost']);
			$rprice = mysqli_real_escape_string($link, $_POST['rprice']);
			$rvat = mysqli_real_escape_string($link, $_POST['rvat']);
			$rprofitmargin = mysqli_real_escape_string($link, $_POST['rprofitmargin']);
			$rprofitpercentage = mysqli_real_escape_string($link, $_POST['rprofitpercentage']);
			$rmaxdiscounted = mysqli_real_escape_string($link, $_POST['rmaxdiscounted']);
			$rmaxdiscountpercentage = mysqli_real_escape_string($link, $_POST['rmaxdiscountpercentage']);
		}
			
		if(isset($_POST['wholesale']) && !empty($_POST['wholesale']) && isset($_POST['retail']) && !empty($_POST['retail']) )   {	$saletype = 'Both';	}
			
		if ( $saletype == 'Both' ) {
			$update = mysqli_query($link,"UPDATE `purchases` SET 
			 `barcode` = '$productbarcode' 
			,`taxable` = '$vatablity' 
			,`quantity` = '$productquantity' 
			,`cost` = '$eachcost' 
			,`tax` = '$purchasevat' 
			,`finalcost` = '$finalcost' 
			,`saletype` = '$saletype' 
			,`wsprice` = '$wsprice' 
			,`wssaletax` = '$wsvat' 
			,`wsprofitmargin` = '$wsprofitmargin' 
			,`wsprofitpercentage` = '$wsprofitpercentage' 
			,`wsmaxdiscounted` = '$wsmaxdiscounted' 
			,`wsmaxdiscountpercentage` = '$wsmaxdiscountpercentage' 
			,`retailproductbarcode` = '$retailproductbarcode' 
			,`rquantity` = '$rquantity' 
			,`rcost` = '$rcost' 
			,`rprice` = '$rprice' 
			,`rtax` = '$rvat' 
			,`rprofitmargin` = '$rprofitmargin' 
			,`rprofitpercentage` = '$rprofitpercentage' 
			,`rmaxdiscounted` = '$rmaxdiscounted' 
			,`rmaxdiscountpercentage` = '$rmaxdiscountpercentage' 
			,`supplierid` = '$supplierid' 
			,`timeedited` = '$time' 
			WHERE `id`='$id'");	
		}
					
		if ( $saletype == 'wholesale' ) {
			$update = mysqli_query($link,"UPDATE `purchases` SET 
			 `barcode` = '$productbarcode' 
			,`taxable` = '$vatablity' 
			,`quantity` = '$productquantity' 
			,`cost` = '$eachcost' 
			,`tax` = '$purchasevat' 
			,`finalcost` = '$finalcost' 
			,`saletype` = '$saletype' 
			,`wsprice` = '$wsprice' 
			,`wssaletax` = '$wsvat' 
			,`wsprofitmargin` = '$wsprofitmargin' 
			,`wsprofitpercentage` = '$wsprofitpercentage' 
			,`wsmaxdiscounted` = '$wsmaxdiscounted' 
			,`wsmaxdiscountpercentage` = '$wsmaxdiscountpercentage' 
			,`retailproductbarcode` = NULL
			,`rquantity` = NULL
			,`rcost` = NULL
			,`rprice` = NULL
			,`rtax` = NULL
			,`rprofitmargin` = NULL
			,`rprofitpercentage` = NULL
			,`rmaxdiscounted` = NULL
			,`rmaxdiscountpercentage` = NULL
			,`supplierid` = '$supplierid' 
			,`timeedited` = '$time' 
			WHERE `id`='$id'");	
		}

		if ( $saletype == 'retail' ) {
			$update = mysqli_query($link,"UPDATE `purchases` SET 
			 `barcode` = '$productbarcode' 
			,`taxable` = '$vatablity' 
			,`quantity` = '$productquantity' 
			,`cost` = '$eachcost' 
			,`tax` = '$purchasevat' 
			,`finalcost` = '$finalcost' 
			,`saletype` = '$saletype' 
			,`wsprice` = NULL
			,`wssaletax` = NULL
			,`wsprofitmargin` = NULL
			,`wsprofitpercentage` = NULL
			,`wsmaxdiscounted` = NULL
			,`wsmaxdiscountpercentage` = NULL
			,`retailproductbarcode` = '$retailproductbarcode' 
			,`rquantity` = '$rquantity' 
			,`rcost` = '$rcost' 
			,`rprice` = '$rprice' 
			,`rtax` = '$rvat' 
			,`rprofitmargin` = '$rprofitmargin' 
			,`rprofitpercentage` = '$rprofitpercentage' 
			,`rmaxdiscounted` = '$rmaxdiscounted' 
			,`rmaxdiscountpercentage` = '$rmaxdiscountpercentage' 
			,`supplierid` = '$supplierid' 
			,`timeedited` = '$time' 
			WHERE `id`='$id'");	
		}

		if ($update) {
			$timeadded = Time_Passed(date($time),'time');
			if ( $saletype == 'wholesale' ) {
				$retailproductname = NULL;
				$retailproductbarcode = NULL;
				$itemperunit = NULL;
				$rquantity = NULL;
				$rcost = NULL;
				$rprice = NULL;
				$rvat = NULL;
				$rprofitmargin = NULL;
				$rprofitpercentage = NULL;
				$rmaxdiscounted = NULL;
				$rmaxdiscountpercentage = NULL;
			}

			if ( $saletype == 'retail' ) {
				$wsprice = NULL;
				$wsvat = NULL;
				$wsprofitmargin = NULL;
				$wsprofitpercentage = NULL;
				$wsmaxdiscounted = NULL;
				$wsmaxdiscountpercentage = NULL;
			}

			$updated = array('id' => $id,'productname' => $productname,'barcode' => $productbarcode,'taxable' => $vatablity,'quantity' => $productquantity,'cost' => $eachcost,'tax' => $purchasevat,'finalcost' => $finalcost,'saletype' => $saletype,'wsprice' => $wsprice,'wssaletax' => $wsvat,'wsprofitmargin' => $wsprofitmargin,'wsprofitpercentage' => $wsprofitpercentage,'wsmaxdiscounted' => $wsmaxdiscounted,'wsmaxdiscountpercentage' => $wsmaxdiscountpercentage,'retailproductname' => $retailproductname, 'retailproductbarcode' => $retailproductbarcode,'itemperunit' => $itemperunit,'rquantity' => $rquantity,'rcost' => $rcost,'rprice' => $rprice,'rtax' => $rvat,'rprofitmargin' => $rprofitmargin,'rprofitpercentage' => $rprofitpercentage,'rmaxdiscounted' => $rmaxdiscounted,'rmaxdiscountpercentage' => $rmaxdiscountpercentage,'suppliername' => $suppliername,'timeadded' =>'','timeedited' => $timeedited );	

			$responseArray = array('id' => 'success', 'message' => 'updated', 'data' => $updated );

			
		} else {
			$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات');
		}
	}
	
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>