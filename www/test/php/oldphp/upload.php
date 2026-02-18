<?php header('Content-type: application/json');		header('Content-Type: text/html; charset=utf-8'); 	
header('cache-control: no-cache'); 					require 'inc/functions.php'; 
// var_dump($_POST);
// var_dump($_FILES);
$logtext = '';				// used for add
session_start();
$time = time();
$uploadsuccess = 0;
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
		// if(!isset($_POST['identifier']) || empty($_POST['identifier']) )   {
			// $responseArray = array('id' => 'danger', 'message' => 'حدث خطأ في الخادم identifier missing');
		// } elseif(!isset($_POST['empname']) || empty($_POST['empname']) )   {
			// $responseArray = array('id' => 'danger', 'message' => 'حدث خطأ في الخادم empname missing');
		if(!isset($_POST['type']) || empty($_POST['type']) )   {
		// } elseif(!isset($_POST['type']) || empty($_POST['type']) )   {
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
					if( is_dir("../userdata/") === false ){	mkdir("../userdata/", 0777);	}
					
					$directory = "../userdata/".$identifier;	
					if( is_dir($directory) === false ){	mkdir($directory, 0777);	}
						
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
				} elseif(!isset($_POST['settlement']) || empty($_POST['settlement']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال كمية السداد للفاتوره');
				} elseif(!isset($_POST['paymenttype']) || empty($_POST['paymenttype']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال آلية السداد');
				} elseif(!isset($_POST['supplier']) || empty($_POST['supplier']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم يتم اختيار مورد');
				} elseif( $_POST['settlement'] == 'partial' && empty($_POST['paid'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم بادخال كمية السداد الجزئي للفاتوره');
				} elseif( $_POST['settlement'] == 'partial' && $_POST['paymenttype'] == 'debt'  )   {
					$responseArray = array('id' => 'danger', 'message' => 'لا يمكن اختيار آلية السداد دين إلا اذا كان للمبلغ كاملاً');
				} elseif( !$_FILES && empty($_POST['original'])  )   {
					$responseArray = array('id' => 'danger', 'message' => 'لم تقم برفع الفاتوره');
				} else { 
					$billtotal = mysqli_real_escape_string($link, $_POST['billtotal']);
					$settlement = mysqli_real_escape_string($link, $_POST['settlement']);
					$paymenttype = mysqli_real_escape_string($link, $_POST['paymenttype']);
					$supplier = mysqli_real_escape_string($link, $_POST['supplier']);
					
					$logtext .= 'من المورد '.$supplier;
					$logtext .= 'بقييمة اجماليه قدرها '.$billtotal;

					if( $settlement == 'partial' )   {
						$paid = mysqli_real_escape_string($link, $_POST['paid']);
						$logtextv1 = 'تم سدادها جزئياً بقدر  '.$paid;
					} elseif ( $settlement == 'full' && $paymenttype == 'debt'  )   {
						$paid = 0;
					} else { // paid in full
						$paid = mysqli_real_escape_string($link, $_POST['billtotal']);
						$logtextv1 = 'مسددة بالكامل ';
					}
					
					if( $paymenttype == 'pos' )   {			$logtext .= $logtextv1;	 $logtext .= 'عن طريق الشبكة '; }
					if( $paymenttype == 'cash' )   {		$logtext .= $logtextv1;	 $logtext .= 'عن طريق الكاش '; }
					if( $paymenttype == 'wire' )   {		$logtext .= $logtextv1;	 $logtext .= 'عن طريق التحويل البنكي '; }
					if( $paymenttype == 'cheque' )   {		$logtext .= $logtextv1;	 $logtext .= 'عن طريق شيك '; }
					if( $paymenttype == 'creditcard' ) {	$logtext .= $logtextv1;  $logtext .= 'عن طريق البطاقة الائتمانيه '; }
					if( $paymenttype == 'debt' )   {		$logtext .= 'عن طريق الدين ';		}

					// add leading zeroes (4 digits)
					$identifier = str_pad($identifier, 4, '0', STR_PAD_LEFT);
					
					if( is_dir("../userdata/") === false ){	mkdir("../userdata/", 0777); }	// make it if not there
					$directory = "../userdata/".$identifier;	
					if( is_dir($directory) === false ){	mkdir($directory, 0777);	}		// make it if not there
					$directory = "../userdata/".$identifier."/Pbills";	
					if( is_dir($directory) === false ){	mkdir($directory, 0777);	}		// make it if not there

					$ins = mysqli_query($link,"INSERT INTO `pbills`( `id`, `identifier`, `total` , `settlement` , `paid` , `paymenttype`, `supplier`, `pids` , `timeadded` , `timeedited` )VALUES( NULL, '$identifier', '$billtotal', '$settlement', '$paid', '$paymenttype', '$supplier', NULL, '$time', NULL )");	

					if ( $ins ) {
						// Get last id number
						$record = mysqli_query($link,"SELECT * FROM `pbills` ORDER BY id DESC LIMIT 1");
						if(@mysqli_num_rows($record) > 0){					
							while($nbill = mysqli_fetch_array($record, MYSQLI_ASSOC)){ $newbillid = $nbill['id'];	}
						}
						$newbillid = str_pad($newbillid, 6, '0', STR_PAD_LEFT);	// Add leading zeroes

						$logtext = 'تم إضافة فاتورة شراء برقم '.$newbillid.' '.$logtext;	// delayed for $newbillid

						// increase transavtion and balance to supplier
						if( !empty($supplier) ) {
							$record = mysqli_query($link,"SELECT * FROM `suppliers` WHERE `suppliername`='$supplier' ");
							if(@mysqli_num_rows($record) > 0){
								while($supinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){	
									$transnum = $supinfo['transnum'];
									$debtfor = $supinfo['debtfor'];
									$transnum = $transnum +1;
									if( $_POST['settlement'] == 'full' && $_POST['paymenttype'] == 'debt'  )   {
										$debtfor = $debtfor + $billtotal;
										$update2 = mysqli_query($link,"UPDATE `suppliers` SET `transnum`='$transnum', `debtfor`='$debtfor' WHERE `suppliername`='$supplier'");
									} elseif ( $_POST['settlement'] == 'partial' )   {
										$debtfor = $debtfor + ( $billtotal - $paid );
										$update2 = mysqli_query($link,"UPDATE `suppliers` SET `transnum`='$transnum', `debtfor`='$debtfor' WHERE `suppliername`='$supplier'");
									} else {
										$update2 = mysqli_query($link,"UPDATE `suppliers` SET `transnum`='$transnum' WHERE `suppliername`='$supplier'");
									}
								}
							}
						}
						
						/*// Get las Bill Id
						$record = mysqli_query($link,"SELECT * FROM `pbills` ORDER BY id DESC LIMIT 1");
						while($billinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){	$billid = $billinfo['id'];	}
						// add leading zeroes (6 digits)
						$billid = str_pad($billid, 6, '0', STR_PAD_LEFT); */

						if ( glob($directory.'/'.$newbillid.'*') )  {				//Check existing files to remove
							$existing = glob($directory.'/'.$newbillid.'*', GLOB_BRACE);	
							for ($x = 0; $x < count($existing); $x++) {
								unlink($existing[$x]);
							}
						}

						if ( !$_FILES ) {	// handle Images
							$fileext = Fileext($_POST['original']);
							if ( file_put_contents($directory.'/'.$newbillid.'.'.$fileext, file_get_contents($_POST['original'])) ) {
								$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','bill','$logtext','$time' )");
								$responseArray = array('id' => 'success', 'message' => 'تمت إضافة صورة الفاتوره بنجاح', 'lastbillid' => $newbillid);
								$uploadsuccess = 1;
							} else {
								$responseArray = array('id' => 'anger', 'message' => 'خطأ في رفع الصوره');
							}
						} else {	// Handdle PDF
							$directory = $directory."/";			$fileName = basename($_FILES["original"]["name"]);
							$targetFilePath = $directory . $newbillid . '.pdf';
							if ( move_uploaded_file($_FILES['original']['tmp_name'], $targetFilePath) ) {
								$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','bill','$logtext','$time' )");
								$responseArray = array('id' => 'success', 'message' => 'تمت إضافة ملف الفاتوره بنجاح', 'lastbillid' => $newbillid);
								$uploadsuccess = 1;
							} else {
								$responseArray = array('id' => 'anger', 'message' => 'خطأ في رفع الملف');
							}
						}

					}
					
					// Send new purchase bill info back 
					if ( $uploadsuccess == 1 ) {
						$id = ltrim($newbillid, '0');
						$record = mysqli_query($link,"SELECT * FROM `pbills` WHERE id = '$id' ");
						$pbillsnum = mysqli_num_rows($record);
						if(@mysqli_num_rows($record) > 0){									
						$z = 0;				$Totalpbills = [];	
							while($pbillinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
								$id = $pbillinfo['id'];
									$billid = str_pad($id, 6, '0', STR_PAD_LEFT);
									if ( glob($directory.'/'.$billid.'*') )  {						//Check existing files
										// $existing = glob($directory.'/'.$billid.'*');
										$existing = glob($directory.'/'.$billid.'*', GLOB_BRACE);
										$info = pathinfo($existing[0]);
										// $billid = $billid.$info["extension"];
										$billid = $info['basename'];
									}
								// $ext = $pbillinfo['ext'];
								$total = $pbillinfo['total'];
								$settlement = $pbillinfo['settlement'];
								$paid = $pbillinfo['paid'];
								$supplier = $pbillinfo['supplier'];
								$paymenttype = $pbillinfo['paymenttype'];
								$timeadded = $pbillinfo['timeadded'];											
								$timeadded = Time_Passed(date($pbillinfo['timeadded']),'time');
								$timeedited = $pbillinfo['timeedited'];											
								if(isset($timeedited) && !empty($timeedited) )   {
									$timeedited = Time_Passed(date($pbillinfo['timeedited']),'time');
								}

							${'pbill'.$z} = array('id' => $id,'billid' => $billid,'total' => $total,'settlement' => $settlement,'paid' => $paid,'supplier' => $supplier, 'paymenttype' => $paymenttype,'timeadded' => $timeadded,'timeedited' => $timeedited );	
							$z++;
							}
						}
						$responseArray['pbillsnum'] = $pbillsnum;
					}

					// Endo fo sending new purchase bill info 
				}
			}				// END of Bill Submittion ///////////////////////////////////////////////////
		}
	}
}

// if requested by AJAX request return JSON response
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($pbillsnum) && $pbillsnum > 0 ){	
		for($a=0;$a<$pbillsnum;$a++){ 
			array_push($Totalpbills,${'pbill'.$a}); 
		}	
		array_push($responseArray,$Totalpbills); 
	}
    // $encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
    header('Content-Type: application/json'); $encoded = json_encode($responseArray);			echo $encoded;
}
?>