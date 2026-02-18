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

			if(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'delete' )   {
				if (!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لا يوجد معرف للأغنام');
				} elseif (!isset($_POST['reason']) || empty($_POST['reason']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'سبب الحذف مطلوب');
				// } elseif (!isset($_POST['price']) || empty($_POST['price']) )   {
					// $responseArray = array('id' => 'danger', 'message' => 'القيمه السوقيه لرأس الأغنام مطلوبه');
				} else {
					$id = mysqli_real_escape_string($link, $_POST['id']);
					$reason = mysqli_real_escape_string($link, $_POST['reason']);
					
					if ( $reason !== 'mistake'	) {
						if (!isset($_POST['price']) || empty($_POST['price']) )   {
							$responseArray = array('id' => 'danger', 'message' => 'القيمه السوقيه لرأس الأغنام مطلوبه');		goto end;
						} else {
							$price = mysqli_real_escape_string($link, $_POST['price']);
						}
					} 

					// check if sheep exists in feed table
					$record = mysqli_query($link,"SELECT * FROM `sheep` WHERE id = '$id' AND userid = '$userid' AND mrahid = '$mrahid'");
					if(@mysqli_num_rows($record) < 1){
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على رأس الأغنام');		goto end;	
					} else {
						while($info = mysqli_fetch_array($record, MYSQLI_ASSOC)){ 							
							$status = $info['status'];
							$tagnumber = $info['tagnumber'];
							$tagcolor  = $info['tagcolor'];
							$breed = $info['breed'];
							$gender = $info['gender'];
							$born = $info['born'];
							$age = $info['age'];
							$weight = $info['weight'];
							$remarks = $info['remarks'];
							$cost = $info['cost'];
							$inclusiondate = $info['inclusiondate'];
							$mother = $info['mother'];
							$father = $info['father'];
							$value = $info['value'];
						}
						
						$log .= 'تم حذف رأس أغنام بمعرف رقم '.$id;
						if ( !empty($tagnumber) ) {		$log .= ' ووسم رقم '.$tagnumber;		}
						if ( !empty($tagcolor) ) {		$log .= ' ووسم '.arabic($tagcolor);		}
						$log .= ' بعمر '.$age;
						$log .= ' بتغيير حالته إلى '.arabic($reason);
						if ( $reason !== 'mistake'	) { $log .= ' بقيمة سوقية تقدر بـ '.$price.' ريال'; }

						$log .= ' لمراح '.$mrahname;

						
						if ( $reason !== 'mistake'	) {
							$update = mysqli_query($link,"UPDATE `sheep` SET `status`='$reason', `timeedited`='$time' , `value`='$price' WHERE `id`='$id'");
							
							if ($update) {	
								$responseArray = array('id' => 'success', 'message' => 'تم الحذف بنجاح' );
								$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','sheep','حذف رأس حلال','$log','$time' )");
							} else {
								$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $update faild');
							}
						} else {
							$delete = mysqli_query($link,"DELETE FROM `sheep` WHERE id = '$id' ");
							if ($delete) {
								$log = 'تم حذف رأس أغنام بمعرف رقم '.$id;
								if ( !empty($tagnumber) ) {		$log .= ' ووسم رقم '.$tagnumber;		}
								if ( !empty($tagcolor) ) {		$log .= ' ووسم '.arabic($tagcolor);		}
								$log .= ' '.arabic($reason);
								$log .= ' لمراح '.$mrahname;

								$responseArray = array('id' => 'success', 'message' => 'تم الحذف بنجاح' );
								$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','sheep','حذف رأس حلال','$log','$time' )");
							} else {
								$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $delete faild');
							}

						}
					}
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'deleteall' )   {
				if (!isset($_SESSION['userid']) || empty($_SESSION['userid']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لا يوجد معرف للمستخدم');
				} elseif (!isset($_SESSION['mrahid']) || empty($_SESSION['mrahid']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لا يوجد معرف للمراح');
				} elseif (!isset($_POST['reason']) || empty($_POST['reason']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'سبب الحذف مطلوب');
				} else {
					$reason = mysqli_real_escape_string($link, $_POST['reason']);
					
					if ( $reason !== 'mistake'	) {
						if (!isset($_POST['price']) || empty($_POST['price']) )   {
							$responseArray = array('id' => 'danger', 'message' => 'القيمه السوقيه لرأس الأغنام مطلوبه');		goto end;
						} else {
							$price = mysqli_real_escape_string($link, $_POST['price']);
						}
					} 

					// check if sheep exists in feed table
					$record = mysqli_query($link,"SELECT * FROM `sheep` WHERE userid = '$userid' AND mrahid = '$mrahid'");
					if(@mysqli_num_rows($record) < 1){
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على أي أغنام');		goto end;	
					} else {
						while($info = mysqli_fetch_array($record, MYSQLI_ASSOC)){ 							
							$id = $info['id'];
							$status = $info['status'];

							if ( $status == 'sick' || $status == 'live'	) {
								if ( $reason !== 'mistake'	) {
									$update = mysqli_query($link,"UPDATE `sheep` SET `status`='$reason', `timeedited`='$time' , `value`='$price' WHERE `id`='$id'");
									if (!$update) {	
										$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $update faild');	goto end;
									}
								} else {
									$delete = mysqli_query($link,"DELETE FROM `sheep` WHERE id = '$id' ");
									if (!$delete) {
										$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $delete faild');	goto end;
									}
								}
							}
						}
						
						if ( isset($update) || isset($delete) ) {
							$responseArray = array('id' => 'success', 'message' => 'تم حذف جميع الأغنام بنجاح');
							
							$log .= 'تم حذف جميع الأغنام بنجاح ';
							$log .= ' بتغيير حالتها إلى '.arabic($reason);
							if ( $reason !== 'mistake'	) { $log .= ' بقيمة سوقية تقدر بـ '.$price.' ريال للرأس الواحد'; }
							$log .= ' لمراح '.$mrahname;
							
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','sheep','حذف جميع رؤوس الحلال','$log','$time' )");

						} else {
							$responseArray = array('id' => 'warning', 'message' => 'لم يتم العثور على أي أغنام');
						}
					}
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'edit' )   {
				if (!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لا يوجد معرف للأغنام');
				} elseif (!isset($_POST['breed']) || empty($_POST['breed']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'اختيار الصنف مطلوب');
				} elseif (!isset($_POST['gender']) || empty($_POST['gender']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'اختيار الجنس مطلوب');
				// } elseif (!isset($_POST['status']) || empty($_POST['status']) )   {
					// $responseArray = array('id' => 'danger', 'message' => 'الحالة الصحيه مطلوبه');
				} else {
					$mrahid = $_SESSION["mrahid"];
					$mrahname = $_SESSION["mrahname"];
					$id = mysqli_real_escape_string($link, $_POST['id']);
					$breed = mysqli_real_escape_string($link, $_POST['breed']);
					$gender = mysqli_real_escape_string($link, $_POST['gender']);
					// $status = mysqli_real_escape_string($link, $_POST['status']);
					
					$log .= 'تم تحديث رأس غنم بمعرف رقم '.$id;

					if ( isset($_POST['tagcolor']) && !empty($_POST['tagcolor']) ) {
						$tagcolor = mysqli_real_escape_string($link, $_POST['tagcolor']);			
					} else {
						$tagcolor = 'none';
					}
					if ( isset($_POST['tagnumber']) && !empty($_POST['tagnumber']) ) {
						$tagnumber = mysqli_real_escape_string($link, $_POST['tagnumber']);			
					} else {
						$tagnumber = '';
					}

					if ( isset($_POST['born']) && !empty($_POST['born'] ) )  {
						$born = '1';
					} else {
						$born = '0';
					}

					if ( isset($_POST['age']) && is_numeric($_POST['age']) ) {
						$age = mysqli_real_escape_string($link, $_POST['age']);						
					} else {
						$age = '';
					}
					if ( isset($_POST['weight']) && !empty($_POST['weight']) ) {
						$weight = mysqli_real_escape_string($link, $_POST['weight']);				
					} else {
						$weight = '';
					}
					if ( isset($_POST['remarks']) && !empty($_POST['remarks']) ) {
						$remarks = mysqli_real_escape_string($link, $_POST['remarks']);				
					} else {
						$remarks = '';
					}
					if ( isset($_POST['cost']) && !empty($_POST['cost']) ) {
						$cost = mysqli_real_escape_string($link, $_POST['cost']);					
					} else {
						$cost = '';
					}
					if ( isset($_POST['inclusiondate']) && is_numeric($_POST['inclusiondate']) ) {
						$inclusiondate = mysqli_real_escape_string($link, $_POST['inclusiondate']);	
					} else {
						$inclusiondate = '';
					}
					if ( isset($_POST['mother']) && !empty($_POST['mother']) ) {
						$mother = mysqli_real_escape_string($link, $_POST['mother']);					
					} else {
						$mother = '';
					}
					if ( isset($_POST['father']) && !empty($_POST['father']) ) {
						$father = mysqli_real_escape_string($link, $_POST['father']);					
					} else {
						$father = '';
					}
					// check if sheep exists in feed table
					$record = mysqli_query($link,"SELECT * FROM `sheep` WHERE id = '$id' AND userid = '$userid' AND mrahid = '$mrahid'");
					if(@mysqli_num_rows($record) > 0){
						while($info = mysqli_fetch_array($record, MYSQLI_ASSOC)){ 							
							// $existingstatus = $info['status'];
							$existingtagnumber = $info['tagnumber'];
							$existingtagcolor  = $info['tagcolor'];
							$existingbreed = $info['breed'];
							$existinggender = $info['gender'];
							$existingborn = $info['born'];
							$existingage = $info['age'];
							$existingweight = $info['weight'];
							$existingremarks = $info['remarks'];
							$existingcost = $info['cost'];
							$existinginclusiondate = $info['inclusiondate'];
							$existingmother = $info['mother'];
							$existingfather = $info['father'];
							$existingvalue = $info['value'];
						}

						if ( $tagnumber == $existingtagnumber && $tagcolor == $existingtagcolor && $breed == $existingbreed && $gender == $existinggender && $born == $existingborn && $age == $existingage && $weight == $existingweight && $remarks == $existingremarks && $cost == $existingcost && $inclusiondate == $existinginclusiondate && $mother == $existingmother && $father == $existingfather	) {
							$responseArray = array('id' => 'danger', 'message' => 'البيانات المدخله مطابقه للسابق');		goto end;
						} 
						
						// check if another sheep exists with the same updated data
						if ( isset($_POST['tagnumber']) && !empty($_POST['tagnumber'])  ) {
							$record = mysqli_query($link,"SELECT * FROM `sheep` WHERE tagnumber = '$tagnumber' AND tagcolor = '$tagcolor' AND userid = '$userid' AND mrahid = '$mrahid' AND status IN ('live', 'sick') AND id != '$id'" );
							if(@mysqli_num_rows($record) > 0){
								$responseArray = array('id' => 'danger', 'message' => 'البيانات المدخله مطابقه لأحد الأغنام الأخرى');	goto end;
							}
						}
						
						if ( !empty($existingtagnumber) ) {		$log .= ' ووسم رقم '.$existingtagnumber;		}
						if ( !empty($existingtagcolor) ) {		$log .= ' ووسم '.arabic($existingtagcolor);		}

						// if ( $status !== $existingstatus ) {
							// $log .= ' تغيير الحاله الصحيه من '.arabic($existingstatus).' إلى '.arabic($status);	
							// if ( $status == 'sick' ) {
								// $statuslog = 'يرجى إضافة حدث بنوع المرض';
							// } else {
								// $statuslog = 'يرجى إضافة حدث بالتعافي من المرض';
							// }
						// }

						if ( $tagnumber !== $existingtagnumber ) {
							if ( empty($existingtagnumber) ) {	$log .= ' تغيير رقم الوسم إلى '.$tagnumber;	
							} elseif ( empty($tagnumber) )  {	$log .= ' حذف رقم الوسم '.$existingtagnumber;
							} else {							$log .= ' تغيير رقم الوسم من '.$existingtagnumber.' إلى '.$tagnumber;	} 
						}

						if ( $tagcolor !== $existingtagcolor ) {
							if ( empty($existingtagcolor) ) {	$log .= ' تغيير لون الوسم إلى '.arabic($tagcolor);	
							} elseif ( empty($tagcolor) )  {	$log .= ' حذف لون الوسم '.arabic($existingtagcolor);
							} else {							$log .= ' تغيير لون الوسم من '.arabic($existingtagcolor).' إلى '.arabic($tagcolor);	} 
						}

						if ( $breed !== $existingbreed ) {
							if ( empty($existingbreed) ) {		$log .= ' تغيير الصنف إلى '.arabic($breed);	
							} elseif ( empty($breed) )  {		$log .= ' حذف الصنف '.arabic($existingbreed);
							} else {							$log .= ' تغيير الصنف من '.arabic($existingbreed).' إلى '.arabic($breed);	} 
						}

						if ( $gender !== $existinggender ) {
							if ( empty($existinggender) ) {		$log .= ' تغيير الجنس إلى '.arabic($gender);	
							} elseif ( empty($gender) )  {		$log .= ' حذف الجنس '.arabic($existinggender);
							} else {							$log .= ' تغيير الجنس من '.arabic($existinggender).' إلى '.arabic($gender);	}
						}
						if ( $born !== $existingborn ) {
							if ( $existingage == 0 ) { 	$log .= ' تغييرها إلى مولود في المراح ';	}
							if ( $existingage == 1 ) { 	$log .= ' تغييرها إلى أصل رأس المال ';	}
							if ( empty($existingage) && $existingage != 0 ) {	$log .= ' تغيير العمر إلى '.mtoy($age);	
							} elseif ( empty($age) && $age != 0 )  {			$log .= ' حذف العمر '.mtoy($existingage);
							} else {											$log .= ' تغيير العمر من '.mtoy($existingage).' إلى '.mtoy($age);	} 
						}
						if ( $age !== $existingage ) {
							if ( empty($existingage) && $existingage != 0 ) {	$log .= ' تغيير العمر إلى '.mtoy($age);	
							} elseif ( empty($age) && $age != 0 )  {			$log .= ' حذف العمر '.mtoy($existingage);
							} else {											$log .= ' تغيير العمر من '.mtoy($existingage).' إلى '.mtoy($age);	} 
						}
						if ( $weight !== $existingweight ) {
							if ( empty($existingweight) ) {		$log .= ' تغيير الوزن إلى '.$weight.' كغم ';	
							} elseif ( empty($weight) )  {		$log .= ' حذف الوزن '.$existingweight.' كغم ';
							} else {							$log .= ' تغيير الوزن من '.$existingweight.' إلى '.$weight.' كغم ';	} 
						}
						if ( $remarks !== $existingremarks ) {
							if ( empty($existingremarks) ) {	$log .= ' تغيير الملاحظات إلى '.$remarks;	
							} elseif ( empty($remarks) )  {		$log .= ' حذف الملاحظات '.$existingremarks;
							} else {							$log .= ' تغيير الملاحظات من '.$existingremarks.' إلى '.$remarks;	} 
						}
						if ( $cost !== $existingcost ) {
							if ( empty($existingcost) ) {		$log .= ' تغيير سعر الشراء إلى '.$cost.' ريال ';	
							} elseif ( empty($cost) )  {		$log .= ' حذف سعر الشراء '.$existingcost.' ريال ';
							} else {							$log .= ' تغيير سعر الشراء من '.$existingcost.' إلى '.$cost.' ريال ';	} 
						}
						
						if ( $inclusiondate !== $existinginclusiondate ) {
							if ( empty($existinginclusiondate) && $existinginclusiondate != 0 ) {	
								$log .= ' تغيير تاريخ الضم للمراح إلى قبل '.mtoy($inclusiondate);	
							} elseif ( empty($inclusiondate) && $inclusiondate != 0 )  {	
								$log .= ' حذف تاريخ الضم للمراح قبل '.mtoy($existinginclusiondate);
							} else {					
								$log .= ' تغيير تاريخ الضم للمراح من قبل '.mtoy($existinginclusiondate).' إلى قبل '.mtoy($inclusiondate);	} 
						}
						if ( $mother !== $existingmother ) {
							if ( empty($existingmother) ) {	$log .= ' تغيير الأم إلى '.arabic($mother);
							} elseif ( empty($mother) )  {	$log .= ' حذف الأم '.arabic($existingmother);
							} else {						$log .= ' تغيير الأم من '.arabic($existingmother).' إلى '.arabic($mother);	} 
						}

						if ( $father !== $existingfather ) {
							if ( empty($existingfather) ) {	$log .= ' تغيير الأب إلى '.arabic($father);
							} elseif ( empty($father) )  {	$log .= ' حذف الأب '.arabic($existingfather);
							} else {						$log .= ' تغيير الأب من '.arabic($existingfather).' إلى '.arabic($father);	} 
						}
						$log .= ' لمراح '.$mrahname;
												
						$update = mysqli_query($link,"UPDATE `sheep` SET `tagnumber`='$tagnumber'
						, `tagcolor`='$tagcolor' 
						, `breed`='$breed' 
						, `gender`='$gender' 
						, `born`='$born' 
						, `age`='$age' 
						, `weight`='$weight' 
						, `remarks`='$remarks' 
						, `cost`='$cost' 
						, `inclusiondate`='$inclusiondate' 
						, `father`='$father' 
						, `mother`='$mother' 
						, `timeedited`='$time' 						
						WHERE `id`='$id'");
						
						
						if ($update) {	
							$responseArray = array('id' => 'success', 'message' => 'تم التحديث بنجاح' );
							if ( isset($statuslog) ) {			$responseArray['message'] .= '. '.$statuslog;	}

							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','sheep','تحديث رأس حلال','$log','$time' )");
							
							// change of age triggers change of agestamp
							if ( $age !== $existingage	) {		$update = mysqli_query($link,"UPDATE `sheep` SET `agestamp`='$time'	WHERE `id`='$id'");		} 

						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $update faild');
						}
						
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على رأس الأغنام المطلوب');	
					}
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'add' )   {
				if (!isset($_SESSION['mrahid']) || empty($_SESSION['mrahid']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لا يوجد معرف للمراح');
				} elseif (!isset($_POST['breed']) || empty($_POST['breed']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'اختيار الصنف مطلوب');
				} elseif (!isset($_POST['gender']) || empty($_POST['gender']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'اختيار الجنس مطلوب');
				} else {
					$mrahid = $_SESSION["mrahid"];
					$mrahname = $_SESSION["mrahname"];
					$status = 'live';
					$breed = mysqli_real_escape_string($link, $_POST['breed']);
					$gender = mysqli_real_escape_string($link, $_POST['gender']);
					
					$log .= 'تم إضافة رأس غنم صنف '.$breed;
					$log .= ' جنس '.$gender;

					if ( isset($_POST['tagcolor']) && !empty($_POST['tagcolor']) ) {
						$tagcolor = mysqli_real_escape_string($link, $_POST['tagcolor']);			$log .= ' بلون وسم '.$tagcolor;
					} else {
						$tagcolor = 'none';
					}
					
					if ( isset($_POST['tagnumber']) && !empty($_POST['tagnumber']) ) {
						$tagnumber = mysqli_real_escape_string($link, $_POST['tagnumber']);			$log .= ' ورقم '.$tagnumber;
					} else {
						$tagnumber = '';
					}

					if ( isset($_POST['born']) && !empty($_POST['born'] ) )  {
						$born = '1';			$log .= ' مولود في المراح ';
					} else {
						$born = '0';
					}

					if ( isset($_POST['age']) && is_numeric($_POST['age']) ) {
						$age = mysqli_real_escape_string($link, $_POST['age']);						$log .= ' وعمر '.$age;
					} else {
						$age = '';
					}

					if ( isset($_POST['weight']) && !empty($_POST['weight']) ) {
						$weight = mysqli_real_escape_string($link, $_POST['weight']);				$log .= ' ووزن '.$weight;
					} else {
						$weight = '';
					}

					if ( isset($_POST['remarks']) && !empty($_POST['remarks']) ) {
						$remarks = mysqli_real_escape_string($link, $_POST['remarks']);				$log .= ' وملاحظات '.$remarks;
					} else {
						$remarks = '';
					}

					if ( isset($_POST['cost']) && !empty($_POST['cost']) ) {
						$cost = mysqli_real_escape_string($link, $_POST['cost']);					$log .= ' وسعر شراء '.$cost;
					} else {
						$cost = '';
					}

					if ( isset($_POST['inclusiondate']) && is_numeric($_POST['inclusiondate']) ) {
						$inclusiondate = mysqli_real_escape_string($link, $_POST['inclusiondate']);	$log .= ' وتاريخ ضم للمراح '.$inclusiondate;
					} else {
						$inclusiondate = '';
					}

					$log .= ' لمراح '.$mrahname;

					// if ( isset($_POST['tagnumber']) && !empty($_POST['tagnumber']) && isset($_POST['tagcolor']) && !empty($_POST['tagcolor']) ) {
					if ( isset($_POST['tagnumber']) && !empty($_POST['tagnumber'])  ) {
						$record = mysqli_query($link,"SELECT * FROM `sheep` WHERE tagnumber = '$tagnumber' AND tagcolor = '$tagcolor' AND userid = '$userid' AND mrahid = '$mrahid' AND status IN ('live', 'sick') ");
						if(@mysqli_num_rows($record) > 0){
							$responseArray = array('id' => 'danger', 'message' => 'رقم ولون الوسم مضاف مسبقاً');		goto end;	
						}
					}

					$ins = mysqli_query($link,"INSERT INTO `sheep`( `id`,`userid`,`mrahid`,`status`,`tagnumber`,`tagcolor`,`breed`,`gender`,`born`,`age`,`weight`,`remarks`,`cost`,`inclusiondate`,`events`,`father`,`mother`,`value`,`agestamp`,`timeadded`,`timeedited` )VALUES(	NULL,'$userid','$mrahid','$status','$tagnumber','$tagcolor','$breed','$gender','$born','$age','$weight','$remarks','$cost','$inclusiondate','','','','','$time','$time',NULL )");			

					if ($ins) {	
						$finder = mysqli_query($link,"SELECT * FROM `sheep` ORDER BY `id` DESC LIMIT 1");
						if(@mysqli_num_rows($finder) > 0){
							while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ 
								$newid = $idfinder['id']; break;
							}
						}
						$responseArray = array('id' => 'success', 'message' => 'تمت الإضافة بنجاح', 'lastid' => $newid );
						$log = arabic($log);
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','sheep','إضافة رأس حلال','$log','$time' )");

					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $ins faild');
					}
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'addmultiple' )   {
				if (!isset($_SESSION['mrahid']) || empty($_SESSION['mrahid']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لا يوجد معرف للمراح');
				} elseif (!isset($_POST['total']) || empty($_POST['total']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'إدخال العدد الإجمالي للأغنام مطلوب');
				} elseif (!isset($_POST['breed']) || empty($_POST['breed']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'اختيار الصنف مطلوب');
				} elseif (!isset($_POST['gender']) || empty($_POST['gender']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'اختيار الجنس مطلوب');
				// } elseif ( isset($_POST['cost']) && empty($_POST['cost']) )   {
					// $responseArray = array('id' => 'danger', 'message' => 'السعر لا يمكن أن يكون صفر. يرجى تركه فارغاً');
				} else {
					$mrahid = $_SESSION["mrahid"];
					$mrahname = $_SESSION["mrahname"];
					$status = 'live';
					$total = mysqli_real_escape_string($link, $_POST['total']);
					$breed = mysqli_real_escape_string($link, $_POST['breed']);
					$gender = mysqli_real_escape_string($link, $_POST['gender']);
					
					$log .= 'تم إضافة عدد '.$total;
					$log .= ' رأس غنم صنف '.arabic($breed);
					$log .= ' جنس '.arabic($gender);

					if ( isset($_POST['tagcolor']) && !empty($_POST['tagcolor'] ) )  {
						$tagcolor = mysqli_real_escape_string($link, $_POST['tagcolor']);			$log .= ' بلون وسم '.arabic($tagcolor);
					} else {
						$tagcolor = 'none';
					}
					
					if ( isset($_POST['tagnumber']) && !empty($_POST['tagnumber'] ) )  {
						$tagnumber = mysqli_real_escape_string($link, $_POST['tagnumber']);			
						$log .= ' بأرقام وسم تبدأ من '.$tagnumber;
						$log .= ' حتى '.$tagnumber+$total-1;
					} else {
						$tagnumber = '';
					}

					if ( isset($_POST['born']) && !empty($_POST['born'] ) )  {
						$born = '1';			$log .= ' مولوده في المراح ';
					} else {
						$born = '0';
					}

					if ( isset($_POST['age']) && is_numeric($_POST['age']) ) {
						$age = mysqli_real_escape_string($link, $_POST['age']);						$log .= ' وعمر '.mtoy($age);
					} else {
						$age = '';
					}

					if ( isset($_POST['weight']) && !empty($_POST['weight'] ) )  {
						$weight = mysqli_real_escape_string($link, $_POST['weight']);				$log .= ' ووزن '.$weight.' كفم';
					} else {
						$weight = '';
					}

					if ( isset($_POST['remarks']) && !empty($_POST['remarks'] ) )  {
						$remarks = mysqli_real_escape_string($link, $_POST['remarks']);				$log .= ' مع ملاحظات '.$remarks;
					} else {
						$remarks = '';
					}

					if ( isset($_POST['cost']) && !empty($_POST['cost']) ) {
						$cost = mysqli_real_escape_string($link, $_POST['cost']);					$log .= ' وسعر شراء '.$cost.' ريال';
					} else {
						$cost = '';
					}

					if ( isset($_POST['inclusiondate']) && !empty($_POST['inclusiondate']) ) {
						$inclusiondate = mysqli_real_escape_string($link, $_POST['inclusiondate']);	$log .= ' وتاريخ ضم للمراح قبل '.mtoy($inclusiondate);
					} else {
						$inclusiondate = '';
					}

					$log .= ' لمراح '.$mrahname;

					if ( isset($tagnumber) && !empty($tagnumber)  ) {
						for($x=$tagnumber;$x<$total+$tagnumber;$x++){ 
							$record = mysqli_query($link,"SELECT * FROM `sheep` WHERE tagnumber = '$x' AND tagcolor = '$tagcolor' AND userid = '$userid' AND mrahid = '$mrahid' AND status IN ('live', 'sick') ");
							if(@mysqli_num_rows($record) > 0){								
								$responseArray = array('id' => 'danger', 'message' => 'رقم ولون الوسم مضاف مسبقاً. رقم الوسم '.$x.' '.arabic($tagcolor));		goto end;	
							}
						}
					}

					if ( isset($tagnumber) && !empty($tagnumber)  ) {
						for($x=$tagnumber;$x<$total+$tagnumber;$x++){ 	// start from tag number
							$ins = mysqli_query($link,"INSERT INTO `sheep`( `id`,`userid`,`mrahid`,`status`,`tagnumber`,`tagcolor`,`breed`,`gender`,`born`,`age`,`weight`,`remarks`,`cost`,`inclusiondate`,`events`,`father`,`mother`,`value`,`agestamp`,`timeadded`,`timeedited` )VALUES(	NULL,'$userid','$mrahid','$status','$x','$tagcolor','$breed','$gender','$born','$age','$weight','$remarks','$cost','$inclusiondate','','','','','$time','$time',NULL )");			
						}
					} else {
						for($x=0;$x<$total;$x++){ 						// no tag number so empty
							$ins = mysqli_query($link,"INSERT INTO `sheep`( `id`,`userid`,`mrahid`,`status`,`tagnumber`,`tagcolor`,`breed`,`gender`,`born`,`age`,`weight`,`remarks`,`cost`,`inclusiondate`,`events`,`father`,`mother`,`value`,`agestamp`,`timeadded`,`timeedited` )VALUES(	NULL,'$userid','$mrahid','$status','$tagnumber','$tagcolor','$breed','$gender','$born','$age','$weight','$remarks','$cost','$inclusiondate','','','','','$time','$time',NULL )");			
						}
					}
					
					if ($ins) {	
						$finder = mysqli_query($link,"SELECT * FROM `sheep` ORDER BY `id` DESC LIMIT 1");
						if(@mysqli_num_rows($finder) > 0){
							while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ 
								$newid = $idfinder['id']; break;
							}
						}
						$responseArray = array('id' => 'success', 'message' => 'تمت الإضافة بنجاح', 'lastid' => $newid );
						// $log = arabic($log);
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','sheep','إضافة حلال بالجمله','$log','$time' )");

					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $ins faild');
					}
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'event' )   {
				if (!isset($_SESSION['mrahid']) || empty($_SESSION['mrahid']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لا يوجد معرف للمراح');
				} elseif (!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لا يوجد معرف لرأس الأغنام');
				} elseif (!isset($_POST['eventtext']) || empty($_POST['eventtext']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'وصف الحدث مطلوب');
				} else {
					$id = mysqli_real_escape_string($link, $_POST['id']);
					$event = mysqli_real_escape_string($link, $_POST['eventtext']);
					
					if (isset($_POST['eventtime']) && !empty($_POST['eventtime']) )   {
						$time = $time - ( $_POST['eventtime'] * 86400 );
					}

					// check if sheep exists in feed table
					$record = mysqli_query($link,"SELECT * FROM `sheep` WHERE id = '$id' AND userid = '$userid' AND mrahid = '$mrahid'");
					if(@mysqli_num_rows($record) < 1){
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على رأس الأغنام المطلوب');	
					} else {	
						while($info = mysqli_fetch_array($record, MYSQLI_ASSOC)){ 							
							$existingevent = $info['events'];
							$existintagnumber = $info['tagnumber'];
							$existintagcolor = $info['tagcolor'];
							$fulltag = '';
							if ( !empty($existintagnumber) ) { 	$fulltag .= $existintagnumber; }
							if ( !empty($existintagcolor) ) { 	$fulltag .= ' '.arabic($existintagcolor); }

						}
						
						$log .= 'تم إضافة حدث لرأس غنم بمعرف رقم '.$id;
						if ( !empty($fulltag) ) { 	
							$log .= ' بوسم '.$fulltag;
						}
						$log .= ' بوصف '.$event;

						
						$fullevent = $event.'^'.$time;

						if ( !empty($existingevent) ) {
							$eventarray = explode(",",$existingevent);	// convert to array
						} 

						array_push($eventarray,$fullevent);
						
						eventsorder($eventarray);
						
						$eventarray = implode(",",$eventarray);			// convert to string


						if (str_contains($event, 'إصابة بمرض')) {
							$updateevent = mysqli_query($link,"UPDATE `sheep` SET `events`='$eventarray', `status`='sick' WHERE `id`='$id'");
							$log .= ' وتغيير الحاله الصحيه من سليم إلى مريض ';
						} elseif (str_contains($event, 'تشافي من مرض')) {
							$updateevent = mysqli_query($link,"UPDATE `sheep` SET `events`='$eventarray', `status`='live' WHERE `id`='$id'");
							$log .= ' وتغيير الحاله الصحيه من مريض إلى سليم ';
						} else {
							$updateevent = mysqli_query($link,"UPDATE `sheep` SET `events`='$eventarray'	WHERE `id`='$id'");
						}
						
						$log .= ' لمراح '.$mrahname;

						if ($updateevent) {	
							$responseArray = array('id' => 'success', 'message' => 'تم التحديث بنجاح' );
							// if ( isset($statuslog) ) {			$responseArray['message'] .= '. '.$statuslog;	}

							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','sheep','إضافة حدث فردي','$log','$time' )");
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $updateevent faild');
						}
					} 
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'multipleevent' )   {
				if (!isset($_SESSION['mrahid']) || empty($_SESSION['mrahid']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لا يوجد معرف للمراح');
				} elseif (!isset($_POST['eventtext']) || empty($_POST['eventtext']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'وصف الحدث مطلوب');
				} else {
					$event = mysqli_real_escape_string($link, $_POST['eventtext']);
					
					if (isset($_POST['eventtime']) && !empty($_POST['eventtime']) )   {
						$time = $time - ( $_POST['eventtime'] * 86400 );
					}

					if (isset($_POST['sheep']) && is_array($_POST['sheep'])) {
						$sheeps = $_POST['sheep'];

						foreach ($sheeps as $sheep) {
							$eventarray = [];
							// check if sheep exists in feed table
							$record = mysqli_query($link,"SELECT * FROM `sheep` WHERE id = '$sheep' AND userid = '$userid' AND mrahid = '$mrahid'");
							if(@mysqli_num_rows($record) < 1){
								$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على أحد رؤوس الأغنام');	 	goto end;
							} else {	
								while($info = mysqli_fetch_array($record, MYSQLI_ASSOC)){ 							
									$existingevent = $info['events'];
									$existintagnumber = $info['tagnumber'];
									$existintagcolor = $info['tagcolor'];
									$fulltag = '';
									if ( !empty($existintagnumber) ) { 	$fulltag .= $existintagnumber; }
									if ( !empty($existintagcolor) ) { 	$fulltag .= ' '.arabic($existintagcolor); }
									if ( empty($fulltag) ) { 	$fulltag = 'بدون وسم'; }
								}
								
								$fullevent = $event.'^'.$time;

								if ( !empty($existingevent) ) {
									$eventarray = explode(",",$existingevent);	// convert to array
								} 

								array_push($eventarray,$fullevent);
								eventsorder($eventarray);								
								$eventarray = implode(",",$eventarray);			// convert to string
								
								if (str_contains($event, 'إصابة بمرض')) {
									$updateevent = mysqli_query($link,"UPDATE `sheep` SET `events`='$eventarray', `status`='sick' WHERE `id`='$sheep'");
								} elseif (str_contains($event, 'تشافي من مرض')) {
									$updateevent = mysqli_query($link,"UPDATE `sheep` SET `events`='$eventarray', `status`='live' WHERE `id`='$sheep'");
								} else {
									$updateevent = mysqli_query($link,"UPDATE `sheep` SET `events`='$eventarray'	WHERE `id`='$sheep'");
								}
														
								// $updateevent = mysqli_query($link,"UPDATE `sheep` SET `events`='$eventarray'	WHERE `id`='$sheep'");
								
								if ($updateevent) {	
									$responseArray = array('id' => 'success', 'message' => 'تم التحديث بنجاح' );
									array_push($sheeparray,$sheep);
									array_push($tagarray,$fulltag);
								} else {
									$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $updateevent faild');
								}
							} 
						}
						
						$sheeparray = implode(", ",$sheeparray);			// convert to string
						$tagarray = implode(", ",$tagarray);				// convert to string
						$log .= 'تم إضافة حدث جماعي للغنم بالمعرفات رقم '.$sheeparray;
						$log .= ' بالوسوم التاليه '.$tagarray;
						$log .= ' بوصف '.$event;
						$log .= ' لمراح '.$mrahname;
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','sheep','إضافة حدث جماعي','$log','$time' )");

					} else {
						$responseArray = array('id' => 'danger', 'message' => 'اختيار الأغنام مطلوب');
					}
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'delevent' )   {
				if (!isset($_SESSION['mrahid']) || empty($_SESSION['mrahid']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لا يوجد معرف للمراح');
				} elseif (!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لا يوجد معرف لرأس الأغنام');
				// } elseif (!isset($_POST['eventtext']) || empty($_POST['eventtext']) )   {
					// $responseArray = array('id' => 'danger', 'message' => 'وصف الحدث مطلوب');
				} else {
					$eventarray = [];
					$id = mysqli_real_escape_string($link, $_POST['id']);
					if ( isset($_POST['evenunixtime']) && !empty($_POST['evenunixtime'] ) ) {	$evenunixtime = $_POST['evenunixtime'];	}
					if ( isset($_POST['eventdescription']) && !empty($_POST['eventdescription'] ) ) {	$eventdescription = $_POST['eventdescription'];	}

					if ( isset($evenunixtime) && !empty($evenunixtime) && isset($eventdescription) && !empty($eventdescription) ) {		//echo 'eventdata exists';
						for ($i = 0; $i < count($evenunixtime); $i++) {
							$fullevent = $eventdescription[$i].'^'.$evenunixtime[$i];
							array_push($eventarray,$fullevent);
						}
						eventsorder($eventarray);						// var_dump ($eventarray);
					}
						
					// check if sheep exists in feed table
					$record = mysqli_query($link,"SELECT * FROM `sheep` WHERE id = '$id' AND userid = '$userid' AND mrahid = '$mrahid'");
					if(@mysqli_num_rows($record) < 1){
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على رأس الأغنام المطلوب');	
					} else {	
						while($info = mysqli_fetch_array($record, MYSQLI_ASSOC)){ 							
							$existingevent = $info['events'];
							$existintagnumber = $info['tagnumber'];
							$existintagcolor = $info['tagcolor'];
							$fulltag = '';
							if ( !empty($existintagnumber) ) { 	$fulltag .= $existintagnumber; }
							if ( !empty($existintagcolor) ) { 	$fulltag .= ' '.arabic($existintagcolor); }
						}
						
						$log .= 'تم حذف حدث لرأس غنم بمعرف رقم '.$id;
						if ( !empty($fulltag) ) { 	
							$log .= ' بوسم '.$fulltag;
						}
						// $log .= ' بوصف '.$event;
						$log .= ' لمراح '.$mrahname;

						if ( !empty($existingevent) ) {	       
							$existingevent = explode(",",$existingevent);	// convert to array
						} 

						if ($eventarray === $existingevent) {					//echo "Same values (order ignored)";
							$responseArray = array('id' => 'warning', 'message' => 'لم يتم إجراء أي تغيير على الأحداث');	

						} else {												//echo "Not Same values (order ignored)";
							$eventarray = implode(", ",$eventarray);				// convert to string
							$updateevent = mysqli_query($link,"UPDATE `sheep` SET `events`='$eventarray'	WHERE `id`='$id'");

							if ($updateevent) {	
								$responseArray = array('id' => 'success', 'message' => 'تم التحديث بنجاح' );

								$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','sheep','حذف حدث فردي','$log','$time' )");
							} else {
								$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $updateevent faild');
							}
						}
					} 
				}				
			} else {
				if(isset($_POST['id']) && !empty($_POST['id']))	{	
					$id = mysqli_real_escape_string($link, $_POST['id']);	
					$record = mysqli_query($link,"SELECT * FROM `sheep` WHERE id = '$id' AND userid = '$userid' AND mrahid = '$mrahid'    ");
				} else { 
					// $record = mysqli_query($link,"SELECT * FROM `sheep` WHERE userid = '$userid' AND mrahid = '$mrahid'    ");
					$record = mysqli_query($link,"SELECT * FROM `sheep` WHERE userid = '$userid' AND mrahid = '$mrahid' ORDER BY FIELD(tagcolor, 'blue', 'red', 'orange', 'green', 'white', 'yellow', 'pink'), tagnumber + 0 ");	// 	(tagnumber + 0) to covernt string to number
				}
				
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
						$born = $sheepinfo['born'];
						$age = $sheepinfo['age'];			$agear = mtoy($age);	$agearshort = mtoyshort($age);
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
								$temparray[2] = $temparray[1];
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

						${'sheepinfo'.$z} = array('id' => $id,'userid' => $userid,'mrahid' => $mrahid, 'status' => $status , 'statusar' => arabic($status) ,'tagnumber' => $tagnumber ,'tagcolor' => $tagcolor ,'tagcolorar' => arabic($tagcolor) ,'breed' => $breed ,'breedar' => arabic($breed) ,'gender' => $gender ,'genderar' => arabic($gender) ,'born' => $born,'age' => $age,'agear' => $agear,'agearshort' => $agearshort ,'weight' => $weight ,'remarks' => $remarks ,'cost' => $cost ,'inclusiondate' => $inclusiondate,'inclusiondatear' => $inclusiondatear ,'events' => $events,'father' => $father,'fatherar' => $fatherar,'mother' => $mother,'motherar' => $motherar,'value' => $value ,'timeadded' => $timeadded ,'timeedited' => $timeedited );	
						$z++;
					}
				}
				$responseArray = array('id' => 'success', 'sheepnum' => $sheepnum);
			}
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($sheepnum) && $sheepnum > 0 ){	for($a=0;$a<$sheepnum;$a++){ array_push($Totalsheepinfos,${'sheepinfo'.$a}); }	array_push($responseArray,$Totalsheepinfos); }
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message

?>

