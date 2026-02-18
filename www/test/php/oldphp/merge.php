<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); require 'inc/functions.php'; 		


$record = mysqli_query($link,"SELECT * FROM `bcdb` LIMIT 100000 ");
if(@mysqli_num_rows($record) > 0){
	while($info = mysqli_fetch_array($record, MYSQLI_ASSOC)){
		$id = $info['id'];
		$barcode = $info['barcode'];
		$itemDescription = $info['itemDescription'];
		$brandName = $info['brandName'];
		$tradeName = $info['tradeName'];
		$storageTemperatureAr = $info['storageTemperatureAr'];
		$warnings = $info['warnings'];
		$ingredientsAr = $info['ingredientsAr'];
		$ingredientsEn = $info['ingredientsEn'];
		$itemWeight = $info['itemWeight'];
		$unitNameAr = $info['unitNameAr'];
		$companyName = $info['companyName'];
		$ageGroupAr = $info['ageGroupAr'];
		$shelfTime = $info['shelfTime'];
		
		$ins = mysqli_query($link,"INSERT INTO `nbcdb`( `id`,`barcode`,`itemDescription`,`brandName`,`tradeName`,`storageTemperatureAr`,`warnings`,`ingredientsAr`,`ingredientsEn`,`itemWeight`,`unitNameAr`,`companyName`,`ageGroupAr`,`shelfTime`,`storageTemperatureEn`,`shelfTimeEn`,`ageGroupEn`,`unitNameEn`,`hsCode`,`arCOO`,`arPackingType`,`referanceNumber`,`arStatus`,`enStatus`,`enPackingType`,`enCOO` )VALUES( NULL,'$barcode','$itemDescription','$brandName','$tradeName','$storageTemperatureAr','$warnings','$ingredientsAr','$ingredientsEn','$itemWeight','$unitNameAr','$companyName','$ageGroupAr','$shelfTime' ,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL )");

		if ( $ins ) {
			$delete = mysqli_query($link,"DELETE FROM bcdb WHERE id = $id ");
			echo "Success<br>";
		} else {
			// echo "Failed<br>";
			if (!$ins)
			{
			  echo '<br>Error ' . mysqli_error($link);
			}
		}

	} 
}

?>