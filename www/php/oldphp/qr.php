<?php 
namespace Salla\ZATCA\Test\Unit;
require 'lib/ZATCA/vendor/autoload.php';
require 'inc/functions.php'; 		
use Salla\ZATCA\GenerateQrCode;
use Salla\ZATCA\Tags\InvoiceDate;
use Salla\ZATCA\Tags\InvoiceTaxAmount;
use Salla\ZATCA\Tags\InvoiceTotalAmount;
use Salla\ZATCA\Tags\Seller;
use Salla\ZATCA\Tags\TaxNumber;

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
			$taxid = $entity['taxid'];
			// $ztime = date("Y-m-d\TH:i:s\Z");	// current time in zulu format

			if(isset($_POST['id']) && !empty($_POST['id']) )   {
				$id = mysqli_real_escape_string($link, $_POST['id']);
				$receiptid = str_pad($id, 6, '0', STR_PAD_LEFT);
				$record = mysqli_query($link,"SELECT * FROM `cbills` WHERE `id`='$id'");
				while($recordinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){	
					$totalvat = $recordinfo['totalvat'];				
					$totalprice = $recordinfo['totalprice'];
					$timeadded = $recordinfo['timeadded'];
					$ztime = gmdate("Y-m-d\TH:i:s\Z", $timeadded);
				} 
				$displayQRCodeAsBase64 = GenerateQrCode::fromArray([
					new Seller($Aname), // seller name        
					new TaxNumber($taxid), // seller tax number
					new InvoiceDate($ztime), // invoice date as Zulu ISO8601 
					new InvoiceTotalAmount($totalprice), // invoice total amount
					new InvoiceTaxAmount($totalvat) // invoice tax amount
				])->render();
				file_put_contents('qr.png', file_get_contents($displayQRCodeAsBase64)); //post new photo
				$responseArray = array('id' => 'success');
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
