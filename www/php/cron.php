<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); /*Arabic*/	
require 'inc/functions.php'; 
$time = time();

$finder = mysqli_query($link,"SELECT * FROM `feed` ");
if(@mysqli_num_rows($finder) > 0){
	while($mfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ 
		$log = '';

		$id = $mfinder['id']; 
		$userid = $mfinder['userid'];
		$mrahid = $mfinder['mrahid'];
		$type = $mfinder['type'];
		$unit = $mfinder['unit'];
		$quantity = $mfinder['quantity'];
		$unitcost = $mfinder['unitcost'];
		$dailyintake = $mfinder['dailyintake'];
		
		if ( $quantity > 0 ) {
			if (isset($dailyintake) && !empty($dailyintake)) {
				// echo $type.' is set<br>';
				if ( $quantity >= $dailyintake ) {
					$newquantity = $quantity - $dailyintake;
				} else {
					$newquantity = 0;
				}
				
				$log .= 'تم خصم '.$dailyintake;
				$log .= ' '.unitar($type);
				$log .= ' '.arabic($type);
				$log .= ' من أصل '.$quantity;
				$log .= ' '.unitar($type);
				$log .= ' والمتبقي '.$newquantity;
				$log .= ' '.unitar($type);

				$update = mysqli_query($link,"UPDATE `feed` SET `quantity`='$newquantity' WHERE `id`='$id'");
				if ($update) {	
					// $logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','feed','إستهلاك يومي علف (النظام)','$log','$time' )");
				} else {
					$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $update faild');
				}

			} else {
				// echo $type.' is not set<br>';
			}
		}
	}
}

$finder = mysqli_query($link,"SELECT * FROM `water` ");
if(@mysqli_num_rows($finder) > 0){
	while($mfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ 
		$log = '';

		$id = $mfinder['id']; 
		$userid = $mfinder['userid'];
		$mrahid = $mfinder['mrahid'];
		$type = $mfinder['type'];
		$unit = $mfinder['unit'];
		$quantity = $mfinder['quantity'];
		$unitcost = $mfinder['unitcost'];
		$dailyintake = $mfinder['dailyintake'];
		
		if ( $quantity > 0 ) {
			if (isset($dailyintake) && !empty($dailyintake)) {
				// echo $type.' is set<br>';
				if ( $quantity >= $dailyintake ) {
					$newquantity = $quantity - $dailyintake;
				} else {
					$newquantity = 0;
				}
				
				$log .= 'تم خصم '.$dailyintake;
				$log .= ' '.unitar($type);
				$log .= ' '.arabic($type);
				$log .= ' من أصل '.$quantity;
				$log .= ' '.unitar($type);
				$log .= ' والمتبقي '.$newquantity;
				$log .= ' '.unitar($type);

				$update = mysqli_query($link,"UPDATE `water` SET `quantity`='$newquantity' WHERE `id`='$id'");
				if ($update) {	
					// $logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','water','إستهلاك يومي مياه (النظام)','$log','$time' )");
				} else {
					$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $update faild');
				}

			} else {
				// echo $type.' is not set<br>';
			}
		}
	}
}

// $record = mysqli_query($link,"SELECT * FROM `sheep` WHERE status = 'live' OR status = 'sick'    ");
$record = mysqli_query($link,"SELECT * FROM `sheep` ");
$sheepnum = mysqli_num_rows($record);
if(@mysqli_num_rows($record) > 0){									
	while($sheepinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
		$id = $sheepinfo['id'];
		$status = $sheepinfo['status'];
		$age = $sheepinfo['age'];
		$inclusiondate = $sheepinfo['inclusiondate'];		
		$agestamp = $sheepinfo['agestamp'];
		if ( monthsPassed($agestamp) >= 1 ) {
			if ( $status == 'live' || $status == 'sick' ) {
				$newage = $age + 1 ;
				$updateevent = mysqli_query($link,"UPDATE `sheep` SET `age`='$newage' WHERE `id`='$id'");
			}
			$newinclusiondate = $inclusiondate + 1 ;
			$updateevent = mysqli_query($link,"UPDATE `sheep` SET `inclusiondate`='$newinclusiondate', `agestamp`='$time' WHERE `id`='$id'");
		}
	}
}

?>