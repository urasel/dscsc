<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'CI Moderation Marks';
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
		
		//Find Currently Active Course/Courses which is assigned to this SI from si_to_course
		$sql = "select stc.course_id from ".DB_PREFIX."si_to_course as stc,  ".DB_PREFIX."course as crs where stc.si_id = ".$cur_user_id." AND stc.wing_id = '".$cur_user_wing_id."' AND crs.status = '0' AND crs.id = stc.course_id ORDER BY crs.name";
		$courseArray = $dbObj->selectDataObj($sql);
		
		if(!empty($courseArray)){
			//Build Course Selection Array
			$sql = "select id, name from ".DB_PREFIX."course where wing_id = '".$cur_user_wing_id."' AND (";
									foreach($courseArray as $course){
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
			$courseList_opt = formSelectElement($courseId, $course_id, 'course_id', 'onchange = processFunction("ci_mod_mark_term")');
			
			//Build Term List Array	
			$query = "select id, name from ".DB_PREFIX."term WHERE course_id = '".$course_id."' AND status = '0' order by name asc";
			$termArr = $dbObj->selectDataObj($query);
			$termId = array();
			$termId[0] = SELECT_TERM_OPT;
			if(!empty($termArr)){			
				foreach($termArr as $item){
					$termId[$item->id] = $item->name;
				}	
			}			
			$termList_opt = formSelectElement($termId, $term_id, 'term_id', 'onchange = processFunction("ci_mod_mark_exr")');
					
			//Build Exercise List Array	
			$query = "select exr.id, exr.name from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$course->course_id."' AND ett.term_id = '".$term->term_id."' AND exr.id = ett.exercise_id order by exr.name asc";
			$exrArr = $dbObj->selectDataObj($query);
	
			$exrId = array();
			$exrId[0] = SELECT_EXERCISE_OPT;
			if(!empty($exrArr)){			
				foreach($exrArr as $item){
					$exrId[$item->id] = $item->name;
				}	
			}			
			$exerciseList_opt = formSelectElement($exrId, $exercise_id, 'exercise_id', ' onchange = processFunction("assign_ci_mark")');
		}//if not empty courseArray
		
		$action = 'view';
		$msg = $_REQUEST['msg'];
		break;
		
	case 'save':	
		
		$course_id = $_POST['course_id'];
		$exercise_id = $_POST['exercise_id'];
		$exr_nam = getNameById('exercise', $exercise_id);
		$term_id = $_POST['term_id'];
		$student_id = $_POST['student_id'];
		$comment = $_POST['comment'];
		$locked_datetime = date('Y-m-d H:i:s');
		
		//Find Exercise Info for inserting weight in marking table
		$sql = "select * FROM ".DB_PREFIX."exercise where id = '".$exercise_id."'";
		$exerciseListArray = $dbObj->selectDataObj($sql);
		$exerciseList = $exerciseListArray[0];
		$exercise_weight = $exerciseList->weight;
		
		//Check for not inserting any blank entry
		if($course_id == "" || $term_id == "" || $exercise_id == ""){
			$msg = PARAM_MISSING;
			$url = 'ci_marking.php?action=view&msg='.$msg;
			redirect($url);
		}
		
		if($_POST['Submit'] == 'Lock' || $_POST['Submit'] == 'Request to Unlock'){
			$ci_status = '1';
		}else{
			$ci_status = '0';
		}
		
		if($_POST['Submit'] == 'Request to Unlock'){
			$lock_status = '2';
		}else if($_POST['Submit'] == 'Lock'){
			$lock_status = '1';
		}else{
			$lock_status = '0';
		}
		
		if($_POST['Submit'] == 'Lock'){
			$query = "select * from ".DB_PREFIX."ci_marking_lock WHERE exercise_id = '".$exercise_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."'";
			$forwarded = $dbObj->selectDataObj($query);
			if(empty($forwarded)){
				$fields = array('wing_id' => $cur_user_wing_id,
							'course_id' => $course_id,
							'term_id' => $term_id,
							'exercise_id' => $exercise_id,
							'locked_by' => $cur_user_id,
							'locked_datetime' => $locked_datetime,
							'status' => $lock_status
							);
				$inserted = $dbObj->insertTableData("ci_marking_lock", $fields);	
			}else{
				$fields = array(
						'locked_by' => $cur_user_id,
						'locked_datetime' => $locked_datetime,
						'status' => $lock_status
						);
				$where = " course_id = '".$course_id."' AND exercise_id = '".$exercise_id."' AND term_id = '".$term_id."'";
				$update_status = $dbObj->updateTableData("ci_marking_lock", $fields, $where);	
			}
		}else if($_POST['Submit'] == 'Request to Unlock'){
			$fields = array(
						'comment' => $comment,
						'wing_id' => $cur_user_wing_id,
						'locked_by' => $cur_user_id,
						'status' => $lock_status
						);
			$where = " course_id = '".$course_id."' AND exercise_id = '".$exercise_id."' AND term_id = '".$term_id."'";
			$update_status = $dbObj->updateTableData("ci_marking_lock", $fields, $where);	
		}//else if
		
		if(($_POST['Submit'] == 'Lock') || ($_POST['Submit'] == 'Save')){
			//Find Student info
			$query = "select * from ".DB_PREFIX."student WHERE wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."'";
			$studentArr = $dbObj->selectDataObj($query);
			
			$number = array();
			foreach($studentArr as $student){
				$key = $student->id;
				$val = "number_".$student->id;				//number_11
				$number[$key] = $_POST[$val];				//$_POST['number_11']
			}//foreach 
		
			if(!empty($student_id)){
				foreach($student_id as $student){
					foreach($number as $key => $value){
						if($key == $student){
							$student_number = trim($value);
							$sign = substr($student_number, 0, 1);
						}//if
						
						$weight_number = (($student_number)*$exercise_weight)/100;
						$weight_number = view_number_two($weight_number);
					}//foreach
					$sql = "UPDATE ".DB_PREFIX."marking SET ci_sign = '".$sign."', ci_mod_marking = '".$student_number."', ci_student_weight = '".$weight_number."', si_id = '".$cur_user_id."', ci_status = '".$ci_status."' WHERE student_id = '".$student."' AND exercise_id = '".$exercise_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."'";
					$inserted = $dbObj->executeData($sql);
				}//foreach	
			}//if
			
			$inserted = $dbObj->executeData($sql);		
			if(!$inserted){
				$msg = ADD_FAILED;		
				$url = 'ci_marking.php?action=view&msg='.$msg;
				redirect($url);
			}//if
		}//if
	
		
		if($_POST['Submit'] == 'Save'){
			$msg = $exr_nam->name.SUCCESSFULLY_SAVED;
		}else if($_POST['Submit'] == 'Lock'){
			$msg = $exr_nam->name.SUCCESSFULLY_LOCKED;
		}else if($_POST['Submit'] == 'Request to Unlock'){
			$msg = REQUEST_SENT.$exr_nam->name;
		}
		$url = 'ci_marking.php?action=view&msg='.$msg;
		redirect($url);

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
				<h1><?php echo CI_MOD_MARK; ?></h1>
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
			if(empty($courseArray)){
	?>
		<table cellpadding="0" cellspacing="0" border="0" width="100%" class="module_content">
			<tr>
				<td height="30" colspan="2">
					<?php echo ERROR_MSG; ?>
				</td>
			</tr>
		</table>
	<?php }else{ ?>
		<div id="exercise_info_container">
			<div class="ds_input">
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
					<tr>
						<td height="30" width="30%">
							<?php echo TERM; ?>:
						</td>
						<td width="70%">
							<div id="loaderContainer"></div>
							<div id="term_display">
								<?php echo $termList_opt; ?>
								<span class="required_field"> *</span>
							</div>
						</td>
					</tr>
					<tr>
						<td height="30" width="30%">
							<?php echo EXERCISE; ?>:
						</td>
						<td width="70%">
							<div id="loaderContainer"></div>
							<div id="exercise_display">
								<?php echo $exerciseList_opt; ?>
								<span class="required_field"> *</span>
							</div>
						</td>
					</tr>
				</table>
			</div>
			
			<div class="ds_output">
				<div id="loaderContainer"></div>
				<div id="exr_info"></div>
			</div>
		</div>
						
		<div class="ds_marking">
			<div id="loaderContainerForStd"></div>
			<br style="clear:both"  />
			<div id="ci_marking_display"></div>
		</div>				
						
<?php }//if not empty courseArray
	}//view		
?>
</div>
			
<?php
require_once("includes/footer.php");
?>