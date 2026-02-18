<?php
//header('Access-Control-Allow-Origin:*'); 
//header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS, REQUEST');
//header('Content-Type: application/json; charset=utf-8');
//header('Access-Control-Max-Age: 3628800');
//define('HTTP_SERVER', 'http://www.payond.net');
// DB informations
$DB_SERVER             = 'localhost';  // DB host
$DB_USERNAME           = 'matrixall';  // DB username
$DB_PASSWORD           = 'M@trix7230';  // DB password
$DB_DATABASE           = 'mrah';  // DB name

// Site settings (don't edit)
// $ROW_SITE = @mysqli_fetch_array(mysqli_query("SELECT * FROM `site_setting` WHERE `id`='1'"));

// Connenction Query
$link = mysqli_connect($DB_SERVER,$DB_USERNAME,$DB_PASSWORD,$DB_DATABASE);

// Fetch Content as UTF-8 for Arabic
$AR= 'SET CHARACTER SET utf8'; 
mysqli_query($link,$AR);
// Insert Content as UTF-8 for Arabic
mysqli_set_charset($link,"utf8");

// Check Connection for errors
if (!$link) {
    echo "Error" . PHP_EOL;
    echo "Debugging error Number: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error Text: " . mysqli_connect_error() . PHP_EOL;
    exit;
} 

//echo "Success" . PHP_EOL;
//echo "</br>" . PHP_EOL;
//echo "Host information: " . mysqli_get_host_info($link) . PHP_EOL;
//echo "</br>" . PHP_EOL;

//mysqli_close($link);
?>