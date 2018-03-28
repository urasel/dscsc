<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Assign Impressions Marks';
$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$action = $_REQUEST['action'];
$msg = '';

//Chek if this user is valid for this file
if($cur_user_group_id != '4'){
	header("Location: dashboard.php");	
}

switch($action){
	case 'view':	
	default:
		
		//Find Currently Active Course which is assigned to this SI
		$sql = "select stc.course_id from ".DB_PREFIX."si_to_course as stc, ".DB_PREFIX."course as crs WHERE stc.wing_id = '".$cur_user_wing_id."' AND stc.si_id = '".$cur_user_id."' AND crs.status = '0' AND crs.id = stc.course_id";
		$activeCourse = $dbObj->selectDataObj($sql);
		
		if(!empty($activeCourse)){
			//Build Course Selection Array
			$sql = "select id, name from ".DB_PREFIX."course where wing_id = '".$cur_user_wing_id."' AND (";
									foreach($activeCourse as $course){
										$sql .= " id = '".$course->course_id."' || ";						
									}
			$sql = rtrim($sql, "|| ");
			$sql = $sql.');';
			$course_list = $dbObj->selectDataObj($sql);
			
			$courseId = array();
			$courseId[0] = SELECT_COURSE_OPT;
			if(!empty($course_list)){			
				foreach($course_list as $item){
					$courseId[$item->id] = $item->name;
				}	
			}			
			$courseList_opt = formSelectElement($courseId, $course_id, 'course_id', 'onchange = processFunction("term_impr_mark_assign")');
		}//if not empty activeCourse
		
		$action = 'view';
		$msg = $_REQUEST['msg'];
		break;
		
	case 'save':	
		
		$course_id = $_POST['course_id'];
		$crs_name = getNameById('course', $course_id);
		$student_id = $_POST['student_id'];
		$comment = $_POST['comment'];
		
		//Find Student info
		$query = "select * from ".DB_PREFIX."student WHERE wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."'";
		$studentArr = $dbObj->selectDataObj($query);
		
		$number = array();
		$total_weight = array();
		foreach($studentArr as $student){
			$key = $student->id;
			$val = "number_".$student->id;				//number_11
			$number[$key] = $_POST[$val];				//$_POST['number_11']
			$total_val = "total_number".$student->id;	
			$total_weight[$key] = $_POST[$total_val];
		}//foreach
		
		$sql = "select * from ".DB_PREFIX."course WHERE id = '".$course_id."'";
		$CourseInfoArr = $dbObj->selectDataObj($sql);
		$courseInfo = $CourseInfoArr[0];
		$si_impr_mark = $courseInfo->si_impr_mark;
		
		if($_POST['Submit'] != 'Request to Unlock'){	
			//Delete All data from ds_to_course Table of selected Syndicate/Term if no ds is selected
			$sql = 'DELETE from '.DB_PREFIX.'si_impression_marking where course_id='.$course_id;
			$delete = $dbObj->executeData($sql);
		}

		if($_POST['Submit'] == 'Save'){
			$status = '0';
		}else if($_POST['Submit'] == 'Lock'){
			$status = '1';
		}else if($_POST['Submit'] == 'Request to Unlock'){
			$status = '2';
		}
		
		if($_POST['Submit'] == 'Lock'){
			$query = "select * from ".DB_PREFIX."si_impr_marking_lock WHERE course_id = '".$course_id."'";
			$locked = $dbObj->selectDataObj($query);
			
			if(empty($locked)){
				$fields = array('course_id' => $course_id,
							'status' => $status,
							'comment' => $comment,
							'si_id' => $cur_user_id
							);
				$inserted = $dbObj->insertTableData("si_impr_marking_lock", $fields);	
			}else{
				$fields = array(
						'status' => $status,
						'si_id' => $cur_user_id
						);
				$where = " course_id = '".$course_id."'";
				
				$update_status = $dbObj->updateTableData("si_impr_marking_lock", $fields, $where);	
			}
			
		}else if($_POST['Submit'] == 'Request to Unlock'){
			$fields = array(
						'comment' => $comment,
						'status' => $status
						);
			$where = " course_id = '".$course_id."'";
			
			$update_status = $dbObj->updateTableData("si_impr_marking_lock", $fields, $where);	
		}//else
		
		if($_POST['Submit'] == 'Save' || $_POST['Submit'] == 'Lock'){	
				//Now Save into database as a new entry
				if(!empty($student_id)){
					$sql = 'INSERT INTO '.DB_PREFIX.'si_impression_marking (`course_id`,`si_id`,`si_impr_mark`,`si_impr_weight`,`student_id`,`total_weight`) VALUES';
							foreach($student_id as $student){
								foreach($number as $key => $value){
									if($key == $student){
										$student_number = $value;
									}//if
								}//foreach
								
								foreach($total_weight as $key => $value){
									if($key == $student){
										$student_weight = $value;
									}//if
								}//foreach
								
								//now convert the si_impression_percent_mark into impression_weight
								$converted_mark = ($si_impr_mark*$student_number)/100;
								$converted_mark = view_number_two($converted_mark);
								$sql .= "('".$course_id."','".$cur_user_id."','".$student_number."','".$converted_mark."','".$student."','".$student_weight."'),";						
							}//foreach
								$sql = rtrim($sql, ",");
								$sql = $sql.';';
				}//if
		}//if
		
		$inserted = $dbObj->executeData($sql);		
		if(!$inserted){
			$msg = ADD_FAILED;		
			$action = 'view';
		}else{
			$msg = $crs_name->name.SUCCESSFULLY_SAVED;
			$url = 'si_impr_marking.php?action=view&msg='.$msg;
			redirect($url);
		}//if inserted
	break;

}//switch


require_once("includes/templates.php");
require_once("templates/top_menu.php");
require_once("templates/left_menu.php");
?>

<div id="right_column">
	<?php
		if(!empty($msg)){
	?>
		<table id="system_message">
			<tr>
				<td>
					<?php echo $msg; ?>
				</td>
			</tr>
		</table>
	<?php
		}
	?>
	<table width="100%" cellspacing="0" cellpadding="0" border="0" class="module_header">
		<tr>
			<td>
				<h1><?php echo ASSIGN_IMPR_MARK; ?></h1>
			</td>	
			<td class="usr_info">
				<?php 
				$group = getNameById('user_group', $cur_user_group_id);
				$usrName = $usr[0]->username;
				$grpName = $group->name;
				echo welcomeMsg($usrName, $grpName);
				?>
			</td>			
		</tr>
	</table>
	<?php
	if($action=="view"){
		if(empty($activeCourse)){
	?>
		<table cellpadding="0" cellspacing="0" border="0" width="100%" class="module_content">
			<tr>
				<td height="30" colspan="2">
					<?php echo ERROR_MSG; ?>
				</td>
			</tr>
		</table>
	<?php }else{ ?>
		<table cellpadding="0" cellspacing="0" border="0" width="250" class="module_content">
			<tr>
				<td height="30" width="30%">
					<?php echo COURSE; ?>:
				</td>
				<td width="70%">
					<?php echo $courseList_opt; ?>
					<span class="required_field"> *</span>
				</td>
			</tr>
		</table>
		
		<div id="loaderContainer"></div>		
		<div id="si_impres_mark" style="height:auto; width:700px; overflow-x:scroll; display:none;">
			<br style="clear:both"  />
			<div id="si_term_marking_display"></div>
		</div>
	<?php	}//if not empty activeCourse
		}//else - action = view
	?>
</div>
			
<?php require_once("includes/footer.php"); ?>
