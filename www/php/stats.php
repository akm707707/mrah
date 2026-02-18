<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		
session_start();
//print_r($_POST);	
if($_POST){
	$time = time();
	$log = '';
	$sheeparray = [];
	$tagarray = [];
	$eventarray = [];
	if ( !$_SESSION ) {
		$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $_SESSION');
	} else {
		if ( !isset($_SESSION['loggedin']) || $_SESSION["loggedin"] !== true )   {
			$responseArray = array('id' => 'danger', 'message' => 'لم تقم بتسجيل الدخول');
		} else {			
			$userid = $_SESSION["userid"];
			$name = $_SESSION["name"];
			$mobile = $_SESSION["mobile"];
			$email = $_SESSION["email"];
			//$pass = $_SESSION["pass"];
			// $mrah = $_SESSION["mrah"];
			if (!isset($_SESSION['mrahid']) || empty($_SESSION['mrahid']) )   {	$responseArray = array('id' => '', 'message' => '');	goto end;	}
			$mrahid = $_SESSION["mrahid"];
			$mrahname = $_SESSION["mrahname"];
			$profit = $_SESSION["profit"];

			// Sheep info 
			$record = mysqli_query($link,"SELECT * FROM `sheep` WHERE userid = '$userid' AND mrahid = '$mrahid'    ");
			$sheepnum = mysqli_num_rows($record);
			if(@mysqli_num_rows($record) > 0){									
				$z = 0;				$responseArray = [];			$Totalsheepinfos = [];	
				while($sheepinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
					$id = $sheepinfo['id'];
					$userid = $sheepinfo['userid'];
					$mrahid = $sheepinfo['mrahid'];
					$status = $sheepinfo['status'];
					$tagnumber = $sheepinfo['tagnumber'];
					$tagcolor = $sheepinfo['tagcolor'];
					$breed = $sheepinfo['breed'];
					$gender = $sheepinfo['gender'];
					$age = $sheepinfo['age'];			$agear = mtoy($age);
					$weight = $sheepinfo['weight'];
					$remarks = $sheepinfo['remarks'];
					$cost = $sheepinfo['cost'];											
					$inclusiondate = $sheepinfo['inclusiondate'];			$inclusiondatear = mtoy($inclusiondate);
														
					$events = $sheepinfo['events'];
					if ( !empty($events) ) {		
						$events = explode(",",$events);
						$eventsarr = [];
						for($a=0;$a<count($events);$a++){ 
							$temparray = explode("^",$events[$a]);
							$temparray[1] = Time_Passed(date($temparray[1]),'time');
							$eventsarr[] = $temparray;
						}
						$events = $eventsarr;
					}
					
					$father = $sheepinfo['father'];		$fatherar = arabic($father);									
					$mother = $sheepinfo['mother'];		$motherar = arabic($mother);	
					$value = $sheepinfo['value'];											
					
					$timeadded = Time_Passed(date($sheepinfo['timeadded']),'time');
					if(isset($sheepinfo['timeedited']) && !empty($_POST['timeedited']))	{
						$timeedited = Time_Passed(date($sheepinfo['timeedited']),'time');
					} else {
						$timeedited = '';
					}

					${'sheepinfo'.$z} = array('id' => $id,'userid' => $userid,'mrahid' => $mrahid, 'status' => $status , 'statusar' => arabic($status) ,'tagnumber' => $tagnumber ,'tagcolor' => $tagcolor ,'tagcolorar' => arabic($tagcolor) ,'breed' => $breed ,'breedar' => arabic($breed) ,'gender' => $gender ,'genderar' => arabic($gender) ,'age' => $age,'agear' => $agear ,'weight' => $weight ,'remarks' => $remarks ,'cost' => $cost ,'inclusiondate' => $inclusiondate,'inclusiondatear' => $inclusiondatear ,'events' => $events,'father' => $father,'fatherar' => $fatherar,'mother' => $mother,'motherar' => $motherar,'value' => $value ,'timeadded' => $timeadded ,'timeedited' => $timeedited );	
					$z++;
				}
			}
			$responseArray = array('id' => 'success', 'sheepnum' => $sheepnum);

			$record = mysqli_query($link,"SELECT * FROM `feed` WHERE userid = '$userid' AND mrahid = '$mrahid'    ");
			$feednum = mysqli_num_rows($record);
			if(@mysqli_num_rows($record) > 0){									
				$z = 0;			$Totalfeedinfos = [];	
				while($feedinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
					$id = $feedinfo['id'];
					$userid = $feedinfo['userid'];
					$mrahid = $feedinfo['mrahid'];
					$type = $feedinfo['type'];
					$name = arabic($type);
					$unit = $feedinfo['unit'];
					$unitar = unitar($type);
					$quantity = $feedinfo['quantity'];
					$unitcost = $feedinfo['unitcost'];
					$dailyintake = $feedinfo['dailyintake'];

					${'feedinfo'.$z} = array('id' => $id,'userid' => $userid,'mrahid' => $mrahid, 'type' => $type , 'name' => $name ,'unit' => $unit ,'unitar' => $unitar ,'quantity' => $quantity ,'unitcost' => $unitcost ,'dailyintake' => $dailyintake );	
					$z++;
				}
			}
			$responseArray["feednum"] = $feednum;
			
			$record = mysqli_query($link,"SELECT * FROM `water` WHERE userid = '$userid' AND mrahid = '$mrahid'    ");
			$waternum = mysqli_num_rows($record);
			if(@mysqli_num_rows($record) > 0){									
				$z = 0;			$Totalwaterinfos = [];	
				while($waterinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
					$id = $waterinfo['id'];
					$userid = $waterinfo['userid'];
					$mrahid = $waterinfo['mrahid'];
					$type = $waterinfo['type'];
					$name = arabic($type);
					$unit = $waterinfo['unit'];
					$unitar = unitar($type);
					$quantity = $waterinfo['quantity'];
					$unitcost = $waterinfo['unitcost'];
					$dailyintake = $waterinfo['dailyintake'];

					${'waterinfo'.$z} = array('id' => $id,'userid' => $userid,'mrahid' => $mrahid, 'type' => $type , 'name' => $name ,'unit' => $unit ,'unitar' => $unitar ,'quantity' => $quantity ,'unitcost' => $unitcost ,'dailyintake' => $dailyintake );	
					$z++;
				}
			}
			$responseArray["waternum"] = $waternum;
			
			$record = mysqli_query($link,"SELECT * FROM `opex` WHERE userid = '$userid' AND mrahid = '$mrahid' AND start <= '$time' AND end >= '$time' ORDER BY start DESC ");
			$opexnum = mysqli_num_rows($record);
			if(@mysqli_num_rows($record) > 0){									
				$z = 0;					$Totalopexinfos = [];	
				while($opexinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
					$id = $opexinfo['id'];
					$userid = $opexinfo['userid'];
					$mrahid = $opexinfo['mrahid'];
					$description = $opexinfo['description'];
					$cycle = $opexinfo['cycle'];				$cyclear = arabic($cycle);
					$cost = $opexinfo['cost'];
					$start = $opexinfo['start'];				$start = Time_Passed(date($opexinfo['start']),'time');
					$end = $opexinfo['end'];					$end = Time_Passed(date($opexinfo['end']),'time');
					$status = $opexinfo['status'];				$status = arabic($status);
					$timeadded = $opexinfo['timeadded'];		$timeadded = Time_Passed(date($opexinfo['timeadded']),'time');
					$timeedited = $opexinfo['timeedited'];		
					if ( !empty($timeedited) ) { $timeedited = Time_Passed(date($opexinfo['timeedited']),'time'); }

					$month = $opexinfo['start'];		$monthnumeric = date('m', $month);			$month = arabic($monthnumeric);
					$endmonth = $opexinfo['end'];		$endmonthnumeric = date('m', $endmonth);	$endmonth = arabic($endmonthnumeric);
					$year = $opexinfo['start'];			$year = date('Y', $year);
					$endyear = $opexinfo['end'];		$endyear = date('Y', $endyear);
					// $daysInMonth = date("t", $timestamp);
					$daysInMonth = date("t", $opexinfo['start']);
					$daysInYear = date("L", $opexinfo['start']) ? 366 : 365;
					
					
					${'opexinfo'.$z} = array('id' => $id,'userid' => $userid,'mrahid' => $mrahid, 'description' => $description , 'cycle' => $cycle, 'cyclear' => $cyclear ,'cost' => $cost ,'start' => $start ,'end' => $end ,'status' => $status ,'timeadded' => $timeadded ,'timeedited' => $timeedited,'month' => $month,'monthnumeric' => $monthnumeric,'year' => $year, 'endmonth' => $endmonth,'endmonthnumeric' => $endmonthnumeric,'endyear' => $endyear,'daysInMonth' => $daysInMonth,'daysInYear' => $daysInYear );	
					$z++;
				}
			}
			$responseArray["opexnum"] = $opexnum;
					
			$record = mysqli_query($link,"SELECT * FROM `capex` WHERE userid = '$userid' AND mrahid = '$mrahid' ORDER BY timeadded DESC    ");
			$capexnum = mysqli_num_rows($record);
			if(@mysqli_num_rows($record) > 0){									
				$z = 0;					$Totalcapexinfos = [];	
				while($capexinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
					$id = $capexinfo['id'];
					$userid = $capexinfo['userid'];
					$mrahid = $capexinfo['mrahid'];
					$description = $capexinfo['description'];
					$cost = $capexinfo['cost'];
					$timeadded = $capexinfo['timeadded'];		$timeadded = Time_Passed(date($capexinfo['timeadded']),'time');
					$timeedited = $capexinfo['timeedited'];		
					if ( !empty($timeedited) ) { $timeedited = Time_Passed(date($capexinfo['timeedited']),'time'); }
					
					${'capexinfo'.$z} = array('id' => $id,'userid' => $userid,'mrahid' => $mrahid, 'description' => $description ,'cost' => $cost ,'timeadded' => $timeadded ,'timeedited' => $timeedited );	
					$z++;
				}
			}
			$responseArray["capexnum"] = $capexnum;
			
// opexspending
			$record = mysqli_query($link,"SELECT * FROM `opex` WHERE userid = '$userid' AND mrahid = '$mrahid' AND start <= '$time' ORDER BY start DESC ");
			$opexspendingnum = mysqli_num_rows($record);
			if(@mysqli_num_rows($record) > 0){									
				$z = 0;					$Totalopexspendinginfos = [];	
				while($opexspendinginfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
					$id = $opexspendinginfo['id'];
					$userid = $opexspendinginfo['userid'];
					$mrahid = $opexspendinginfo['mrahid'];
					$description = $opexspendinginfo['description'];
					$cycle = $opexspendinginfo['cycle'];				$cyclear = arabic($cycle);
					$cost = $opexspendinginfo['cost'];
					$start = $opexspendinginfo['start'];				$start = Time_Passed(date($opexspendinginfo['start']),'time');
					$end = $opexspendinginfo['end'];					$end = Time_Passed(date($opexspendinginfo['end']),'time');
					$status = $opexspendinginfo['status'];				$status = arabic($status);
					$timeadded = $opexspendinginfo['timeadded'];		$timeadded = Time_Passed(date($opexspendinginfo['timeadded']),'time');
					$timeedited = $opexspendinginfo['timeedited'];		
					if ( !empty($timeedited) ) { $timeedited = Time_Passed(date($opexspendinginfo['timeedited']),'time'); }

					$month = $opexspendinginfo['start'];		$monthnumeric = date('m', $month);			$month = arabic($monthnumeric);
					$endmonth = $opexspendinginfo['end'];		$endmonthnumeric = date('m', $endmonth);	$endmonth = arabic($endmonthnumeric);
					$year = $opexspendinginfo['start'];			$year = date('Y', $year);
					$endyear = $opexspendinginfo['end'];		$endyear = date('Y', $endyear);
					// $daysInMonth = date("t", $timestamp);
					$daysInMonth = date("t", $opexspendinginfo['start']);
					$daysInYear = date("L", $opexspendinginfo['start']) ? 366 : 365;
					
					
					${'opexspendinginfo'.$z} = array('id' => $id,'userid' => $userid,'mrahid' => $mrahid, 'description' => $description , 'cycle' => $cycle, 'cyclear' => $cyclear ,'cost' => $cost ,'start' => $start ,'end' => $end ,'status' => $status ,'timeadded' => $timeadded ,'timeedited' => $timeedited,'month' => $month,'monthnumeric' => $monthnumeric,'year' => $year, 'endmonth' => $endmonth,'endmonthnumeric' => $endmonthnumeric,'endyear' => $endyear,'daysInMonth' => $daysInMonth,'daysInYear' => $daysInYear );	
					$z++;
				}
			}
			$responseArray["opexspendingnum"] = $opexspendingnum;

//purchasepending
			$record = mysqli_query($link,"SELECT * FROM `purchases` WHERE userid = '$userid' AND mrahid = '$mrahid' AND time <= '$time' ORDER BY time DESC    ");
			$purchasespendingnum = mysqli_num_rows($record);
			if(@mysqli_num_rows($record) > 0){									
				$z = 0;					$Totalpurchasepurchaseinfos = [];	
				while($purchasepurchaseinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
					$id = $purchasepurchaseinfo['id'];
					$category = $purchasepurchaseinfo['category'];
					$type = $purchasepurchaseinfo['type'];
					$name = arabic($type);
					$quantity = $purchasepurchaseinfo['quantity'];
					$unitcost = $purchasepurchaseinfo['unitcost'];
					$totalcost = $purchasepurchaseinfo['totalcost'];
					$settlement = $purchasepurchaseinfo['settlement'];
					$paid = $purchasepurchaseinfo['paid'];
					$remarks = $purchasepurchaseinfo['remarks'];
					$suppliername = $purchasepurchaseinfo['suppliername'];
					$suppliermobile = $purchasepurchaseinfo['suppliermobile'];
					$timeadded = Time_Passed(date($purchasepurchaseinfo['time']),'time');
					$unitar = unitar($type);

					${'purchasepurchaseinfo'.$z} = array('id' => $id, 'category' => $category, 'type' => $type , 'name' => $name ,'quantity' => $quantity ,'unitcost' => $unitcost ,'totalcost' => $totalcost ,'settlement' => $settlement ,'paid' => $paid ,'remarks' => $remarks ,'suppliername' => $suppliername ,'suppliermobile' => $suppliermobile ,'time' => $timeadded ,'unitar' => $unitar );	
					$z++;
				}
			}
			$responseArray["purchasespendingnum"] = $purchasespendingnum;		
			
			
// New born Sheep info 
			$record = mysqli_query($link,"SELECT * FROM `sheep` WHERE userid = '$userid' AND mrahid = '$mrahid' AND born = 1 AND status IN ('live', 'sick')	");
			// $record = mysqli_query($link,"SELECT * FROM `sheep` WHERE userid = '$userid' AND mrahid = '$mrahid' AND timeadded > '0'	");
			$newbornsheepnum = mysqli_num_rows($record);
			if(@mysqli_num_rows($record) > 0){									
				$z = 0;							$Totalnewbornsheepinfos = [];	
				while($newbornsheepinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
					$id = $newbornsheepinfo['id'];
					$userid = $newbornsheepinfo['userid'];
					$mrahid = $newbornsheepinfo['mrahid'];
					$status = $newbornsheepinfo['status'];
					$tagnumber = $newbornsheepinfo['tagnumber'];
					$tagcolor = $newbornsheepinfo['tagcolor'];
					$breed = $newbornsheepinfo['breed'];
					$gender = $newbornsheepinfo['gender'];
					$age = $newbornsheepinfo['age'];			$agear = mtoy($age);
					$weight = $newbornsheepinfo['weight'];
					$remarks = $newbornsheepinfo['remarks'];
					$cost = $newbornsheepinfo['cost'];											
					$inclusiondate = $newbornsheepinfo['inclusiondate'];			$inclusiondatear = mtoy($inclusiondate);
														
					$events = $newbornsheepinfo['events'];
					if ( !empty($events) ) {		
						$events = explode(",",$events);
						$eventsarr = [];
						for($a=0;$a<count($events);$a++){ 
							$temparray = explode("^",$events[$a]);
							$temparray[1] = Time_Passed(date($temparray[1]),'time');
							$eventsarr[] = $temparray;
						}
						$events = $eventsarr;
					}
					
					$father = $newbornsheepinfo['father'];		$fatherar = arabic($father);									
					$mother = $newbornsheepinfo['mother'];		$motherar = arabic($mother);	
					$value = $newbornsheepinfo['value'];											
					
					$timeadded = Time_Passed(date($newbornsheepinfo['timeadded']),'time');
					if(isset($newbornsheepinfo['timeedited']) && !empty($_POST['timeedited']))	{
						$timeedited = Time_Passed(date($newbornsheepinfo['timeedited']),'time');
					} else {
						$timeedited = '';
					}

					${'newbornsheepinfo'.$z} = array('id' => $id,'userid' => $userid,'mrahid' => $mrahid, 'status' => $status , 'statusar' => arabic($status) ,'tagnumber' => $tagnumber ,'tagcolor' => $tagcolor ,'tagcolorar' => arabic($tagcolor) ,'breed' => $breed ,'breedar' => arabic($breed) ,'gender' => $gender ,'genderar' => arabic($gender) ,'age' => $age,'agear' => $agear ,'weight' => $weight ,'remarks' => $remarks ,'cost' => $cost ,'inclusiondate' => $inclusiondate,'inclusiondatear' => $inclusiondatear ,'events' => $events,'father' => $father,'fatherar' => $fatherar,'mother' => $mother,'motherar' => $motherar,'value' => $value ,'timeadded' => $timeadded ,'timeedited' => $timeedited );	
					$z++;
				}
			}
			$responseArray["newbornsheepnum"] = $newbornsheepnum;

// Bought Sheep info 
			$record = mysqli_query($link,"SELECT * FROM `sheep` WHERE userid = '$userid' AND mrahid = '$mrahid' AND born = 0 AND status IN ('live', 'sick', 'sold') AND cost != '' ");
			// $record = mysqli_query($link,"SELECT * FROM `sheep` WHERE userid = '$userid' AND mrahid = '$mrahid' AND born = 0 AND status IN ('live', 'sick') AND cost != '' ");
			$boughtsheepnum = mysqli_num_rows($record);
			if(@mysqli_num_rows($record) > 0){									
				$z = 0;							$Totalboughtsheepinfos = [];	
				while($boughtsheepinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
					$id = $boughtsheepinfo['id'];
					$userid = $boughtsheepinfo['userid'];
					$mrahid = $boughtsheepinfo['mrahid'];
					$status = $boughtsheepinfo['status'];
					$tagnumber = $boughtsheepinfo['tagnumber'];
					$tagcolor = $boughtsheepinfo['tagcolor'];
					$breed = $boughtsheepinfo['breed'];
					$gender = $boughtsheepinfo['gender'];
					$age = $boughtsheepinfo['age'];			$agear = mtoy($age);
					$weight = $boughtsheepinfo['weight'];
					$remarks = $boughtsheepinfo['remarks'];
					$cost = $boughtsheepinfo['cost'];											
					$inclusiondate = $boughtsheepinfo['inclusiondate'];			$inclusiondatear = mtoy($inclusiondate);
														
					$events = $boughtsheepinfo['events'];
					if ( !empty($events) ) {		
						$events = explode(",",$events);
						$eventsarr = [];
						for($a=0;$a<count($events);$a++){ 
							$temparray = explode("^",$events[$a]);
							$temparray[1] = Time_Passed(date($temparray[1]),'time');
							$eventsarr[] = $temparray;
						}
						$events = $eventsarr;
					}
					
					$father = $boughtsheepinfo['father'];		$fatherar = arabic($father);									
					$mother = $boughtsheepinfo['mother'];		$motherar = arabic($mother);	
					$value = $boughtsheepinfo['value'];											
					
					$timeadded = Time_Passed(date($boughtsheepinfo['timeadded']),'time');
					if(isset($boughtsheepinfo['timeedited']) && !empty($_POST['timeedited']))	{
						$timeedited = Time_Passed(date($boughtsheepinfo['timeedited']),'time');
					} else {
						$timeedited = '';
					}

					${'boughtsheepinfo'.$z} = array('id' => $id,'userid' => $userid,'mrahid' => $mrahid, 'status' => $status , 'statusar' => arabic($status) ,'tagnumber' => $tagnumber ,'tagcolor' => $tagcolor ,'tagcolorar' => arabic($tagcolor) ,'breed' => $breed ,'breedar' => arabic($breed) ,'gender' => $gender ,'genderar' => arabic($gender) ,'age' => $age,'agear' => $agear ,'weight' => $weight ,'remarks' => $remarks ,'cost' => $cost ,'inclusiondate' => $inclusiondate,'inclusiondatear' => $inclusiondatear ,'events' => $events,'father' => $father,'fatherar' => $fatherar,'mother' => $mother,'motherar' => $motherar,'value' => $value ,'timeadded' => $timeadded ,'timeedited' => $timeedited );	
					$z++;
				}
			}
			$responseArray["boughtsheepnum"] = $boughtsheepnum;
			
//previous profits
			$record = mysqli_query($link,"SELECT * FROM `mrah` WHERE id = '$mrahid' ");
			$mrahnum = mysqli_num_rows($record);
			if(@mysqli_num_rows($record) > 0){									
				while($mrahinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
					$previousprofit = $mrahinfo['profit'];
					$previousexpense = $mrahinfo['expense'];
				}
			}
			$responseArray["previousprofit"] = $previousprofit;		
			$responseArray["previousexpense"] = $previousexpense;		
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($sheepnum) && $sheepnum > 0 ){	for($a=0;$a<$sheepnum;$a++){ array_push($Totalsheepinfos,${'sheepinfo'.$a}); }	array_push($responseArray,$Totalsheepinfos); }
	if ( isset($feednum) && $feednum > 0 ){	for($a=0;$a<$feednum;$a++){ array_push($Totalfeedinfos,${'feedinfo'.$a}); }	array_push($responseArray,$Totalfeedinfos); }
	if ( isset($waternum) && $waternum > 0 ){	for($a=0;$a<$waternum;$a++){ array_push($Totalwaterinfos,${'waterinfo'.$a}); }	array_push($responseArray,$Totalwaterinfos); }
	if ( isset($opexnum) && $opexnum > 0 ){	for($a=0;$a<$opexnum;$a++){ array_push($Totalopexinfos,${'opexinfo'.$a}); }	array_push($responseArray,$Totalopexinfos); }
	if ( isset($capexnum) && $capexnum > 0 ){	for($a=0;$a<$capexnum;$a++){ array_push($Totalcapexinfos,${'capexinfo'.$a}); }	array_push($responseArray,$Totalcapexinfos); }
	if ( isset($opexspendingnum) && $opexspendingnum > 0 ){	for($a=0;$a<$opexspendingnum;$a++){ array_push($Totalopexspendinginfos,${'opexspendinginfo'.$a}); }	array_push($responseArray,$Totalopexspendinginfos); }
	if ( isset($purchasespendingnum) && $purchasespendingnum > 0 ){	for($a=0;$a<$purchasespendingnum;$a++){ array_push($Totalpurchasepurchaseinfos,${'purchasepurchaseinfo'.$a}); }	array_push($responseArray,$Totalpurchasepurchaseinfos); }
	if ( isset($newbornsheepnum) && $newbornsheepnum > 0 ){	for($a=0;$a<$newbornsheepnum;$a++){ array_push($Totalnewbornsheepinfos,${'newbornsheepinfo'.$a}); }	array_push($responseArray,$Totalnewbornsheepinfos); }
	if ( isset($boughtsheepnum) && $boughtsheepnum > 0 ){	for($a=0;$a<$boughtsheepnum;$a++){ array_push($Totalboughtsheepinfos,${'boughtsheepinfo'.$a}); }	array_push($responseArray,$Totalboughtsheepinfos); }


	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message

?>

