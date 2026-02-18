<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		//print_r($_POST);	
if($_POST){
	// Retreive Suppliers	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$record = mysqli_query($link,"SELECT * FROM `suppliers` ");
	$supnum = mysqli_num_rows($record);
	if(@mysqli_num_rows($record) > 0){									
		$z = 0;				
		$responseArray = [];			
		$Totalsuppliers = [];	
		while($supplier = mysqli_fetch_array($record, MYSQLI_ASSOC)){
			$id = $supplier['id'];		
			$suppliername = $supplier['suppliername'];		
			${'supplier'.$z} = array('id' => $id,'suppliername' => $suppliername);	// additional key to identify on-behalf submission
			$z++;
		}
		// $responseArray = array('id' => 'success', 'supnum' => $supnum);
	} else { 																				
		$responseArray = array('id' => 'warning', 'message' => 'لا يوجد موردين مضافين');
	}
	
	//Retreive Categories	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$catrecord = mysqli_query($link,"SELECT * FROM `category`");
	$catnum = mysqli_num_rows($catrecord);
	$Catdata = [];
	while($catinfo = mysqli_fetch_array($catrecord, MYSQLI_ASSOC)){																
		$Cat = $catinfo['category'];
		array_push($Catdata,$Cat);
	}
	
	//Retreive last bill id 	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	$record = mysqli_query($link,"SELECT * FROM `pbills` ORDER BY id DESC LIMIT 1");
	if(@mysqli_num_rows($record) > 0){ 
		while($billinfo = mysqli_fetch_array($record, MYSQLI_ASSOC)){	$billid = $billinfo['id'];	}
	} else { $billid = 0; }	
	
	$billrecord = mysqli_query($link,"SELECT * FROM `pbills`");
	$billnum = mysqli_num_rows($billrecord);
	$Totalbills = [];	
	$Billdata = [];
	$z = 0;				
	while($billinfo = mysqli_fetch_array($billrecord, MYSQLI_ASSOC)){																
		$id = $billinfo['id'];		
		$ext = $billinfo['ext'];		
		${'bill'.$z} = array('id' => $id,'ext' => $ext);	
		$z++;
	}
	
} else {
	$responseArray = array('id' => 'danger', 'message' => 'خطأ في الخادم');
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	$responseArray = array('id' => 'success');
	if ( isset($supnum) && $supnum > 0 ){	
		for($a=0;$a<$supnum;$a++){ 
			array_push($Totalsuppliers,${'supplier'.$a}); 
		}	
		$responseArray['supnum']=$supnum;
		array_push($responseArray,$Totalsuppliers);		
	} else {
		$responseArray['supnum']='0';
	}
	if ( isset($catnum) && $catnum > 0 ){	
		array_push($responseArray,$Catdata);
		$responseArray['catnum']=$catnum;
		// array_push($responseArray,$Totalsuppliers);		
	} else {
		$responseArray['catnum']='0';
	}
	if ( isset($billnum) && $billnum > 0 ){	
		for($a=0;$a<$billnum;$a++){ 
			array_push($Totalbills,${'bill'.$a}); 
		}	
		$responseArray['billnum']=$billnum;
		array_push($responseArray,$Totalbills);		
	} else {
		$responseArray['billnum']='0';
	}
	
	$responseArray['lastbillid']=$billid;
	
	$encoded = json_encode($responseArray);		
	header('Content-Type: application/json');	
	echo $encoded;
} else {    
	echo $responseArray['message'];		
}		// else just display the message
?>