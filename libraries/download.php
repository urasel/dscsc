<?php
/*
Find Current User
*/
require_once("../includes/session.php");
require_once("../includes/db_config.php");
require_once("../includes/db_connect.php");
require_once("../libraries/user.class.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	redirect("login.php");
	exit;
}
$user_id = $usr[0]->id;

/*
|-----------------
| Chip Error Manipulation
|------------------
*/

error_reporting(-1);

/*
|-----------------
| Chip Constant Manipulation
|------------------
*/

//define( "CHIP_DEMO_FSROOT",__DIR__ . "/" );
define( "CHIP_DEMO_FSROOT",	dirname(__FILE__) . "/" );
/*
|-----------------
| Chip Download Class
|------------------
*/

require_once("class.chip_download.php");

/*
|-----------------
| Class Instance
|------------------
*/

$download_path = CHIP_DEMO_FSROOT . "../attach_file/";
/*echo 'File does not exists!';
echo '<br /><a href="javascript:window.history.back(1)">Go Back</a>';
exit;*/	


$file = $_REQUEST['file'];

$args = array(
		'download_path'		=>	$download_path,
		'file'				=>	$file,		
		'extension_check'	=>	TRUE,
		'referrer_check'	=>	FALSE,
		'referrer'			=>	NULL,
		);
$download = new chip_download( $args );

/*
|-----------------
| Pre Download Hook
|------------------
*/

$download_hook = $download->get_download_hook();
//$download->chip_print($download_hook);
//exit;

/*
|-----------------
| Download
|------------------
*/

if( $download_hook['download'] == TRUE ) {

	/* You can write your logic before proceeding to download */
	
	/* Let's download file */
	$download->get_download();

}

?>