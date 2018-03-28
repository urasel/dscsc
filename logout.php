<?php
require_once("includes/header.php");
session_destroy();
header("Location:index.php");	
exit;		
//require_once("includes/footer.php");
?>
