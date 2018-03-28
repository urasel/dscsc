<?php
//include required files
require_once("includes/session.php");
require_once("includes/db_config.php");
require_once("includes/db_connect.php");
require_once("libraries/user.class.php");
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
redirect("login.php");
	exit;
}

global $dbObj;
$cur_user_group_id = $usr[0]->group_id;

//Chek if this user is valid for this file
if($cur_user_group_id != '4'){			
	header("Location: dashboard.php");		//Only SI has the permission of downloading reprots
}

$catType = $_SESSION['report_type'];

if($catType == 'exercise'){
	$rows_head = $_SESSION['exercise'][0];
	$rows = $_SESSION['exercise'][1];
	$rows_foot = $_SESSION['exercise'][2];
	$data = "" ;
	$sep = "\t"; //tabbed character
	
	//for head
	$total_rows = count($rows_head);
	if($total_rows > 0){
		for($i = 0; $i <= $total_rows; $i++){
			$data .= trim($rows_head[$i])."\n";
		}
	}//if
	
	if(count($rows)>0){
		//Find out total fields dynamically
		$columns = count($fields);
	
		for($k=0; $k < count( $rows ); $k++) {
			$row = $rows[$k];
			$line = '';
			
			$f_counter = 0;
			foreach ($row as $value){
				$value = str_replace('"', '""', $value);
				$line .= '"' . $value . '"' . "\t";
				$f_counter++;
			}//if
			$data .= trim($line)."\n";
		}//if
		
		$data = str_replace("\r","",$data);
		
		if (count( $rows ) == 0) {
		  $data .= "\n(0) Records Found!\n";
		}//if
	}//if
	
	//for foot
	$data .= 'Average Mks:'.trim($rows_foot)."\n";
	
	
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=exercise_report.xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Lacation: excel.htm?id=yes");
	print $data ;
	die();
}else if($catType == 'term'){
	$rows_head = $_SESSION['term'][0];
	$rows = $_SESSION['term'][1];
	$rows_foot = $_SESSION['term'][2];
	$data = "" ;
	$sep = "\t"; //tabbed character
	 
	//for head
	if(count($rows_head)>0){
		$data .= trim($rows_head[0])."\n";
		$data .= trim($rows_head[1])."\n";
		$data .= trim($rows_head[2])."\n";
	}
	
	if(count($rows)>0){
		  
		//Find out total fields dynamically
		//$fields = (array_keys($rows[0]));
		$columns = count($fields);

		for($k=0; $k < count( $rows ); $k++) {
			$row = $rows[$k];
			$line = '';
			
			$f_counter = 0;
			foreach ($row as $value){
				
				$value = str_replace('"', '""', $value);
				$line .= '"' . $value . '"' . "\t";
				$f_counter++;
			}
			$data .= trim($line)."\n";
		}
		
		$data = str_replace("\r","",$data);
		
		if (count( $rows ) == 0) {
		  $data .= "\n(0) Records Found!\n";
		}
		
	}
	
	//for foot
	$data .= 'Average Mks:'.trim($rows_foot)."\n";
 
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=term_report.xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Lacation: excel.htm?id=yes");
	print $data ;
	die();
}else if($catType == 'course'){
	$rows_head = $_SESSION['course'][0];
	$rows = $_SESSION['course'][1];
	$data = "" ;
	$sep = "\t"; //tabbed character
	
	//for head
	if(count($rows_head)>0){
		$data .= trim($rows_head[0])."\n";
		$data .= trim($rows_head[1])."\n";
	}
	
	if(count($rows)>0){
		//Find out total fields dynamically
		//$fields = (array_keys($rows[0]));
		$columns = count($fields);

		for($k=0; $k < count( $rows ); $k++) {
			$row = $rows[$k];
			$line = '';
			
			$f_counter = 0;
			foreach ($row as $value){
				$value = str_replace('"', '""', $value);
				$line .= '"' . $value . '"' . "\t";
				$f_counter++;
			}
			$data .= trim($line)."\n";
		}
		
		$data = str_replace("\r","",$data);
		
		if (count( $rows ) == 0) {
		  $data .= "\n(0) Records Found!\n";
		}
		
	}else{
		$data = "\n(0) Records Found!\n";
	}
 
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=course_report.xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Lacation: excel.htm?id=yes");
	print $data ;
	die();
}else if($catType == 'progress'){
	$rows_head = $_SESSION['progress'][0];
	$rows = $_SESSION['progress'][1];
	$data = "" ;
	$sep = "\t"; //tabbed character
	
	//for head
	if(count($rows_head)>0){
		$data .= trim($rows_head[0])."\n";
		$data .= trim($rows_head[1])."\n";
		$data .= trim($rows_head[2])."\n";
		$data .= trim($rows_head[3])."\n";
	}
	
	if(count($rows)>0){
		//Find out total fields dynamically
		//$fields = (array_keys($rows[0]));
		$columns = count($fields);

		for($k=0; $k < count( $rows ); $k++) {
			$row = $rows[$k];
			$line = '';
			
			$f_counter = 0;
			foreach ($row as $value){
				$value = str_replace('"', '""', $value);
				$line .= '"' . $value . '"' . "\t";
				$f_counter++;
			}
			$data .= trim($line)."\n";
		}
		
		$data = str_replace("\r","",$data);
		
		if (count( $rows ) == 0) {
		  $data .= "\n(0) Records Found!\n";
		}
		
	}else{
		$data = "\n(0) Records Found!\n";
	}
 
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=performance_report.xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	header("Lacation: excel.htm?id=yes");
	print $data ;
	die();
}
?>	  
