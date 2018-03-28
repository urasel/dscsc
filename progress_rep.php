<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Performance Analysis';
$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$cur_user_off_nam = $usr[0]->official_name;
$cur_user_rank = getNameById('rank',$usr[0]->rank_id);
$cur_user_rank = $cur_user_rank->name;
$action = $_REQUEST['action'];
$msg = '';

//Chek if this user is valid for this file
if($cur_user_group_id != '5' && $cur_user_group_id != '4'){			
	header("Location: dashboard.php");		//Only DS & SI can view Progress reports
}

switch($action){
	case 'view':	
	default:
		
		if(isset($_POST['submit'])){
			$course_id = $_POST['course_id'];
			$term_id = $_POST['term_id'];
			$student_id = $_POST['student_id'];
			$exercise_id = $_POST['exercise_id'];
			$exercise_type_id = $_POST['exercise_type_id'];
		}//if
		
		if($cur_user_group_id == '5'){
			
			//Find Currently Active Course
			$sql = "SELECT id from ".DB_PREFIX."course WHERE wing_id = '".$cur_user_wing_id."' AND status = '0'";
			$activeCourseArr = $dbObj->selectDataObj($sql);
			$course_id = $activeCourseArr[0]->id;
			
			//Find Currently Active Term which is assigned to this DS
			$sql = "select id from ".DB_PREFIX."term WHERE wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."' AND status = '0'";
			$activeTermArr = $dbObj->selectDataObj($sql);
			$term_id = $activeTermArr[0]->id;
			
			//Find syndicate of the DS
			$sql = "select syndicate_id from ".DB_PREFIX."ds_to_course WHERE wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."' AND term_id = '".$term_id."' AND ds_id = '".$cur_user_id."'";
			$activeSynArr = $dbObj->selectDataObj($sql);
			$syndicate_id = $activeSynArr[0]->syndicate_id;
			
			//Build Student List Array	
			$query = "select std.id, std.full_name, std.student_id from ".DB_PREFIX."student_to_syndicate as sts, ".DB_PREFIX."student as std where sts.wing_id = ".$cur_user_wing_id." AND sts.course_id = '".$course_id."' AND sts.term_id = '".$term_id."' AND sts.syndicate_id = '".$syndicate_id."' AND std.id = sts.student_id ORDER BY std.student_id asc";
			$studentArr = $dbObj->selectDataObj($query);
			
			$stdId = array();
			$stdId[0] = SELECT_STUDENT_OPT;
			if(!empty($studentArr)){			
				foreach($studentArr as $item){
					$stdId[$item->id] = $item->student_id.' &raquo; '.$item->full_name;
				}//foreach
			}//if
			$stuList_opt = formSelectElement($stdId, $student_id, 'student_id');
			
			//Build Exercise Type List Array
			$sql = "select * from ".DB_PREFIX."exercise_type WHERE wing_id = '".$cur_user_wing_id."' ORDER BY name asc";
			$exrTypeArr = $dbObj->selectDataObj($sql);
	
			$exrTypeId = array();
			$exrTypeId[0] = SELECT_EXERCISE_TYPE_OPT;
			if(!empty($exrTypeArr)){			
				foreach($exrTypeArr as $item){
					$exrTypeId[$item->id] = $item->name;
				}//foreach
			}//if
			$exerciseTypeList_opt = formSelectElement($exrTypeId, $exercise_type_id, 'exercise_type_id', 'onchange = processFunction("exercise_by_type")');
					
					
			if($exercise_type_id != 0){
				$ex_ty_cond = " AND exr.type_id = '".$exercise_type_id."' ";
			}//if
					
			//Build Exercise List Array	
			//$query = "select exr.id, exr.name, exr.weight from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$course_id."' AND ett.term_id = '".$term_id."' AND exr.id = ett.exercise_id ".$ex_ty_cond." order by exr.name asc";
			
			$query = "select exr.id, exr.name, exr.weight from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where ett.term_id = '".$term_id."' AND exr.id = ett.exercise_id ".$ex_ty_cond." order by exr.name asc";
			
			$exerciseListArr = $dbObj->selectDataObj($query);
	
			$exrId = array();
			$exrId[0] = SELECT_EXERCISE_OPT;
			if(!empty($exerciseListArr)){			
				foreach($exerciseListArr as $item){
					$exrId[$item->id] = $item->name;
				}//foreach
			}//if
			$exerciseList_opt = formSelectElement($exrId, $exercise_id, 'exercise_id');
			
			//Set Indicator for submiting form
			$ind = 0;

		}else if($cur_user_group_id == '4'){
			
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
						}//foreach
					}//if
					$courseList_opt = formSelectElement($courseId, $course_id, 'course_id', 'onchange = processFunction("progress_result_term")');
					
					//Build Term List Array	
					$query = "select id, name from ".DB_PREFIX."term WHERE course_id = '".$course_id."' order by name asc";
					$termArr = $dbObj->selectDataObj($query);
					$termId = array();
					$termId[0] = SELECT_TERM_OPT;
					if(!empty($termArr)){			
						foreach($termArr as $item){
							$termId[$item->id] = $item->name;
						}//foreach
					}//if
					$termList_opt = formSelectElement($termId, $term_id, 'term_id', 'onchange = processFunction("si_result_exr")');
					
					//Build Exercise Type List Array
					$sql = "select * from ".DB_PREFIX."exercise_type WHERE wing_id = '".$cur_user_wing_id."' ORDER BY name asc";
					$exrTypeArr = $dbObj->selectDataObj($sql);
			
					$exrTypeId = array();
					$exrTypeId[0] = SELECT_EXERCISE_TYPE_OPT;
					if(!empty($exrTypeArr)){			
						foreach($exrTypeArr as $item){
							$exrTypeId[$item->id] = $item->name;
						}//foreach
					}//if
					$exerciseTypeList_opt = formSelectElement($exrTypeId, $exercise_type_id, 'exercise_type_id', 'onchange = processFunction("exercise_by_type")');
							
					//Build Exercise List Array
					if($exercise_type_id == '0' || $exercise_type_id == ""){
						if($term_id == '0' || $term_id == ""){
							$sql = "select exr.id, exr.name from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$course_id."' AND ett.term_id = '".$term_id."' AND exr.id = ett.exercise_id order by exr.name asc";
						}else{
							$sql = "select exr.id, exr.name from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$course_id."' AND ett.term_id = '".$term_id."' AND exr.id = ett.exercise_id order by exr.name asc";
						}//else
					}else{
						if($term_id == '0' || $term_id == ""){
							$sql = "SELECT * FROM ".DB_PREFIX."exercise WHERE type_id = '".$exercise_type_id."'";
						}else{
							$sql = "select exr.id, exr.name from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett, ".DB_PREFIX."exercise_type as et where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$course_id."' AND ett.term_id = '".$term_id."' AND exr.id = ett.exercise_id AND exr.type_id = '".$exercise_type_id."' order by exr.name asc";
						}//else
					}//else

					$exrArr = $dbObj->selectDataObj($sql);
					$exrId = array();
					$exrId[0] = SELECT_EXERCISE_OPT;
					if(!empty($exrArr)){			
						foreach($exrArr as $item){
							$exrId[$item->id] = $item->name;
						}//foreach
					}//if
					$exerciseList_opt = formSelectElement($exrId, $exercise_id, 'exercise_id');
					
					if($student_id != '0'){			
						//Build Student List Array	
						$query = "select id, full_name, student_id from ".DB_PREFIX."student where wing_id = ".$cur_user_wing_id." AND course_id = '".$course_id."'";
						$studentArr = $dbObj->selectDataObj($query);
					}else{
							//Build Student List Array
							if($exercise_type_id == '0' || $exercise_type_id == ""){
								$sql = "SELECT distinct std.id, std.full_name, std.student_id FROM ".DB_PREFIX."marking AS dm, ".DB_PREFIX."exercise AS ex, ".DB_PREFIX."student AS std WHERE dm.exercise_id = ex.id AND std.id = dm.student_id ORDER BY std.student_id";
							}else{
								$sql = "SELECT distinct std.id, std.full_name, std.student_id FROM ".DB_PREFIX."marking AS dm, ".DB_PREFIX."exercise_type AS et, ".DB_PREFIX."exercise AS ex, ".DB_PREFIX."student AS std WHERE dm.exercise_id = ex.id AND et.id = ex.type_id AND et.id = '".$exercise_type_id."' AND std.id = dm.student_id  ORDER BY std.student_id";
							}//else
							$studentArr = $dbObj->selectDataObj($sql);
							
							//Build Exercise Array
							if($term_id != '0'){
								if($exercise_id == '0' || $exercise_id == ""){
									if($exercise_type_id == '0' || $exercise_type_id == ""){
										$sql = "SELECT distinct dm.exercise_id, ex.id, ex.name, ex.weight FROM ".DB_PREFIX."marking AS dm, ".DB_PREFIX."exercise AS ex WHERE dm.exercise_id = ex.id AND dm.course_id = '".$course_id."' AND dm.term_id = '".$term_id."' ";
									}else{
										$sql = "SELECT distinct dm.exercise_id, ex.id, ex.name, ex.weight FROM ".DB_PREFIX."marking AS dm, ".DB_PREFIX."exercise_type AS et, ".DB_PREFIX."exercise AS ex WHERE dm.exercise_id = ex.id AND et.id = ex.type_id AND et.id = '".$exercise_type_id."' AND dm.course_id = '".$course_id."' AND dm.term_id = '".$term_id."'";
									}//else
								}else{
									$sql = "SELECT distinct dm.exercise_id, exr.name, exr.weight, exr.id FROM ".DB_PREFIX."marking as dm, ".DB_PREFIX."exercise as exr WHERE dm.exercise_id = '".$exercise_id."' AND dm.term_id = '".$term_id."' AND dm.course_id = '".$course_id."' AND dm.exercise_id = exr.id AND exr.id = '".$exercise_id."'";
								}//else
							}else{
								if($exercise_type_id == '0'){
									$sql = "SELECT * FROM ".DB_PREFIX."exercise WHERE course_id = '".$course_id."'";
								}else{
									$sql = "SELECT * FROM ".DB_PREFIX."exercise WHERE course_id = '".$course_id."' AND type_id = '".$exercise_type_id."'";
								}//else
							}//else
						$exerciseListArr = $dbObj->selectDataObj($sql);
					}//else
					
					$stdId = array();
					$stdId[0] = SELECT_STUDENT_OPT;

					if(!empty($studentArr)){			
						foreach($studentArr as $item){
							$stdId[$item->id] = $item->student_id.' &raquo; '.$item->full_name;
						}//foreach
					}//if
					$stuList_opt = formSelectElement($stdId, $student_id, 'student_id');
				}//if not empty courseArray
				
				//Set Indicator for submiting form
				$ind = 1;
			}//else if
		
		if(!empty($exercise_id)){
			//Find Exercise Info
			$sql = "select * from ".DB_PREFIX."exercise WHERE id = '".$exercise_id."' ";
			$exrArr = $dbObj->selectDataObj($sql);
			$exercise = $exrArr[0];
			$joinExercise = $exercise->join_course;
			if($joinExercise == 1 && $cur_user_wing_id == 2){
				$exercise->weight = $exercise->air_weight;
			}else if($joinExercise == 1 && $cur_user_wing_id == 3){
				$exercise->weight = $exercise->navy_weight;
			}else if($joinExercise == 1 && $cur_user_wing_id == 1){
				$exercise->weight = $exercise->weight;
			}else{
				$exercise->weight = $exercise->weight;
			}
			$weight = $exercise->weight;
			$exr_mark = $exercise->mark;
			$marking_type = $exercise->marking_type;
		}else{
			//Build Exercise List Array
			if($term_id != '0'){
				if($exercise_type_id == '0' || $exercise_type_id == ""){
					$sql = "select * from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$course_id."' AND ett.term_id = '".$term_id."' AND exr.id = ett.exercise_id order by exr.name asc";
				}else{
					$sql = "select * from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett, ".DB_PREFIX."exercise_type as et where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$course_id."' AND ett.term_id = '".$term_id."' AND exr.id = ett.exercise_id AND et.id = '".$exercise_type_id."' AND et.id = exr.type_id order by exr.name asc";
				}//else
			}else if($term_id == '0'){
				if($exercise_type_id == '0' || $exercise_type_id == ""){
					$sql = "select distinct exr.id, exr.* from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$course_id."'  AND exr.id = ett.exercise_id order by exr.name asc";
				}else{
					$sql = "select distinct exr.id, exr.* from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett, ".DB_PREFIX."exercise_type as et where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$course_id."' AND exr.id = ett.exercise_id AND et.id = '".$exercise_type_id."' AND et.id = exr.type_id order by exr.name asc";
				}//else
			}//else
			$exrArr = $dbObj->selectDataObj($sql);
			
			//Find DS Impression mark Info of this term
			$sql = "select * from ".DB_PREFIX."term WHERE id = '".$term_id."'";
			$termInfoArry = $dbObj->selectDataObj($sql);
			$termInfo = $termInfoArry[0];
			$ds_impr_mark = $termInfo->ds_impr_mark;
		}//else
		
		//Build Term List Array	-- for viewing course report
		$query = "select * from ".DB_PREFIX."term where course_id = '".$course_id."'";
		$courseTermArr = $dbObj->selectDataObj($query);
		
		if($term_id == '0'){
			$sql = "select * from ".DB_PREFIX."course WHERE id = '".$course_id."'";
			$courseInfoArr = $dbObj->selectDataObj($sql);
			$courseInfo = $courseInfoArr[0];
			$si_impr_mark = $courseInfo->si_impr_mark;
		}//if
							
		$action = 'view';
		$msg = $_REQUEST['msg'];
		break;

}//switch

require_once("includes/templates.php");
require_once("templates/top_menu.php");
require_once("templates/left_menu.php");
?>

<div id="right_column">
	<table width="100%" cellspacing="0" cellpadding="0" border="0" class="module_header">
		<tr>
			<td>
				<h1><?php echo PERFORMANCE_ANALYSIS; ?></h1>
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
	<?php if($action=="view"){
	if((($cur_user_group_id == 5) && (empty($syndicate_id))) || (($cur_user_group_id == 4) && (empty($courseArray)))){ ?>
		<table cellpadding="0" cellspacing="0" border="0" width="100%" class="module_content">
				<tr>
					<td height="30" colspan="2">
						<?php echo ERROR_MSG; ?>
					</td>
				</tr>
			</table>
	<?php }else{ ?>
			<form action="progress_rep.php" method="post" name="progress_rep" id="progress_rep" onsubmit="return validateProgressResult(<?php echo $ind; ?>);">
				<table cellpadding="0" cellspacing="0" border="0" width="350" class="module_content">
					<tr>
						<td height="30" width="30%">
							<?php echo COURSE; ?>:
						</td>
						<td width="70%">
							<strong>
							<?php
								if($cur_user_group_id == '5'){
								 	$course_name = getNameById('course', $course_id);
									echo $course_name->name;
									echo '<input type="hidden" name="course_id" id="course_id" value="'.$course_id.'" />';
								}else if($cur_user_group_id == '4'){
									echo $courseList_opt.'<span class="required_field"> *</span>';
								}//else if
							?>
							</strong>
						</td>
					</tr>
					<tr>
						<td height="30">
							<?php echo TERM; ?>:
						</td>
						<td>
						<div id="term_display">
							<strong>
							<?php 	
								if($cur_user_group_id == '5'){
									$term = getNameById('term', $term_id);
									echo $term->name;
									echo '<input type="hidden" name="term_id" id="term_id" value="'.$term_id.'" />';
								}else if($cur_user_group_id == '4'){
									echo $termList_opt;
								}
							?>
							</strong>
						</div>
						</td>
					</tr>
					<tr>
						<td height="30">
							<?php echo SELECT_STUDENT; ?>:
						</td>
						<td>
							<div id="student_display">
								<?php echo $stuList_opt; ?>
							</div>
						</td>
					</tr>
					<tr>
						<td height="30">
							<?php echo EXERCISE_TYPE; ?>:
						</td>
						<td>
							<div id="exercise_type_display">
								<?php echo $exerciseTypeList_opt; ?>
							</div>
						</td>
					</tr>
					<tr>
						<td height="30">
							<?php echo EXERCISE_NAME; ?>:
						</td>
						<td>
						<div id="loaderContainer"></div>
						<div id="exr_display">
							<?php echo $exerciseList_opt; ?>
						</div>
						</td>
					</tr>
					<tr height="50">
						<td colspan="2">
							<input type="submit" name="submit" id="submit" value="Show Reports" />
						</td>
					</tr>
				</table>
				<input type="hidden" name="action" value="view" />
			</form>
		<?php 
		}//if not empty activeCourse or activeTerm
	if($student_id != '0'){   //Perfor this action when user wants to see the result of a particular student
		if (isset($_POST['submit'])){
			if((empty($exercise_id) || $exercise_id == '0')){echo '<div style="width:700px; overflow-x:scroll;" >';}?>
			<table cellpadding="0" cellspacing="0" border="0" class="module_content" width="100%">
				<tr>
					<td>
						<table cellpadding="0" cellspacing="0" border="0" class="datagrid" width="100%">
							<tr height="30">
								<td colspan="9">&nbsp;
								</td>
							</tr>
						<?php 
						
						//if(!empty($term_id) || $term_id != '0'){			  //means it is either term or exercise result
							if(!empty($exercise_id) || $exercise_id != '0'){  //means if there is an exercise id
																			  //the user wants to see an exercise result ?>
							<tr class="head">
								<td align="center" width="10%" rowspan="2">
									<strong><?php echo CN; ?></strong>
								</td>
								<?php if($cur_user_group_id == '4' && $syndicate_id == 0){ ?>
								<td align="center" width="10%" rowspan="2">
									<strong><?php echo SYNDICATE; ?></strong>
								</td>
								<?php } ?>
								<td width="5%" rowspan="2">
									<strong><?php echo RANK; ?></strong>
								</td>
								<td width="10%" rowspan="2">
									<strong><?php echo STUDENT_NAME; ?></strong>
								</td>
								<td colspan="2" width="10%" align="center">
									<strong><?php echo DS_FROM; ?></strong>
								</td>
								<td rowspan="2" align="center" width="10%">
									<strong><?php echo SI_NUMBER_PERCENT; ?></strong>
								</td>
								<td rowspan="2" align="center" width="10%">
									<strong><?php echo CI_NUMBER_PERCENT; ?></strong>
								</td>
								<td colspan="2" width="10%" align="center">
									<strong><?php echo MOD; ?></strong>
								</td>
								<td rowspan="2" width="5%">
									<strong><?php echo GRADE; ?></strong>
								</td>
							</tr>
							<tr class="head">
								<td align="center">
									<strong><?php echo PERCENT; ?></strong>
								</td>
								<td align="center">
									<strong><?php echo WT.' ('.$weight.')'; ?></strong>
								</td>
								<td align="center">
									<strong><?php echo PERCENT; ?></strong>
								</td>
								<td align="center">
									<strong><?php echo WT.' ('.$weight.')'; ?></strong>
								</td>
							</tr>
							<?php 
							//Find number of each student exercise if already inserted in same term & course
							if($cur_user_group_id == '5' && !empty($exercise_id)){
									$sql = "select * from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise_id."' AND student_id = '".$student_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."'";
							}else if($cur_user_group_id == '4'){
									$sql = "select * from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise_id."' AND student_id = '".$student_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' AND status = '1'";
							}//else groupId == 4
							
							$number = $dbObj->selectDataObj($sql);
							$student_number = $number[0];
							
						if(!empty($student_number)){
							$from_ds = $student_number->ds_marking;
							$from_ds_weight = ($from_ds*$weight)/100;
							$si_mod = $student_number->si_mod_marking;
							$ci_mod = $student_number->ci_mod_marking;
							$total_mod = $from_ds+$si_mod+$ci_mod;
							$total_mod_weight = ($total_mod*$weight)/100;
							
							//Find Student Info
							$sql = "select * from ".DB_PREFIX."student WHERE id = '".$student_id."' ";
							$stdInfoArr = $dbObj->selectDataObj($sql);
							$stdInfo = $stdInfoArr[0];
							
							//Find syndicate of student
							$sql = "select syndicate_id from ".DB_PREFIX."student_to_syndicate WHERE student_id = '".$student_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."'";
							$synArr = $dbObj->selectDataObj($sql);
							$syndicate = $synArr[0];
							$syndicateName = getNameById('syndicate', $syndicate->syndicate_id);
							?>
							<tr>
								<td align="center" height="30">
									<?php echo $stdInfo->student_id; ?>
								</td>
								<?php if($cur_user_group_id == '4'){ ?>
								<td align="center">
									<?php echo $syndicateName->name; ?>
								</td>
								<?php } ?>
								<td class="general_padding">
									<?php 	$rank = getNameById('rank', $stdInfo->rank_id);
											echo $rank->short_name; ?>
								</td>
								<td class="general_padding">			
									<?php echo $stdInfo->official_name; ?>
								</td>
								<td align="center">
									<?php echo view_number_two($from_ds); ?>
								</td>
								<td align="center">
									<?php echo view_number_two($from_ds_weight); ?>
								</td>
								<td align="center">
									<?php echo view_number_two($si_mod); ?>
								</td>
								<td align="center">
									<?php echo view_number_two($ci_mod); ?>
								</td>
								<td align="center">
									<?php echo view_number_two($total_mod); ?> 
								</td>
								<td align="center">
									<?php echo view_number_two($total_mod_weight); ?> 
								</td>
								<td class="general_padding">
									<?php echo findGrade($total_mod); ?>
								</td>
							</tr>
							<?php	
					}else{	//if empty studentArr ?>
							<tr>
								<td height="30" colspan="9"><?php echo EMPTY_DATA; ?></td>
							</tr>
					<?php }	
					}else if(empty($exercise_id) || ($exercise_id == '0')){//if empty exercise id ---> means it is a term result?>
						<tr class="head">
								<td align="center" width="10%" rowspan="2">
									<strong><?php echo CN; ?></strong>
								</td>
								<td align="center" width="20%" rowspan="2">
									<strong><?php echo RANK; ?></strong>
								</td>
								<td align="center" width="20%" rowspan="2">
									<strong><?php echo STUDENT_NAME; ?></strong>
								</td>
								<?php if(($cur_user_group_id == '4') && ($term_id != '0')){ ?>
								<td align="center" width="25%" rowspan="2">
									<strong><?php echo SYNDICATE; ?></strong>
								</td>
								<?php } 
										foreach($exrArr as $exercise){ ?>
								<td align="center" width="20%" colspan="2">
									<strong><?php echo $exercise->name; ?></strong>
								</td>
								<?php }//foreach ?>
							</tr>
							<tr class="head">
								<?php 
								$exam_total_weight = 0;
								foreach($exrArr as $exercise){ ?>
								<td align="center" width="20%">
									<strong><?php echo PERCENT; ?></strong>
								</td>
								<td align="center" width="20%">
									<strong><?php echo WT.'<br />'.$exercise->weight; ?></strong>
								</td>
								<?php 
									$exam_total_weight += $exercise->weight;
									$total = $exam_total_weight+$ds_impr_mark;
								}//foreach ?>
							</tr>
							<?php 
							//Find Student Info
							$sql = "select * from ".DB_PREFIX."student WHERE id = '".$student_id."' ";
							$studentInfoArr = $dbObj->selectDataObj($sql);
							$studentInfo = $studentInfoArr[0];
							
							//Find syndicate of student
							$sql = "select syndicate_id from ".DB_PREFIX."student_to_syndicate WHERE student_id = '".$student_id."' ";
							$synArr = $dbObj->selectDataObj($sql);
							$syndicate = $synArr[0];
							$syndicateName = getNameById('syndicate', $syndicate->syndicate_id);
								
							?>
							<tr>
								<td class="general_padding" height="40">
									<?php echo $studentInfo->student_id; ?>
								</td>
								<td class="general_padding">
									<?php 	$rank = getNameById('rank', $studentInfo->rank_id);
											echo $rank->short_name; ?>
								</td>
								<td class="general_padding">			
									<?php echo $studentInfo->official_name; ?>
								</td>
								<?php if($cur_user_group_id == '4' && $term_id != '0'){ ?>
								<td class="general_padding">
									<?php echo $syndicateName->name; ?>
								</td>
								<?php }
								$total_exam_weight = 0;
								foreach($exrArr as $exercise){
									//Find Exercise Info 
									$sql = "select * from ".DB_PREFIX."exercise WHERE id = '".$exercise->id."'";
									$exerciseInfoArr = $dbObj->selectDataObj($sql);
									$exerInfo = $exerciseInfoArr[0];
									$joinExercise = $exerInfo->join_course;
									if($joinExercise == 1 && $cur_user_wing_id == 2){
										$exerInfo->weight = $exerInfo->air_weight;
									}else if($joinExercise == 1 && $cur_user_wing_id == 3){
										$exerInfo->weight = $exerInfo->navy_weight;
									}else if($joinExercise == 1 && $cur_user_wing_id == 1){
										$exerInfo->weight = $exerInfo->weight;
									}else{
										$exerInfo->weight = $exerInfo->weight;
									}
									$weight = $exerInfo->weight;
									
									//Find marking of students of different exams
									if($cur_user_group_id == '5'){
										if($term_id != '0'){
											$sql = "select * from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise->id."' AND student_id = '".$student_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."'";
										}else{
											$sql = "select * from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise->id."' AND student_id = '".$student_id."' AND course_id = '".$course_id."'";
										}//else
									}else if($cur_user_group_id == '4'){
										if($term_id != '0'){
											$sql = "select * from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise->id."' AND student_id = '".$student_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' AND status = '1'";
										}else{
											$sql = "select * from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise->id."' AND student_id = '".$student_id."' AND course_id = '".$course_id."' AND status = '1'";
										}
									}//else if
									$markArr = $dbObj->selectDataObj($sql);
									$mark = $markArr[0];
									
									$s_mark = ($mark->ds_marking+$mark->si_mod_marking+$mark->ci_mod_marking);
									$converted_s_mark = ($s_mark*$weight)/100;
									$total_exam_weight += $converted_s_mark;
									$total_exam_percent = ($total_exam_weight*100)/$exam_total_weight;
								?>
								<td class="general_padding_number">
									<?php echo view_number_two($s_mark); ?>
								</td>
								<td class="general_padding_number">
									<?php echo view_number_two($converted_s_mark); ?>
								</td>	
									
						<?php }//foreach ?>
							</tr>
						<?php  }//if exercise_id is empty ->>> means user wants to see term result:::END ?>
					</table>
				</td>
			</tr>
		</table>
	<?php if(empty($exercise_id)){echo '</div>';} //This portion create scroll bar for term & course result?>
		<?php } // if the user is an active user ???????? (Confusing Comment)
			if($_POST==true && $exercise_id=='0'){ // For generating Term result?>
						<table align="center">
							<tr>
								<td height="300" colspan="15" align="center">
									<?php 
									$trmName = getNameById('term', $term_id);
									$trmName = $trmName->name;
									$stdInf = getNameById('student', $student_id);
									
									$arr = array();
									$title = $trmName.' Performance Analysis of '.$stdInf->student_id.' ('.$stdInf->full_name.')';
									
									//Find total exercise of this term
									
									if($term_id != '0'){
											if($exercise_type_id == '0' || $exercise_type_id == ""){
												$sql = "SELECT distinct dm.exercise_id, ex.id, ex.name, ex.exercise_id FROM ".DB_PREFIX."marking AS dm, ".DB_PREFIX."exercise AS ex WHERE dm.exercise_id = ex.id AND dm.course_id = '".$course_id."' AND dm.term_id = '".$term_id."' order by ex.name asc";
											}else{
												$sql = "SELECT distinct dm.exercise_id, ex.id, ex.name, ex.exercise_id FROM ".DB_PREFIX."marking AS dm, ".DB_PREFIX."exercise_type AS et, ".DB_PREFIX."exercise AS ex WHERE dm.exercise_id = ex.id AND et.id = ex.type_id AND et.id = '".$exercise_type_id."' AND dm.course_id = '".$course_id."' AND dm.term_id = '".$term_id."' order by ex.name asc";
											}//else
									}else if($term_id == '0'){
										if($exercise_type_id == '0' || $exercise_type_id == ""){
											$sql = "select distinct exr.id, exr.name, exr.weight, exr.mark from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$course_id."'  AND exr.id = ett.exercise_id order by exr.name asc";
										}else{
											$sql = "select distinct exr.id, exr.name, exr.weight, exr.mark from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett, ".DB_PREFIX."exercise_type as et where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$course_id."' AND exr.id = ett.exercise_id AND et.id = '".$exercise_type_id."' AND et.id = exr.type_id order by exr.name asc";
										}//else 										
									}//else if
									$exerArr = $dbObj->selectDataObj($sql);
									$exerciseInATerm = sizeof($exerArr);
									
									if(!empty($exerArr)){
										foreach($exerArr as $exer){
											//Find mark of the student of each exercise of selected term & course
											$graph_add_cond = ($cur_user_group_id == 4) ? 'AND status = 1' : '';
											if($term_id != '0'){
												$sql = "select (ds_marking + si_mod_marking + ci_mod_marking) as total from ".DB_PREFIX."marking WHERE term_id = '".$term_id."' AND course_id = '".$course_id."' AND student_id = '".$student_id."' AND exercise_id = '".$exer->id."' $graph_add_cond";
											}else if($term_id == '0'){
												$sql = "select (ds_marking + si_mod_marking + ci_mod_marking) as total from ".DB_PREFIX."marking WHERE course_id = '".$course_id."' AND student_id = '".$student_id."' AND exercise_id = '".$exer->id."' $graph_add_cond";
											}
											$percentStuMarkArr = $dbObj->selectDataObj($sql);
											$percentStuMark = $percentStuMarkArr[0];
											if($percentStuMark == '' || $percentStuMark == 0){
												$percentStudentMark = 0;
											}else{
												$percentStudentMark = $percentStuMark->total;
											}//else
											$arr[$exer->name] = $percentStudentMark;
										}//foerach
									if($exerciseInATerm > 1){
										//Draw the line chart
										draw_line($arr,$title);
									?>
									<a id="example4" href="graph/demo5.png"><img alt="Line chart" src="graph/demo5.png" style="border: 1px solid gray; cursor:crosshair;" height="350" width="600"/></a>
									<?php 
										}//if there is more than one exercise the graph will be drawn
									}//if not empty exerciseArr ?>
								</td>
							</tr>
						</table>
	<?php		
			}//else post==true && forms a term result
		}else{ ?>
		<div id="all_student_performance" style="width:700px; overflow-x:scroll">
			<table cellpadding="0" cellspacing="0" border="0" class="module_content" width="100%">
				<tr>
					<td>
						<table cellpadding="0" cellspacing="0" border="0" class="datagrid" width="100%">
							<tr height="30" valign="top">
								<td colspan="6">
								<?php if($cur_user_group_id == '4'){ ?>
									<a href="download_reports.php"><img src="images/xls.ico" title="Download Report in MS-Excel Format" /></a>
									<a href="download.php?action=getpdf"><img src="images/pdf.png" title="Download Report in PDF Format" height="16" width="16" /></a>
								<?php }//if ?>
									<a class="print_button" href="progress_rep_view.php?course_id=<?php echo $course_id;?>&term_id=<?php echo $term_id;?>&exercise_type_id=<?php echo $exercise_type_id; ?>&exercise_id=<?php echo $exercise_id;?>" target="_blank"><img src="images/print.png" title="Print" height="16" width="16" /></a>
								</td>
							</tr>
							<tr class="head">
								<td align="center" width="10%" rowspan="2">
									<strong><?php echo SER_NO; ?></strong>
								</td>
								<td align="center" width="10%" rowspan="2">
									<strong><?php echo CN; ?></strong>
								</td>
								<td align="center" width="10%" rowspan="2">
									<strong><?php echo RANK; ?></strong>
								</td>
								<td align="center" width="20%" rowspan="2">
									<strong><?php echo STUDENT_NAME; ?></strong>
								</td>
								<?php
								//For downloading Reports as XLS format
								//if group_id == 4 --->>> Only SI can download the reprot
								if($cur_user_group_id == '4'){
									$_SESSION['report_type'] = 'progress';
									$crsNam = getNameById("course", $course_id);
									$crsName = $crsNam->name;
									$downloadTitle[0] = CONFIDENTIAL."\n";
									$downloadTitle[1] = COURSE.': '.$crsName."\n";
									$termNam = getNameById("term", $term_id);
									$termName = $termNam->name;
									$exerTypNam = getNameById("exercise_type", $exercise_type_id);
									$exerTypName = $exerTypNam->name;
									$exerNam = getNameById("exercise", $exercise_id);
									$exerName = $exerNam->name;
									if($term_id != '0' || (!empty($term_id))){
										$downloadTitle[2] = TERM.': '.$termName."\n";
									}
									if($exercise_type_id != '0' || (!empty($exercise_type_id))){
										$downloadTitle[3] = EXERCISE_TYPE.': '.$exerTypName."\n";
									}
									if($exercise_id != '0' || (!empty($exercise_id))){
										$downloadTitle[4] = EXERCISE_RES_SHT.$exerName."\n";
									}
									
									$arr[0]['sl'] = 'Ser No';
									$arr[0]['student_id'] = 'C/N';
									$arr[0]['rank_id'] = 'Rank';
									$arr[0]['student_name'] = 'Student Name';
									foreach($exerciseListArr as $exercise){
										$arr[0][$exercise->name] = $exercise->name;
										$joinExercise = $exercise->join_course;
										if($joinExercise == 1 && $cur_user_wing_id == 2){
											$exercise->weight = $exercise->air_weight;
										}else if($joinExercise == 1 && $cur_user_wing_id == 3){
											$exercise->weight = $exercise->navy_weight;
										}else if($joinExercise == 1 && $cur_user_wing_id == 1){
											$exercise->weight = $exercise->weight;
										}else{
											$exercise->weight = $exercise->weight;
										}
										$arr[0][$exercise->weight] = 'Wt ('.$exercise->weight.')';		
									}//foreach
									$arr[0]['total_weight'] = 'Total Wt';
									$arr[0]['total_percent'] = 'Total (%)';
								}//if
								
								$str_pad = '
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr height="50">
										<td align="center">'.CONFIDENTIAL.'</td>
									</tr>
									<tr>
										<td align="center">Course ID: '.$crsName.'</td>
									</tr>';
									if($term_id != '0' || (!empty($term_id))){
										$str_pad .= '
											<tr>
												<td align="center">Term: '.$termName.'</td>
											</tr>';
									}//if
									if($exercise_type_id != '0' || (!empty($exercise_type_id))){
									$str_pad .= '
										<tr>
											<td align="center">Exercise Type: '.$exerTypName.'</td>
										</tr>';
									}//
									if($exercise_id != '0' || (!empty($exercise_id))){
									$str_pad .= '
										<tr>
											<td align="center">Exercise: '.$exerName.'</td>
										</tr>';
									}//if
								$str_pad .= '
								</table>
								<table border="1" cellpadding="0" width="700" cellspacing="0">
									<tr>
										<td align="center" width="5%">'.SER_NO.'</td>
										<td align="center" width="8%">'.CN.'</td>
										<td align="center" width="5%">'.RANK.'</td>
										<td align="center" width="10%">'.STUDENT_NAME.'</td>';	
								
								$examTotalWeight = 0;
								foreach($exerciseListArr as $exercise){ ?>
								<td align="center" width="50%" colspan="2">
									<strong><?php echo $exercise->name; ?></strong>
								</td>
								<?php
									$joinExercise = $exercise->join_course;
									if($joinExercise == 1 && $cur_user_wing_id == 2){
										$exercise->weight = $exercise->air_weight;
									}else if($joinExercise == 1 && $cur_user_wing_id == 3){
										$exercise->weight = $exercise->navy_weight;
									}else if($joinExercise == 1 && $cur_user_wing_id == 1){
										$exercise->weight = $exercise->weight;
									}else{
										$exercise->weight = $exercise->weight;
									}
									$str_pad .= '
										<td align="center" width="8%">'.$exercise->name.' ('.PERCENT.')</td>
										<td align="center" width="8%">'.$exercise->name.' ('.WT.')</td>'; 
								$examTotalWeight += $exercise->weight;
								} ?>
								<td align="center" width="5%" rowspan="2">
									<strong><?php echo TOTAL_WT.'<br />('.$examTotalWeight.')'; ?></strong>
								</td>
								<td align="center" width="5%" rowspan="2">
									<strong><?php echo TOTAL_PERCENT; ?></strong>
								</td>
								<?php 
								$str_pad .= '
									<td align="center" width="8%">'.TOTAL_WT.'</td>
									<td align="center" width="8%">'.TOTAL_PERCENT.'</td>
								</tr>';
								?>
							</tr>
							<tr class="head">
								<?php 
								foreach($exerciseListArr as $exercise){ 
									$joinExercise = $exercise->join_course;
									if($joinExercise == 1 && $cur_user_wing_id == 2){
										$exercise->weight = $exercise->air_weight;
									}else if($joinExercise == 1 && $cur_user_wing_id == 3){
										$exercise->weight = $exercise->navy_weight;
									}else if($joinExercise == 1 && $cur_user_wing_id == 1){
										$exercise->weight = $exercise->weight;
									}else{
										$exercise->weight = $exercise->weight;
									}
								
								?>
								<td align="center" width="5%">
									<strong><?php echo PERCENT; ?></strong>
								</td>
								<td align="center" width="5%">
									<strong><?php echo WT.'<br />'.$exercise->weight; ?></strong>
								</td>
								<?php }//foreach ?>
							</tr>
							<?php
							$rownum = 0;
							foreach($studentArr as $student){
								$sl = $rownum + 1;
								$class = (($rownum%2)==0) ? ' class="even"' : ' class="odd"';
								
								$sql = "SELECT * from ".DB_PREFIX."student WHERE id = '".$student->id."'";
								$studentInfoArr = $dbObj->selectDataObj($sql);
								$studentInfo = $studentInfoArr[0];
								$rnkName = getNameById('rank', $studentInfo->rank_id);
								$rnkName =  $rnkName->short_name;
							?>
							<tr <?php echo $class; ?>>
								<td align="center">
									<?php echo $sl;
										$arr[$sl]['sl'] = $sl;
									?>
								</td>
								<td align="center">
									<?php echo $studentInfo->student_id;
										$arr[$sl]['student_id'] = $studentInfo->student_id;
									?>
								</td>
								<td align="center">
									<?php echo $rnkName;
										$arr[$sl]['rank_id'] = $rnkName;
									?>
								<td>			
									<?php echo $studentInfo->official_name;
										$arr[$sl]['student_name'] = $studentInfo->official_name;
										$str_pad .= 
										'<tr>
											<td align="center">'.$sl.'</td>
											<td align="center">'.$studentInfo->student_id.'</td>
											<td align="center">'.$rnkName.'</td>
											<td>'.$studentInfo->official_name.'</td>';
									?>
								</td>
								<?php 
								$studentTotalWeight = 0;
								foreach($exerciseListArr as $exercise){ 
									$sql = "SELECT * FROM ".DB_PREFIX."marking WHERE exercise_id = '".$exercise->id."' AND course_id = '".$course_id."' AND student_id = '".$student->id."'";
									$markInfoArr = $dbObj->selectDataObj($sql);
									$markInfo = $markInfoArr[0];
									$total_percent = ($markInfo->ds_marking+$ds_marking->si_mod_marking+$ds_marking->ci_mod_marking);
									$total_weight = ($markInfo->ds_student_weight+$ds_marking->si_student_weight+$ds_marking->ci_student_weight);
									$studentTotalWeight += $total_weight;
									$studentTotalPercent = (($studentTotalWeight*100)/$examTotalWeight);
								?>
								<td align="right">			
									<?php echo view_number_two($total_percent);
										$arr[$sl][$exercise->name] = view_number_two($total_percent);
									?>
								</td>
								<td align="right">			
									<?php echo view_number_two($total_weight);
										$arr[$sl][$exercise->weight] = view_number_two($total_weight);
										
										$str_pad .= '
											<td align="right">'.view_number_two($total_percent).'</td>
											<td align="right">'.view_number_two($total_weight).'</td>';
									?>
								</td>
								<?php } ?>
								<td class="number_padding_left">	
									<?php echo view_number_two($studentTotalWeight);
										$arr[$sl]['total_weight'] = view_number_two($studentTotalWeight);
									?>
								</td>
								<td class="number_padding_left">
									<?php echo view_number_two($studentTotalPercent);
										$arr[$sl]['total_percent'] = view_number_two($studentTotalPercent);
									?>
								</td>
							<?php
								$str_pad .= '
									<td align="right">'.view_number_two($studentTotalWeight).'</td>
									<td align="right">'.view_number_two($studentTotalPercent).'</td>
								</tr>';
								$rownum++;
							}//foreach 
								$str_pad .= '
								</table>
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td height="30">
											'.GEN_BY.$cur_user_rank.' '.$cur_user_off_nam.ON. date('d M Y').AT.date('h:m:i a').'
										</td>
									</tr>
									<tr height="50">
										<td align="center">'.CONFIDENTIAL.'</td>
									</tr>
								</table>';
							?>
							</tr>							
						</table>
					</td>
				</tr>
			</table>
		</div>
	<?php 
		}//else 
		$_SESSION['progress'] = '';
		$_SESSION['progress'][0] = $downloadTitle; 
		$_SESSION['progress'][1] = $arr;
		
		//For assinging Term Report in PDF Format 
		$content = $str_pad;
		require_once("html2fpdf/html2fpdf.php");		
		
		//set path and file name of desired PDF	
		$pdf_dir_name = 'report/';	
		$report_id = $cur_user_id.'_'.date('ymdhis');	
		$pdf_file_name = $report_id.'.pdf';		
		$pdf_invoice_name = $pdf_dir_name.$pdf_file_name;
		
		//Create the object and Save PDF file
		$pdf=new HTML2FPDF();
		$pdf->SetFont('Helvetica','',6);
		$pdf->AddPage();
		$pdf->WriteHTML($content);
		/*echo '<pre>';
		print_r(htmlspecialchars($content));
		exit;*/
		$pdf->Output($pdf_invoice_name);
		$_SESSION['pdf'] = '';
		$_SESSION['pdf'] = $pdf_invoice_name;
		
	}// if action is view?>	
</div>
			
<?php
require_once("includes/footer.php");
?>