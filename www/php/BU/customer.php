<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		
session_start();
//print_r($_POST);	
if($_POST){
	$time = time();			$rs = '';
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
		// DELETE costumer SECTION ////////////////////////////////////////////////////////////////////////////////////////////
			if(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'delete' )   {
				if(isset($_POST['id']) && !empty($_POST['id']) )   {
					$id = mysqli_real_escape_string($link, $_POST['id']);
						// for logging purposes
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `customers` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logname = $logrecord['name'];
						$logmobile = $logrecord['mobile'];
						$logbalance = $logrecord['balance'];	
						$logtransactions = $logrecord['transactions'];	
						
					$delete = mysqli_query($link,"DELETE FROM customers WHERE id = $id ");
					if ($delete) {	
						$responseArray = array('id' => 'success', 'message' => 'تم حذف العميل بنجاح');
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','customer','تم حذف عميل باسم $logname ورقم جوال $logmobile ورصيد $logbalance وعدد تعاملات $logtransactions ','$time' )");
						
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات($delete');
					}
				} else {
					$responseArray = array('id' => 'danger', 'message' => 'لا توجد بيانات');
				}

		// ADD costumer SECTION ////////////////////////////////////////////////////////////////////////////////////////////
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'add' )   {
				if (!isset($_POST['costumername']) || empty($_POST['costumername']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال اسم العميل add');
				} elseif( !empty($_POST['costumermobile']) && !is_numeric($_POST['costumermobile']) )   {
						$responseArray = array('id' => 'danger', 'message' => 'يجب أن يحتوي رفم الجوال على أرقام فقط');	
				} else {
					$name = mysqli_real_escape_string($link, $_POST['costumername']);

					if(isset($_POST['costumermobile']) && !empty($_POST['costumermobile']))	{
						$mobile = mysqli_real_escape_string($link, $_POST['costumermobile']);				
					} else {
						$mobile = '';
					}

					$record = mysqli_query($link,"SELECT * FROM `customers` WHERE name = '$name'   ");
					if(@mysqli_num_rows($record) > 0){
						$responseArray = array('id' => 'danger', 'message' => 'العميل مضاف مسبقاً');		goto end;	
					} else {
						
						$randnum = rand(0,9999);
						$pass = str_pad($randnum, 4, '0', STR_PAD_LEFT);

						$ins = mysqli_query($link,"INSERT INTO `customers`( `id`, `name`, `mobile`, `password`, `balance`, `transactions`, `timeadded`, `timeedited` )VALUES( NULL,'$name','$mobile','$pass', 0, 0,'$time', NULL )");
						if ($ins) {
							$finder = mysqli_query($link,"SELECT * FROM `customers` ORDER BY `id` DESC");
							if(@mysqli_num_rows($finder) > 0){ while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ $id = $idfinder['id']; break;} } else { $id = 1; }
							// $finder = mysqli_query($link,"SELECT * FROM `customers` ORDER BY `id` DESC");
							// if(@mysqli_num_rows($finder) > 0){ while($idfinder = mysqli_fetch_array($finder, MYSQLI_ASSOC)){ $id = $idfinder['id']; break;} }

							$responseArray = array('id' => 'success', 'message' => 'تمت الإضافه بنجاح', 'lastid' => $id);
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','customer','تم إضافة عميل باسم $name ورقم جوال $mobile','$time' )");
						} else {
							$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات$ins (Add)');
						}
					}
				}
				
		// UPDATE costumer SECTION ////////////////////////////////////////////////////////////////////////////////////////////		
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'update' )   {	
				if(!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $id');
				} elseif (!isset($_POST['costumername']) || empty($_POST['costumername']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال اسم العميل');
				} elseif (!isset($_POST['costumerbalance']) || empty($_POST['costumerbalance']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال رصيد العميل');
				} elseif ( !is_numeric($_POST['costumerbalance']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'يجب أن يحتوي رصيد العميل على أرقام فقط');	
				} elseif (!isset($_POST['costumertransactions']) || empty($_POST['costumertransactions']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال عدد تعاملات العميل');
				} elseif ( !is_numeric($_POST['costumertransactions']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'يجب أن يحتوي عدد تعاملات العميل على أرقام فقط');	
				} elseif( !empty($_POST['costumermobile']) && !is_numeric($_POST['costumermobile']) )   {
						$responseArray = array('id' => 'danger', 'message' => 'يجب أن يحتوي رفم الجوال على أرقام فقط');	
				} else {
					$id = mysqli_real_escape_string($link, $_POST['id']);
						// for logging purposes
						$logrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `customers` WHERE id = '$id' "), MYSQLI_ASSOC);
						$logname = $logrecord['name'];
						$logmobile = $logrecord['mobile'];
						$logbalance = $logrecord['balance'];	
						$logtransactions = $logrecord['transactions'];
						
					$name = mysqli_real_escape_string($link, $_POST['costumername']);
					
					if(isset($_POST['costumermobile']) && !empty($_POST['costumermobile']))	{
						$mobile = mysqli_real_escape_string($link, $_POST['costumermobile']);				
					} else {
						$mobile = '';
					}
					
					$balance = mysqli_real_escape_string($link, $_POST['costumerbalance']);
					$transactions = mysqli_real_escape_string($link, $_POST['costumertransactions']);

					// Updating Section
					$update = mysqli_query($link,"UPDATE `customers` SET `name`='$name', `mobile`='$mobile', `balance`='$balance', `transactions`='$transactions' , `timeedited`='$time'	WHERE `id`='$id'");

					if ($update) {	
						$responseArray = array('id' => 'success', 'message' => 'تم التحديث بنجاح');
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','customer','تم تحديث بيانات عميل باسم $logname ورقم جوال $logmobile و رصيد $logbalance وعدد تعاملات $logtransactions  إلى اسم $name وجوال $mobile و رصيد $balance  وتعاملات $transactions','$time' )");
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $update');
					}
				}
			} else {
		// FETCH SPECIFIC costumer SECTION ///////////////////////////////////////////////////////////////////////////////////////		
				if(isset($_POST['id']) && !empty($_POST['id']))	{										// for editing
					$id = mysqli_real_escape_string($link, $_POST['id']);	
					$record = mysqli_query($link,"SELECT * FROM `customers` WHERE id = '$id'   ");
				} else {																				// for all retreival
					$record = mysqli_query($link,"SELECT * FROM `customers` ");
				}
				
				$costumernum = mysqli_num_rows($record);
				if(@mysqli_num_rows($record) > 0){	
					$z = 0;					
					$Totalcostumerinfos = [];	
					while($customersinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
						$id = $customersinfo['id'];
						$name = $customersinfo['name'];
						$mobile = $customersinfo['mobile'];
						$password = $customersinfo['password'];
						$balance = $customersinfo['balance'];
						$transactions = $customersinfo['transactions'];
						$timeadded = Time_Passed(date($customersinfo['timeadded']),'time');
						
						if(isset($_POST['timeedited']) && !empty($_POST['timeedited']))	{
							$timeedited = Time_Passed(date($customersinfo['timeedited']),'time');
						} else {
							$timeedited = '';
						}

						${'costumerinfo'.$z} = array('id' => $id,'name' => $name,'mobile' => $mobile,'password' => $password,'balance' => $balance,'transactions' => $transactions,'timeadded' => $timeadded,'timeedited' => $timeedited );	
						$z++;
					}
				} else {
					$responseArray = array('id' => 'danger', 'message' => 'no costumer exists');
				}
					 
				$responseArray = array('id' => 'success', 'costumernum' => $costumernum);
				
			}
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
end:
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($costumernum) && $costumernum > 0 ){
		for($a=0;$a<$costumernum;$a++){
			array_push($Totalcostumerinfos,${'costumerinfo'.$a}); 
		}	
		array_push($responseArray,$Totalcostumerinfos); 
	}
/* 	if ( isset($catnum) && $catnum > 0 ){
		array_push($responseArray,$catarray); 	
	}
 */	
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
	
} else {    echo $responseArray['message'];		}		// else just display the message
?>