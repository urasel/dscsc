<?php
$cur_user_id = $usr[0]->id;

//Find Name by ID
function getNameById($table, $id){
	global  $dbObj;
	$sql = "select * from ".DB_PREFIX.$table." where id='".$id."'";	
	$result = $dbObj->selectDataObj($sql);
	return $result[0];
}

//Welcome Message
function welcomeMsg($usrName, $grpName){
	$message = WELCOME.$usrName.' ('.$grpName.')';
	return $message;
}

//Redirect to a URL
function redirect($url = ''){
	echo '<script type="text/javascript">
	window.location = "'.$url.'"
	</script>';
	exit;
}

//This function find out the Grade
function findGrade($gradeNumber){
	
	$gradeNumber=number_format($gradeNumber,2);
	
	if($gradeNumber == 0 || ($gradeNumber > 0 && $gradeNumber < 39.99)){
		$grade = 'F';
	}else if($gradeNumber >= 40 && $gradeNumber <=44.99){
		$grade = 'C';
	}else if($gradeNumber >= 45 && $gradeNumber <= 49.99){
		$grade = 'B-';
	}else if($gradeNumber >= 50 && $gradeNumber <= 62.99){
		$grade = 'B';
	}else if($gradeNumber >= 63 && $gradeNumber <= 65.99){
		$grade = 'B+';
	}else if($gradeNumber >= 66 && $gradeNumber <=74.99){
		$grade = 'A';
	}else if($gradeNumber >= 75 && $gradeNumber <=100){
		$grade = 'A+';
	}
	
	return $grade;
			
}//function

function pagination($total_rows, $per_page ,$page = 1, $param='', $url = '?'){       
		$total = $total_rows;
        $adjacents = "2"; 

    	$page = ($page == 0 ? 1 : $page);  
    	$start = ($page - 1) * $per_page;								
		
    	$prev = $page - 1;							
    	$next = $page + 1;
        $lastpage = ceil($total/$per_page);
    	$lpm1 = $lastpage - 1;
    	
    	$pagination = "";
    	if($lastpage > 1)
    	{	
    		$pagination .= "<ul class='pagination'>";
                    $pagination .= "<li class='details'>Page $page of $lastpage</li>";
    		if ($lastpage < 7 + ($adjacents * 2))
    		{	
    			for ($counter = 1; $counter <= $lastpage; $counter++)
    			{
    				if ($counter == $page)
    					$pagination.= "<li><a class='current'>$counter</a></li>";
    				else
    					$pagination.= "<li><a href='{$url}page=".$counter.$param."'>".$counter."</a></li>";					
    			}
    		}
    		elseif($lastpage > 5 + ($adjacents * 2))
    		{
    			if($page < 1 + ($adjacents * 2))		
    			{
    				for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
    				{
    					if ($counter == $page)
    						$pagination.= "<li><a class='current'>$counter</a></li>";
    					else
    						$pagination.= "<li><a href='{$url}page=".$counter.$param."'>".$counter."</a></li>";					
    				}
    				$pagination.= "<li class='dot'>...</li>";
    				$pagination.= "<li><a href='{$url}page=$lpm1".$param."'>$lpm1</a></li>";
    				$pagination.= "<li><a href='{$url}page=$lastpage".$param."'>$lastpage</a></li>";		
    			}
    			elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
    			{
    				$pagination.= "<li><a href='{$url}page=1".$param."'>1</a></li>";
    				$pagination.= "<li><a href='{$url}page=2".$param."'>2</a></li>";
    				$pagination.= "<li class='dot'>...</li>";
    				for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
    				{
    					if ($counter == $page)
    						$pagination.= "<li><a class='current'>$counter</a></li>";
    					else
    						$pagination.= "<li><a href='{$url}page=".$counter.$param."'>".$counter."</a></li>";					
    				}
    				$pagination.= "<li class='dot'>..</li>";
    				$pagination.= "<li><a href='{$url}page=$lpm1".$param."'>$lpm1</a></li>";
    				$pagination.= "<li><a href='{$url}page=$lastpage".$param."'>$lastpage</a></li>";		
    			}
    			else
    			{
    				$pagination.= "<li><a href='{$url}page=1".$param."'>1</a></li>";
    				$pagination.= "<li><a href='{$url}page=2".$param."'>2</a></li>";
    				$pagination.= "<li class='dot'>..</li>";
    				for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
    				{
    					if ($counter == $page)
    						$pagination.= "<li><a class='current'>$counter</a></li>";
    					else
    						$pagination.= "<li><a href='{$url}page=".$counter.$param."'>".$counter."</a></li>";					
    				}
    			}
    		}
    		
    		if ($page < $counter - 1){ 
    			$pagination.= "<li><a href='{$url}page=$next".$param."'>Next</a></li>";
                $pagination.= "<li><a href='{$url}page=$lastpage".$param."'>Last</a></li>";
    		}else{
    			$pagination.= "<li><a class='current'>Next</a></li>";
                $pagination.= "<li><a class='current'>Last</a></li>";
            }
    		$pagination.= "</ul>\n";		
    	}
    
    
        return $pagination;
}

//This funcction uploads multiple files
//Input: array of uploadable files
//Output: array of file names 
function upload_file($files, $path, $user_id){
	$uploaded = array();
	$counter = 0;
	$err_counter = 0;
	
	if(!empty($files)){
		foreach($files as $item){
			if(!empty($item['name'])){
				$temp_name = $item['name'];
				$temp_arr = explode(".", $temp_name);
				$name = $temp_arr[0];
				$ext = $temp_arr[sizeof($temp_arr)-1];
				$size = $item['size'];
				
				$file = $name.'_'.$user_id.'_'.date('YmdHis').'.'.$ext;
				
				//form exact path with name
				$upload_file = $path.$file;
				
				//if(move_uploaded_file($item['tmp_name'],$upload_file)){
				//$uploaded[$counter] = $file;
				$uploaded['file'][$counter]['tmp_name'] = $item['tmp_name'];
				$uploaded['file'][$counter]['uploadable'] = $upload_file;
				$uploaded['file'][$counter]['upfile'] = $file;
				
				
				//Check for accepted extentions and size allowed: 256KB
				if(($ext == 'jpg' ||$ext == 'png' ||$ext == 'gif' || $ext == 'jpeg' ||
					$ext == 'JPG' ||$ext == 'PNG' ||$ext == 'GIF' || $ext == 'JPEG') && ($size < 262145)){
				}else{
					if(!($ext == 'jpg' ||$ext == 'png' ||$ext == 'gif' || $ext == 'jpeg' ||
							$ext == 'JPG' ||$ext == 'PNG' ||$ext == 'GIF' || $ext == 'JPEG') && ($size > 262145)){
						$msg = "Attached file format is not supported and File size is bigger than 256 KB";
					}else if(!($ext == 'jpg' ||$ext == 'png' ||$ext == 'gif' || $ext == 'jpeg' ||
								$ext == 'JPG' ||$ext == 'PNG' ||$ext == 'GIF' || $ext == 'JPEG') && ($size < 262145)){
						$msg = "Attached file format is not supported";
					}else{
						$msg = "File size is bigger than 256 KB";
					}
					
					//Track Error
					$uploaded['error'][$counter] = $msg;
					$err_counter++;
				}//else
				
				$counter++;
			}//foreach	
		}//if - name is not empty
		
		//Now Upload the Files if no error found
		$up_counter = 0;
		if($err_counter == 0 && !empty($uploaded['file'])){
			foreach($uploaded['file'] as $ufile){
				if(move_uploaded_file($ufile['tmp_name'],$ufile['uploadable'])){
					$uploaded['uploaded'][$up_counter] = $ufile['upfile'];					
				}
				$up_counter++;
			}
		}
		$uploaded['error_counter'] = $err_counter;
	}//if	
	//exit;
	return $uploaded;
}//function


//Receive Date of format: 2011-06-28
//Return Date as of format: 28 June, 2011
function dateConvertion($date='0000-00-00'){
	if(!empty($date)){
		$datePortion = explode("-", $date);	
									
		$timeStamp = mktime(0,0,0, $datePortion[1] , $datePortion[2], $datePortion[0]);
		$newDate = date("d F, Y", $timeStamp);
	
		return $newDate;
	}//if
}//function

//This function creates a select box with supplied info
function formSelectElement($options, $selected = '', $name = 'select_element', $param = ''){
	$str = '';
	$elem = '<select class="formSelectElement" name="'.$name.'" id="'.$name.'" '.$param.'>';
	//$elem .= 	'<option  value="">'.$first_opt.'</option>';
		
	if(!empty($options)){
		if(is_array($selected)){			
			foreach($options as $key => $val){
				if(in_array($key, $selected)){
					$str = 'selected="Selected"';
				}else{
					$str = '';
				}
				$elem1 = '<option '.$str.' value="'.$key.'">'.$val.'</option>';	
				$elem .= $elem1;		
			}//foreach	
		}else{		
			foreach($options as $key => $val){
				if($key == $selected){
					$str = 'selected="Selected"';
				}else{
					$str = '';
				}
				
				$elem1 = '<option '.$str.' value="'.$key.'">'.$val.'</option>';	
				$elem .= $elem1;		
			}//foreach	
		}//else
	}//if
	$elem .= '</select>';
		
	return $elem;
}

//View number in the format of --->>> XX.XX
function view_number($number){
	$num = $number;
	$num_type = gettype($num);
	
	if($num_type == double){
		if($num < 100){
			$num = number_format($num, 2, '.', '');
		}else{
			$num = number_format($num, 1, '.', '');
		}//if
	}//if
	
	return $num;
}

function view_number_two($number){
	$num = $number;
	$num = number_format($num, 2, '.', '');
	return $num;
}

//This function out the position of a student
function findPosition($numberArray, $percent_mark){
		/*echo '<pre>';
		print_r($numberArray);
		echo $percent_mark;*/
		if(!empty($numberArray)){
			$percent_mark = number_format($percent_mark,2,'.','');
			foreach($numberArray as $key){
				$key_total = number_format($key->total,2,'.','');
				if($percent_mark == $key_total){
					$position = $key->position;
				}
			}//foreach
		}else{
			$position = 0;
		}
		return $position;
}


//This function draw a line graph
function draw_line($arr,$title){

	include "libchart/classes/libchart.php";

	$chart = new LineChart();


    

	$dataSet = new XYDataSet();
	
	$arr['']=63;
	
	
	if(!empty($arr)){
		foreach($arr as $key=>$val){
			$dataSet->addPoint(new Point($key, $val));
		}//foreach
		
	
		
		
		$chart->setDataSet($dataSet);
		$chart->setTitle($title);
		$chart->render("graph/demo5.png");
	}
}//function 

//This function downloads reports in PDF format
function download_report($pdf_invoice_name, $cur_user_id){
	/*echo getcwd();
	echo $pdf_invoice_name;
	exit;*/

	// We'll be outputting a PDF
	header('Content-type: application/pdf');
	
	// It will be called downloaded.pdf
	header('Content-Disposition: attachment; filename="'.$cur_user_id.'_'.date('ymdhis').'.pdf"');
	
	// The PDF source is in original.pdf
	readfile($pdf_invoice_name);
}//format


//This function generate Security Key while creating user
function generateSecKey ($length = 8){

    // start with a blank password
    $password = "";

    // define possible characters - any character in this string can be
    // picked for use in the password, so if you want to put vowels back in
    // or add special characters such as exclamation marks, this is where
    // you should do it
    $possible = "123467890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNPQRTVWXYZ!@#$%^&*";

    // we refer to the length of $possible a few times, so let's grab it now
    $maxlength = strlen($possible);
  
    // check for length overflow and truncate if necessary
    if ($length > $maxlength) {
      $length = $maxlength;
    }
	
    // set up a counter for how many characters are in the password so far
    $i = 0; 
    
    // add random characters to $password until $length is reached
    while ($i < $length) { 

      // pick a random character from the possible ones
      $char = substr($possible, mt_rand(0, $maxlength-1), 1);
        
      // have we already used this character in $password?
      if (!strstr($password, $char)) { 
        // no, so it's OK to add it onto the end of whatever we've already got...
        $password .= $char;
        // ... and increase the counter by one
        $i++;
      }

    }

    // done!
    return $password;
}//function 


//Find user manual for different group
function user_manual($cur_user_group_id){
	if($cur_user_group_id == '1'){
		$target = 'sup_usr';
	}else if($cur_user_group_id == '2'){
		$target = 'wng_adm';
	}else if($cur_user_group_id == '3'){
		$target = 'wng_mng';
	}else if($cur_user_group_id == '4'){
		$target = 'mnl_psi';
	}else if($cur_user_group_id == '5'){
		$target = 'mn_ds';
	}
	
	$final_target = 'manuals/'.$target.'.pdf';
	
	return $final_target;
}//function
?>
