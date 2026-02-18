<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require '../../php/inc/functions.php'; 
if($_POST['bc']){
	$Arrays = [];		
	$Catdata = [];		
	$bc = mysqli_real_escape_string($link, $_POST['bc']);


	$url = 'https://www.sfda.gov.sa/GetFoodFullSearch.php?Barcode='.$bc;

	$response = file_get_contents($url);
	$response = json_decode($response);
	$responseArray = $response;

	
	/*
	if(isset($_POST['db']) && !empty($_POST['db']) && $_POST['db'] == 'own' )   {
		$record = mysqli_query($link,"SELECT * FROM `ownbcdb` WHERE `barcode`='$bc'");
	} else { 
		$record = mysqli_query($link,"SELECT * FROM `bcdb` WHERE `barcode`='$bc'");
	}
	if(@mysqli_num_rows($record) > 0){
		if( $_POST['db'] == 'universal' )   {
			$catrecord = mysqli_query($link,"SELECT * FROM `category`");
			while($catinfo = mysqli_fetch_array($catrecord, MYSQLI_ASSOC)){																
				$Cat = $catinfo['category'];
				array_push($Catdata,$Cat);
			}
		}
		while($info = mysqli_fetch_array($record, MYSQLI_ASSOC)){
			if( $_POST['db'] == 'own' )   {
				$barcode = $info['barcode'];	
				$itemDescription = $info['description'];	
				$tradename = '';
				$data = array('barcode' => $barcode,'itemDescription' => $itemDescription,'tradename' => $tradename);	
			}
			if( $_POST['db'] == 'universal' )   {
				$barcode = $info['barcode'];	
				$itemDescription = $info['itemDescription'];	
				$brandname = $info['brandName'];
				$tradename = $info['tradeName'];
				$itemWeight = $info['itemWeight'];
				$unitNameAr = $info['unitNameAr'];
				$companyName = $info['companyName'];
				$data = array('barcode' => $barcode,'itemDescription' => $itemDescription,'brandname' => $brandname,'tradename' => $tradename,'itemWeight' => $itemWeight.' '.$unitNameAr,'companyName' => $companyName);	
			}
			array_push($Arrays,$data);
		}
		
		
		$responseArray = array('id' => 'success', 'message' => $Arrays, 'catdata' => $Catdata );			
	} else {	
		if( $_POST['db'] == 'own' )   { $errmsg = 'المنتج غير معرف جاري البحث في قاعدة البيانات الشامله'; } else { $errmsg = 'المنتج غير معرف'; }
		$responseArray = array('id' => 'danger', 'message' => $errmsg );
	}
	*/
} else {
	$responseArray = array('id' => 'danger', 'message' => 'من فضلك حاول في وقت لاحق');
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>