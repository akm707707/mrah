<?php /*header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8');*/ 
header("Content-type: application/pdf");
header('Content-type: application/json');	
header('Content-Type: text/html; charset=utf-8'); /*Arabic*/	
header('cache-control: no-cache'); // uploadfile via ajax for iphone
require 'inc/functions.php'; 		
require_once('lib/TCPDF/examples/tcpdf_include.php');
session_start();
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
			$hijdev = $entity['hijdev'];
			$fiscal = $entity['fiscal'];
			$username = $_SESSION['username'];
			$userid = $_SESSION['userid'];
			$clearance = $_SESSION['userclearance'];
			$empname = $_SESSION['name'];
			
			$addrecord = mysqli_fetch_array(mysqli_query($link," SELECT * FROM `contacts` WHERE identifier = '$identifier' "), MYSQLI_ASSOC);
			$address = $addrecord['address'];

			if(isset($_POST['id']) && !empty($_POST['id']) )   {
				$id = mysqli_real_escape_string($link, $_POST['id']);
				$receiptid = str_pad($id, 6, '0', STR_PAD_LEFT);

				$stmt = $link->prepare("SELECT * FROM `cbills` WHERE `id` = ?");
				$stmt->bind_param("i", $id);
				$stmt->execute();
				$record = $stmt->get_result();
				// $record = mysqli_query($link,"SELECT * FROM `cbills` WHERE `id`='$id'");
				while($recordinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){	
					$customerid = $recordinfo['cid'];
						if ( !empty($recordinfo['cid']) ) {
							$customerrecord = mysqli_query($link,"SELECT * FROM `customers` WHERE `id`='$customerid'");
							if(@mysqli_num_rows($customerrecord) > 0){
								while($customerinfo = mysqli_fetch_array($customerrecord, MYSQLI_ASSOC)){
									$customername = $customerinfo['name'];
									$customermobile = $customerinfo['mobile'];
								}
							} else {
								$customername = '';
								$customermobile = '';
							}
						} else {
							$customername = '';
							$customermobile = '';
						}

					$bcs = $recordinfo['bcs'];							$bcs = explode(",",$bcs);
					$itemdescarr = [];				
					for($a=0;$a<count($bcs);$a++){
						$desc = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE barcode = '$bcs[$a]' OR FIND_IN_SET('$bcs[$a]', wsbc)");
						if(@mysqli_num_rows($desc) > 0){									
							while($descinfo = mysqli_fetch_array($desc, MYSQLI_ASSOC)){
								if ( $bcs[$a] == $descinfo['barcode'] ) {
									$description = $descinfo['description'];		array_push($itemdescarr,$description);
								} else {
									$wsbcs = $descinfo['wsbc'];			$wsbcs = explode(",",$wsbcs);
									$wsdescs = $descinfo['wsdescription'];		$wsdescs = explode(",",$wsdescs);
									for($k=0;$k<count((array)$wsbcs);$k++){
										if ( $bcs[$a] == $wsbcs[$k] ) { array_push($itemdescarr,$wsdescs[$k]);	}
									}
								}
							}					
						}
					}
					$qtys = $recordinfo['qtys'];						$qtys = explode(",",$qtys);
					$costs = $recordinfo['costs'];						$costs = explode(",",$costs);
					$prevatprice = $recordinfo['ptunitprice'];			$prevatprice = explode(",",$prevatprice);
					$vat = $recordinfo['vat'];							$vat = explode(",",$vat);
					$discount = $recordinfo['discount'];				$discount = explode(",",$discount);
					$itemprice = $recordinfo['itemprice'];				$itemprice = explode(",",$itemprice);
					$totalvat = $recordinfo['totalvat'];				
					$totaldiscount = $recordinfo['totaldiscount'];
					$totalprice = $recordinfo['totalprice'];
					$purchasetype = $recordinfo['ptype'];
					$cashier = $recordinfo['cashier'];
					$timeadded = $recordinfo['timeadded'];
				}


				//measures are calculated in this way: (inches * 72) or (millimeters * 72 / 25.4)
				// 80 mm width
				$custom_layout = array('100', '2000');
				// $custom_layout = array('100', '100');
				$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $custom_layout, true, 'UTF-8', false);

				$flag = 1;
				label: 
				// set document information
				$pdf->setCreator(PDF_CREATOR);
				$pdf->setAuthor('Abudlkarim Alharbi');
				$pdf->setTitle('B2C Receipt');
				$pdf->setSubject('B2C Receipt');
				$pdf->setKeywords('Grocery, B2C Receipt, Receipt');
				$pdf->SetPrintHeader(false);
				$pdf->SetPrintFooter(false);

				$lg = Array();
				$lg['a_meta_charset'] = 'UTF-8';
				$lg['a_meta_dir'] = 'rtl';
				$lg['a_meta_language'] = 'fa';
				$lg['w_page'] = 'page';

				// set image scale factor
				// $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
				$pdf->setLanguageArray($lg);
				$pdf->AddPage();
				$pdf->SetAutoPageBreak(TRUE, 0);
				// set JPEG quality
				// $pdf->setJPEGQuality(75);
				$pdf->setRTL(true);
				$cellborder = 0;

				$pdf->setFont('arbfonts22016adobearabic', 'B', 22);
				$pdf->MultiCell(80, 7, 'فاتورة ضريبيه مبسطة', $border=$cellborder, $align='C', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M');
				$pdf->Ln();
				$pdf->setFont('arbfonts22016adobearabic', '', 18);
				$pdf->MultiCell(80, 7, 'رقم الفاتورة '.$receiptid, $border=$cellborder, $align='C', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M');
				$pdf->Ln();
				$pdf->setFont('arbfonts22016adobearabic', 'B', 18);
				$pdf->MultiCell(80, 7, $Aname, $border=$cellborder, $align='C', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M');
				$pdf->Ln();
				$pdf->setFont('arbfonts22016adobearabic', 'B', 14);
				$pdf->MultiCell(80, 7, $address, $border=$cellborder, $align='C', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M');
				$pdf->Ln();
				$pdf->setFont('arbfonts22016adobearabic', 'B', 18);
				$pdf->MultiCell(80, 7, 'التاريخ '.HijriDate($hijdev).'هـ الموافق '.$Gdate.'م', $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M');
				$pdf->Ln();
				$pdf->MultiCell(80, 7, 'السجل التجاري '.$crid, $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M');
				$pdf->Ln();
				$pdf->MultiCell(80, 7, 'الرقم الضريبي '.$taxid, $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M');
				$pdf->Ln();$pdf->MultiCell(80, 7, 'اسم العميل '.$customername, $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M');
				$pdf->writeHTML("<hr>", false, false, false, false, '');	// horizontal line
				$pdf->Ln();
				$pdf->MultiCell(80, 7, 'جوال العميل '.$customermobile, $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M');
				$pdf->Ln();
				$pdf->writeHTML("<hr>", false, false, false, false, '');	// horizontal line
				// $pdf->Ln();
				$pdf->MultiCell(80, 7, 'اسم المحاسب '.$empname, $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M');
				$pdf->Ln();
				$pdf->writeHTML("<hr>", false, false, false, false, '');	// horizontal line


				// $pdf->setFont('arbfonts22016adobearabic', 'B', 22);
				// $pdf->Cell(0, 0, 'فاتورة ضريبيه مبسطة',0,1,'C');
				// $pdf->setFont('arbfonts22016adobearabic', '', 18);
				// $pdf->Cell(0, 0, 'رقم الفاتورة '.$receiptid,0,1,'C');
				// $pdf->setFont('arbfonts22016adobearabic', 'B', 18);
				// $pdf->Cell(0, 0, $Aname,0,1,'C');
				// $pdf->Cell(0, 0, $address,0,1,'C');
				// $pdf->Cell(0, 0, 'التاريخ 10/10/1410هـ',0,1,'R');
				// $pdf->Cell(0, 0, 'الرقم الضريبي '.$taxid,0,1,'R');
				// $pdf->writeHTML("<hr>", false, false, false, false, '');	// horizontal line
				// $pdf->Cell(0, 0, 'اسم العميل '.$customername,0,1,'R');
				// $pdf->Cell(0, 0, 'جوال العميل '.$customermobile,0,1,'R');
				// $pdf->writeHTML("<hr>", false, false, false, false, '');	// horizontal line

				$xw = 16;
				$xh = 11;
				// Multicell Header
				$pdf->setFont('arbfonts22016adobearabic', 'B', 12);
				$pdf->MultiCell($xw-3, $xh, 'المنتجات', $border=$cellborder, $align='C', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
				$pdf->MultiCell($xw-5, $xh, 'الكميه', $border=$cellborder, $align='C', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
				$pdf->MultiCell($xw-4, $xh, 'سعر'." \n".' الوحده', $border=$cellborder, $align='C', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
				$pdf->MultiCell($xw+2, $xh, 'ضريبة القيمة المضافة', $border=$cellborder, $align='C', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
				$pdf->MultiCell($xw+10, $xh, 'السعر شامل ضريبة القيمة المضافة', $border=$cellborder, $align='C', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
				$pdf->Ln();
				$pdf->MultiCell(80, 0.5, '', $border=0, $align='C', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M');
				$pdf->writeHTML("<hr>", false, false, false, false, '');	// horizontal line

				$prevattotal = 0;
				for($a=0;$a<count($bcs);$a++){
					// Items TO BE REPEATED
					$xw = 80;
					$xh = 7;
					$pdf->MultiCell($xw, $xh, $itemdescarr[$a], $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='M');
					$pdf->Ln();
					$xw = 16;
					$xh = 7;
					$pdf->MultiCell($xw-3, $xh, '', $border=$cellborder, $align='C', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
					$pdf->MultiCell($xw-5, $xh, $qtys[$a], $border=$cellborder, $align='C', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
					$pdf->MultiCell($xw-4, $xh, $prevatprice[$a], $border=$cellborder, $align='C', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
					$pdf->MultiCell($xw+2, $xh, $vat[$a], $border=$cellborder, $align='C', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
					$pdf->MultiCell($xw+10, $xh, $itemprice[$a], $border=$cellborder, $align='C', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
					$pdf->Ln();
					$prevattotal += $prevatprice[$a];
				}

				// // Cost Before Tax
				$pdf->Ln();
				$pdf->setFont('arbfonts22016adobearabic', 'B', 14);
				$xw = 80;
				$xh = 7;
				$pdf->MultiCell($xw-20, $xh, 'إجمالي المبلغ الخاضع للضريبه', $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
				$pdf->MultiCell($xw-60, $xh, $prevattotal, $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
				$pdf->writeHTML("<hr>", true, false, false, false, '');	// horizontal line

				// Total VAT
				$pdf->MultiCell($xw-20, $xh, 'ضريبة القيمة المضافة', $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
				$pdf->MultiCell($xw-60, $xh, $totalvat, $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
				$pdf->writeHTML("<hr>", true, false, false, false, '');	// horizontal line

				// Total with VAT
				$pdf->MultiCell($xw-20, $xh, 'المجموع مع الضريبة', $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
				$pdf->MultiCell($xw-60, $xh, $totalprice+$totaldiscount, $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
				$pdf->writeHTML("<hr>", true, false, false, false, '');	// horizontal line

				// Discount
				$pdf->MultiCell($xw-20, $xh, 'الخصم', $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
				$pdf->MultiCell($xw-60, $xh, $totaldiscount, $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
				$pdf->writeHTML("<hr>", true, false, false, false, '');	// horizontal line

				// Total after Discount
				$pdf->MultiCell($xw-20, $xh, 'المجموع بعد الخصم', $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
				$pdf->MultiCell($xw-60, $xh, $totalprice, $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
				$pdf->writeHTML("<hr>", true, false, false, false, '');	// horizontal line

				// Total Paid
				if ( $purchasetype == 'pos' || $purchasetype == 'cash' || $purchasetype == 'wire' || $purchasetype == 'cheque' || $purchasetype == 'creditcard' ) { $paid = $totalprice;	$left = 0; } else { $paid = 0;	$left = $totalprice; }
				$pdf->MultiCell($xw-20, $xh, 'المدفوع', $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
				$pdf->MultiCell($xw-60, $xh, $paid, $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
				$pdf->writeHTML("<hr>", true, false, false, false, '');	// horizontal line

				// Remaining
				$pdf->MultiCell($xw-20, $xh, 'المتبقي', $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
				$pdf->MultiCell($xw-60, $xh, $left, $border=$cellborder, $align='R', $fill=0, 0, '', '', $reseth=true, $strech=0, $ishtml=false, $autopadding=true, $maxh=$xh, $valign='M');
				$pdf->writeHTML("<hr>", true, false, false, false, '');	// horizontal line


				$pdf->Ln();
				$pdf->Ln();
				$pdf->Cell(0, 0, 'إغلاق الفاتورة',0,1,'C');
				$pdf->writeHTML("<hr>", true, false, false, false, '');	// horizontal line
				// $pdf->Ln();
/////////// QR Code Section
// Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)

				$pdf->Image('qr.png', '', '', 40, 40, 'PNG', '', 'C', true, 150, 'C', false, false, 0, false, false, false);
				$contentHeight = $pdf->GetY();
				if ( $flag > 0 ) {
						$custom_layout = array('100', $contentHeight+60);
						$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $custom_layout, true, 'UTF-8', false);
						$flag = 0;
						goto label;
				}

				//Close and output PDF document
				// $pdf->Output('example_018.pdf', 'I');				/* I: show in (inline browser if exists) */
				$pdf->Output(dirname(__FILE__).'/cbill.pdf', 'F');		/* F: save to server */
				$responseArray = array('id' => 'success', 'message' => 'تم إصدار الفاتوره بنجاح');
			} else {
				$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم $id doesnt exists');
			}
		}
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
end:

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	$encoded = json_encode($responseArray);		
	header('Content-Type: application/json');	
	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message

?>