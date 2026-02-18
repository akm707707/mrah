<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require '../inc/functions.php'; 		
session_start();
date_default_timezone_set('Asia/Riyadh');
$targetdate = $Gdate;
$now=time();

///////////////////////////////////////////////////
// $daystartdate = date("Y-m-d", strtotime("today midnight")); 						//echo $daystartdate; echo '</br>';
// $daystarttime = date("H:i", strtotime("today midnight")); 							//echo $daystarttime; echo '</br>';
// $daystart = date("Y-m-d H:i", strtotime("today midnight")); 						//echo $daystart; echo '</br>';
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

			if(isset($_POST['key']) && !empty($_POST['key']) )   {				
				if ( $_POST['key'] == 'daily' ) { $since = $todaystartunix; }			// Get today Unix
				if ( $_POST['key'] == 'weekly' ) { $since = $thisweekstartunix; }		// Get This week Unix
				if ( $_POST['key'] == 'monthly' ) { $since = $thismonthstartunix; }		// Get This month Unix
				if ( $_POST['key'] == 'yearly' ) { $since = $thisyearstartunix; }		// Get This year Unix
				if ( $_POST['key'] == 'all' ) { $since = '28127847'; }					// Get alltimes Unix

				// Transversing in Daily operations
				if(isset($_POST['key']) && !empty($_POST['key']) && isset($_POST['direction']) && !empty($_POST['direction'])){
					if ( $_POST['key'] == 'daily' && $_POST['direction'] == 'previous' ) {
						$now = strtotime($_POST['date'])-1;								// last second of yesterday
						$targetdate = date('Y-m-d',strtotime($_POST['date'])-1);		// Date of previous day 
						$since = strtotime(date('Y-m-d',strtotime($_POST['date'])-1));	// Unix Start of previous day 
					}
					if ( $_POST['key'] == 'daily' && $_POST['direction'] == 'next' ) {
						$now = strtotime('+2 day', strtotime($_POST['date'])-1);		//Last second of tomorrow
						$targetdate = date('Y-m-d',strtotime($_POST['date'])+86400);	// Date of previous day 
						$since = strtotime('+1 day', strtotime($_POST['date']));		//Unix Start tomorrow
					}
				}

				//Transversing in Weekly operations
				if(isset($_POST['key']) && !empty($_POST['key']) && isset($_POST['direction']) && !empty($_POST['direction'])){
					if ( $_POST['key'] == 'weekly' && $_POST['direction'] == 'previous' ) {
						$oneweekago = date('Y-m-d', strtotime('-6 days', strtotime($_POST['date'])));
						$date = new DateTime($oneweekago);
						$date->modify('last sunday');
						$targetdate = $date->format('Y-m-d');			//first day of previous week
						$since = strtotime($targetdate);				//Unix Start of previous week
						$now = strtotime($targetdate)+604799;			//Last Unix of previous week
					}
					if ( $_POST['key'] == 'weekly' && $_POST['direction'] == 'next' ) {
						$oneweekahead = date('Y-m-d', strtotime('+8 days', strtotime($_POST['date'])));
						$date = new DateTime($oneweekahead);
						$date->modify('last sunday');
						$targetdate = $date->format('Y-m-d');			//first day of next week
						$since = strtotime($targetdate);				//Unix Start of next week
						$now = strtotime($targetdate)+604799;			//Last Unix of next week
					}
				}

				//Transversing in Monthly operations
				if(isset($_POST['key']) && !empty($_POST['key']) && isset($_POST['direction']) && !empty($_POST['direction'])){
					if ( $_POST['key'] == 'monthly' && $_POST['direction'] == 'previous' ) {
						$oneweekago = date('Y-m-d', strtotime('-4 weeks', strtotime($_POST['date'])));
						$date = new DateTime($oneweekago);
						$date->modify('first day of this month');
						$targetdate = $date->format('Y-m-d');			//first day of previous week
						$since = strtotime($targetdate);				//Unix Start of previous week
						$now = strtotime($targetdate)+2591999;			//Last Unix of previous week
					}
					if ( $_POST['key'] == 'monthly' && $_POST['direction'] == 'next' ) {
						$oneweekahead = date('Y-m-d', strtotime('+1 month', strtotime($_POST['date'])));
						$date = new DateTime($oneweekahead);
						$date->modify('first day of this month');
						$targetdate = $date->format('Y-m-d');			//first day of next week
						$since = strtotime($targetdate);				//Unix Start of next week
						$now = strtotime($targetdate)+2591999;			//Last Unix of next week
					}
				}

				//Transversing in Yearly operations
				if(isset($_POST['key']) && !empty($_POST['key']) && isset($_POST['direction']) && !empty($_POST['direction'])){
					if ( $_POST['key'] == 'yearly' && $_POST['direction'] == 'previous' ) {
						$oneweekago = date('Y-m-d', strtotime('-1 year', strtotime($_POST['date'])));
						$date = new DateTime($oneweekago);
						$date->modify('first day of this month');
						$targetdate = $date->format('Y-m-d');			//first day of previous week
						$since = strtotime($targetdate);				//Unix Start of previous week
						$now = strtotime($targetdate)+2591999;			//Last Unix of previous week
					}
					if ( $_POST['key'] == 'yearly' && $_POST['direction'] == 'next' ) {
						$oneweekahead = date('Y-m-d', strtotime('+1 year', strtotime($_POST['date'])));
						$date = new DateTime($oneweekahead);
						$date->modify('first day of this month');
						$targetdate = $date->format('Y-m-d');			//first day of next week
						$since = strtotime($targetdate);				//Unix Start of next week
						$now = strtotime($targetdate)+2591999;			//Last Unix of next week
					}
				}
				 

				// sales, cost of sales, discounts, pretaxprofit, posttaxprofit, tax(VAT)
				$record = mysqli_query($link,"SELECT * FROM `invoices` WHERE timeadded >= '$since' AND timeadded <= '$now' ");
				$invoicenum = mysqli_num_rows($record);
				$qtyall = 0;	$costall = 0;		$discountall = 0;	$totalpriceall = 0;	
				$vatall = 0;	$costvatall = 0;	$zatcavat = 0;		$opexall = 0;
				if(@mysqli_num_rows($record) > 0){
					while($invoiceinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
						$qty = $invoiceinfo['qty'];					$qtyall +=	$qty;
						$cost = $invoiceinfo['cost'];				$costall +=	$cost;
						$costvatall += ( $cost * ( 1 - ( 1/ ( 1 + $Evat / 100) ) ) );
						$discount = $invoiceinfo['discount'];		$discountall +=	$discount;
						$totalprice = $invoiceinfo['totalprice'];	$totalpriceall +=	(float)$totalprice;
						$vat = $invoiceinfo['vat'];
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
				
				$timestamp = strtotime($targetdate);
				$daysInMonth = date("t", $timestamp);
				// OPEX for operational Expenses
				$record = mysqli_query($link,"SELECT * FROM `opex` WHERE start <= '$since' AND end >= '$now' ");
				$opexnum = mysqli_num_rows($record);
				if(@mysqli_num_rows($record) > 0){
					while($opexinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
						$cycle = $opexinfo['cycle'];					
						$cost = $opexinfo['cost'];					
						if ( $cycle == 'yearly' ) { 
							if ( $_POST['key'] == 'daily' ) { $opexall += ( $cost / 365 ); }
							if ( $_POST['key'] == 'weekly' ) { $opexall += ( $cost / 52 ); }	//7.02
							if ( $_POST['key'] == 'monthly' ) { $opexall += ( $cost / 12 ); }
							if ( $_POST['key'] == 'yearly' ) { $opexall += ( $cost ); }
							if ( $_POST['key'] == 'all' ) { $opexall += ( $cost ); }
						} else {
							// $timestamp = strtotime($targetdate);
							// $daysInMonth = date("t", $timestamp);
							if ( $_POST['key'] == 'daily' ) { $opexall += ( $cost / $daysInMonth ); }
							if ( $_POST['key'] == 'weekly' ) { $opexall += ( $cost / ($daysInMonth/7) ); }	//7.04
							if ( $_POST['key'] == 'monthly' ) { $opexall += ( $cost ); }
							if ( $_POST['key'] == 'yearly' ) { $opexall += ( $cost ); }
							if ( $_POST['key'] == 'all' ) { $opexall += ( $cost ); }
						}
					}
				}
				
				// OPEX for Employee Salary
				$record = mysqli_query($link,"SELECT * FROM `users` WHERE status > 0 ");
				$usernum = mysqli_num_rows($record);
				if(@mysqli_num_rows($record) > 0){
					while($userinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
						$salary = $userinfo['salary'];					$salary = (float)$salary; 
						$allowences = $userinfo['allowences'];			$allowences = (float)$allowences; 
						$timeadded = $userinfo['timeadded'];
						// get how many months employee is on duty
						$date1 = new DateTime();
						$date1->setTimestamp($timeadded); //<--- Pass a UNIX TimeStamp
						$date1->format('H:i'); //"prints" 20:26
						$date2 = new DateTime($targetdate);
						$interval = $date1->diff($date2);
						$months = $interval->m;
						$months += ($interval->y * 12);

						if ( $_POST['key'] == 'daily' && $months > 0 ) { 
							$opexall += ( ($salary+$allowences) / $daysInMonth ); 
						}
						if ( $_POST['key'] == 'weekly' && $months > 0 ) { 
							$opexall += ( ($salary+$allowences) / ($daysInMonth/7) ); 
						}	
						if ( $_POST['key'] == 'monthly' && $months > 0 ) { 
							$opexall += ( ($salary+$allowences) ); 
						}
						if ( $_POST['key'] == 'yearly' && $months > 0) { 
							if ( $months > 12 ) {
								$opexall += ( ($salary+$allowences) * 12 ); 
							} else {
								$opexall += ( ($salary+$allowences) * $months ); 
							}
						}
						if ( $_POST['key'] == 'all' && $months > 0 ) { 
								$opexall += ( ($salary+$allowences) * $months ); 
						}

						// if ( $_POST['key'] == 'daily' ) { $opexall += ( ($salary+$allowences) / $daysInMonth ); }
						// if ( $_POST['key'] == 'weekly' ) { $opexall += ( ($salary+$allowences) / ($daysInMonth/7) ); }	
						// if ( $_POST['key'] == 'monthly' ) { $opexall += ( ($salary+$allowences) ); }
						// if ( $_POST['key'] == 'yearly' ) { $opexall += ( ($salary+$allowences)*12 ); }
						// if ( $_POST['key'] == 'all' ) { $opexall += ( ($salary+$allowences) ); }
					}
				}
				$opexall = round($opexall, 2);									
				
				$responseArray = array('id' => 'success', 'invoicenum' => $invoicenum, 'opexnum' => $opexnum, 'targetdate' => $targetdate, 'costall' => $costall, 'discountall' => $discountall, 'totalpriceall' => $totalpriceall, 'vatall' => $vatall, 'costvatall' => $costvatall, 'zatcavat' => $zatcavat, 'opexall' => $opexall);
			}
		}
	}
} 

end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	// if ( isset($invoicenum) && $invoicenum > 0 ){
		// $allinvoices = array('costall' => $costall, 'discountall' => $discountall, 'totalpriceall' => $totalpriceall, 'vatall' => $vatall, 'costvatall' => $costvatall, 'zatcavat' => $zatcavat, 'opexall' => $opexall );
		// array_push($responseArray,$allinvoices); 
	// }
	
	

	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
	
} else {    echo $responseArray['message'];		}		// else just display the message
?>