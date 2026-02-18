<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require '../inc/functions.php'; 		
session_start();
date_default_timezone_set('Asia/Riyadh');
///////////////////////////////////////////////////
$daystartdate = date("Y-m-d", strtotime("today midnight")); 						//echo $daystartdate; echo '</br>';
$daystarttime = date("H:i", strtotime("today midnight")); 							//echo $daystarttime; echo '</br>';
$daystart = date("Y-m-d H:i", strtotime("today midnight")); 						//echo $daystart; echo '</br>';
$todaystartunix =  strtotime(date("Y-m-d H:i", strtotime("today midnight")));		//echo $todaystartunix; echo '</br>';
$thisweekstartunix = strtotime(date('Y-m-d', strtotime("sunday -1 week")));			//echo $thisweekstartunix; echo '</br>';
$thismonthstartunix = strtotime(date('Y-m-01', strtotime("this month")));			//echo $thismonthstartunix; echo '</br>';
$thisyearstartunix = strtotime(date('Y-01-01', strtotime("this year")));			//echo $thisyearstartunix; echo '</br>';
$weekagounix =  strtotime(date("Y-m-d H:i", strtotime("-1 week midnight")));		//echo $weekagounix; echo '</br>';
$monthagounix =  strtotime(date("Y-m-d H:i", strtotime("-1 month midnight")));		//echo $monthagounix; echo '</br>';
$yearagounix =  strtotime(date("Y-m-d H:i", strtotime("-1 year midnight")));		//echo $yearagounix; echo '</br>';
///////////////////////////////////////////////////

if ( $_SERVER["REQUEST_METHOD"] != "POST") {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
} else {
	$time = time();
	if ( !$_SESSION ) {
		$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $_SESSION');	
	} else {
		if ( !isset($_SESSION['loggedin']) || $_SESSION["loggedin"] !== true )   {
			$responseArray = array('id' => 'danger', 'message' => 'لم تقم بتسجيل الدخول');			
		} elseif ( !isset($_SESSION['userstatus']) || $_SESSION["userstatus"] != 1 )   {
			$responseArray = array('id' => 'danger', 'message' => 'الحساب غير نشط');				
		} elseif ( !isset($_SESSION['userclearance']) || empty($_SESSION["userclearance"])  )   {
			$responseArray = array('id' => 'danger', 'message' => 'لايمكن الوصول للتصاريح');			
		} elseif ( !str_contains($_SESSION["userclearance"], 'setting')  )   {
			$responseArray = array('id' => 'danger', 'message' => 'العمليه غير مصرحه');				
		} else {						
			$identifier = $_SESSION["identifier"];
			$entity = mysqli_fetch_array(mysqli_query($link,"SELECT * FROM `entities` WHERE `id`='$identifier'"), MYSQLI_ASSOC);
			$Aname = $entity['Aname'];
			$Ename = $entity['Ename'];
			$crid = $entity['crid'];
			$taxid = $entity['taxid'];
			$Evat = $entity['vat'];
			$fiscal = $entity['fiscal'];
			$username = $_SESSION['username'];
			$userid = $_SESSION['userid'];
			$clearance = $_SESSION['userclearance'];
			$empname = $_SESSION['name'];

/*
$daystartdate = date("Y-m-d", strtotime("today midnight")); 						//echo $daystartdate; echo '</br>';
$daystarttime = date("H:i", strtotime("today midnight")); 							//echo $daystarttime; echo '</br>';
$daystart = date("Y-m-d H:i", strtotime("today midnight")); 						//echo $daystart; echo '</br>';
$todaystartunix =  strtotime(date("Y-m-d H:i", strtotime("today midnight")));		//echo $todaystartunix; echo '</br>';
$thisweekstartunix = strtotime(date('Y-m-d', strtotime("sunday -1 week")));			//echo $thisweekstartunix; echo '</br>';
$thismonthstartunix = strtotime(date('Y-m-01', strtotime("this month")));			//echo $thismonthstartunix; echo '</br>';
$thisyearstartunix = strtotime(date('Y-01-01', strtotime("this year")));			//echo $thisyearstartunix; echo '</br>';
$weekagounix =  strtotime(date("Y-m-d H:i", strtotime("-1 week midnight")));		//echo $weekagounix; echo '</br>';
$monthagounix =  strtotime(date("Y-m-d H:i", strtotime("-1 month midnight")));		//echo $monthagounix; echo '</br>';
$yearagounix =  strtotime(date("Y-m-d H:i", strtotime("-1 year midnight")));		//echo $yearagounix; echo '</br>';
*/
			$Evat = 15;
			// fetch today sales
			// $record = mysqli_query($link,"SELECT * FROM `invoices` WHERE timeadded >= '$todaystartunix' ");
			$record = mysqli_query($link,"SELECT * FROM invoices ");
			$invoicenum = mysqli_num_rows($record);
			if(@mysqli_num_rows($record) > 0){
				$qtyall = 0;	$costall = 0;		$discountall = 0;	$totalpriceall = 0;	
				$vatall = 0;	$costvatall = 0;	$zatcavat = 0;
				while($entityinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
					$qty = $entityinfo['qty'];					$qtyall +=	$qty;
					$cost = $entityinfo['cost'];				$costall +=	$cost;
					$costvatall += ( $cost * ( 1 - ( 1/ ( 1 + $Evat / 100) ) ) );
					$discount = $entityinfo['discount'];		$discountall +=	$discount;
					$totalprice = $entityinfo['totalprice'];	$totalpriceall +=	$totalprice;
					$vat = $entityinfo['vat'];
					if ( $discount == 0 ) { 
						$vatall +=	$vat; 
					} else { 
						$vatall += ( $totalprice * ( 1 - ( 1/ ( 1 + $Evat / 100) ) ) );
					}
				}
				$zatcavat = $vatall - $costvatall;	
				$costall = round($costall, 2);
				$discountall = round($discountall, 2);
				$totalpriceall = round($totalpriceall, 2);
				$vatall = round($vatall, 2);
				$costvatall = round($costvatall, 2);
				$zatcavat = round($zatcavat, 2);
								
			}
			
			$responseArray = array('id' => 'success', 'invoicenum' => $invoicenum);
		}
	}
} 

end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	// if ( isset($usersnum) && $usersnum > 0 ){
		// for($a=0;$a<$usersnum;$a++){
			// array_push($Totalusersinfo,${'usersinfo'.$a}); 
		// }	
		// array_push($responseArray,$Totalusersinfo); 
	// }
	if ( isset($invoicenum) && $invoicenum > 0 ){
		$allinvoices = array('costall' => $costall, 'discountall' => $discountall, 'totalpriceall' => $totalpriceall, 'vatall' => $vatall, 'costvatall' => $costvatall, 'zatcavat' => $zatcavat );
		array_push($responseArray,$allinvoices); 
	}
	
	

	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
	
} else {    echo $responseArray['message'];		}		// else just display the message
?>