<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		
session_start();
//print_r($_POST);	
if($_POST){
	$time = time();
	$log = '';
	$updatelog = '';
	$suplog = '';
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

			if(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'delete' )   {
				if (!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'معرف التكلفه مطلوب');
				} elseif(!isset($_POST['category']) || empty($_POST['category']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'نوع التكلفه مطلوب');
				} else {
					$id = mysqli_real_escape_string($link, $_POST['id']);
					$category = mysqli_real_escape_string($link, $_POST['category']);
					
					$record = mysqli_query($link,"SELECT * FROM `$category` WHERE userid = '$userid' AND mrahid = '$mrahid' AND id = '$id'  ");
					if(@mysqli_num_rows($record) < 1){
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على التكلفه');		goto end;	
					} else {
						if ( $category == 'opex' ) {
							while($info = mysqli_fetch_array($record, MYSQLI_ASSOC)){
								$description = $info['description'];
								$cycle = $info['cycle'];
								$cost  = $info['cost'];
								$start = $info['start'];
								$end = $info['end'];
							}
							
							$log .= 'تم حذف تكلفه تشغيليه';
							$log .= ' بمعرف رقم '.$id;
							$log .= ' بشكل '.arabic($cycle);
							$log .= ' بوصف '.$description;
							$log .= ' بمبلغ '.$cost.' ريال';

							if ($cycle == 'monthly') {	$log .= ' لشهر '.arabic(date('m', $start)).' '.date('Y', $start);	}
							if ($cycle == 'yearly') {	$log .= ' يبدأ من شهر '.arabic(date('m', $start)).' '.date('Y', $start);	}

							$log .= ' لمراح '.$mrahname;
							$title = 'حذف تكلفه تشغيليه';
						}
						
						if ( $category == 'capex' ) {
							while($info = mysqli_fetch_array($record, MYSQLI_ASSOC)){
								$description = $info['description'];
								$cost  = $info['cost'];
							}
							
							$log .= 'تم حذف تكلفه رأس ماليه';
							$log .= ' بمعرف رقم '.$id;
							$log .= ' بوصف '.$description;
							$log .= ' بمبلغ '.$cost.' ريال';
							$log .= ' لمراح '.$mrahname;
							$title = 'حذف تكلفه رأس ماليه';
						}
						
 						$delete = mysqli_query($link,"DELETE FROM `$category` WHERE id = '$id' "); 
						// $delete = true;
						if (isset($delete)) {	
							$responseArray = array('id' => 'success', 'message' => 'تم حذف التكلفه بنجاح');
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','$category','$title','$log','$time' )");
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات isset($delete)');
						}
					}
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'editopex' )   {
				if (!isset($_SESSION['mrahid']) || empty($_SESSION['mrahid']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لا يوجد معرف للمراح');
				} elseif (!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'معرف التكلفه مطلوب');
				} elseif (!isset($_POST['description']) || empty($_POST['description']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'وصف التكاليف التشغيلية مطلوب');
				} elseif (!isset($_POST['cost']) || empty($_POST['cost']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'قيمة التكاليف التشغيلية مطلوبه');
				} elseif ( !is_numeric($_POST['cost'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'قيمة التكلفه يجب أن تحتوي على أرقام فقط');
				} elseif (!isset($_POST['cycle']) || empty($_POST['cycle']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'دورة التكلفة مطلوبه');
				} elseif (!isset($_POST['month']) || empty($_POST['month']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'شهر التكلفه مطلوب');
				} elseif (!isset($_POST['year']) || empty($_POST['year']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'عام التكلفه مطلوب');
				} else {
					$mrahid = $_SESSION["mrahid"];
					$mrahname = $_SESSION["mrahname"];
					$id = mysqli_real_escape_string($link, $_POST['id']);
					$description = mysqli_real_escape_string($link, $_POST['description']);
					$cost = mysqli_real_escape_string($link, $_POST['cost']);
					$cycle = mysqli_real_escape_string($link, $_POST['cycle']);
					$month = mysqli_real_escape_string($link, $_POST['month']);
					$year = mysqli_real_escape_string($link, $_POST['year']);

					// $cycledate = strftime("%F", strtotime($year."-".$month."-01"));		// Construct Date
					// $start = strtotime($cycledate);										// Get unix
					$cycledate = date("Y-m-d", strtotime("$year-$month-01"));
					$start = strtotime($cycledate); // This is now redundant, see optimization below
					$cycledateformatted = Time_Passed(date($start),'time'); 			// For logging purposes ($logins)
					if ($cycle == 'monthly') {	$next = date('Y-m-d', strtotime('+1 month', strtotime($cycledate))); 	 }
					if ($cycle == 'yearly') {	$next = date('Y-m-d', strtotime('+1 year', strtotime($cycledate)));		 }
					$end = strtotime($next) - 1;										// Get unix of the end (-1 deduct a second)
										
					// check if opex exists
					$record = mysqli_query($link,"SELECT * FROM `opex` WHERE id = '$id' AND userid = '$userid' AND mrahid = '$mrahid'");
					if(@mysqli_num_rows($record) > 0){
						while($info = mysqli_fetch_array($record, MYSQLI_ASSOC)){ 							
							$existingdescription = $info['description'];
							$existingcost = $info['cost'];
							$existingcycle = $info['cycle'];
							$existingstart = $info['start'];		$existingstart = (int)$existingstart;
							$existingend = $info['end'];	
							$existingtimeadded = $info['timeadded'];							
							$existingtimeedited = $info['timeedited'];							
						}

						if ( $description == $existingdescription && $cost == $existingcost && $cycle == $existingcycle && $start == $existingstart && $end == $existingend ) {
							$responseArray = array('id' => 'danger', 'message' => 'بيانات التكلفه المعدله مطابقه للسابق');		goto end;
						} 
						
						$record = mysqli_query($link,"SELECT * FROM `opex` WHERE userid = '$userid' AND mrahid = '$mrahid' AND description = '$description' AND cost = '$cost' AND cycle = '$cycle' AND start = '$start' AND end = '$end' ");
						if(@mysqli_num_rows($record) > 0){
							$responseArray = array('id' => 'danger', 'message' => 'التكلفه الشهريه مضافه مسبقاً');		goto end;
						} else {
							$log .= 'تم تعديل التكلفه التشغيليه';
							$log .= ' للمعرف رقم '.$id;
							if ( $description !== $existingdescription ) {	$log .= ' بنغيير الوصف من '.$existingdescription.' إلى '.$description;	}
							if ( $cost != $existingcost ) {	$log .= ' تغيير التكلفه من '.$existingcost.' إلى '.$cost.' ريال';	}
							if ( $cycle !== $existingcycle ) {	
								$log .= ' تغيير دورة التكلفه من '.arabic($existingcycle).' إلى '.arabic($cycle);
								if ( $existingcycle == 'monthly') { 
									$log .= ' وتغيير تاريخ التكلفه من شهر '.arabic(date('m', $existingstart)).' '.date('Y', $existingstart).' إلى تكلفه سنويه تبدأ '.arabic(date('m', $start)).' '.date('Y', $existingstart);
								}
								if ( $existingcycle == 'yearly') { 
									$log .= ' وتغيير تاريخ التكلفه من '.arabic(date('m', $existingstart)).' '.date('Y', $existingstart).' إلى تكلفه شهريه لشهر '.arabic(date('m', $start)).' '.date('Y', $existingstart);	
								}
							} else {
								if ( $start !== $existingstart ) {
									if ( $existingcycle == 'monthly') {
										$log .= ' وتغيير تاريخ التكلفه من شهر '.arabic(date('m', $existingstart)).' '.date('Y', $existingstart).' إلى شهر '.arabic(date('m', $start)).' '.date('Y', $start);	
									}
									if ( $existingcycle == 'yearly') {
										$log .= ' وتغيير تاريخ التكلفه من '.arabic(date('m', $existingstart)).' '.date('Y', $existingstart).' ليدأ '.arabic(date('m', $start)).' '.date('Y', $start);	
									}
								}
							}
							$log .= ' لمراح '.$mrahname;
							
							
							$update = mysqli_query($link,"UPDATE `opex` SET `description`='$description', `cost`='$cost' , `cycle`='$cycle' , `start`='$start' , `end`='$end', `timeedited`='$time' WHERE `id`='$id'");
							
							if ($update) {	
								$responseArray = array('id' => 'success', 'message' => 'تم التحديث بنجاح' );
								$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','opex','تعديل تكلفه تشغيليه','$log','$time' )");
							} else {
								$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $update faild');
							}
						}
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثوؤ على التكلفه المطلوبه');	
					}
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'editcapex' )   {
				if (!isset($_SESSION['mrahid']) || empty($_SESSION['mrahid']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لا يوجد معرف للمراح');
				} elseif (!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'معرف التكلفه مطلوب');
				} elseif (!isset($_POST['description']) || empty($_POST['description']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'وصف التكاليف التشغيلية مطلوب');
				} elseif (!isset($_POST['cost']) || empty($_POST['cost']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'قيمة التكاليف التشغيلية مطلوبه');
				} elseif ( !is_numeric($_POST['cost'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'قيمة التكلفه يجب أن تحتوي على أرقام فقط');
				} else {
					$mrahid = $_SESSION["mrahid"];
					$mrahname = $_SESSION["mrahname"];
					$id = mysqli_real_escape_string($link, $_POST['id']);
					$description = mysqli_real_escape_string($link, $_POST['description']);
					$cost = mysqli_real_escape_string($link, $_POST['cost']);
										
					// check if opex exists
					$record = mysqli_query($link,"SELECT * FROM `capex` WHERE id = '$id' AND userid = '$userid' AND mrahid = '$mrahid'");
					if(@mysqli_num_rows($record) > 0){
						while($info = mysqli_fetch_array($record, MYSQLI_ASSOC)){ 							
							$existingdescription = $info['description'];
							$existingcost = $info['cost'];
							$existingtimeadded = $info['timeadded'];							
							$existingtimeedited = $info['timeedited'];							
						}

						if ( $description == $existingdescription && $cost == $existingcost ) {
							$responseArray = array('id' => 'danger', 'message' => 'بيانات التكلفه المعدله مطابقه للسابق');		goto end;
						} 
						
						$record = mysqli_query($link,"SELECT * FROM `capex` WHERE userid = '$userid' AND mrahid = '$mrahid' AND description = '$description' AND cost = '$cost' ");
						if(@mysqli_num_rows($record) > 0){
							$responseArray = array('id' => 'danger', 'message' => 'التكلفه الرأسماليه مضافه مسبقاً');		goto end;
						} else {
							$log .= 'تم تعديل التكلفه الرأسماليه';
							$log .= ' للمعرف رقم '.$id;
							if ( $description !== $existingdescription ) {	$log .= ' بنغيير الوصف من '.$existingdescription.' إلى '.$description;	}
							if ( $cost != $existingcost ) {	$log .= ' تغيير التكلفه من '.$existingcost.' إلى '.$cost.' ريال';	}
							$log .= ' لمراح '.$mrahname;
							
							$update = mysqli_query($link,"UPDATE `capex` SET `description`='$description', `cost`='$cost', `timeedited`='$time' WHERE `id`='$id'");
							
							if ($update) {	
								$responseArray = array('id' => 'success', 'message' => 'تم التحديث بنجاح' );
								$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','capex','تعديل تكلفه رأسماليه','$log','$time' )");
							} else {
								$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $update faild');
							}
						}
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثوؤ على التكلفه المطلوبه');	
					}
				}		
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'addopex' )   {
				if (!isset($_SESSION['mrahid']) || empty($_SESSION['mrahid']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لا يوجد معرف للمراح');
				} elseif (!isset($_POST['description']) || empty($_POST['description']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'وصف التكاليف التشغيلية مطلوب');
				} elseif (!isset($_POST['cost']) || empty($_POST['cost']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'قيمة التكاليف التشغيلية مطلوبه');
				} elseif ( !is_numeric($_POST['cost'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'قيمة التكلفه يجب أن تحتوي على أرقام فقط');
				} elseif (!isset($_POST['cycle']) || empty($_POST['cycle']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'دورة التكلفة مطلوبه');
				} elseif (!isset($_POST['month']) || empty($_POST['month']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'شهر التكلفه مطلوب');
				} elseif (!isset($_POST['year']) || empty($_POST['year']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'عام التكلفه مطلوب');
				} else {
					$mrahid = $_SESSION["mrahid"];
					$mrahname = $_SESSION["mrahname"];
					$description = mysqli_real_escape_string($link, $_POST['description']);
					$cost = mysqli_real_escape_string($link, $_POST['cost']);
					$cycle = mysqli_real_escape_string($link, $_POST['cycle']);
					$month = mysqli_real_escape_string($link, $_POST['month']);
					$year = mysqli_real_escape_string($link, $_POST['year']);

					// $cycledate = strftime("%F", strtotime($year."-".$month."-01"));		// Construct Date
					// $start = strtotime($cycledate);										// Get unix
					$cycledate = date("Y-m-d", strtotime("$year-$month-01"));
					$start = strtotime($cycledate); // This is now redundant, see optimization below
					$cycledateformatted = Time_Passed(date($start),'time'); 			// For logging purposes ($logins)
					if ($cycle == 'monthly') {	$next = date('Y-m-d', strtotime('+1 month', strtotime($cycledate))); 	$logcycle = 'شهري'; }
					if ($cycle == 'yearly') {	$next = date('Y-m-d', strtotime('+1 year', strtotime($cycledate)));		$logcycle = 'سنوي'; }
					$end = strtotime($next) - 1;										// Get unix of the end (-1 deduct a second)

					$log .= 'تم إضافة تكلفه تشغيليه';
					$log .= ' بشكل '.arabic($cycle);
					$log .= ' بوصف '.$description;
					$log .= ' بمبلغ '.$cost.' ريال';

					if ($cycle == 'monthly') {	$log .= ' لشهر '.arabic($month);	}
					if ($cycle == 'yearly') {	$log .= ' يبدأ من شهر '.arabic($month);	}

					$log .= ' من العام '.$year;
					$log .= ' لمراح '.$mrahname;
					
					// check if opex exists
					$record = mysqli_query($link,"SELECT * FROM `opex` WHERE userid = '$userid' AND mrahid = '$mrahid' AND description = '$description' AND start = '$start' AND end = '$end' ");
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id' => 'warning', 'message' => 'التكلفه مضافه مسبقاً');		goto end;	
					} else {
						$ins1 = mysqli_query($link,"INSERT INTO `opex`( `id`,`userid`,`mrahid`,`description`,`cycle`,`cost`,`start`,`end` ,`status` ,`timeadded` ,`timeedited` )VALUES(	NULL,'$userid','$mrahid','$description','$cycle','$cost','$start','$end','active','$time',NULL )");
					}
					
					// Entry into History
					if ( isset($ins1) ) {	
						$responseArray = array('id' => 'success', 'message' => 'تمت الإضافة بنجاح' );
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','opex','إضافة تكلفه تشغيليه','$log','$time' )");
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $ins1 faild');
					}
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'addcapex' )   {
				if (!isset($_SESSION['mrahid']) || empty($_SESSION['mrahid']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لا يوجد معرف للمراح');
				} elseif (!isset($_POST['description']) || empty($_POST['description']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'وصف التكاليف الرأسماليه مطلوب');
				} elseif (!isset($_POST['cost']) || empty($_POST['cost']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'قيمة التكاليف الرأسماليه مطلوبه');
				} elseif ( !is_numeric($_POST['cost'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'قيمة التكلفه يجب أن تحتوي على أرقام فقط');
				} else {
					$mrahid = $_SESSION["mrahid"];
					$mrahname = $_SESSION["mrahname"];
					$description = mysqli_real_escape_string($link, $_POST['description']);
					$cost = mysqli_real_escape_string($link, $_POST['cost']);

					$log .= 'تم إضافة تكلفه رأس ماليه';
					$log .= ' بوصف '.$description;
					$log .= ' ومبلغ '.$cost.' ريال';
					$log .= ' لمراح '.$mrahname;
					
					// check if opex exists
					$record = mysqli_query($link,"SELECT * FROM `capex` WHERE userid = '$userid' AND mrahid = '$mrahid' AND description = '$description' AND cost = '$cost' ");
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id' => 'warning', 'message' => 'التكلفه مضافه مسبقاً');		goto end;	
					} else {
						$ins1 = mysqli_query($link,"INSERT INTO `capex`( `id`,`userid`,`mrahid`,`description`,`cost`,`timeadded` ,`timeedited` )VALUES(	NULL,'$userid','$mrahid','$description','$cost','$time',NULL )");
					}
					
					// Entry into History
					if ( isset($ins1) ) {	
						$responseArray = array('id' => 'success', 'message' => 'تمت الإضافة بنجاح' );
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','capex','إضافة تكلفه رأسماليه','$log','$time' )");
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $ins1 faild');
					}
				}
			} else {
				if(isset($_POST['id']) && !empty($_POST['id']))	{	
					$id = mysqli_real_escape_string($link, $_POST['id']);	
					$record = mysqli_query($link,"SELECT * FROM `opex` WHERE id = '$id' AND userid = '$userid' AND mrahid = '$mrahid'    ");
				} else { 
					$record = mysqli_query($link,"SELECT * FROM `opex` WHERE userid = '$userid' AND mrahid = '$mrahid' ORDER BY start DESC    ");
				}
					$opexnum = mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){									
						$z = 0;				$responseArray = [];			$Totalopexinfos = [];	
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
							
							
							
							${'opexinfo'.$z} = array('id' => $id,'userid' => $userid,'mrahid' => $mrahid, 'description' => $description , 'cycle' => $cycle, 'cyclear' => $cyclear ,'cost' => $cost ,'start' => $start ,'end' => $end ,'status' => $status ,'timeadded' => $timeadded ,'timeedited' => $timeedited,'month' => $month,'monthnumeric' => $monthnumeric,'year' => $year, 'endmonth' => $endmonth,'endmonthnumeric' => $endmonthnumeric,'endyear' => $endyear );	
							$z++;
						}
					}
					
					$record = mysqli_query($link,"SELECT * FROM `capex` WHERE userid = '$userid' AND mrahid = '$mrahid' ORDER BY timeadded DESC    ");
					$capexnum = mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){									
						$z = 0;				$responseArray = [];			$Totalcapexinfos = [];	
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
					

					$responseArray = array('id' => 'success', 'opexnum' => $opexnum , 'capexnum' => $capexnum );
			}
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($opexnum) && $opexnum > 0 ){	for($a=0;$a<$opexnum;$a++){ array_push($Totalopexinfos,${'opexinfo'.$a}); }	array_push($responseArray,$Totalopexinfos); }
	if ( isset($capexnum) && $capexnum > 0 ){	for($a=0;$a<$capexnum;$a++){ array_push($Totalcapexinfos,${'capexinfo'.$a}); }	array_push($responseArray,$Totalcapexinfos); }
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message

?>

