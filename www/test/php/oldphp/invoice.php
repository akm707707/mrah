<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		
session_start();
//print_r($_POST);	
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
			$empname = $_SESSION['name'];

			$record0 = mysqli_query($link,"SELECT * FROM `cbills` ORDER BY id DESC LIMIT 1 ");
			if(@mysqli_num_rows($record0) > 0){
				while ( $cbillsinfo = mysqli_fetch_array( $record0, MYSQLI_ASSOC ) ){ 	$bid = $cbillsinfo['id'];	}
				$bid = $bid + 1;
			} else { $bid = 1; }
				
			if(isset($_POST['customername']) && !empty($_POST['customername']) )   {
				$customername = mysqli_real_escape_string($link, $_POST['customername']);
				$record1 = mysqli_query($link,"SELECT * FROM `customers` WHERE `name`='$customername' ");
				if(@mysqli_num_rows($record1) > 0){
					while($cusinfo = mysqli_fetch_array($record1, MYSQLI_ASSOC)){
							$cusid = $cusinfo['id'];
							$cusmobile = $cusinfo['mobile'];
							$cusbalance = $cusinfo['balance'];
							$custransactions = $cusinfo['transactions'];
					}
				} else { $customername = '';	$cusid = NULL; }
			} else { $customername = ''; 	$cusid = NULL; }
			
			if ( $_POST['paymenttype'] == 'debt' ) { 
				if( empty($_POST['customername']) ) {
					$responseArray = array('id' => 'danger', 'message' => 'يجب إدخال اسم العميل لفواتير الدين');		goto end;	
				}
			} 
			
			$barcodes = array_slice($_POST['barcodes'],1);
			$soldas = array_slice($_POST['soldas'],1);
			$itemdescription = array_slice($_POST['itemdescription'],1);
			$quantity = array_slice($_POST['quantity'],1);
			$pretaxprice = array_slice($_POST['pretaxprice'],1);
			$vat = array_slice($_POST['vat'],1);
			$discount = array_slice($_POST['discount'],1);
			$price = array_slice($_POST['price'],1);

			$totalvat = mysqli_real_escape_string($link, $_POST['totalvat']);
			$totaldiscount = mysqli_real_escape_string($link, $_POST['totaldiscount']);
			$totalprice = mysqli_real_escape_string($link, $_POST['totalprice']);
			if( !isset($_POST['paymenttype'] ) || empty($_POST['paymenttype']))	{
				$responseArray = array('id' => 'danger', 'message' => 'لم يتم تحديد طريقة الدقع');		goto end;	
			} else {
				$paymenttype = mysqli_real_escape_string($link, $_POST['paymenttype']);
				if ( $paymenttype == 'debt' ) { $paid = 0; } else { $paid = 1; }
			}
				
			if(count($barcodes) == 0) {
				$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإضافة منتجات ');		goto end;	
			} elseif (count($soldas) == 0) {
				$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإضافة منتجات');		goto end;	
			} elseif (count($itemdescription) == 0) {
				$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإضافة منتجات');		goto end;	
			} elseif (count($quantity) == 0) {
				$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإضافة منتجات');		goto end;	
			} elseif (count($pretaxprice) == 0) {
				$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإضافة منتجات');		goto end;	
			} elseif (count($vat) == 0) {
				$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإضافة منتجات');		goto end;	
			} elseif (count($discount) == 0) {
				$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإضافة منتجات');		goto end;	
			} elseif (count($price) == 0) {
				$responseArray = array('id' => 'danger', 'message' => 'لم تقم بإضافة منتجات');		goto end;	
			} else {
				$barcodesarr = [];
				$soldasarr = [];
				$itemdescriptionarr = [];
				$quantityarr = [];
				$costarr = [];
				$pretaxpricearr = [];
				$vatarr = [];
				$discountarr = [];
				$pricearr = [];
				$allitems = [];
				
				for($i=0;$i<count($barcodes);$i++){
					$record2 = mysqli_query($link,"SELECT * FROM `inventory` WHERE `bc`='$barcodes[$i]' ");
					if(@mysqli_num_rows($record2) > 0){
						while ( $invinfo = mysqli_fetch_array( $record2, MYSQLI_ASSOC ) ){ 	
							$qty0 = $invinfo['qty'];	
							$cost = $invinfo['cost'];	
						}
						$newqty = $qty0 - $quantity[$i];
						$update1 = mysqli_query($link,"UPDATE `inventory` SET `qty`='$newqty' WHERE `bc`='$barcodes[$i]' ");
						if (!$update1) { $responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات$update1');	goto end; }
					} else {
						$record2 = mysqli_query($link,"SELECT * FROM `inventory` WHERE `wsbc`='$barcodes[$i]' ");
						if(@mysqli_num_rows($record2) > 0){
							while ( $invinfo = mysqli_fetch_array( $record2, MYSQLI_ASSOC ) ){ 	
								$qty0 = $invinfo['qty'];
								$cost = $invinfo['cost'];	
								$itemsperbox0 = $invinfo['itemsperbox']; 	
							}
							$newqty = $qty0 - ( $quantity[$i] * $itemsperbox0 );
							$update2 = mysqli_query($link,"UPDATE `inventory` SET `qty`='$newqty' WHERE `wsbc`='$barcodes[$i]' ");
							if (!$update2) { $responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات$update2');	goto end; }
						} else { $responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على المنتج في المخزون $record2');	goto end; }
					}
									
					array_push($barcodesarr,$barcodes[$i]);
					array_push($soldasarr,$soldas[$i]);
					array_push($quantityarr,$quantity[$i]);
					array_push($costarr,$cost);
					array_push($pretaxpricearr,$pretaxprice[$i]);
					array_push($vatarr,$vat[$i]);
					array_push($discountarr,$discount[$i]);
					// array_push($pricearr,$price[$i]);
					array_push($pricearr,(float)$price[$i]+(float)$discount[$i]);
// $salary = (float)$salary; 
					${'item'.$i} = array('bc' => $barcodes[$i],'soldas' => $soldas[$i],'quantity' => $quantity[$i],'pretaxprice' => $pretaxprice[$i],'vat' => $vat[$i],'discount' => $discount[$i],'price' => $price[$i], );
					
					$imploder = implode(",",${'item'.$i});
					array_push($allitems,$imploder);
					
					$ins0 = mysqli_query($link,"INSERT INTO `invoices`( `id`,`bid`,`cid`,`bc`,`itemdesc`,`qty`,`cost`,`ptprice`,`vat`,`discount`,`totalprice`,`ptype`,`paid`,`cashier`	,`timeadded`,`timeedited` )VALUES(	NULL,'$bid','$cusid','$barcodes[$i]','$itemdescription[$i]','$quantity[$i]','$cost','$pretaxprice[$i]','$vat[$i]','$discount[$i]','$price[$i]','$paymenttype','$paid','$empname','$time', NULL )");
					
					if (!$ins0) { $responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات$ins0');	goto end; }
					
				}
				


				$barcodesarr = implode(",",$barcodesarr);
				$soldasarr = implode(",",$soldasarr);
				$quantityarr = implode(",",$quantityarr);
				$costarr = implode(",",$costarr);
				$itemdescriptionarr = implode(",",$itemdescriptionarr);
				$pretaxpricearr = implode(",",$pretaxpricearr);
				$vatarr = implode(",",$vatarr);
				$discountarr = implode(",",$discountarr);
				$pricearr = implode(",",$pricearr);

				$allitems = implode(":",$allitems);
						
				$ins = mysqli_query($link,"INSERT INTO `cbills`( `id`,`cid`,`allitems`,`bcs`,`soldas`,`qtys`,`costs`,`ptunitprice`,`vat`,`discount`,`itemprice`,`totalvat`,`totaldiscount`,`totalprice`,`ptype`,`cashier`,`timeadded`,`timeedited` )VALUES(	NULL,'$cusid','$allitems','$barcodesarr','$soldasarr','$quantityarr','$costarr','$pretaxpricearr','$vatarr','$discountarr','$pricearr','$totalvat','$totaldiscount','$totalprice','$paymenttype','$empname','$time', NULL )");

				if ($ins) {
					// Update customer stats if any
					if( !empty($customername) ) {
						if ( $paymenttype == 'debt' ) {
							$cusbalance = $cusbalance + $totalprice;
						}
						$custransactions = $custransactions +1;
						$update2 = mysqli_query($link,"UPDATE `customers` SET `balance`='$cusbalance',`transactions`='$custransactions' WHERE `id`='$cusid'");
					}

					$lastid = $bid +1;
					$responseArray = array('id' => 'success', 'message' => 'تمت الإضافه بنجاح', 'lastid' => $lastid); 
				} else {
					$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات$ins');
				}
			}
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم$_POST');
}
end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	
	// if ( isset($customernum) && $customernum > 0 ){	
		// for($a=0;$a<$customernum;$a++){ 
			// array_push($Totalcustomers,${'customerinfo'.$a}); 
		// }	
		// array_push($responseArray,$Totalcustomers); 
	// }
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>