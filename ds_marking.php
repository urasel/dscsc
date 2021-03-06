<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Assign Marks';
$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$action = $_REQUEST['action'];
//echo '<pre>';
//print_r($_REQUEST);exit;
$postSynType = $_REQUEST['SyndicateType'];
$msg = '';

//Chek if this user is valid for this file
if($cur_user_group_id != '5'){			
	header("Location: dashboard.php");		//Only DS can give DS Marks
}

switch($action){
	case 'view':	
	default:
		
		//Find Currently Active Course
		$sql = "SELECT id from ".DB_PREFIX."course WHERE wing_id = '".$cur_user_wing_id."' AND status = '0'";
		$activeCourseArr = $dbObj->selectDataObj($sql);
		$course_id = $activeCourseArr[0]->id;
		
		//Find Currently Active Term which is assigned to this DS
		$sql = "select id from ".DB_PREFIX."term WHERE wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."' AND status = '0'";
		$activeTermArr = $dbObj->selectDataObj($sql);
		$term_id = $activeTermArr[0]->id;
		
		//Find syndicate of the DS
		$sql = "select syndicate_id from ".DB_PREFIX."ds_to_course WHERE wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."' AND term_id = '".$term_id."' AND ds_id = '".$cur_user_id."' AND syndicatetype = '".$postSynType."'";
		$activeSynArr = $dbObj->selectDataObj($sql);
		
		$syndicate_id = $activeSynArr[0]->syndicate_id;
		
		if(empty($syndicate_id)){
			$sql = "select syndicate_id from ".DB_PREFIX."ds_to_course WHERE ds_id = '".$cur_user_id."'";
			$activeSynArr = $dbObj->selectDataObj($sql);
			$syndicate_id = $activeSynArr[0]->syndicate_id;
		}
		$syndicateQuery = "select id, syndicate_type from ".DB_PREFIX."syndicate WHERE id = '".$syndicate_id."'";
		$syndicate = $dbObj->selectDataObj($syndicateQuery);
		$syndicateType = $syndicate[0]->syndicate_type;
		/*
		$termquery = "select trm.id, trm.name from ".DB_PREFIX."term as trm, ".DB_PREFIX."ds_to_course as dtc where trm.id = dtc.term_id AND dtc.ds_id = '".$cur_user_id."' order by trm.name asc";
		$trmArr = $dbObj->selectDataObj($termquery);
		echo '<pre>';
		print_r($trmArr);
		*/
		
		$courseIdArr = '';
		$courseQuery = "select session from ".DB_PREFIX."course WHERE id = '".$course_id."'";
		$coursesSession = $dbObj->selectDataObj($courseQuery);
		$coursesSession = $coursesSession[0]->session;
		$courseArrQuery = "select id from ".DB_PREFIX."course WHERE session = '".$coursesSession."'";
		$coursesArr = $dbObj->selectDataObj($courseArrQuery);
		
		$armyCourse = "select crs.id from ".DB_PREFIX."course as crs WHERE crs.wing_id = 1 AND crs.status = '0'";
		$armyCourse = $dbObj->selectDataObj($armyCourse);
		//echo '<pre>';
		//print_r($armyCourse[0]->id);
		$armyCourseIdForJoinSyn = $armyCourse[0]->id;
		$termQuery = "select trm.id, trm.name from ".DB_PREFIX."term as trm WHERE trm.wing_id = 1 AND trm.course_id = '".$armyCourseIdForJoinSyn."' AND trm.status = '0'";
		
		
		$armyTerm = $dbObj->selectDataObj($termQuery);
		//print_r($armyTerm);
		$joinActiveTerm = $armyTerm[0]->id;
		
		
		
		
		foreach($coursesArr as $course){
			$courseIdArr[] = $course->id;
		}
		$courseIdArr = join(', ', $courseIdArr);
		
		
		$allactiveTermsQuery = "select trm.id, trm.name from ".DB_PREFIX."term as trm WHERE trm.course_id IN (".$courseIdArr.") AND trm.status = '0'";
		$allActiveTermsArr = $dbObj->selectDataObj($allactiveTermsQuery);
		
		foreach($allActiveTermsArr as $term){
			$alltermIdArr[] = $term->id;
		}
		$allTermIdArr = join(', ', $alltermIdArr);
		
		
		//$query = "select syn.id, syn.name from ".DB_PREFIX."syndicate as syn, ".DB_PREFIX."ds_to_course as dtc where syn.id = dtc.syndicate_id AND dtc.ds_id = '".$cur_user_id."' order by syn.name asc";
		$query = "select syn.id, syn.name from ".DB_PREFIX."syndicate as syn, ".DB_PREFIX."ds_to_course as dtc where syn.id = dtc.syndicate_id AND dtc.ds_id = '".$cur_user_id."' AND dtc.course_id IN (".$courseIdArr.") AND dtc.term_id IN (".$allTermIdArr.") order by syn.name asc";
		$synArr = $dbObj->selectDataObj($query);
		
		$synId = array();
		$synId[0] = SELECT_SYNDICATE_OPT;
		if(!empty($synArr)){			
			foreach($synArr as $item){
				$synId[$item->id] = $item->name;
			}	
		}			
		$synList_opt = formSelectElement($synId, 0, 'syndicate_id',' onchange = processFunction("syndicate_wise_exercise")');
				
		//Build Exercise List Array	
		if($syndicateType == 1){
			$query = "select exr.id, exr.name from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where ett.term_id = '".$term_id."'  AND exr.id = ett.exercise_id order by exr.name asc";
		
		}else{
			$query = "select exr.id, exr.name from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$course_id."' AND ett.term_id = '".$term_id."' AND exr.id = ett.exercise_id order by exr.name asc";
		
		}
		$exrArr = $dbObj->selectDataObj($query);
		
		
		
		//echo '<pre>';
		//print_r($exrArr);exit;
		$exrId = array();
		$exrId[0] = SELECT_EXERCISE_OPT;
		if(!empty($exrArr)){			
			foreach($exrArr as $item){
				$exrId[$item->id] = $item->name;
			}	
			
		}			
		$exerciseList_opt = formSelectElement($exrId, $exercise_id, 'exercise_id', ' onchange = processFunction("assign_ds_mark")');
		
		$action = 'view';
		$msg = $_REQUEST['msg'];
		break;
		
	case 'save':	
		//Find Currently Active Course
		$sql = "SELECT id from ".DB_PREFIX."course WHERE wing_id = '".$cur_user_wing_id."' AND status = '0'";
		$activeCourseArr = $dbObj->selectDataObj($sql);
		$course_id = $activeCourseArr[0]->id;
		
		//Find Currently Active Term which is assigned to this DS
		$sql = "select id from ".DB_PREFIX."term WHERE wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."' AND status = '0'";
		$activeTermArr = $dbObj->selectDataObj($sql);
		$term_id = $activeTermArr[0]->id;
		
		//Find syndicate of the DS
		$sql = "select syndicate_id from ".DB_PREFIX."ds_to_course WHERE wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."' AND term_id = '".$term_id."' AND ds_id = '".$cur_user_id."' AND syndicatetype = '".$postSynType."'";
		$activeSynArr = $dbObj->selectDataObj($sql);
		
		$syndicate_id = $activeSynArr[0]->syndicate_id;
		
		$course_id = $_POST['course_id'];
		$term_id = $term_id;
		$exercise_id = $_POST['exercise_id'];
		$exr_nam = getNameById('exercise', $exercise_id);
		$student_id = $_POST['student_id'];
		$locked_datetime = date('Y-m-d H:i:s');
		$comment = $_POST['comment'];
		
		//Check for not inserting any blank entry
		if($course_id == "" || $term_id == "" || $exercise_id == ""){
			$msg = PARAM_MISSING;
			$url = 'ds_marking.php?action=view&msg='.$msg;
			redirect($url);
		}
		
		//Find Exercise Info for inserting weight in marking table
		$sql = "select * FROM ".DB_PREFIX."exercise where id = '".$exercise_id."'";
		$exerciseListArray = $dbObj->selectDataObj($sql);
		$exerciseList = $exerciseListArray[0];
		
		$joinExercise = $exerciseList->join_course;
		if($joinExercise == 1 && $cur_user_wing_id == 2){
			$exercise_weight = $exerciseList->air_weight;
		}else if($joinExercise == 1 && $cur_user_wing_id == 3){
			$exercise_weight = $exerciseList->navy_weight;
		}else if($joinExercise == 1 && $cur_user_wing_id == 1){
			$exercise_weight = $exerciseList->weight;
		}else{
			$exercise_weight = $exerciseList->weight;
		}
		
		
		if($_POST['Submit'] == 'Forward' || $_POST['Submit'] == 'Request to Unlock'){
			$status = '1';
		}else{
			$status = '0';
		}
		
		if($_POST['Submit'] == 'Request to Unlock'){
			$lock_status = '2';
		}else if($_POST['Submit'] == 'Forward'){
			$lock_status = '1';
		}else{
			$lock_status = '0';
		}
		
		if($_POST['Submit'] == 'Forward'){
		
			$query = "select * from ".DB_PREFIX."marking_lock WHERE exercise_id = '".$exercise_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' AND syndicate_id = '".$syndicate_id."'";
			//echo $query;exit;
			$forwarded = $dbObj->selectDataObj($query);
			
			if(empty($forwarded)){
				$fields = array('course_id' => $activeCourseArr[0]->id,
							'term_id' => $term_id,
							'exercise_id' => $exercise_id,
							'syndicate_id' => $syndicate_id,
							'locked_by' => $cur_user_id,
							'locked_datetime' => $locked_datetime,
							'status' => $lock_status
							);
							
				$inserted = $dbObj->insertTableData("marking_lock", $fields);	
			}else{
				$fields = array(
						'locked_by' => $cur_user_id,
						'status' => $lock_status,
						'locked_datetime' => $locked_datetime
						);
				$where = " course_id = '".$course_id."' AND exercise_id = '".$exercise_id."' AND term_id = '".$term_id."' AND syndicate_id = '".$syndicate_id."'";
				
				$update_status = $dbObj->updateTableData("marking_lock", $fields, $where);	
			}
			
			
		}else if($_POST['Submit'] == 'Request to Unlock'){
			$fields = array(
						'comment' => $comment,
						'locked_by' => $cur_user_id,
						'status' => $lock_status
						);
			$where = " course_id = '".$activeCourseArr[0]->id."' AND exercise_id = '".$exercise_id."' AND term_id = '".$term_id."' AND syndicate_id = '".$syndicate_id."'";
			
			$update_status = $dbObj->updateTableData("marking_lock", $fields, $where);	
		}else if($_POST['Submit'] == 'Delete'){
			
			$where = "course_id='".$course_id."' AND term_id = '".$term_id."' AND exercise_id = '".$exercise_id."' AND syndicate_id = '".$syndicate_id."'";	
			$delete = $dbObj->deleteTableData("marking", $where);	
			
			if($delete){
				$msg = 'Marks have been deleted successfully';
			}else{
				$msg = 'Marks could not be delted';
			}
			
			$url = 'ds_marking.php?action=view&msg='.$msg;
			redirect($url);
			
		}//els eif
		
		if($_POST['Submit'] == 'Save'){
			//Find Student info
			
			$syndicate_id = $_POST['marking_syndicate_id'];
			$syndicateQuery = "select id, syndicate_type from ".DB_PREFIX."syndicate WHERE id = '".$syndicate_id."'";
			$syndicate = $dbObj->selectDataObj($syndicateQuery);
			$syndicateType = $syndicate[0]->syndicate_type;
			if($syndicateType == 1){
				$courseIdArr = '';
				$courseQuery = "select session from ".DB_PREFIX."course WHERE id = '".$course_id."'";
				$coursesSession = $dbObj->selectDataObj($courseQuery);
				$coursesSession = $coursesSession[0]->session;
				$courseArrQuery = "select id from ".DB_PREFIX."course WHERE session = '".$coursesSession."'";
				$coursesArr = $dbObj->selectDataObj($courseArrQuery);
				
				$armyCourse = "select crs.id from ".DB_PREFIX."course as crs WHERE crs.wing_id = 1 AND crs.status = '0'";
				$armyCourse = $dbObj->selectDataObj($armyCourse);
				//echo '<pre>';
				//print_r($armyCourse[0]->id);
				$armyCourseIdForJoinSyn = $armyCourse[0]->id;
				$termQuery = "select trm.id, trm.name from ".DB_PREFIX."term as trm WHERE trm.wing_id = 1 AND trm.course_id = '".$armyCourseIdForJoinSyn."' AND trm.status = '0'";
				$armyTerm = $dbObj->selectDataObj($termQuery);
				//print_r($armyTerm);
				$joinActiveTerm = $armyTerm[0]->id;
				
				
				
				
				foreach($coursesArr as $course){
					$courseIdArr[] = $course->id;
				}
				$courseIdArr = join(', ', $courseIdArr);
				
				if($term_id == 0 || $term_id == ""){
					$add_term = "";
				}else{
					$add_term = " AND stc.term_id = '".$joinActiveTerm."' ";
				}
				
				if($syndicate_id == 0 || $syndicate_id == ""){
					$add_syndicate = "";
				}else{
					$add_syndicate = " AND stc.syndicate_id = '".$syndicate_id."' ";
				}
				
				
				$query = "select std.*,wng.name wng, stc.term_id, stc.syndicate_id,stc.wing_id, std.ba_no, std.rank_id, std.official_name from ".DB_PREFIX."student as std, ".DB_PREFIX."student_to_syndicate as stc, ".DB_PREFIX."wing as wng where stc.course_id IN (".$courseIdArr.") AND std.wing_id = wng.id  AND stc.student_id = std.id ".$add_syndicate." order by wng.name asc";
				
				$studentArr = $dbObj->selectDataObj($query);
				
				$number = array();
				foreach($studentArr as $student){
					$key = $student->id;
					//$val = "number_".$student->id;				//number_11
					$val = "number_".$student->id;	
					$number[$key] = $_POST[$val];				//$_POST['number_11']
				}

				
				//Delete All data from ds_to_course Table of selected Syndicate/Term if no ds is selected
				$sql = 'DELETE from '.DB_PREFIX.'marking where course_id IN ('.$courseIdArr.') AND term_id = '.$joinActiveTerm.' AND exercise_id = '.$exercise_id.' AND syndicate_id = '.$syndicate_id.' AND ds_id = '.$cur_user_id;
				$delete = $dbObj->executeData($sql);			
				
			}else{
				$course_id = $activeCourseArr[0]->id;
				$courseIdArr = '';
				$courseQuery = "select session from ".DB_PREFIX."course WHERE id = '".$course_id."'";
				$coursesSession = $dbObj->selectDataObj($courseQuery);
				$coursesSession = $coursesSession[0]->session;
				$courseArrQuery = "select id from ".DB_PREFIX."course WHERE session = '".$coursesSession."'";
				$coursesArr = $dbObj->selectDataObj($courseArrQuery);
				foreach($coursesArr as $course){
					$courseIdArr[] = $course->id;
				}
				$courseIdArr = join(', ', $courseIdArr);
				
				$armyCourse = "select crs.id from ".DB_PREFIX."course as crs WHERE crs.wing_id = 1 AND crs.status = '0'";
				$armyCourse = $dbObj->selectDataObj($armyCourse);
				//echo '<pre>';
				//print_r($armyCourse[0]->id);
				$armyCourseIdForJoinSyn = $armyCourse[0]->id;
				$termQuery = "select trm.id, trm.name from ".DB_PREFIX."term as trm WHERE trm.wing_id = 1 AND trm.course_id = '".$armyCourseIdForJoinSyn."' AND trm.status = '0'";
				$armyTerm = $dbObj->selectDataObj($termQuery);
				//print_r($armyTerm);
				$joinActiveTerm = $armyTerm[0]->id;
				
				$query = "select * from ".DB_PREFIX."student WHERE wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."'";
				$studentArr = $dbObj->selectDataObj($query);
				$number = array();
				foreach($studentArr as $student){
					$key = $student->id;
					//$val = "number_".$student->id;				//number_11
					$val = "number_".$student->id;				//number_11
					$number[$key] = $_POST[$val];				//$_POST['number_11']
				}
				$exercise_id = $_POST['exercise_id'];
				$exercise_type_query = "select join_course from ".DB_PREFIX."exercise WHERE id = '".$exercise_id."'";
				$exercise_type_check = $dbObj->selectDataObj($exercise_type_query);
				$exercise_type = $exercise_type_check[0]->join_course;
				
				//Delete All data from ds_to_course Table of selected Syndicate/Term if no ds is selected
				//$sql = 'DELETE from '.DB_PREFIX.'marking where course_id='.$course_id.' AND term_id = '.$term_id.' AND exercise_id = '.$exercise_id.' AND syndicate_id = '.$syndicate_id;
				if($exercise_type == 1){
					$sql = 'DELETE from '.DB_PREFIX.'marking where course_id IN ('.$courseIdArr.') AND term_id = '.$joinActiveTerm.' AND exercise_id = '.$exercise_id.' AND syndicate_id = '.$syndicate_id.' AND ds_id = '.$cur_user_id;
				}else{
					$sql = 'DELETE from '.DB_PREFIX.'marking where course_id='.$course_id.' AND term_id = '.$term_id.' AND exercise_id = '.$exercise_id.' AND ds_id = '.$cur_user_id;
				}
				
				$delete = $dbObj->executeData($sql);
			}
			
			//print_r($number);
			//print_r($_POST);
			//exit;
			
			//Now Save into database
			if(!empty($student_id)){
				$sql = 'INSERT INTO '.DB_PREFIX.'marking (`course_id`,`term_id`,`exercise_id`,`ds_id`,`status`,`syndicate_id`,`student_id`,`ds_marking`, `ds_student_weight`) VALUES';		//ci status will not affect anything (hope so)
				
				if($syndicateType == 1){
					$saveTermId = $joinActiveTerm;
				}else{
					$saveTermId = $term_id;
				}
						foreach($student_id as $skey =>$student){
							$course_id = $_POST['join_course_id'][$skey];
							
							foreach($number as $key=>$value){
								if($key == $student){
									$student_number = $value;
								}//if
								$weight_number = ($student_number*$exercise_weight)/100;
								$weight_number = view_number_two($weight_number);
							}//foreach
							$sql .= "('".$course_id."','".$saveTermId."','".$exercise_id."','".$cur_user_id."','".$status."','".$syndicate_id."','".$student."','".$student_number."', '".$weight_number."'),";						
						}//foreach
							$sql = rtrim($sql, ",");
							$sql = $sql.';';
			}
			//if
			
			$inserted = $dbObj->executeData($sql);
			
			if(!$inserted){
				$msg = ADD_FAILED;		
				$action = 'view';
				$url = 'ds_marking.php?action=view&msg='.$msg;
				redirect($url);
			}//if
		}//if
		
		if($_POST['Submit'] == 'Forward'){
			//Find Student info
			$course_id = $activeCourseArr[0]->id;
			$syndicate_id = $_POST['marking_syndicate_id'];
			$syndicateQuery = "select id, syndicate_type from ".DB_PREFIX."syndicate WHERE id = '".$syndicate_id."'";
			$syndicate = $dbObj->selectDataObj($syndicateQuery);
			$syndicateType = $syndicate[0]->syndicate_type;
			
				$courseIdArr = '';
				$courseQuery = "select session from ".DB_PREFIX."course WHERE id = '".$course_id."'";
				$coursesSession = $dbObj->selectDataObj($courseQuery);
				$coursesSession = $coursesSession[0]->session;
				$courseArrQuery = "select id from ".DB_PREFIX."course WHERE session = '".$coursesSession."'";
				$coursesArr = $dbObj->selectDataObj($courseArrQuery);
				foreach($coursesArr as $course){
					$courseIdArr[] = $course->id;
				}
				$courseIdArr = join(', ', $courseIdArr);
				
				
				$query = "select * from ".DB_PREFIX."student WHERE wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."'";
				$studentArr = $dbObj->selectDataObj($query);
				$number = array();
				
				foreach($studentArr as $student){
					$key = $student->id;
					//$val = "number_".$student->id;				//number_11
					$val = "number_".$student->id;				//number_11
					$number[$key] = $_POST[$val];				//$_POST['number_11']
				}
				
				$exercise_id = $_POST['exercise_id'];
				$exercise_type_query = "select join_course from ".DB_PREFIX."exercise WHERE id = '".$exercise_id."'";
				$exercise_type_check = $dbObj->selectDataObj($exercise_type_query);
				$exercise_type = $exercise_type_check[0]->join_course;
				
				if($exercise_type == 1){
					//$sql = 'DELETE from '.DB_PREFIX.'marking where course_id IN ('.$courseIdArr.') AND term_id = '.$joinActiveTerm.' AND exercise_id = '.$exercise_id.' AND syndicate_id = '.$syndicate_id.' AND ds_id = '.$cur_user_id;
				}else{
					//$sql = 'DELETE from '.DB_PREFIX.'marking where course_id='.$course_id.' AND term_id = '.$term_id.' AND exercise_id = '.$exercise_id.' AND ds_id = '.$cur_user_id;
				}
				
				$delete = $dbObj->executeData($sql);
			
			
			//Now Save into database
			if(!empty($student_id)){
				$sql = 'INSERT INTO '.DB_PREFIX.'marking (`course_id`,`term_id`,`exercise_id`,`ds_id`,`status`,`syndicate_id`,`student_id`,`ds_marking`, `ds_student_weight`) VALUES';		//ci status will not affect anything (hope so)
				
						foreach($student_id as $skey =>$student){
							$course_id = $_POST['join_course_id'][$skey];
							
							foreach($number as $key=>$value){
								if($key == $student){
									$student_number = $value;
								}//if
								$weight_number = ($student_number*$exercise_weight)/100;
								$weight_number = view_number_two($weight_number);
							}//foreach
							$sql .= "('".$course_id."','".$term_id."','".$exercise_id."','".$cur_user_id."','".$status."','".$syndicate_id."','".$student."','".$student_number."', '".$weight_number."'),";						
						}//foreach
							$sql = rtrim($sql, ",");
							$sql = $sql.';';
			}
			//if
			
			$inserted = $dbObj->executeData($sql);
			
			if(!$inserted){
				$msg = ADD_FAILED;		
				$action = 'view';
				$url = 'ds_marking.php?action=view&msg='.$msg;
				redirect($url);
			}//if
		}//if
		
		if($_POST['Submit'] == 'Save'){
			$msg = $exr_nam->name.SUCCESSFULLY_SAVED;
		}else if($_POST['Submit'] == 'Forward'){
			$msg = $exr_nam->name.FORWARDED_RESULT_SI;
		}else if($_POST['Submit'] == 'Request to Unlock'){
			$msg = REQUEST_SENT.$exr_nam->name;
		}
		$url = 'ds_marking.php?action=view&msg='.$msg;
		redirect($url);

		break;

}//switch


require_once("includes/templates.php");
require_once("templates/top_menu.php");
require_once("templates/left_menu.php");
?>

<div id="right_column">
	<?php if(!empty($msg)){ ?>
		<table id="system_message">
			<tr>
				<td>
					<?php echo $msg; ?>
				</td>
			</tr>
		</table>
	<?php } ?>
	<table width="100%" cellspacing="0" cellpadding="0" border="0" class="module_header">
		<tr>
			<td>
				<h1><?php echo ASSIGN_MARK; ?></h1>
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
		if(empty($syndicate_id)){ ?>
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
							<strong>
							<?php 	$course_name = getNameById('course', $course_id);
									echo $course_name->name; ?>
									<input type="hidden" name="course_id" id="course_id" value="<?php echo $course_id; ?>" />
									<input type="hidden" name="term_id" id="term_id" value="<?php echo $term_id; ?>" />
							</strong>
						</td>
					</tr>
					<tr>
						<td height="30" width="30%">
							<?php echo TERM; ?>:
						</td>
						<td width="70%">
							<strong>
							<?php 	$term = getNameById('term', $term_id);
									echo $term->name; ?>
							</strong>
						</td>
					</tr>
					<!--
					<tr>
						<td height="30" width="30%">
							<?php echo SYNDICATE; ?>:
						</td>
						<td width="70%">
							<strong>
							<?php 	$syndicate = getNameById('syndicate', $syndicate_id);
									echo $syndicate->name; ?>
							</strong>
						</td>
					</tr>
					-->
					<tr>
						<td height="30" width="30%">
							<?php echo SYNDICATE; ?>:
						</td>
						<td width="70%">
							<?php echo $synList_opt; ?>
							<span class="required_field"> *</span>
						</td>
					</tr>
					<!--
					<tr>
						<td height="30" width="30%">
							<?php echo EXERCISE; ?>:
						</td>
						<td width="70%">
							<?php echo $exerciseList_opt; ?>
							<span class="required_field"> *</span>
						</td>
					</tr>
					-->
					<tr>
						<td height="30" width="30%">
							<?php echo EXERCISE; ?>:
						</td>
						<td width="70%">
							<div id="loaderContainer"></div>
							<div id="exercise_display"></div>
						</td>
					</tr>
				</table>
			</div>
		
			<div class="ds_output">
				<div id="loaderContainer"></div>
				<div id="exr_info"></div>
			</div>
		</div>
		
		<?php } ?>	
				
		<div class="ds_marking">
			<div id="loaderContainer"></div>
			<br style="clear:both"  />
			<div id="ds_marking_display"></div>
		</div>				
						
	<?php } ?>
</div>
			
<?php
require_once("includes/footer.php");
?>
