<?php header('Content-type: application/json');		header('Content-Type: text/html; charset=utf-8'); 	
header('cache-control: no-cache'); 					require '../inc/functions.php'; 
session_start();
//print_r($_POST);	
$allresps = [];
if ( $_SERVER["REQUEST_METHOD"] != "POST") {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');						array_push($allresps,$responseArray);
} else {	//Aname=&crid=&taxid=&fiscalday=
	$time = time();
	if ( !$_SESSION ) {
		$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $_SESSION');			array_push($allresps,$responseArray);
	} else {
		if ( !isset($_SESSION['loggedin']) || $_SESSION["loggedin"] !== true )   {
			$responseArray = array('id' => 'danger', 'message' => 'لم تقم بتسجيل الدخول');			array_push($allresps,$responseArray);
		} elseif ( !isset($_SESSION['userstatus']) || $_SESSION["userstatus"] != 1 )   {
			$responseArray = array('id' => 'danger', 'message' => 'الحساب غير نشط');				array_push($allresps,$responseArray);
		} elseif ( !isset($_SESSION['userclearance']) || empty($_SESSION["userclearance"])  )   {
			$responseArray = array('id' => 'danger', 'message' => 'لايمكن الوصول للتصاريح');				array_push($allresps,$responseArray);
		// } elseif ( !str_contains($_SESSION["userclearance"], 'setting')  )   {
			// $responseArray = array('id' => 'danger', 'message' => 'العمليه غير مصرحه');				array_push($allresps,$responseArray);
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
			
			if(isset($_POST['fetch']) && !empty($_POST['fetch']) )   {

				$contacts = mysqli_fetch_array(mysqli_query($link,"SELECT * FROM `contacts` WHERE `identifier`='$identifier'"), MYSQLI_ASSOC);
				$address = $contacts['address'];
				$website = $contacts['website'];
				$email = $contacts['email'];
				$mobile = $contacts['mobile'];
				$landline1 = $contacts['landline1'];
				$landline2 = $contacts['landline2'];
				$fax = $contacts['fax'];
				$twitter = $contacts['twitter'];
				$facebook = $contacts['facebook'];
				$instagram = $contacts['instagram'];
				$snapchat = $contacts['snapchat'];
				$whatsapp = $contacts['whatsapp'];
				$linkedin = $contacts['linkedin'];
				
				$contacts = array('address' => $address, 'website' => $website, 'email' => $email, 'mobile' => $mobile, 'landline1' => $landline1, 'landline2' => $landline2, 'fax' => $fax, 'twitter' => $twitter, 'facebook' => $facebook, 'instagram' => $instagram, 'snapchat' => $snapchat, 'whatsapp' => $whatsapp, 'linkedin' => $linkedin);
				
				$responseArray = array('id' => 'success', 'contacts' => $contacts );		array_push($allresps,$responseArray);
			
			} else {
				
				$logrecord = mysqli_fetch_array(mysqli_query($link,"SELECT * FROM `contacts` WHERE `identifier`='$identifier'"), MYSQLI_ASSOC);
				$logaddress = $logrecord['address'];
				$logwebsite = $logrecord['website'];
				$logemail = $logrecord['email'];
				$logmobile = $logrecord['mobile'];
				$loglandline1 = $logrecord['landline1'];
				$loglandline2 = $logrecord['landline2'];
				$logfax = $logrecord['fax'];
				$logtwitter = $logrecord['twitter'];
				$logfacebook = $logrecord['facebook'];
				$loginstagram = $logrecord['instagram'];
				$logsnapchat = $logrecord['snapchat'];
				$logwhatsapp = $logrecord['whatsapp'];
				$loglinkedin = $logrecord['linkedin'];

				if(isset($_POST['address']) )   {
					$address = mysqli_real_escape_string($link, $_POST['address']);
					//unset($record);
					$record = mysqli_query($link,"SELECT * FROM `contacts` WHERE identifier = '$identifier'   ");
					// echo mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){
						$record = mysqli_fetch_array($record, MYSQLI_ASSOC);
						  if ($address != $record['address']) {
							  //unset($update);
							  $update = mysqli_query($link,"UPDATE `contacts` SET `address` = '$address'	WHERE `identifier`='$identifier'");
								if ($update) {	
									$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','setting','تم تحديث العنوان من $logaddress إلى $address','$time' )");
									$responseArray = array('id' => 'success', 'message' => 'تم تحديث العنوان بنجاح');
									array_push($allresps,$responseArray);
								} else {
									$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ أثناء عملية التحديث');
									array_push($allresps,$responseArray);
								}
						  } else { 
							$responseArray = array('id' => 'warning', 'message' => 'العنوان مطابق للسابق'); 
							array_push($allresps,$responseArray);
						  }
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على أيى بيانات');
						array_push($allresps,$responseArray);
					}
				}

				if(isset($_POST['website']) )   {
					$website = mysqli_real_escape_string($link, $_POST['website']);
					unset($record);
					$record = mysqli_query($link,"SELECT * FROM `contacts` WHERE identifier = '$identifier'   ");
					// echo mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){
						$record = mysqli_fetch_array($record, MYSQLI_ASSOC);
						  if ($website != $record['website']) {
							  unset($update);
							  $update = mysqli_query($link,"UPDATE `contacts` SET `website` = '$website'	WHERE `identifier`='$identifier'");
								if ($update) {	
									$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','setting','تم تحديث الموقع الإلكتروني من $logwebsite إلى $website','$time' )");
									$responseArray = array('id' => 'success', 'message' => 'تم تحديث الموقع بنجاح');
									array_push($allresps,$responseArray);
								} else {
									$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ أثناء عملية التحديث');
									array_push($allresps,$responseArray);
								}
						  } else { 
							$responseArray = array('id' => 'warning', 'message' => 'الموقع مطابق للسابق'); 
							array_push($allresps,$responseArray);
						  }
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على أيى بيانات');
						array_push($allresps,$responseArray);
					}
				}
				
				if(isset($_POST['email']) )   {
					$email = mysqli_real_escape_string($link, $_POST['email']);
					unset($record);
					$record = mysqli_query($link,"SELECT * FROM `contacts` WHERE identifier = '$identifier'   ");
					// echo mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){
						$record = mysqli_fetch_array($record, MYSQLI_ASSOC);
						  if ($email != $record['email']) {
							  unset($update);
							  $update = mysqli_query($link,"UPDATE `contacts` SET `email` = '$email'	WHERE `identifier`='$identifier'");
								if ($update) {	
									$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','setting','تم تحديث البريد الإلكتروني من $logemail إلى $email','$time' )");
									$responseArray = array('id' => 'success', 'message' => 'تم تحديث البريد الالكتروني بنجاح');
									array_push($allresps,$responseArray);
								} else {
									$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ أثناء عملية التحديث');
									array_push($allresps,$responseArray);
								}
						  } else { 
							$responseArray = array('id' => 'warning', 'message' => 'البريد الالكتروني مطابق للسابق'); 
							array_push($allresps,$responseArray);
						  }
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على أيى بيانات');
						array_push($allresps,$responseArray);
					}
				}
				
				if(isset($_POST['mobile']) )   {
					if( !empty($_POST['mobile']) && !is_numeric($_POST['mobile']) )   {
						$responseArray = array('id' => 'danger', 'message' => 'يجب أن يحتوي رقم الجوال على أرقام فقط');	
						array_push($allresps,$responseArray);
					} else {
						$mobile = mysqli_real_escape_string($link, $_POST['mobile']);
						unset($record);
						$record = mysqli_query($link,"SELECT * FROM `contacts` WHERE identifier = '$identifier'   ");
						// echo mysqli_num_rows($record);
						if(@mysqli_num_rows($record) > 0){
							$record = mysqli_fetch_array($record, MYSQLI_ASSOC);
							  if ($mobile != $record['mobile']) {
								  unset($update);
								  $update = mysqli_query($link,"UPDATE `contacts` SET `mobile` = '$mobile'	WHERE `identifier`='$identifier'");
									if ($update) {	
										$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','setting','تم تحديث رقم الجوال من $logmobile إلى $mobile','$time' )");
										$responseArray = array('id' => 'success', 'message' => 'تم تحديث رقم الجوال بنجاح');
										array_push($allresps,$responseArray);
									} else {
										$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ أثناء عملية التحديث');
										array_push($allresps,$responseArray);
									}
							  } else { 
								$responseArray = array('id' => 'warning', 'message' => 'رقم الجوال مطابق للسابق'); 
								array_push($allresps,$responseArray);
							  }
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على أيى بيانات');
							array_push($allresps,$responseArray);
						}
					}
				}
				
				if(isset($_POST['landline1']) )   {
					if( !empty($_POST['landline1']) && !is_numeric($_POST['landline1']) )   {
						$responseArray = array('id' => 'danger', 'message' => 'يجب أن يحتوي رقم الهاتف على أرقام فقط');	
						array_push($allresps,$responseArray);
					} else {
						$landline1 = mysqli_real_escape_string($link, $_POST['landline1']);
						unset($record);
						$record = mysqli_query($link,"SELECT * FROM `contacts` WHERE identifier = '$identifier'   ");
						// echo mysqli_num_rows($record);
						if(@mysqli_num_rows($record) > 0){
							$record = mysqli_fetch_array($record, MYSQLI_ASSOC);
							  if ($landline1 != $record['landline1']) {
								  unset($update);
								  $update = mysqli_query($link,"UPDATE `contacts` SET `landline1` = '$landline1'	WHERE `identifier`='$identifier'");
									if ($update) {	
										$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','setting','تم تحديث رقم الهاتف1 من $loglandline1 إلى $landline1','$time' )");
										$responseArray = array('id' => 'success', 'message' => 'تم تحديث رقم الهاتف 1 بنجاح');
										array_push($allresps,$responseArray);
									} else {
										$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ أثناء عملية التحديث');
										array_push($allresps,$responseArray);
									}
							  } else { 
								$responseArray = array('id' => 'warning', 'message' => 'رقم الهاتف 1 مطابق للسابق'); 
								array_push($allresps,$responseArray);
							  }
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على أيى بيانات');
							array_push($allresps,$responseArray);
						}
					}
				}
				
				if(isset($_POST['landline2']) )   {
					if( !empty($_POST['landline2']) && !is_numeric($_POST['landline2']) )   {
						$responseArray = array('id' => 'danger', 'message' => 'يجب أن يحتوي رقم الهاتف على أرقام فقط');	
						array_push($allresps,$responseArray);
					} else {
						$landline2 = mysqli_real_escape_string($link, $_POST['landline2']);
						unset($record);
						$record = mysqli_query($link,"SELECT * FROM `contacts` WHERE identifier = '$identifier'   ");
						// echo mysqli_num_rows($record);
						if(@mysqli_num_rows($record) > 0){
							$record = mysqli_fetch_array($record, MYSQLI_ASSOC);
							  if ($landline2 != $record['landline2']) {
								  unset($update);
								  $update = mysqli_query($link,"UPDATE `contacts` SET `landline2` = '$landline2'	WHERE `identifier`='$identifier'");
									if ($update) {	
										$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','setting','تم تحديث رقم الهاتف2 من $loglandline2 إلى $landline2','$time' )");
										$responseArray = array('id' => 'success', 'message' => 'تم تحديث رقم الهاتف 2 بنجاح');
										array_push($allresps,$responseArray);
									} else {
										$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ أثناء عملية التحديث');
										array_push($allresps,$responseArray);
									}
							  } else { 
								$responseArray = array('id' => 'warning', 'message' => 'رقم الهاتف 2 مطابق للسابق'); 
								array_push($allresps,$responseArray);
							  }
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على أيى بيانات');
							array_push($allresps,$responseArray);
						}
					}
				}
				
				if(isset($_POST['fax']) )   {
					if( !empty($_POST['fax']) && !is_numeric($_POST['fax']) )   {
						$responseArray = array('id' => 'danger', 'message' => 'يجب أن يحتوي رقم الفاكس على أرقام فقط');	
						array_push($allresps,$responseArray);
					} else {
						$fax = mysqli_real_escape_string($link, $_POST['fax']);
						unset($record);
						$record = mysqli_query($link,"SELECT * FROM `contacts` WHERE identifier = '$identifier'   ");
						// echo mysqli_num_rows($record);
						if(@mysqli_num_rows($record) > 0){
							$record = mysqli_fetch_array($record, MYSQLI_ASSOC);
							  if ($fax != $record['fax']) {
								  unset($update);
								  $update = mysqli_query($link,"UPDATE `contacts` SET `fax` = '$fax'	WHERE `identifier`='$identifier'");
									if ($update) {	
										$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','setting','تم تحديث رقم الفاكس من $logfax إلى $fax','$time' )");
										$responseArray = array('id' => 'success', 'message' => 'تم تحديث رقم الفاكس بنجاح');
										array_push($allresps,$responseArray);
									} else {
										$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ أثناء عملية التحديث');
										array_push($allresps,$responseArray);
									}
							  } else { 
								$responseArray = array('id' => 'warning', 'message' => 'رقم الفاكس مطابق للسابق'); 
								array_push($allresps,$responseArray);
							  }
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على أيى بيانات');
							array_push($allresps,$responseArray);
						}
					}
				}
				
				if(isset($_POST['twitter']) )   {
					$twitter = mysqli_real_escape_string($link, $_POST['twitter']);
					unset($record);
					$record = mysqli_query($link,"SELECT * FROM `contacts` WHERE identifier = '$identifier'   ");
					// echo mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){
						$record = mysqli_fetch_array($record, MYSQLI_ASSOC);
						  if ($twitter != $record['twitter']) {
							  unset($update);
							  $update = mysqli_query($link,"UPDATE `contacts` SET `twitter` = '$twitter'	WHERE `identifier`='$identifier'");
								if ($update) {	
									$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','setting','تم تحديث معرف تويتر من $logtwitter إلى $twitter','$time' )");
									$responseArray = array('id' => 'success', 'message' => 'تم تحديث حساب تويتر بنجاح');
									array_push($allresps,$responseArray);
								} else {
									$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ أثناء عملية التحديث');
									array_push($allresps,$responseArray);
								}
						  } else { 
							$responseArray = array('id' => 'warning', 'message' => 'حساب تويتر مطابق للسابق'); 
							array_push($allresps,$responseArray);
						  }
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على أيى بيانات');
						array_push($allresps,$responseArray);
					}
				}
				
				if(isset($_POST['facebook']) )   {
					$facebook = mysqli_real_escape_string($link, $_POST['facebook']);
					unset($record);
					$record = mysqli_query($link,"SELECT * FROM `contacts` WHERE identifier = '$identifier'   ");
					// echo mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){
						$record = mysqli_fetch_array($record, MYSQLI_ASSOC);
						  if ($facebook != $record['facebook']) {
							  unset($update);
							  $update = mysqli_query($link,"UPDATE `contacts` SET `facebook` = '$facebook'	WHERE `identifier`='$identifier'");
								if ($update) {	
									$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','setting','تم تحديث معرف فيسبوك من $logfacebook إلى $facebook','$time' )");
									$responseArray = array('id' => 'success', 'message' => 'تم تحديث حساب فيسبوك بنجاح');
									array_push($allresps,$responseArray);
								} else {
									$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ أثناء عملية التحديث');
									array_push($allresps,$responseArray);
								}
						  } else { 
							$responseArray = array('id' => 'warning', 'message' => 'حساب فيسبوك مطابق للسابق'); 
							array_push($allresps,$responseArray);
						  }
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على أيى بيانات');
						array_push($allresps,$responseArray);
					}
				}
				
				if(isset($_POST['instagram']) )   {
					$instagram = mysqli_real_escape_string($link, $_POST['instagram']);
					unset($record);
					$record = mysqli_query($link,"SELECT * FROM `contacts` WHERE identifier = '$identifier'   ");
					// echo mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){
						$record = mysqli_fetch_array($record, MYSQLI_ASSOC);
						  if ($instagram != $record['instagram']) {
							  unset($update);
							  $update = mysqli_query($link,"UPDATE `contacts` SET `instagram` = '$instagram'	WHERE `identifier`='$identifier'");
								if ($update) {	
									$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','setting','تم تحديث معرف انستقرام من $loginstagram إلى $instagram','$time' )");
									$responseArray = array('id' => 'success', 'message' => 'تم تحديث حساب انستقرام بنجاح');
									array_push($allresps,$responseArray);
								} else {
									$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ أثناء عملية التحديث');
									array_push($allresps,$responseArray);
								}
						  } else { 
							$responseArray = array('id' => 'warning', 'message' => 'حساب انستقرام مطابق للسابق'); 
							array_push($allresps,$responseArray);
						  }
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على أيى بيانات');
						array_push($allresps,$responseArray);
					}
				}
				
				if(isset($_POST['snapchat']) )   {
					$snapchat = mysqli_real_escape_string($link, $_POST['snapchat']);
					unset($record);
					$record = mysqli_query($link,"SELECT * FROM `contacts` WHERE identifier = '$identifier'   ");
					// echo mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){
						$record = mysqli_fetch_array($record, MYSQLI_ASSOC);
						  if ($snapchat != $record['snapchat']) {
							  unset($update);
							  $update = mysqli_query($link,"UPDATE `contacts` SET `snapchat` = '$snapchat'	WHERE `identifier`='$identifier'");
								if ($update) {	
									$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','setting','تم تحديث معرف سناب شات من $logsnapchat إلى $snapchat','$time' )");
									$responseArray = array('id' => 'success', 'message' => 'تم تحديث حساب سناب شات بنجاح');
									array_push($allresps,$responseArray);
								} else {
									$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ أثناء عملية التحديث');
									array_push($allresps,$responseArray);
								}
						  } else { 
							$responseArray = array('id' => 'warning', 'message' => 'حساب سناب شات مطابق للسابق'); 
							array_push($allresps,$responseArray);
						  }
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على أيى بيانات');
						array_push($allresps,$responseArray);
					}
				}
				
				if(isset($_POST['whatsapp']) )   {
					if( !empty($_POST['whatsapp']) && !is_numeric($_POST['whatsapp']) )   {
						$responseArray = array('id' => 'danger', 'message' => 'يجب أن يحتوي رقم الواتس اب على أرقام فقط');	
						array_push($allresps,$responseArray);
					} else {
						$whatsapp = mysqli_real_escape_string($link, $_POST['whatsapp']);
						unset($record);
						$record = mysqli_query($link,"SELECT * FROM `contacts` WHERE identifier = '$identifier'   ");
						// echo mysqli_num_rows($record);
						if(@mysqli_num_rows($record) > 0){
							$record = mysqli_fetch_array($record, MYSQLI_ASSOC);
							  if ($whatsapp != $record['whatsapp']) {
								  unset($update);
								  $update = mysqli_query($link,"UPDATE `contacts` SET `whatsapp` = '$whatsapp'	WHERE `identifier`='$identifier'");
									if ($update) {	
										$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','setting','تم تحديث معرف واتس اب من $logwhatsapp إلى $whatsapp','$time' )");
										$responseArray = array('id' => 'success', 'message' => 'تم تحديث رقم الواتساب بنجاح');
										array_push($allresps,$responseArray);
									} else {
										$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ أثناء عملية التحديث');
										array_push($allresps,$responseArray);
									}
							  } else { 
								$responseArray = array('id' => 'warning', 'message' => 'رقم الواتساب مطابق للسابق'); 
								array_push($allresps,$responseArray);
							  }
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على أيى بيانات');
							array_push($allresps,$responseArray);
						}
					}
				}
				
				if(isset($_POST['linkedin']) )   {
					$linkedin = mysqli_real_escape_string($link, $_POST['linkedin']);
					unset($record);
					$record = mysqli_query($link,"SELECT * FROM `contacts` WHERE identifier = '$identifier'   ");
					// echo mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){
						$record = mysqli_fetch_array($record, MYSQLI_ASSOC);
						  if ($linkedin != $record['linkedin']) {
							  unset($update);
							  $update = mysqli_query($link,"UPDATE `contacts` SET `linkedin` = '$linkedin'	WHERE `identifier`='$identifier'");
								if ($update) {	
									$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','setting','تم تحديث معرف لنكد ان من $loglinkedin إلى $linkedin','$time' )");
									$responseArray = array('id' => 'success', 'message' => 'تم تحديث حساب حساب لنكدان بنجاح');
									array_push($allresps,$responseArray);
								} else {
									$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ أثناء عملية التحديث');
									array_push($allresps,$responseArray);
								}
						  } else { 
							$responseArray = array('id' => 'warning', 'message' => 'حساب حساب لنكدان مطابق للسابق'); 
							array_push($allresps,$responseArray);
						  }
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'لم يتم العثور على أيى بيانات');
						array_push($allresps,$responseArray);
					}
				}
			}
		}
	
	}
}
// if requested by AJAX request return JSON response
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $encoded = json_encode($allresps);		header('Content-Type: application/json');	echo $encoded;
}
?>