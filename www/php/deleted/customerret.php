<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		//print_r($_POST);	
if($_POST){
	$record = mysqli_query($link,"SELECT * FROM `suppliers` ");
	$suppliernum = mysqli_num_rows($record);
	if(@mysqli_num_rows($record) > 0){									$z = 0;				$responseArray = [];			$Totalsuppliers = [];	
		while($supplier = mysqli_fetch_array($record, MYSQLI_ASSOC)){
			$id = $supplier['id'];		
			$suppliername = $supplier['suppliername'];		
			$suppliermobile = $supplier['suppliermobile'];		
			$supplierlandline1 = $supplier['supplierlandline1'];		
			$supplierlandline2 = $supplier['supplierlandline2'];		
			$supplierwebsite = $supplier['supplierwebsite'];		
			$supplieremail = $supplier['supplieremail'];		
			$transactionsnumber = $supplier['transnum'];		
			$timeadded = Time_Passed(date($supplier['timeadded']),'time');			

			${'supplier'.$z} = array('id' => $id,'suppliername' => $suppliername,'suppliermobile' => $suppliermobile,'supplierlandline1' => $supplierlandline1,'supplierlandline2' => $supplierlandline2,'supplierwebsite' => $supplierwebsite,'supplieremail' => $supplieremail,'timeadded' => $timeadded,'transactionsnumber' => $transactionsnumber);	
			$z++;
		}
		$responseArray = array('id' => 'success', 'suppliernum' => $suppliernum);
	} else { 																				
		$responseArray = array('id' => 'danger', 'message' => 'no supplier exists');
	}
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if ( isset($suppliernum) && $suppliernum > 0 ){	for($a=0;$a<$suppliernum;$a++){ array_push($Totalsuppliers,${'supplier'.$a}); }	array_push($responseArray,$Totalsuppliers); }
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message
?>