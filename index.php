<?php
require_once("includes/header.php");

//check for logged in
$usr = $user->getUser();
print_r($usr);
if(empty($usr)){
	header("Location:login.php");
	exit;
}else{
	header("Location:dashboard.php");
	exit;
}


require_once("includes/templates.php");
require_once("templates/top_menu.php");
require_once("templates/left_menu.php");
?>
<div id="right_column">
	<div id="cpanel">
				
	</div>
</div>
			
<?php
require_once("includes/footer.php");
?>
