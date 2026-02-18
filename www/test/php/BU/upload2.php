<?php header('Content-type: application/json');		header('Content-Type: text/html; charset=utf-8'); 	
header('cache-control: no-cache'); 					require 'inc/functions.php'; 
// var_dump($_FILES);
session_start();
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
	} elseif ( !str_contains($_SESSION["userclearance"], 'setting')  )   {
		$responseArray = array('id' => 'danger', 'message' => 'العمليه غير مصرحه');				array_push($allresps,$responseArray);
	} else {
		if(!isset($_POST['original']) || empty($_POST['original']) )   {
			$responseArray = array('id' => 'danger', 'message' => 'لم تقم برفع الصوره');
		} elseif(!isset($_POST['identifier']) || empty($_POST['identifier']) )   {
			$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ في الخادم identifier missing');
		} elseif(!isset($_POST['empname']) || empty($_POST['empname']) )   {
			$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ في الخادم empname missing');
		} elseif(!isset($_POST['type']) || empty($_POST['type']) )   {
			$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ في الخادم type missing');
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
			
			// Handle Logo Submittion ///////////////////////////////////////////////////
			if ( $_POST['type'] == 'logo' ) {
				if(!isset($_POST['cropped']) || empty($_POST['cropped']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ في الخادم cropped missing');
				} else {
					$empname = mysqli_real_escape_string($link, $_POST['empname']);
					$identifier = mysqli_real_escape_string($link, $_POST['identifier']);
					// add leading zeroes (4 digits)
					$identifier = str_pad($identifier, 4, '0', STR_PAD_LEFT);
					$fileext = Fileext($_POST['original']);
					//check if mother folder "userdata" exists or not and then make it 
					if( is_dir("../userdata/") === false ){	mkdir("../userdata/", 0755);	}
					
					$directory = "../userdata/".$identifier;	
					if( is_dir($directory) === false ){	mkdir($directory, 0755);	}
						
					if ( glob($directory.'/original.*') )  {						//Check existing files
					
						$existing = glob($directory.'/original*', GLOB_BRACE);	
						$existingcount = count(glob($directory.'/original*', GLOB_BRACE));
						// delete files and upload new one
						for ($x = 0; $x < $existingcount; $x++) {
							unlink($existing[$x]);
						}
						 // Rename old file and  add new one
						// $existing = $existing[0];	// first file path	of existing photo
						// $existingext = substr($existing, -3);	//existing photo extension
						// rename($existing,$directory.'/original'.$existingcount.'.'.$existingext); //rename existing photo to replace it
						
					}
					
					file_put_contents($directory.'/original.'.$fileext, file_get_contents($_POST['original'])); //post new photo
					
					$fileext = Fileext($_POST['cropped']);
					
					if ( glob($directory.'/cropped.*') )  {						//Check if file exists to delete it
						$existing = glob($directory.'/cropped*', GLOB_BRACE);	
						$existingcount = count(glob($directory.'/cropped*', GLOB_BRACE));

						// delete files and upload new one
						for ($x = 0; $x < $existingcount; $x++) {
							unlink($existing[$x]);
						}
						
						// Rename old file and  add new one
						// $existing = $existing[0];	// first file path	of existing photo
						// $existingext = substr($existing, -3);	//existing photo extension
						// rename($existing,$directory.'/cropped'.$existingcount.'.'.$existingext); //rename existing photo to replace it
						
					} 
					
					file_put_contents($directory.'/cropped.'.$fileext, file_get_contents($_POST['cropped'])); //post new photo
					$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','setting','تم تحديث شعار المنشأه','$time' )");

					$responseArray = array('id' => 'success', 'message' => 'تم رقع صورة الشعار بنجاح');	
				}
			}
			
			// Handle Bill Submittion ///////////////////////////////////////////////////
			if ( $_POST['type'] == 'bill' ) {

				if(!isset($_POST['billtotal']) || empty($_POST['billtotal']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال التكلفه الكليه للفاتوره');
				} elseif(!isset($_POST['original']) || empty($_POST['original']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم برفع صورة الفاتوره');
				} elseif(!isset($_POST['settlement']) || empty($_POST['settlement']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال كمية السداد للفاتوره');
				} elseif(!isset($_POST['paymenttype']) || empty($_POST['paymenttype']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال آلية السداد');
				} elseif( $_POST['settlement'] == 'partial' && empty($_POST['paid'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال كمية السداد الجزئي للفاتوره');
				} else { 
					$empname = mysqli_real_escape_string($link, $_POST['empname']);
					$identifier = mysqli_real_escape_string($link, $_POST['identifier']);
					$billtotal = mysqli_real_escape_string($link, $_POST['billtotal']);
					$settlement = mysqli_real_escape_string($link, $_POST['settlement']);
					$paymenttype = mysqli_real_escape_string($link, $_POST['paymenttype']);

					if( $settlement == 'partial' )   {
						$paid = mysqli_real_escape_string($link, $_POST['paid']);
					} else { // paid in full
						$paid = mysqli_real_escape_string($link, $_POST['billtotal']);
					}
					
					// add leading zeroes (4 digits)
					$identifier = str_pad($identifier, 4, '0', STR_PAD_LEFT);
					$fileext = Fileext($_POST['original']);
					//check if mother folder "userdata" exists or not and then make it 
					if( is_dir("../userdata/") === false ){	mkdir("../userdata/", 0755);	}
					
					$directory = "../userdata/".$identifier;	
					if( is_dir($directory) === false ){	mkdir($directory, 0755);	}

					$directory = "../userdata/".$identifier."/Pbills";	
					if( is_dir($directory) === false ){	mkdir($directory, 0755);	}
						
					// make new sql table and get the id and name here
					// id - extension - purchase total - time added
					// Add record to pbills 

					$ins = mysqli_query($link,"INSERT INTO `pbills`( `id`, `identifier`, `ext`, `total` , `settlement` , `paid` , `paymenttype` , `timeadded` , `timeedited` )VALUES( NULL, '$identifier', '$fileext', '$billtotal', '$settlement', '$paid', '$paymenttype', '$time', NULL )");	
					
					if ($ins) {
						// Get las Bill Id
						$record = mysqli_query($link,"SELECT * FROM `pbills` ORDER BY id DESC LIMIT 1");
						while($billinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){	$billid = $billinfo['id'];	}
						// add leading zeroes (6 digits)
						$billid = str_pad($billid, 6, '0', STR_PAD_LEFT);

						file_put_contents($directory.'/'.$billid.'.'.$fileext, file_get_contents($_POST['original'])); //post new photo
						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','bill','تم رفع فاتوره رقم $billid','$time' )");

						$responseArray = array('id' => 'success', 'message' => 'تمت إضافة الفاتوره بنجاح', 'lastbillid' => $billid);
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ في الخادم $ins');
					}
				}
			}
		}
	}
}

// if requested by AJAX request return JSON response
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // $encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
    header('Content-Type: application/json'); $encoded = json_encode($responseArray);			echo $encoded;
}
?>