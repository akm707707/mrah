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

			if(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'intake' )   {
				if (!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'معرف المياه مطلوب');
				} elseif(!isset($_POST['int']) || !is_numeric($_POST['int']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'كمية المياه مطلوبه');
				} else {
					$waterid = mysqli_real_escape_string($link, $_POST['id']);
					$int = mysqli_real_escape_string($link, $_POST['int']);
					$intake = $int;


					$record = mysqli_query($link,"SELECT * FROM `water` WHERE id = '$waterid'  ");
					if(@mysqli_num_rows($record) < 1){
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على المياه');		goto end;	
					} else {
						while($waterfetcher = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							$existingintake = $waterfetcher['dailyintake'];
							$type = $waterfetcher['type'];
							$unit = $waterfetcher['unit'];
						}
						if ( $intake === $existingintake ) {
							$responseArray = array('id' => 'warning', 'message' => 'الاستهلاك اليومي للمياه هو نفس السابق' );
						} else {
							$update = mysqli_query($link,"UPDATE `water` SET `dailyintake`='$intake' WHERE `id`='$waterid'");
							if ( $update ) {
								if (is_null($existingintake)) {
									$log .= 'تم تحديث الاستهلاك اليومي للمياه';
									$log .= ' إلى '.$intake;
									$log .= ' '.unitar($type).' يومياً';
								} else {
									$log .= 'تم تحديث الاستهلاك اليومي للمياه';
									$log .= ' من '.$existingintake;
									$log .= ' '.unitar($type).' يومياً';
									$log .= ' إلى '.$intake;
									$log .= ' '.unitar($type).' يومياً';
								}

								$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','water','تحديث الاستهلاك اليومي للمياه','$log','$time' )");
								$responseArray = array('id' => 'success', 'message' => 'تم تحديث الاستهلاك اليومي بنجاح');
							}
						}
					}
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'deletepurchase' )   {
				if (!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'معرف المشتريات مطلوب');
				} else {
					$purchaseid = mysqli_real_escape_string($link, $_POST['id']);

					$record = mysqli_query($link,"SELECT * FROM `purchases` WHERE userid = '$userid' AND mrahid = '$mrahid' AND id = '$purchaseid'  ");
					if(@mysqli_num_rows($record) < 1){
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على المشتريات');		goto end;	
					} else {
						while($purchaseinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							$purchaseid = $purchaseinfo['id'];
							$purchasetype = $purchaseinfo['type'];
							$purchasequantity = $purchaseinfo['quantity'];
							$purchaseunitcost = $purchaseinfo['unitcost'];
							$purchasetotalcost  = $purchaseinfo['totalcost'];
							$purchasesettlement  = $purchaseinfo['settlement'];
							$purchasepaid  = $purchaseinfo['paid'];
							$purchaseremarks  = $purchaseinfo['remarks'];
							$purchasesuppliername  = $purchaseinfo['suppliername'];
							$purchasesuppliermobile  = $purchaseinfo['suppliermobile'];
							$purchasetime = Time_Passed(date($purchaseinfo['time']),'time');
						}

						$log .= 'تم حذف مشتريات مياه بكمية '.$purchasequantity;
						$log .= ' '.unitar($purchasetype);
						$log .= ' لمراح '.$mrahname;
						$log .= ' بتكلفه فرديه '.$purchaseunitcost.' ريال  لكل لتر';
						$log .= ' وتكلفة إجماليه '.$purchasetotalcost.' ريال';

						if ( $purchasesettlement == 'full'  ) {	$log .= ' تم سدادها بالكامل'; 	};
						if ( $purchasesettlement == 'debt'  ) {	$log .= ' مدينه بالكامل'; 	};
						if ( $purchasesettlement == 'partial'  ) {	$log .= ' تم دفع '.$purchasepaid.' ريال فقط';		}
						if ( $purchasesuppliername !== ''  ) {	$log .= ' للبائع '.$purchasesuppliername.' ورقمه '.$purchasesuppliermobile;	}

						$record = mysqli_query($link,"SELECT * FROM `water` WHERE userid = '$userid' AND mrahid = '$mrahid' AND type = '$purchasetype'  ");
						if(@mysqli_num_rows($record) < 1){
							$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على المياه المرتبط بالمشتريات');		goto end;	
						} else {
							while($waterinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
								$waterid = $waterinfo['id'];
								$watertype = $waterinfo['type'];
								$waterunit = $waterinfo['unit'];
								$waterquantity = $waterinfo['quantity'];
								$waterunitcost  = $waterinfo['unitcost'];
								$waterdailyintake  = $waterinfo['dailyintake'];
							}
						
							if ( $purchasequantity > $waterquantity ) {
								$responseArray = array('id' => 'danger', 'message' => 'كمية المياه في المشريات أكثر من المياه المتوفر مما يعني انه تم إستهلاكه وبالتالي لا يمكن حذفه');		goto end;	
							}
							
							$delete = mysqli_query($link,"DELETE FROM `purchases` WHERE id = '$purchaseid' ");
							// $delete =  true;
							if (isset($delete)) {
								$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','purchase','حذف مشتريات مياه','$log','$time' )");
								$newquantity = $waterquantity - $purchasequantity;
								// Avoid Dividing By zero
								if ( $newquantity > 0 ) {
									$newunitcost = ( ( $waterquantity * $waterunitcost ) - ( $purchasequantity * $purchaseunitcost ) ) / $newquantity;
									$newunitcost = round($newunitcost, 3);  
								} else {
									$newunitcost = 0;
								}

								$updatelog .= 'بسبب حذف مشتريات مياه ';
								$updatelog .= 'تم تعديل كمية المياه ';
								$updatelog .= ' من '.$waterquantity;
								$updatelog .= ' إلى '.$newquantity;
								$updatelog .= ' '.unitar($watertype);
								if ( $waterunitcost != $newunitcost ) {
									$updatelog .= ' والسعر من '.$waterunitcost;
									$updatelog .= ' إلى '.$newunitcost.' ريال لكل لتر';
								}
								$updatelog .= ' لمراح '.$mrahname;
								
								$update = mysqli_query($link,"UPDATE `water` SET `quantity`='$newquantity', `unitcost`='$newunitcost' WHERE `id`='$waterid'");

								if (isset($update)) {
									$logins2 = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','water','تعديل كمية المياه','$updatelog','$time' )");

									$responseArray = array('id' => 'success', 'message' => 'تم حذف المشتريات بنجاح' );
									if ( $purchasesuppliername !== ''  ) {
										$record = mysqli_query($link,"SELECT * FROM `suppliers` WHERE userid = '$userid' AND mrahid = '$mrahid' AND suppliername = '$purchasesuppliername' ");
										if(@mysqli_num_rows($record) > 0){									
											while($supplierinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
												$supid = $supplierinfo['id'];
												$supname = $supplierinfo['suppliername'];
												$supmobile = $supplierinfo['suppliermobile'];
												$suptotal = $supplierinfo['total'];
												$suppaid = $supplierinfo['paid'];
												$suptime = Time_Passed(date($supplierinfo['time']),'time');
											}
											$newtotal = $suptotal - $purchasetotalcost;
											$newpaid = $suppaid - $purchasepaid;
											$supupdate = mysqli_query($link,"UPDATE `suppliers` SET `total`='$newtotal', `paid`='$newpaid' WHERE `id`='$supid'");
											
											if ($supupdate) {	
												$responseArray["message"] .= " . كما تم تحديث معلومات البائع بنجاح";
											} else {
												$responseArray["message"] .= " . فشل تحديث معلومات البائع ";
											}
										}
									}

								} else {
									$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات isset($update) Feed');
								}
							
							} else {
								$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات isset($delete) Purchase');
							}
							
						}
//// END Of Fetch Feed
					}
				}				
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'deletesupplier' )   {
				if (!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'معرف البائع مطلوب');
				} else {
					$supplierid = mysqli_real_escape_string($link, $_POST['id']);

					$record = mysqli_query($link,"SELECT * FROM `suppliers` WHERE userid = '$userid' AND mrahid = '$mrahid' AND id = '$supplierid'  ");
					if(@mysqli_num_rows($record) < 1){
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على البائع');		goto end;	
					} else {
						while($supplierinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							$id = $supplierinfo['id'];
							$suppliername = $supplierinfo['suppliername'];
							$suppliermobile = $supplierinfo['suppliermobile'];
							$total = $supplierinfo['total'];
							$paid = $supplierinfo['paid'];
						}
						
						$delete = mysqli_query($link,"DELETE FROM `suppliers` WHERE id = '$supplierid' ");
						if (isset($delete)) {	
							$responseArray = array('id' => 'success', 'message' => 'تم حذف البائع بنجاح');
							$log .= 'تم حذف البائع  '.$suppliername;
							$log .= ' برقم جوال  '.$suppliermobile;
							if ( $total > 0 ) {
								$log .= ' وباجمالي مشتريات  '.$total.' ريال';
								if ( $total == $paid ) { $log .= ' تم دقعها بالكامل'; } else { $log .= ' تم دقع'.$paid.' ريال منها فقط'; }
							} else {
								$log .= ' بلا مشتريات';
							}

							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','supplier','حذف بائع مياه','$log','$time' )");
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات isset($delete)');
						}
					}
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'edit' )   {
				if (!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'معرف المياه مطلوب');
				} elseif(!isset($_POST['quantity']) || !is_numeric($_POST['quantity']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'كمية المياه مطلوبه');
				} elseif(!isset($_POST['unitcost']) || !is_numeric($_POST['unitcost']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'تكلفة الوحده مطلوبه');
				} else {
					$id = mysqli_real_escape_string($link, $_POST['id']);
					$quantity = mysqli_real_escape_string($link, $_POST['quantity']);
					$unitcost = mysqli_real_escape_string($link, $_POST['unitcost']);
					
					// check if water exists in water table
					$record = mysqli_query($link,"SELECT * FROM `water` WHERE id = '$id' AND userid = '$userid' AND mrahid = '$mrahid'");
					if(@mysqli_num_rows($record) > 0){
						while($info = mysqli_fetch_array($record, MYSQLI_ASSOC)){ 							
							$existingtype = $info['type'];
							$name = arabic($existingtype);
							$existingquantity = $info['quantity'];
							$existingunitcost = $info['unitcost'];
							$unitar = unitar($existingtype);
						}

						if ( $quantity == $existingquantity && $unitcost == $existingunitcost ) {
							$responseArray = array('id' => 'danger', 'message' => 'كمية المياه وتكلفة الوحده  مطابقه للسابق');		goto end;
						} 
												
						$log .= 'تم تحديث المياه يدوياً ';
						$log .= ' بمعرف رقم '.$id;
						if ( $quantity !== $existingquantity ) {
							$log .= ' بنغيير الكميه من '.$existingquantity;
							$log .= ' إلى '.$quantity.' '.$unitar;
						} 

						if ( $unitcost !== $existingunitcost ) {
							$log .= ' وتغيير التكلفه الفرديه  من '.$existingunitcost;
							$log .= ' إلى '.$unitcost.' ريال لكل لتر';
						} 
						
						$update = mysqli_query($link,"UPDATE `water` SET `quantity`='$quantity', `unitcost`='$unitcost' WHERE `id`='$id'");
						
						if ($update) {	
							$responseArray = array('id' => 'success', 'message' => 'تم التحديث بنجاح' );

							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','water','تحديث المياه يدوياً','$log','$time' )");
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $update faild');
						}
						
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثوؤ على الهلف المطلوب');	
					}
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'editpurchase' )   {
				if (!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'معرف المياه مطلوب');
				} elseif(!isset($_POST['totalcost']) || empty($_POST['totalcost']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'التكلفة الكليه مطلوبه');
				} elseif(!isset($_POST['settlement']) || empty($_POST['settlement']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'حالة الدفع مطلوبه');
				} else {
					$id = mysqli_real_escape_string($link, $_POST['id']);
					$totalcost = mysqli_real_escape_string($link, $_POST['totalcost']);
					$settlement = mysqli_real_escape_string($link, $_POST['settlement']);

					if ( $settlement == 'partial'  ) {
						if (!isset($_POST['paid']) || empty($_POST['paid']) )   {
							$responseArray = array('id' => 'danger', 'message' => 'المبلغ الاضافي الذي تم سداده مطلوب');		goto end;	
						} elseif ( !is_numeric($_POST['paid']) )   {
							$responseArray = array('id' => 'danger', 'message' => 'المبلغ الجزئي المدفوع لابد أن يكون رقما أكبر من الصفر');		goto end;	
						} else {
							$paid = mysqli_real_escape_string($link, $_POST['paid']);
						}
					} else {
						$newpaid = $totalcost;
					}

					
					// check if water exists in water table
					$record = mysqli_query($link,"SELECT * FROM `purchases` WHERE id = '$id' AND userid = '$userid' AND mrahid = '$mrahid'");
					if(@mysqli_num_rows($record) > 0){
						while($info = mysqli_fetch_array($record, MYSQLI_ASSOC)){ 
							$existingtotalcost = $info['totalcost'];
							$existingpaid = $info['paid'];
							
							$existingtype = $info['type'];
							$name = arabic($existingtype);
							$existingquantity = $info['quantity'];
							$existingunitcost = $info['unitcost'];
							$existingsettlement = $info['settlement'];
							$existingremarks = $info['remarks'];
							$existingsuppliername = $info['suppliername'];
							$existingsuppliermobile = $info['suppliermobile'];
							$existingtime = Time_Passed(date($info['time']),'time');
							$unitar = unitar($existingtype);

						}
						
						if ( $settlement == 'partial'  ) {
							$newpaid = $existingpaid + $paid;
							if ( $newpaid > $totalcost ) {
								$responseArray = array('id' => 'danger', 'message' => 'المبلغ المدفوع أكثر من التكلفة الاجماليه');		goto end;
							} 
							if ( $newpaid == $totalcost ) {
								$responseArray = array('id' => 'danger', 'message' => 'المبلغ المدفوع مساوي للتكلفة الاجماليه. يرجى اختيار سداد كامل المبلغ');		goto end;
							} 
						}
						
						$log .= 'تم تحديث حالة الدفع لمشترى المياه '.$existingquantity.' '.$unitar;
						$log .= ' بمعرف رقم '.$id;
						$log .= ' من '.arabic($existingsettlement);
						$log .= ' بمقدار '.$existingpaid.' ريال';
						$log .= ' إلى '.arabic($settlement);
						$log .= ' بمقدار '.$newpaid.' ريال';

						if ( $existingsuppliername !== ''  ) {		$log .= ' للبائع '.$existingsuppliername;		}
						
						$update = mysqli_query($link,"UPDATE `purchases` SET `settlement`='$settlement', `paid`='$newpaid' WHERE `id`='$id'");
						
						if ($update) {	
							$responseArray = array('id' => 'success', 'message' => 'تم التحديث بنجاح' );
							///// update supplier
							if ( $existingsuppliername !== ''  ) {
								$record = mysqli_query($link,"SELECT * FROM `suppliers` WHERE userid = '$userid' AND mrahid = '$mrahid' AND suppliername = '$existingsuppliername' ");
								if(@mysqli_num_rows($record) > 0){									
									while($supplierinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
										$supid = $supplierinfo['id'];
										$supname = $supplierinfo['suppliername'];
										$supmobile = $supplierinfo['suppliermobile'];
										$suptotal = $supplierinfo['total'];
										$suppaid = $supplierinfo['paid'];
										$suptime = Time_Passed(date($supplierinfo['time']),'time');
									}
									if ( $settlement == 'partial'  ) {
										$newpaid = $paid + $suppaid;
									} else {
										$newpaid = ( $totalcost - $existingpaid ) + $suppaid;
									}
									$supupdate = mysqli_query($link,"UPDATE `suppliers` SET `paid`='$newpaid' WHERE `id`='$supid'");
									if ($supupdate) {	
										$responseArray["message"] .= " . كما تم تحديث معلومات البائع بنجاح";
									} else {
										$responseArray["message"] .= " . فشل تحديث معلومات البائع ";
									}
								}
							}
							///// End of add supplier								
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','purchase','تحديث حالة الدفع لمشتريات مياه','$log','$time' )");
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $update faild');
						}
						
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثوؤ على المشتريات');	
					}
				}	
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'add' )   {
				if (!isset($_SESSION['mrahid']) || empty($_SESSION['mrahid']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لا يوجد معرف للمراح');
				} elseif (!isset($_POST['type']) || empty($_POST['type']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'اختيار الصنف مطلوب');
				} elseif (!isset($_POST['quantity']) || empty($_POST['quantity']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'اختيار الكميه مطلوب');
				} elseif (!isset($_POST['totalcost']) || empty($_POST['totalcost']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'التكلفة الاجماليه مطلوبه');
				} elseif (!isset($_POST['settlement']) || empty($_POST['settlement']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'حالة الدفع مطلوبه');
				} else {
					$mrahid = $_SESSION["mrahid"];
					$mrahname = $_SESSION["mrahname"];
					$type = mysqli_real_escape_string($link, $_POST['type']);
					$quantity = mysqli_real_escape_string($link, $_POST['quantity']);
					$unit = mysqli_real_escape_string($link, $_POST['unit']);
					$totalcost = mysqli_real_escape_string($link, $_POST['totalcost']);
					$settlement = mysqli_real_escape_string($link, $_POST['settlement']);
					$remarks = mysqli_real_escape_string($link, $_POST['remarks']);
					$suppliername = mysqli_real_escape_string($link, $_POST['suppliername']);
					$suppliermobile = mysqli_real_escape_string($link, $_POST['suppliermobile']);
					
					$unitcost = $totalcost / $quantity;							$unitcost = round($unitcost, 8);   

					$log .= 'تم إضافة مشتريات مياه جديده '.$quantity;
					$log .= ' '.unitar($type);
					// $log .= ' '.arabic($type);
					$log .= ' لمراح '.$mrahname;
					$log .= ' بتكلفه فرديه '.$unitcost.' ريال لكل لتر';
					$log .= ' وتكلفة إجماليه '.$totalcost.' ريال';
					
					if ( $settlement == 'full'  ) {	$paid = $totalcost;		$log .= ' تم سدادها بالكامل'; 	};
					if ( $settlement == 'debt'  ) {	$paid = 0;		$log .= ' مدينه بالكامل'; };
					
					if ( $settlement == 'partial'  ) {
						if (!isset($_POST['paid']) || empty($_POST['paid']) || $_POST['paid'] == 0 )   {
							$responseArray = array('id' => 'danger', 'message' => 'المبلغ الجزئي المدفوع مطلوب');		goto end;	
						} else {
							$paid = mysqli_real_escape_string($link, $_POST['paid']);
							if ( $paid >= $totalcost )   {
								$responseArray = array('id' => 'danger', 'message' => 'المبلغ الجزئي المدفوع لايمكن أن يكون مساوي أو أكبر من المبلغ الاجمالي');		goto end;
							}
							$log .= ' تم دفع '.$paid.' فقط';
						}
					}
					
					if ( $remarks !== ''  ) {	$log .= ' مع ملاحظات '.$remarks; 	};
					if ( $suppliername !== ''  ) {	$log .= ' للبائع '.$suppliername; 	};
					if ( $suppliermobile !== ''  ) {	$log .= ' رقم جوال '.$suppliermobile; 	};

					// check if water exists in water table
					$record = mysqli_query($link,"SELECT * FROM `water` WHERE type = '$type' AND userid = '$userid' AND mrahid = '$mrahid'");
					if(@mysqli_num_rows($record) > 0){
						while($info = mysqli_fetch_array($record, MYSQLI_ASSOC)){ 
							$waterid = $info['id'];
							$watertype = $info['type'];
							$waterunit = $info['unit'];
							$waterquantity = $info['quantity'];
							$waterunitcost = $info['unitcost'];
							$waterdailyintake = $info['dailyintake'];
						}
						$newquantity = $waterquantity + $quantity;
						$newcost = ( ($waterquantity*$waterunitcost) + ($quantity*$unitcost) ) / $newquantity;
						$newcost = round($newcost, 8);   
						$update = mysqli_query($link,"UPDATE `water` SET `quantity`='$newquantity', `unitcost`='$newcost' WHERE `id`='$waterid'");

						$updatelog .= 'بسبب إضافة مشتريات مياه جديده ';
						$updatelog .= 'تم تعديل كمية '.arabic($type);
						$updatelog .= ' من '.$waterquantity;
						$updatelog .= ' إلى '.$newquantity;
						$updatelog .= ' '.unitar($type);
						if ( $waterunitcost != $newcost ) {
							$updatelog .= ' والسعر من '.$waterunitcost.' ريال';
							$updatelog .= ' إلى '.$newcost.' ريال';
						}
						$updatelog .= ' لمراح '.$mrahname;
						
						
					} else {	// Adding new Feed
						$ins1 = mysqli_query($link,"INSERT INTO `water`( `id`,`userid`,`mrahid`,`type`,`unit`,`quantity`,`unitcost`,`dailyintake` )VALUES(	NULL,'$userid','$mrahid','$type','$unit','$quantity','$unitcost',NULL )");
						
						$updatelog .= 'بسبب إضافة مشتريات مياه جديده ';
						$updatelog .= 'تم إضافة كمية مياه جديده ';
						$updatelog .= 'بمقدار '.$quantity;
						$updatelog .= ' '.unitar($type);
						$updatelog .= ' وتكلفة فرديه '.$unitcost.' ريال لكل لتر';
						$updatelog .= ' لمراح '.$mrahname;

					}
					
					// Entry into History
					if ( isset($ins1) || isset($update) ) {	

						$ins2 = mysqli_query($link,"INSERT INTO `purchases`( `id`,`userid`,`mrahid`,`category`,`type`,`quantity`,`unitcost`,`totalcost`,`settlement`,`paid`,`remarks`,`suppliername`,`suppliermobile`,`time` )VALUES(	NULL,'$userid','$mrahid','water','$type','$quantity','$unitcost','$totalcost','$settlement','$paid','$remarks','$suppliername','$suppliermobile','$time' )");			

						if ($ins2) {	
							$responseArray = array('id' => 'success', 'message' => 'تمت الإضافة بنجاح' );
							///// add supplier
							if ( $suppliername !== ''  ) {
								$record = mysqli_query($link,"SELECT * FROM `suppliers` WHERE userid = '$userid' AND mrahid = '$mrahid' AND suppliername = '$suppliername'    ");
								if(@mysqli_num_rows($record) > 0){
									while($supplierinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
										$supid = $supplierinfo['id'];
										$supname = $supplierinfo['suppliername'];
										$supmobile = $supplierinfo['suppliermobile'];
										$suptotal = $supplierinfo['total'];
										$suppaid = $supplierinfo['paid'];
										$suptime = Time_Passed(date($supplierinfo['time']),'time');
									}
									$newtotal = $totalcost + $suptotal;
									$newpaid = $paid + $suppaid;
									$supupdate = mysqli_query($link,"UPDATE `suppliers` SET `total`='$newtotal', `paid`='$newpaid' WHERE `id`='$supid'");
									if ($supupdate) {	
										$responseArray["message"] .= " . كما تم تحديث معلومات البائع بنجاح";
									} else {
										$responseArray["message"] .= " . غشل تحديث معلومات البائع ";
									}
								} else {
									$supins = mysqli_query($link,"INSERT INTO `suppliers`( `id`,`userid`,`mrahid`,`category`,`suppliername`,`suppliermobile`,`total`,`paid`,`time` )VALUES(	NULL,'$userid','$mrahid','water','$suppliername','$suppliermobile','$totalcost','$paid','$time' )");	
									$suplog .= 'تم إضافة بائع مياه بإسم '.$suppliername;
									$suplog .= ' ورقم جوال '.$suppliermobile;
									$suplog .= ' لمراح '.$mrahname;

									if ($supins) {	
										$responseArray["message"] .= " . كما تم إضافة معلومات بائع جديد بنجاح";
									} else {
										$responseArray["message"] .= " . فشل إضافة بائع جديد ";
									}
								}
							}
							///// End of add supplier							
							
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','purchase','إضافة مشتريات مياه','$log','$time' )");

							$logins2 = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','water','إضافة كمية مياه','$updatelog','$time' )");
							
							if (isset($supins)) {	
								$logins3 = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','suppliers','إضافة بائع مياه','$suplog','$time' )");
							}
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $ins faild');
						}
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $ins1 faild');
					}

				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'addsupplier' )   {
				if (!isset($_SESSION['mrahid']) || empty($_SESSION['mrahid']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لا يوجد معرف للمراح');
				} elseif (!isset($_POST['suppliername']) || empty($_POST['suppliername']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'اسم البائع مطلوب');
				} elseif (!isset($_POST['suppliermobile']) || empty($_POST['suppliermobile']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'رقم البائع مطلوب');
				} else {
					$mrahid = $_SESSION["mrahid"];
					$mrahname = $_SESSION["mrahname"];
					$suppliername = mysqli_real_escape_string($link, $_POST['suppliername']);
					$suppliermobile = mysqli_real_escape_string($link, $_POST['suppliermobile']);

					
					$log .= 'تم إضافة بائع مياه بإسم '.$suppliername;
					$log .= ' ورقم جوال '.$suppliermobile;
					$log .= ' لمراح '.$mrahname;

					$record = mysqli_query($link,"SELECT * FROM `suppliers` WHERE userid = '$userid' AND mrahid = '$mrahid' AND suppliername = '$suppliername '    ");
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id' => 'danger', 'message' => 'البائع مضاف مسبقاً');		goto end;	
					} else {
						$supins = mysqli_query($link,"INSERT INTO `suppliers`( `id`,`userid`,`mrahid`,`category`,`suppliername`,`suppliermobile`,`total`,`paid`,`time` )VALUES(	NULL,'$userid','$mrahid','water','$suppliername','$suppliermobile',0,0,'$time' )");	
						if ($supins) {	
							$responseArray = array('id' => 'success', 'message' => 'تمت الإضافة بنجاح' );
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`userid`,`mrahid`,`category`,`title`,`details`,`time` )VALUES( NULL,'$userid','$mrahid','supplier','إضافة بائع مياه','$log','$time' )");

						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $supins faild');
						}
					}
				}
			} else {
				if(isset($_POST['id']) && !empty($_POST['id']))	{	
					$id = mysqli_real_escape_string($link, $_POST['id']);	
					$record = mysqli_query($link,"SELECT * FROM `water` WHERE id = '$id' AND userid = '$userid' AND mrahid = '$mrahid'    ");
				} else { 
					$record = mysqli_query($link,"SELECT * FROM `water` WHERE userid = '$userid' AND mrahid = '$mrahid'    ");
				}
					$waternum = mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){									
						$z = 0;				$responseArray = [];			$Totalwaterinfos = [];	
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
					
					$record = mysqli_query($link,"SELECT * FROM `purchases` WHERE userid = '$userid' AND mrahid = '$mrahid' AND category = 'water' ORDER BY time DESC    ");
					$purchasenum = mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){									
						$z = 0;				$responseArray = [];			$Totalpurchaseinfos = [];	
						while($purchaseinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							$id = $purchaseinfo['id'];
							$type = $purchaseinfo['type'];
							$name = arabic($type);
							$quantity = $purchaseinfo['quantity'];
							$unitcost = $purchaseinfo['unitcost'];
							$totalcost = $purchaseinfo['totalcost'];
							$settlement = $purchaseinfo['settlement'];
							$paid = $purchaseinfo['paid'];
							$remarks = $purchaseinfo['remarks'];
							$suppliername = $purchaseinfo['suppliername'];
							$suppliermobile = $purchaseinfo['suppliermobile'];
							$time = Time_Passed(date($purchaseinfo['time']),'time');
							$unitar = unitar($type);

							${'purchaseinfo'.$z} = array('id' => $id, 'type' => $type , 'name' => $name ,'quantity' => $quantity ,'unitcost' => $unitcost ,'totalcost' => $totalcost ,'settlement' => $settlement ,'paid' => $paid ,'remarks' => $remarks ,'suppliername' => $suppliername ,'suppliermobile' => $suppliermobile ,'time' => $time ,'unitar' => $unitar );	
							$z++;
						}
					}
					
					$record = mysqli_query($link,"SELECT * FROM `suppliers` WHERE userid = '$userid' AND mrahid = '$mrahid' AND category = 'water' ORDER BY time DESC    ");
					$suppliernum = mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){									
						$z = 0;				$responseArray = [];			$Totalsupplierinfos = [];	
						while($supplierinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							$id = $supplierinfo['id'];
							$suppliername = $supplierinfo['suppliername'];
							$suppliermobile = $supplierinfo['suppliermobile'];
							$total = $supplierinfo['total'];
							$paid = $supplierinfo['paid'];
							$time = Time_Passed(date($supplierinfo['time']),'time');

							${'supplierinfo'.$z} = array('id' => $id, 'suppliername' => $suppliername , 'suppliermobile' => $suppliermobile ,'total' => $total ,'paid' => $paid ,'time' => $time );	
							$z++;
						}
					}

					$responseArray = array('id' => 'success', 'waternum' => $waternum , 'suppliernum' => $suppliernum, 'purchasenum' => $purchasenum );
			}
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($waternum) && $waternum > 0 ){	for($a=0;$a<$waternum;$a++){ array_push($Totalwaterinfos,${'waterinfo'.$a}); }	array_push($responseArray,$Totalwaterinfos); }
	if ( isset($suppliernum) && $suppliernum > 0 ){	for($a=0;$a<$suppliernum;$a++){ array_push($Totalsupplierinfos,${'supplierinfo'.$a}); }	array_push($responseArray,$Totalsupplierinfos); }
	if ( isset($purchasenum) && $purchasenum > 0 ){	for($a=0;$a<$purchasenum;$a++){ array_push($Totalpurchaseinfos,${'purchaseinfo'.$a}); }	array_push($responseArray,$Totalpurchaseinfos); }
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message

?>

