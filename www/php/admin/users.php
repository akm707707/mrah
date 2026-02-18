<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require '../inc/functions.php'; 		
session_start();
//print_r($_POST);	
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
		// } elseif ( !str_contains($_SESSION["userclearance"], 'setting')  )   {
			// $responseArray = array('id' => 'danger', 'message' => 'العمليه غير مصرحه');				
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
			
			if(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'delete' )   {	// DELETE EMPLOYEE SECTION //////////////
				if(isset($_POST['id']) && !empty($_POST['id']) )   {
					$id = mysqli_real_escape_string($link, $_POST['id']);
						// for logging purposes
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `users` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logname = $logrecord['name'];
						$logusername = $logrecord['username'];
						$logmobile = $logrecord['mobile'];	
						$logemail = $logrecord['email'];	
						$logsalary = $logrecord['salary'];	
						$logallowences = $logrecord['allowences'];	
						$logstatus = $logrecord['status'];	
						$logclearance = $logrecord['clearance'];	

					$delete = mysqli_query($link,"DELETE FROM users WHERE id = $id AND eid = '$identifier'");
					if ($delete) {	
							if ($logstatus == 1) { $logstatus = 'نشط'; } else { $logstatus = 'خامل'; }
							$logclearance = explode(",",$logclearance);	// make array 
							if ( count((array)$logclearance) > 0 ) {
								for($i=0;$i<count((array)$logclearance);$i++){
									if ( $logclearance[$i] == 'setting') { $logclearance[$i] = 'إدارة المنشأه'; }
									if ( $logclearance[$i] == 'user') { $logclearance[$i] = ' الموظفين'; }
									if ( $logclearance[$i] == 'capex') { $logclearance[$i] = ' التكاليف الرأسماليه'; }
									if ( $logclearance[$i] == 'opex') { $logclearance[$i] = ' التكاليف التشغيليه'; }
									if ( $logclearance[$i] == 'barcode') { $logclearance[$i] = ' الباركود'; }
									if ( $logclearance[$i] == 'category') { $logclearance[$i] = ' التصنيفات'; }
									if ( $logclearance[$i] == 'purchase') { $logclearance[$i] = ' المشتريات'; }
									if ( $logclearance[$i] == 'supplier') { $logclearance[$i] = ' الموردين'; }
									if ( $logclearance[$i] == 'opurchase') { $logclearance[$i] = ' الدفوعات'; }
									if ( $logclearance[$i] == 'inventory') { $logclearance[$i] = ' المخزون'; }
									if ( $logclearance[$i] == 'inventoryedit') { $logclearance[$i] = ' تعديل المخزون'; }
									if ( $logclearance[$i] == 'invoice') { $logclearance[$i] = ' إصدار الفواتير'; }
									if ( $logclearance[$i] == 'invoices') { $logclearance[$i] = ' إدارة الغواتير'; }
									if ( $logclearance[$i] == 'customer') { $logclearance[$i] = ' العملاء'; }
								}
							}
							$logclearance = implode(",",$logclearance);	// make a string of values					
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','user','تم حذف الموظف $logname  باسم مستخدم $logusername  وجوال $logmobile  وايميل $logemail  وراتب $logsalary وبدلات $logallowences وحاله $logstatus وتصريح <br>$logclearance ','$time' )");
					// Delete photo if photo exists 
						$identifier = str_pad($identifier, 4, '0', STR_PAD_LEFT);				// add leading zeroes (4 digits)
						$empid = str_pad($id, 4, '0', STR_PAD_LEFT);
						
						$directory = "../../userdata/".$identifier."/employee";	

						$file = glob($directory.'/'.$empid.'*'); // Will find files start with $empid
						if (count($file) > 0) { 	
							$info = pathinfo($file[0]);	
							unlink($directory.'/'.$info['basename']);
						} 
						$responseArray = array('id' => 'success', 'message' => 'تم حذف الموظف بنجاح');
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات($delete');
					}
					
				} else {
					$responseArray = array('id' => 'danger', 'message' => 'لا توجد بيانات');
				}
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'add' )   {	// ADD EMPLOYEE SECTION /////////////
				// $responseArray = array('id' => 'success', 'message' => 'تمت الإضافه بنجاح');
				if (!isset($_POST['name']) || empty($_POST['name']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال اسم الموظف');
				} elseif (!isset($_POST['mobile']) || empty($_POST['mobile']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال رقم جوال الموظف');
				} elseif ( !is_numeric($_POST['mobile'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'رقم الجوال يجب أن يحتوي على أرقام فقط');
				} elseif (!isset($_POST['password']) || empty($_POST['password']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال كلمة المرور الموظف');
				} elseif (!isset($_POST['clearance']) || empty($_POST['clearance']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال تصريح الموظف');
				} elseif( !empty($_POST['salary']) && !is_numeric($_POST['salary']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'يجب أن يحتوي الراتب على أرقام فقط');	
				} elseif( !empty($_POST['allowences']) && !is_numeric($_POST['allowences']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'يجب أن تحتوي البدلات على أرقام فقط');	
				} else {
					$name = mysqli_real_escape_string($link, $_POST['name']);
					$mobile = mysqli_real_escape_string($link, $_POST['mobile']);
					$password = mysqli_real_escape_string($link, $_POST['password']);	
					$password 	= a2e($password);	$hashed = md5($password);
					$status = mysqli_real_escape_string($link, $_POST['status']);	
					
					if (isset($_POST['email']) && !empty($_POST['email']) )   {
						$email = mysqli_real_escape_string($link, $_POST['email']);
					} else { $email = ''; }
					if (isset($_POST['salary']) && !empty($_POST['salary']) )   {
						$salary = mysqli_real_escape_string($link, $_POST['salary']);
					} else { $salary = ''; }
					if (isset($_POST['allowences']) && !empty($_POST['allowences']) )   {
						$allowences = mysqli_real_escape_string($link, $_POST['allowences']);
					} else { $allowences = ''; }
					if (isset($_POST['clearance']) && !empty($_POST['clearance']) )   {
						// $clearance = implode(",",$_POST['clearance']);
						$clearance = mysqli_real_escape_string($link, $_POST['clearance']);

					} else { $clearance = ''; }
					
					// $record = mysqli_query($link,"SELECT * FROM `users` WHERE mobile = '$mobile' OR username = '$username'   ");
					$record = mysqli_query($link,"SELECT * FROM `users` WHERE mobile = '$mobile' ");
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id' => 'danger', 'message' => 'الموظف مضاف مسبقاً');		goto end;	
					} else {
						
						$ins = mysqli_query($link,"INSERT INTO `users`( `id`, `eid`, `name`, `username`, `mobile`, `email`, `hashed`, `password`, `salary`, `allowences`, `status`, `clearance`, `color`, `timeadded`, `timeedited` )VALUES( NULL,'$identifier','$name', '$mobile', '$mobile','$email', '$hashed', '$password', '$salary', '$allowences', '$status', '$clearance', 'sidebar-gradient-black-blue', '$time', NULL )");
						
						if ($ins) {
							// For logging purposes
							if ($status == 1) { $status = 'نشط'; } else { $status = 'خامل'; }
							$clearance = explode(",",$clearance);	// make array 
							if ( count((array)$clearance) > 0 ) {
								for($i=0;$i<count((array)$clearance);$i++){
									if ( $clearance[$i] == 'setting') { $clearance[$i] = 'إدارة المنشأه'; }
									if ( $clearance[$i] == 'user') { $clearance[$i] = ' الموظفين'; }
									if ( $clearance[$i] == 'capex') { $clearance[$i] = ' التكاليف الرأسماليه'; }
									if ( $clearance[$i] == 'opex') { $clearance[$i] = ' التكاليف التشغيليه'; }
									if ( $clearance[$i] == 'barcode') { $clearance[$i] = ' الباركود'; }
									if ( $clearance[$i] == 'category') { $clearance[$i] = ' التصنيفات'; }
									if ( $clearance[$i] == 'purchase') { $clearance[$i] = ' المشتريات'; }
									if ( $clearance[$i] == 'supplier') { $clearance[$i] = ' الموردين'; }
									if ( $clearance[$i] == 'opurchase') { $clearance[$i] = ' الدفوعات'; }
									if ( $clearance[$i] == 'inventory') { $clearance[$i] = ' المخزون'; }
									if ( $clearance[$i] == 'inventoryedit') { $clearance[$i] = ' تعديل المخزون'; }
									if ( $clearance[$i] == 'invoice') { $clearance[$i] = ' إصدار الفواتير'; }
									if ( $clearance[$i] == 'invoices') { $clearance[$i] = ' إدارة الغواتير'; }
									if ( $clearance[$i] == 'customer') { $clearance[$i] = ' العملاء'; }
								}
							}
							$clearance = implode(",",$clearance);	// make a string of values
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','user','تم إضافة موظف بإسم $name  باسم مستخدم $mobile  وجوال $mobile  وايميل $email  وراتب $salary وبدلات $allowences وحاله $status وتصريح <br>$clearance','$time' )");
						// Fetch inserted employee id number
							$finder = mysqli_query($link,"SELECT id FROM `users` WHERE eid = '$identifier' ORDER BY id  DESC LIMIT 1");
							if(@mysqli_num_rows($finder) > 0){ while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ $lastid = $idfinder['id']; break;} } else { $lastid = 1; }

						
							// $finder = mysqli_query($link,"SELECT id FROM `users` WHERE eid = '$identifier' ORDER BY id  DESC LIMIT 1");
							// if(@mysqli_num_rows($finder) > 0){ 
								// while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ 
									// $lastid = $idfinder['id'];		break; 
								// }
							// }
							// Handle photo upload 
							if (isset($_POST['photo']) && !empty($_POST['photo']) )   {

								// add leading zeroes (4 digits)
								$identifier = str_pad($identifier, 4, '0', STR_PAD_LEFT);
								$empid = str_pad($lastid, 4, '0', STR_PAD_LEFT);
								$fileext = Fileext($_POST['photo']);
								//check if mother folder "userdata" exists or not and then make it 
								if( is_dir("../../userdata/") === false ){	mkdir("../userdata/", 0777);	}
								
								$directory = "../../userdata/".$identifier;	
								if( is_dir($directory) === false ){	mkdir($directory, 0777);	}

								$directory = "../../userdata/".$identifier."/employee";	
								if( is_dir($directory) === false ){	mkdir($directory, 0777);	}

								file_put_contents($directory.'/'.$empid.'.'.$fileext, file_get_contents($_POST['photo'])); //post  photo
								
							}

							$responseArray = array('id' => 'success', 'message' => 'تمت الإضافه بنجاح', 'lastid' => $lastid, 'clearance' => $clearance);
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $ins');
						}
					}
				} 
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'update' )   {	// UPDATE EMPLOYEE SECTION ////////
				if (!isset($_POST['name']) || empty($_POST['name']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال اسم الموظف');
				} elseif (!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم id missing');
				} elseif (!isset($_POST['mobile']) || empty($_POST['mobile']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال رقم جوال الموظف');
				} elseif (!isset($_POST['password']) || empty($_POST['password']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال كلمة المرور الموظف');
				} elseif (!isset($_POST['clearance']) || empty($_POST['clearance']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال تصريح الموظف');
				} elseif( !empty($_POST['salary']) && !is_numeric($_POST['salary']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'يجب أن يحتوي الراتب على أرقام فقط');	
				} elseif( !empty($_POST['allowences']) && !is_numeric($_POST['allowences']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'يجب أن تحتوي البدلات على أرقام فقط');	
				} else {
					$id = mysqli_real_escape_string($link, $_POST['id']);				
						// for logging purposes
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `users` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logname = $logrecord['name'];
						$logusername = $logrecord['username'];
						$logmobile = $logrecord['mobile'];	
						$logemail = $logrecord['email'];	
						$logsalary = $logrecord['salary'];	
						$logallowences = $logrecord['allowences'];	
						$logstatus = $logrecord['status'];	
						$logclearance = $logrecord['clearance'];	
						
					$name = mysqli_real_escape_string($link, $_POST['name']);
					$mobile = mysqli_real_escape_string($link, $_POST['mobile']);
					$password = mysqli_real_escape_string($link, $_POST['password']);	
					$password 	= a2e($password);	$hashed = md5($password);
					$status = mysqli_real_escape_string($link, $_POST['status']);	
					
					if (isset($_POST['email']) && !empty($_POST['email']) )   {
						$email = mysqli_real_escape_string($link, $_POST['email']);
					} else { $email = ''; }
					if (isset($_POST['salary']) && !empty($_POST['salary']) )   {
						$salary = mysqli_real_escape_string($link, $_POST['salary']);
					} else { $salary = ''; }
					if (isset($_POST['allowences']) && !empty($_POST['allowences']) )   {
						$allowences = mysqli_real_escape_string($link, $_POST['allowences']);
					} else { $allowences = ''; }
					if (isset($_POST['clearance']) && !empty($_POST['clearance']) )   {
						// $clearance = implode(",",$_POST['clearance']);
						$clearance = mysqli_real_escape_string($link, $_POST['clearance']);
					} else { $clearance = ''; }
					
					$record = mysqli_query($link,"SELECT * FROM `users` WHERE ( mobile = '$mobile' OR username = '$username') AND (id != '$id')   ");
					if(@mysqli_num_rows($record) > 1){
						$responseArray = array('id' => 'danger', 'message' => 'الموظف مضاف مسبقاً update');		goto end;	
					} else {

						$update = mysqli_query($link,"UPDATE `users` SET `name`='$name', `username`='$mobile', `mobile`='$mobile', `email`='$email' , `hashed`='$hashed', `password`='$password', `salary`='$salary', `allowences`='$allowences', `status`='$status', `clearance`='$clearance', `timeedited`='$time'	WHERE `id`='$id'");
						
						if ($update) {
							// For logging purposes
							if ($status == 1) { $status = 'نشط'; } else { $status = 'خامل'; }
							if ($logstatus == 1) { $logstatus = 'نشط'; } else { $logstatus = 'خامل'; }
							$clearance = explode(",",$clearance);	// make array 
							if ( count((array)$clearance) > 0 ) {
								for($i=0;$i<count((array)$clearance);$i++){
									if ( $clearance[$i] == 'setting') { $clearance[$i] = 'إدارة المنشأه'; }
									if ( $clearance[$i] == 'user') { $clearance[$i] = ' الموظفين'; }
									if ( $clearance[$i] == 'capex') { $clearance[$i] = ' التكاليف الرأسماليه'; }
									if ( $clearance[$i] == 'opex') { $clearance[$i] = ' التكاليف التشغيليه'; }
									if ( $clearance[$i] == 'barcode') { $clearance[$i] = ' الباركود'; }
									if ( $clearance[$i] == 'category') { $clearance[$i] = ' التصنيفات'; }
									if ( $clearance[$i] == 'purchase') { $clearance[$i] = ' المشتريات'; }
									if ( $clearance[$i] == 'supplier') { $clearance[$i] = ' الموردين'; }
									if ( $clearance[$i] == 'opurchase') { $clearance[$i] = ' الدفوعات'; }
									if ( $clearance[$i] == 'inventory') { $clearance[$i] = ' المخزون'; }
									if ( $clearance[$i] == 'inventoryedit') { $clearance[$i] = ' تعديل المخزون'; }
									if ( $clearance[$i] == 'invoice') { $clearance[$i] = ' إصدار الفواتير'; }
									if ( $clearance[$i] == 'invoices') { $clearance[$i] = ' إدارة الغواتير'; }
									if ( $clearance[$i] == 'customer') { $clearance[$i] = ' العملاء'; }
								}
							}
							$clearance = implode(",",$clearance);	// make a string of values
							$logclearance = explode(",",$logclearance);	// make array 
							if ( count((array)$logclearance) > 0 ) {
								for($i=0;$i<count((array)$logclearance);$i++){
									if ( $logclearance[$i] == 'setting') { $logclearance[$i] = 'إدارة المنشأه'; }
									if ( $logclearance[$i] == 'user') { $logclearance[$i] = ' الموظفين'; }
									if ( $logclearance[$i] == 'capex') { $logclearance[$i] = ' التكاليف الرأسماليه'; }
									if ( $logclearance[$i] == 'opex') { $logclearance[$i] = ' التكاليف التشغيليه'; }
									if ( $logclearance[$i] == 'barcode') { $logclearance[$i] = ' الباركود'; }
									if ( $logclearance[$i] == 'category') { $logclearance[$i] = ' التصنيفات'; }
									if ( $logclearance[$i] == 'purchase') { $logclearance[$i] = ' المشتريات'; }
									if ( $logclearance[$i] == 'supplier') { $logclearance[$i] = ' الموردين'; }
									if ( $logclearance[$i] == 'opurchase') { $logclearance[$i] = ' الدفوعات'; }
									if ( $logclearance[$i] == 'inventory') { $logclearance[$i] = ' المخزون'; }
									if ( $logclearance[$i] == 'inventoryedit') { $logclearance[$i] = ' تعديل المخزون'; }
									if ( $logclearance[$i] == 'invoice') { $logclearance[$i] = ' إصدار الفواتير'; }
									if ( $logclearance[$i] == 'invoices') { $logclearance[$i] = ' إدارة الغواتير'; }
									if ( $logclearance[$i] == 'customer') { $logclearance[$i] = ' العملاء'; }
								}
							}
							$logclearance = implode(",",$logclearance);	// make a string of values

							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','user','تم تحديث بيانات الموظف <br>$logname  باسم مستخدم $logusername  وجوال $logmobile  وايميل $logemail  وراتب $logsalary وبدلات $logallowences وحاله $logstatus وتصريح $logclearance إلى<br> اسم $name  باسم مستخدم $mobile  وجوال $mobile  وايميل $email  وراتب $salary وبدلات $allowences وحاله $status وتصريح $clearance','$time' )");
							// Handle photo upload 
							if (isset($_POST['photo']) && !empty($_POST['photo']) )   {

								// Delete photo if photo exists 
								$identifier = str_pad($identifier, 4, '0', STR_PAD_LEFT);				// add leading zeroes (4 digits)
								$empid = str_pad($id, 4, '0', STR_PAD_LEFT);
								
								$directory = "../../userdata/".$identifier."/employee";	

								$file = glob($directory.'/'.$empid.'*'); // Will find files start with $empid
								if (count($file) > 0) { 	
									$info = pathinfo($file[0]);	
									unlink($directory.'/'.$info['basename']);
								} 

								// add leading zeroes (4 digits)
								$fileext = Fileext($_POST['photo']);
								//check if mother folder "userdata" exists or not and then make it 
								if( is_dir("../../userdata/") === false ){	mkdir("../userdata/", 0777);	}
								
								$directory = "../../userdata/".$identifier;	
								if( is_dir($directory) === false ){	mkdir($directory, 0777);	}

								$directory = "../../userdata/".$identifier."/employee";	
								if( is_dir($directory) === false ){	mkdir($directory, 0777);	}

								file_put_contents($directory.'/'.$empid.'.'.$fileext, file_get_contents($_POST['photo'])); //post  photo
								
							}

							$responseArray = array('id' => 'success', 'message' => 'تمت التحديث بنجاح', 'clearance' => $clearance);
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $update');
						}
					}
				} 

			} else {		
				if(isset($_POST['id']) && !empty($_POST['id']))	{	// FETCH SPECIFIC EMPLOYEE SECTION /////////////
					$id = mysqli_real_escape_string($link, $_POST['id']);	
					$record = mysqli_query($link,"SELECT * FROM `users` WHERE id = '$id' AND eid = '$identifier' ");
					$usernum = mysqli_num_rows($record);

					if(@mysqli_num_rows($record) > 0){									
						while($empinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							$id = $empinfo['id'];
							$name = $empinfo['name'];
							$username = $empinfo['username'];
							$mobile = $empinfo['mobile'];
							$email = $empinfo['email'];
							$hashed = $empinfo['hashed'];
							$password = $empinfo['password'];
							$salary = $empinfo['salary'];
							$allowences = $empinfo['allowences'];
							$status = $empinfo['status'];
							$clearance = $empinfo['clearance'];
							$clearance = explode(",",$clearance);
							$timeadded = $empinfo['timeadded'];											
							$timeadded = Time_Passed(date($empinfo['timeadded']),'time');
							
							if( !empty( $empinfo['timeedited'] ) )	{
								$timeedited = Time_Passed(date($empinfo['timeedited']),'time');
							} else { $timeedited = ''; }

							$empdata = array('id' => $id,'name' => $name,'username' => $username,'mobile' => $mobile,'email' => $email,'password' => $password,'salary' => $salary,'allowences' => $allowences,'status' => $status,'clearance' => $clearance,'timeadded' => $timeadded,'timeedited' => $timeedited );	
						}
						$responseArray = array('id' => 'success', 'empdata' => $empdata, 'usernum' => $usernum);
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'no employee found exists');
					}
				} else {	// FETCH EMPLOYEES  SECTION //////////////////////////////////////////////
				
					// fetch admin data to exclude him 
					$entrecord = mysqli_query($link,"SELECT * FROM `entities` WHERE id = '$identifier' ");
					if(@mysqli_num_rows($entrecord) > 0){
						while($entityinfo = mysqli_fetch_array($entrecord, MYSQLI_ASSOC)){
							$entitymobile = $entityinfo['mobile'];
						}
					}
					
					$record = mysqli_query($link,"SELECT * FROM `users` WHERE eid = '$identifier' ");
					$usersnum = mysqli_num_rows($record);
					if(@mysqli_num_rows($record) > 0){
						$z = 0;					
						$Totalusersinfo = [];	
						while($usersinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							$id = $usersinfo['id'];
							$name = $usersinfo['name'];
							$username = $usersinfo['username'];
							$mobile = $usersinfo['mobile'];
							$email = $usersinfo['email'];
							$password = $usersinfo['password'];
							$salary = $usersinfo['salary'];
							$allowences = $usersinfo['allowences'];
							$status = $usersinfo['status'];
							$clearance = $usersinfo['clearance'];
							$timeadded = $usersinfo['timeadded'];								
							$timeadded = Time_Passed(date($usersinfo['timeadded']),'time');
							
							if( !empty( $usersinfo['timeedited'] ) )	{
								$timeedited = Time_Passed(date($usersinfo['timeedited']),'time');
							} else { $timeedited = ''; }

							if ( $mobile == $entitymobile ) {
								$usersnum = $usersnum - 1; 	// skip the admin						
							} else { 
								${'usersinfo'.$z} = array('id' => $id,'name' => $name,'username' => $username,'mobile' => $mobile,'email' => $email,'password' => $password,'salary' => $salary,'allowences' => $allowences,'status' => $status,'clearance' => $clearance,'timeadded' => $timeadded,'timeedited' => $timeedited );	
								$z++;
							}
						}
					} 
					$responseArray = array('id' => 'success', 'usersnum' => $usersnum);
				}
			}
		}
	}
} 
end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($usersnum) && $usersnum > 0 ){
		for($a=0;$a<$usersnum;$a++){
			array_push($Totalusersinfo,${'usersinfo'.$a}); 
		}	
		array_push($responseArray,$Totalusersinfo); 
	}

	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
	
} else {    echo $responseArray['message'];		}		// else just display the message
?>