<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}

$pdf_invoice_name = $_SESSION['pdf'];
download_report($pdf_invoice_name, $usr[0]->id);
?>