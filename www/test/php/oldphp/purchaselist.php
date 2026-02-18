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
			
			// add leading zeroes (4 digits)
			$identifier = str_pad($identifier, 4, '0', STR_PAD_LEFT);
			$directory = "../userdata/".$identifier."/Pbills";	

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
				$pas = $purchase['pas'];	// purchased as ( ws, retail)
				$bc = $purchase['bc'];
				$vatablity = $purchase['vatablity'];
				$qty = $purchase['qty'];
				$cost = $purchase['cost'];
				$vat = $purchase['vat'];
				$totalcost = $purchase['totalcost'];
				$saletype = $purchase['saletype'];
				$supplier = $purchase['supplier'];
				$billid = $purchase['billid'];

				if( !empty($billid))	{	
					$billid = str_pad($billid, 6, '0', STR_PAD_LEFT);
					if ( glob($directory.'/'.$billid.'*') )  {						//Check existing files
						// $existing = glob($directory.'/'.$billid.'*');
						$existing = glob($directory.'/'.$billid.'*', GLOB_BRACE);
						$info = pathinfo($existing[0]);
						// $billid = $billid.$info["extension"];
						$billid = $info['basename'];
					}
				}				

				// $buid = $purchase['buid'];
				$timeadded = Time_Passed(date($purchase['timeadded']),'time');

				if ( $pas == 'retail' ) {
					$record2 = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE `barcode` = $bc ");
					if(@mysqli_num_rows($record2) > 0){
						while($barcode = mysqli_fetch_array($record2, MYSQLI_ASSOC)){
							$description = $barcode['description'];
							$ipb = '';
						}
					}
				}
				if ( $pas == 'ws' ) {
					$record2 = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE FIND_IN_SET('$bc', wsbc) ");
					if(@mysqli_num_rows($record2) > 0){					
						while($barcode = mysqli_fetch_array($record2, MYSQLI_ASSOC)){
							$wsbc = $barcode['wsbc'];				$wsbc = explode(",",$wsbc);
							$wsdesc = $barcode['wsdescription'];	$wsdesc = explode(",",$wsdesc);
							$wsipb = $barcode['wsitemsperbox'];		$wsipb = explode(",",$wsipb);
							
							for($i=0;$i<count((array)$wsbc);$i++){
								if ( $wsbc[$i] == $bc ) { 
									$description = $wsdesc[$i];
									// $ipb = $wsipb[$i];
								}
							}
						}
					}
				}
			
				// store BC to fetch names
				// $bcArray1[] = $bc;

				${'purchase'.$z} = array('id' => $id,'pas' => $pas,'bc' => $bc,'description' => $description,/*'ipb' => $ipb,*/'vatablity' => $vatablity,'qty' => $qty,'cost' => $cost,'vat' => $vat,'totalcost' => $totalcost,'saletype' => $saletype,'supplier' => $supplier,'billid' => $billid,'timeadded' => $timeadded );	

				$z++;
			}
			
			$responseArray = array('id' => 'success', 'purchasenum' => $purchasenum);
			
		} else { 																				
			$responseArray = array('id' => 'danger', 'purchasenum' => $purchasenum);
		}

		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

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