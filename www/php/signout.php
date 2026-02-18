<?php header('Content-type: application/json');	header('Content-Type: text/html; charset=utf-8'); /*Arabic*/	
require 'inc/functions.php'; 
session_start();

// remove all session variables
session_unset(); 

// destroy the session
session_destroy(); 

//sleep for 3 seconds
// sleep(3);
if ( !empty($_SESSION) ) {
	$responseArray = array('id' => 'danger', 'message' => $_SESSION);
} else {
	$responseArray = array('id' => 'success', 'message' => 'signout succeded');
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	$encoded = json_encode($responseArray);		header('Content-Type: application/json');	echo $encoded;
} else {    echo $responseArray['message'];		}		// else just display the message

?>