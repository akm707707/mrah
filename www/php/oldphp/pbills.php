<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php';
session_start();
$logtext = '';
//print_r($_POST);	
if($_POST){
	$time = time();
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
			
			// add leading zeroes (4 digits)
			$identifier = str_pad($identifier, 4, '0', STR_PAD_LEFT);
			$directory = "../userdata/".$identifier."/Pbills";	
		
			if(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'delete' )   {
				
				if(isset($_POST['id']) && !empty($_POST['id']) )   {
					$id = mysqli_real_escape_string($link, $_POST['id']);
						// Logging
						$record = mysqli_query($link,"SELECT * FROM `pbills` WHERE `id`='$id' ");
						if(@mysqli_num_rows($record) > 0){					
							while($billinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){	
								$logid = $billinfo['id'];	
								$logtotal = $billinfo['total'];	
								$logsettlement = $billinfo['settlement'];	
								$logpaid = $billinfo['paid'];	
								$logpaymenttype = $billinfo['paymenttype'];	
								$logsupplier = $billinfo['supplier'];	
								$logpids = $billinfo['pids'];	
							}
						}
						
						$logtext .= 'تم حذف فاتورة شراء برقم '.$logid.' ومبلغ اجمالي  '.$logtotal .' ';
						if( $settlement == 'partial' )   {
							$logtextv2 = 'تم سدادها جزئياً بقدر  '.$logpaid.' ';
						} else { // paid in full
							$logtextv2 = 'مسددة بالكامل ';
						}
						if( $logpaymenttype == 'pos' )   {			$logtext .= $logtextv2;		$logtext .= 'عن طريق الشبكة '; }
						if( $logpaymenttype == 'cash' )   {		$logtext .= $logtextv2;		$logtext .= 'عن طريق الكاش '; }
						if( $logpaymenttype == 'wire' )   {		$logtext .= $logtextv2;		$logtext .= 'عن طريق التحويل البنكي '; }
						if( $logpaymenttype == 'cheque' )   {		$logtext .= $logtextv2;		$logtext .= 'عن طريق شيك '; }
						if( $logpaymenttype == 'creditcard' ) {	$logtext .= $logtextv2;		$logtext .= 'عن طريق البطاقة الائتمانيه '; }
						if( $logpaymenttype == 'debt' )   {		$logtext .= 'عن طريق الدين ';		}

						$logtext .= 'من المورد '.$logsupplier;

					$delete = mysqli_query($link,"DELETE FROM pbills WHERE id = $id ");
					if ($delete) {
						
						$record = mysqli_query($link,"SELECT * FROM `suppliers` WHERE `suppliername`='$logsupplier' ");
						if(@mysqli_num_rows($record) > 0){
							while($supinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){	
								$debtfor = $supinfo['debtfor'];
								$transnum = $supinfo['transnum'];	$transnum = $transnum -1;
								$update1 = mysqli_query($link,"UPDATE `suppliers` SET `transnum`='$transnum' WHERE `suppliername`='$logsupplier'");
							}
						}
						// Delete bill
						if(isset($_POST['restore']) && !empty($_POST['restore']) )   {	
						
							if( !empty($logsupplier) ) {
								// Calculate how mych debt was added when bill sumbited
								if( $logsettlement == 'full' && $logpaymenttype == 'debt'  )   {
									$debtfor = $debtfor - $logtotal;
									$update2 = mysqli_query($link,"UPDATE `suppliers` SET `debtfor`='$debtfor' WHERE `suppliername`='$logsupplier'");
								} elseif ( $logsettlement == 'partial' )   {
									$debtfor = $debtfor - ( $logtotal - $logpaid );
									$update2 = mysqli_query($link,"UPDATE `suppliers` SET `debtfor`='$debtfor' WHERE `suppliername`='$logsupplier'");
								} 
							}
						}
						
						$zeroedid = str_pad($id, 6, '0', STR_PAD_LEFT);
						if ( glob($directory.'/'.$zeroedid.'*') )  {						//Check existing files
							$existing = glob($directory.'/'.$zeroedid.'*', GLOB_BRACE);	
							$existingcount = count(glob($directory.'/'.$zeroedid.'*', GLOB_BRACE));
							// delete files and upload new one
							for ($x = 0; $x < $existingcount; $x++) {
								unlink($existing[$x]);
							}
						}
						
						// delete debts

						$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','category','$logtext','$time' )");
						$responseArray = array('id' => 'success', 'message' => 'تم حذف الفاتورة بنجاح');
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات');
					}
				} else {
					$responseArray = array('id' => 'danger', 'message' => 'لا توجد بيانات');
				}
				
			} elseif (isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'update' )   {
			
				if(!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'حطأ في الخادم $id');
				} elseif(!isset($_POST['billtotal']) || empty($_POST['billtotal']) )   {
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
				} else { 

					$id = mysqli_real_escape_string($link, $_POST['id']);
					$billtotal = mysqli_real_escape_string($link, $_POST['billtotal']);
					$settlement = mysqli_real_escape_string($link, $_POST['settlement']);
					$paymenttype = mysqli_real_escape_string($link, $_POST['paymenttype']);
					$supplier = mysqli_real_escape_string($link, $_POST['supplier']);

					// Get last id number
					$record = mysqli_query($link,"SELECT * FROM `pbills` WHERE `id`='$id' ");
					if(@mysqli_num_rows($record) > 0){					
						while($billinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){	
							$logid = $billinfo['id'];	
							$logtotal = $billinfo['total'];	
							$logsettlement = $billinfo['settlement'];	
							$logpaid = $billinfo['paid'];	
							$logpaymenttype = $billinfo['paymenttype'];	
							$logsupplier = $billinfo['supplier'];	
							$logpids = $billinfo['pids'];	
						}
					}
					
					$zeroedid = str_pad($id, 6, '0', STR_PAD_LEFT);
					
					// For Logging 
					$logtext .= 'تم تعديل فاتورة الشراء رقم '.$logid.' ';
					if ( $logsupplier != $supplier ) {		$logtext .= 'بتغيير المورد من '.$logsupplier.' إلى '.$supplier.' '; }
					if ( $logtotal != $billtotal ) {		$logtext .= 'وتغيير القيمة الكليه من '.$logtotal.' إلى '.$billtotal.' '; }
					if ( $logsettlement != $settlement ) {
						if( $logsettlement == 'partial' )   {
							$logtextv1 = 'مسددة جزئياً بقدر  '.$logpaid.' ';
						} else { // paid in full
							$logtextv1 = 'مسددة بالكامل ';
						}
						if( $logpaymenttype == 'pos' )   {		$logtext .= $logtextv1;		$logtext .= 'عن طريق الشبكة '; }
						if( $logpaymenttype == 'cash' )   {		$logtext .= $logtextv1;		$logtext .= 'عن طريق الكاش '; }
						if( $logpaymenttype == 'wire' )   {		$logtext .= $logtextv1;		$logtext .= 'عن طريق التحويل البنكي '; }
						if( $logpaymenttype == 'cheque' )   {	$logtext .= $logtextv1;		$logtext .= 'عن طريق شيك '; }
						if( $logpaymenttype == 'creditcard' ) {	$logtext .= $logtextv1;		$logtext .= 'عن طريق البطاقة الائتمانيه '; }
						if( $logpaymenttype == 'debt' )   {		$logtext .= 'عن طريق الدين ';		}
					}
					

					if( $settlement == 'partial' )   {
						$paid = mysqli_real_escape_string($link, $_POST['paid']);
						$logtextv2 = 'تم سدادها جزئياً بقدر  '.$paid.' ';
					} else { // paid in full
						$paid = mysqli_real_escape_string($link, $_POST['billtotal']);
						$logtextv2 = 'مسددة بالكامل ';
					}
					
					$logtext .= 'إلى ';
					if( $paymenttype == 'pos' )   {			$logtext .= $logtextv2;			$logtext .= 'عن طريق الشبكة '; }
					if( $paymenttype == 'cash' )   {		$logtext .= $logtextv2;			$logtext .= 'عن طريق الكاش '; }
					if( $paymenttype == 'wire' )   {		$logtext .= $logtextv2;			$logtext .= 'عن طريق التحويل البنكي '; }
					if( $paymenttype == 'cheque' )   {		$logtext .= $logtextv2;			$logtext .= 'عن طريق شيك '; }
					if( $paymenttype == 'creditcard' ) {	$logtext .= $logtextv2;			$logtext .= 'عن طريق البطاقة الائتمانيه '; }
					if( $paymenttype == 'debt' )   {		$logtext .= 'عن طريق الدين ';		}

					// add leading zeroes (4 digits)
					$identifier = str_pad($identifier, 4, '0', STR_PAD_LEFT);
					//check if mother folder "userdata" exists or not and then make it 
					if( is_dir("../userdata/") === false ){	mkdir("../userdata/", 0777);	}
					
					$directory = "../userdata/".$identifier;	
					if( is_dir($directory) === false ){	mkdir($directory, 0777);	}

					$directory = "../userdata/".$identifier."/Pbills";	
					if( is_dir($directory) === false ){	mkdir($directory, 0777);	}

					$update = mysqli_query($link,"UPDATE `pbills` SET 
					`total` = '$billtotal',
					`settlement` = '$settlement',
					`paid` = '$paid',
					`paymenttype` = '$paymenttype',
					`supplier` = '$supplier',
					`timeedited` = '$time'
					WHERE `id`='$id'");
					
					if ($update) {
					// Modify Supplier balance
						if( !empty($supplier) ) {
							$record = mysqli_query($link,"SELECT * FROM `suppliers` WHERE `suppliername`='$supplier' ");
							if(@mysqli_num_rows($record) > 0){
								while($supinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){	
									$debtfor = $supinfo['debtfor'];
									// Calculate how mych debt was added when bill sumbited
									if( $logsettlement == 'full' && $logpaymenttype == 'debt'  )   {
										$billdebt = $logtotal;
									} elseif ( $logsettlement == 'partial' )   {
										$billdebt = $logtotal - $logpaid;
									} else {
										$billdebt = 0;
									}
									if( $_POST['settlement'] == 'full' && $_POST['paymenttype'] == 'debt'  )   {
										$debtfor = $debtfor - $billdebt + $billtotal;
										$update2 = mysqli_query($link,"UPDATE `suppliers` SET `debtfor`='$debtfor' WHERE `suppliername`='$supplier'");
									} elseif ( $_POST['settlement'] == 'partial' )   {
										$debtfor = $debtfor - $billdebt + ( $billtotal - $paid );
										$update2 = mysqli_query($link,"UPDATE `suppliers` SET `debtfor`='$debtfor' WHERE `suppliername`='$supplier'");
									} else {
										$debtfor = $debtfor - $billdebt;
										$update2 = mysqli_query($link,"UPDATE `suppliers` SET `debtfor`='$debtfor' WHERE `suppliername`='$supplier'");
									}
								}
							}
						}
					}

					if ( !$_FILES ) {	// handle Images
						// Update Bill image if exists
						if(isset($_POST['original']) && !empty($_POST['original']) )   {		$logtext .= 'مع رفع صورة فاتورة جدده ';
							$fileext = Fileext($_POST['original']);
							
							if ($update) {
																
								if ( glob($directory.'/'.$zeroedid.'*') )  {						//Check existing files
									$existing = glob($directory.'/'.$zeroedid.'*', GLOB_BRACE);	
									$existingcount = count(glob($directory.'/'.$zeroedid.'*', GLOB_BRACE));
									// delete files and upload new one
									for ($x = 0; $x < $existingcount; $x++) {
										unlink($existing[$x]);
									}
								}

								file_put_contents($directory.'/'.$zeroedid.'.'.$fileext, file_get_contents($_POST['original'])); //post new photo
								$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','bill','$logtext','$time' )");

							} else {
								$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ في الخادم $update');
							}
						}
					} else {	// Handdle PDF
					
						$directory = $directory."/";
						$fileName = basename($_FILES["original"]["name"]);
						
						if ( isset($fileName) ) { 	$logtext .= 'مع رفع صورة فاتورة جدده ';		}
						
						if ($update) {

							if ( glob($directory.'/'.$zeroedid.'*') )  {						//Check existing files
								$existing = glob($directory.'/'.$zeroedid.'*', GLOB_BRACE);	
								$existingcount = count(glob($directory.'/'.$zeroedid.'*', GLOB_BRACE));
								// delete files and upload new one
								for ($x = 0; $x < $existingcount; $x++) {
									unlink($existing[$x]);
								}
							}

							$targetFilePath = $directory . $zeroedid . '.pdf';

							move_uploaded_file($_FILES['original']['tmp_name'], $targetFilePath);							
							
							$logins = mysqli_query($link,"INSERT INTO `logs`( `id`,`eid`,`name`,`section`,`info`,`timeadded` )VALUES(	NULL,'$identifier','$empname','bill','$logtext','$time' )");

						} else {
							$responseArray = array('id' => 'danger', 'message' => 'حدث خطأ في الخادم $pdfins');
						}
					}
					// Send updated purchase bill info back 
					if ( $update ) {
						$id = ltrim($zeroedid, '0');
						$record = mysqli_query($link,"SELECT * FROM `pbills` WHERE id = '$id'   ");
						$pbillsnum = mysqli_num_rows($record);
						if(@mysqli_num_rows($record) > 0){									
						$z = 0;				$responseArray = [];			$Totalpbills = [];	
						$pbcs = [];			$pdescs = [];		$pqtys = [];		$pcost = [];	
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
								$pids = $pbillinfo['pids'];					$pids = explode(",",$pids);

									for($i=0;$i<count((array)$pids);$i++){
									// for($i=1;$i<2;$i++){
										$purchaserecord = mysqli_query($link,"SELECT * FROM `purchases` WHERE id = '$pids[$i]'  ");
										if(@mysqli_num_rows($purchaserecord) > 0){									
											while($purchase = mysqli_fetch_array($purchaserecord, MYSQLI_ASSOC)){
												$bc = $purchase['bc'];					array_push($pbcs, $bc);
												$qty = $purchase['qty'];				array_push($pqtys, $qty);
												$totalcost = $purchase['totalcost'];	array_push($pcost, $totalcost);
												$pas = $purchase['pas'];				
												
												if ( $pas == 'retail' ) {
													$record2 = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE `barcode` = $bc ");
													if(@mysqli_num_rows($record2) > 0){
														while($barcode = mysqli_fetch_array($record2, MYSQLI_ASSOC)){
															$description = $barcode['description'];		array_push($pdescs, $description);
														}
													}
												}
												
												if ( $pas == 'ws' ) {
													$record2 = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE FIND_IN_SET('$bc', wsbc) ");
													if(@mysqli_num_rows($record2) > 0){					
														while($barcode = mysqli_fetch_array($record2, MYSQLI_ASSOC)){
															$wsbc = $barcode['wsbc'];				$wsbc = explode(",",$wsbc);
															$wsdesc = $barcode['wsdescription'];	$wsdesc = explode(",",$wsdesc);
															
															for($k=0;$k<count((array)$wsbc);$k++){
																if ( $wsbc[$k] == $bc ) { 
																	$description = $wsdesc[$k];			array_push($pdescs, $description);
																}
															}
														}
													}
												}
											}
										}
									}
								$supplier = $pbillinfo['supplier'];
								$paymenttype = $pbillinfo['paymenttype'];
								$timeadded = $pbillinfo['timeadded'];											
								$timeadded = Time_Passed(date($pbillinfo['timeadded']),'time');
								$timeedited = $pbillinfo['timeedited'];											
								if(isset($timeedited) && !empty($timeedited) )   {
									$timeedited = Time_Passed(date($pbillinfo['timeedited']),'time');
								}

							${'pbill'.$z} = array('id' => $id,'billid' => $billid,'total' => $total,'settlement' => $settlement,'paid' => $paid, 'paymenttype' => $paymenttype,'pbcs' => $pbcs,'pdescs' => $pdescs,'pqtys' => $pqtys,'pcost' => $pcost,'supplier' => $supplier,'timeadded' => $timeadded,'timeedited' => $timeedited );	
							$z++;
							}
						}
						$responseArray = array('id' => 'success','message' => 'تم التحديث بنجاح', 'pbillsnum' => $pbillsnum);
					}

					// Endo fo sending new purchase bill info 
				}	
			
			} elseif (isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'add' )   {
				/*
					HANDLED IN UPLOAD PHP FILE
				*/
			
			} elseif(isset($_POST['key']) && !empty($_POST['key']) && $_POST['key'] == 'purchaseupdate' )   {	
			
				if(!isset($_POST['id']) || empty($_POST['id']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $id');
				} elseif (!isset($_POST['paymenttype']) || empty($_POST['paymenttype']) )   {
					$responseArray = array('id' => 'danger', 'message' => 'يجب اختيار آلية السداد');
				} else {
					$id = mysqli_real_escape_string($link, $_POST['id']);	
					$paymenttype = mysqli_real_escape_string($link, $_POST['paymenttype']);

					$record = mysqli_query($link,"SELECT * FROM `pbills` WHERE id = '$id'   ");
					if(@mysqli_num_rows($record) > 0){									
						while($pbillinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
							$total = $pbillinfo['total'];
							$paid = $pbillinfo['paid'];
							$supplier = $pbillinfo['supplier'];
							$billdebt = $total - $paid;
						}
					}
					// Updating Section
					$update = mysqli_query($link,"UPDATE `pbills` SET `paid`='$total', `settlement`='full', `paymenttype`='$paymenttype', `timeedited`='$time'	WHERE `id`='$id'");

					if ($update) {
						// Modify Supplier balance
						if( !empty($supplier) ) {
							$record = mysqli_query($link,"SELECT * FROM `suppliers` WHERE `suppliername`='$supplier' ");
							if(@mysqli_num_rows($record) > 0){
								while($supinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){	
									$debtfor = $supinfo['debtfor'];
									$debtfor = $debtfor - $billdebt;
									$update2 = mysqli_query($link,"UPDATE `suppliers` SET `debtfor`='$debtfor' WHERE `suppliername`='$supplier'");
								}
							}
						}
						$responseArray = array('id' => 'success', 'message' => 'تم سداد المديونية بنجاح','paid' => $total,'settlement' => 'full','paymenttype' => $paymenttype);
					} else {
						$responseArray = array('id' => 'danger', 'message' => 'خطأ في قاعدة البيانات $update');
					}
				}
			} else {
				if(isset($_POST['id']) && !empty($_POST['id']))	{	
					$id = mysqli_real_escape_string($link, $_POST['id']);	
					$record = mysqli_query($link,"SELECT * FROM `pbills` WHERE id = '$id'   ");
				} else { 
					$record = mysqli_query($link,"SELECT * FROM `pbills` WHERE identifier = '$identifier' ");
				}
				
				$pbillsnum = mysqli_num_rows($record);
				if(@mysqli_num_rows($record) > 0){									
				$z = 0;				$responseArray = [];			$Totalpbills = [];	
					while($pbillinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){
						$pbcs = [];			$pdescs = [];		$pqtys = [];		$pcost = [];	

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
						$pids = $pbillinfo['pids'];					$pids = explode(",",$pids);

							for($i=0;$i<count((array)$pids);$i++){
							// for($i=1;$i<2;$i++){
								$purchaserecord = mysqli_query($link,"SELECT * FROM `purchases` WHERE id = '$pids[$i]'  ");
								if(@mysqli_num_rows($purchaserecord) > 0){									
									while($purchase = mysqli_fetch_array($purchaserecord, MYSQLI_ASSOC)){
										$bc = $purchase['bc'];					array_push($pbcs, $bc);
										$qty = $purchase['qty'];				array_push($pqtys, $qty);
										$totalcost = $purchase['totalcost'];	array_push($pcost, $totalcost);
										$pas = $purchase['pas'];				
										
										if ( $pas == 'retail' ) {
											$record2 = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE `barcode` = $bc ");
											if(@mysqli_num_rows($record2) > 0){
												while($barcode = mysqli_fetch_array($record2, MYSQLI_ASSOC)){
													$description = $barcode['description'];		array_push($pdescs, $description);
												}
											}
										}
										
										if ( $pas == 'ws' ) {
											$record2 = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE FIND_IN_SET('$bc', wsbc) ");
											if(@mysqli_num_rows($record2) > 0){					
												while($barcode = mysqli_fetch_array($record2, MYSQLI_ASSOC)){
													$wsbc = $barcode['wsbc'];				$wsbc = explode(",",$wsbc);
													$wsdesc = $barcode['wsdescription'];	$wsdesc = explode(",",$wsdesc);
													
													for($k=0;$k<count((array)$wsbc);$k++){
														if ( $wsbc[$k] == $bc ) { 
															$description = $wsdesc[$k];			array_push($pdescs, $description);
														}
													}
												}
											}
										}
									}
								}
							}
						$supplier = $pbillinfo['supplier'];
						$paymenttype = $pbillinfo['paymenttype'];
						$timeadded = $pbillinfo['timeadded'];											
						$timeadded = Time_Passed(date($pbillinfo['timeadded']),'time');
						$timeedited = $pbillinfo['timeedited'];											
						if(isset($timeedited) && !empty($timeedited) )   {
							$timeedited = Time_Passed(date($pbillinfo['timeedited']),'time');
						}

					${'pbill'.$z} = array('id' => $id,'billid' => $billid,'total' => $total,'settlement' => $settlement,'paid' => $paid, 'paymenttype' => $paymenttype,'pbcs' => $pbcs,'pdescs' => $pdescs,'pqtys' => $pqtys,'pcost' => $pcost,'supplier' => $supplier,'timeadded' => $timeadded,'timeedited' => $timeedited );	
					$z++;
					}
				}
				$responseArray = array('id' => 'success', 'pbillsnum' => $pbillsnum);
			}
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($pbillsnum) && $pbillsnum > 0 ){	
		for($a=0;$a<$pbillsnum;$a++){ 
			array_push($Totalpbills,${'pbill'.$a}); 
		}	
		array_push($responseArray,$Totalpbills); 
	}
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>