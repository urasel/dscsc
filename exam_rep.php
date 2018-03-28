<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Exercise Results';
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
	header("Location: dashboard.php");		//Only DS & SI can view Exercise reports
}
//echo $action;exit;
switch($action){
	case 'view':	
	default:
		
		if(isset($_POST['submit'])){
			if($cur_user_group_id == '4'){
				$course_id = $_REQUEST['course_id'];
				$term_id = $_REQUEST['term_id'];
				$syndicate_id = $_REQUEST['syndicate_id'];
			}//if
			$exercise_id = $_REQUEST['exercise_id'];
			
			//Find Exercise Info
			$sql = "select * from ".DB_PREFIX."exercise WHERE id = '".$exercise_id."' ";
			$exrArr = $dbObj->selectDataObj($sql);
			
			$exercise = $exrArr[0];
			
			$joinExercise = $exercise->join_course;
			if($joinExercise == 1 && $cur_user_wing_id == 2){
				$weight = $exercise->air_weight;
			}else if($joinExercise == 1 && $cur_user_wing_id == 3){
				$weight = $exercise->navy_weight;
			}else if($joinExercise == 1 && $cur_user_wing_id == 1){
				$weight = $exercise->weight;
			}else{
				$weight = $exercise->weight;
			}
			
		}//if
		
		if($_POST['submit'] == 'Sort By Position'){
			$sort = 'posn';
		}else if($_POST['submit'] == 'Sort By C/N'){
			$sort = 'cn';
		}else{
			$sort = 'general';
		}
		
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
			$sql = "select syndicate_id from ".DB_PREFIX."ds_to_course WHERE wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."' AND term_id = '".$term_id."' AND ds_id = '".$cur_user_id."' AND syndicatetype = 0";
			$activeSynArr = $dbObj->selectDataObj($sql);
			$syndicate_id = $activeSynArr[0]->syndicate_id;
			
			//Build Exercise List Array	
			//$query = "select exr.id, exr.name from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$course_id."' AND ett.term_id = '".$term_id."' AND exr.id = ett.exercise_id order by exr.name asc";
			
			$query = "select exr.id, exr.name from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where ett.term_id = ".$term_id." AND ett.exercise_id = exr.id order by exr.name asc";
			
			$exrArr = $dbObj->selectDataObj($query);
	
			$exrId = array();
			$exrId[0] = SELECT_EXERCISE_OPT;
			if(!empty($exrArr)){			
				foreach($exrArr as $item){
					$exrId[$item->id] = $item->name;
				}	
			}			
			$exerciseList_opt = formSelectElement($exrId, $exercise_id, 'exercise_id', ' onchange = processFunction("ds_exr_result")');
			
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
					}	
				}			
				$courseList_opt = formSelectElement($courseId, $course_id, 'course_id', 'onchange = processFunction("si_result_term")');
				
				//Build Term List Array	
				$query = "select id, name from ".DB_PREFIX."term WHERE course_id = '".$course_id."' order by name asc";
				$termArr = $dbObj->selectDataObj($query);
				$termId = array();
				$termId[0] = SELECT_TERM_OPT;
				if(!empty($termArr)){			
					foreach($termArr as $item){
						$termId[$item->id] = $item->name;
					}	
				}			
				$termList_opt = formSelectElement($termId, $term_id, 'term_id', 'onchange = processFunction("si_result_exr")');
						
				//Build Exercise List Array	
				$query = "select exr.id, exr.name from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$course_id."' AND ett.term_id = '".$term_id."' AND exr.id = ett.exercise_id order by exr.name asc";
				$exrArr = $dbObj->selectDataObj($query);
		
				$exrId = array();
				$exrId[0] = SELECT_EXERCISE_OPT;
				if(!empty($exrArr)){			
					foreach($exrArr as $item){
						$exrId[$item->id] = $item->name;
					}	
				}			
				$exerciseList_opt = formSelectElement($exrId, $exercise_id, 'exercise_id');
				
				//Build Syndicate List Array	
				$query = "select syn.id, syn.name from ".DB_PREFIX."syndicate as syn, ".DB_PREFIX."syndicate_to_course as stc where syn.wing_id = ".$cur_user_wing_id." AND stc.course_id = '".$course_id."' AND syn.id = stc.syndicate_id order by syn.name asc";
				$synArr = $dbObj->selectDataObj($query);
		
				$synId = array();
				$synId[0] = SELECT_SYNDICATE_OPT;
				if(!empty($synArr)){			
					foreach($synArr as $item){
						$synId[$item->id] = $item->name;
					}	
				}			
				$synList_opt = formSelectElement($synId, $syndicate_id, 'syndicate_id');
			}//if not empty courseArray
		}//else if
		
		//Build Exercise Info
		$sql = "select * from ".DB_PREFIX."exercise WHERE id = '".$exercise_id."'";
		$sel_exr_arr = $dbObj->selectDataObj($sql);
		$sel_exr = $sel_exr_arr[0];
		$marking_type = $sel_exr->marking_type;

		//Find student's marks -- marks which has been assigned to the student of this course and this term
		if($cur_user_group_id == '5'){
			if($_POST['submit'] == 'Sort By Position'){
				$sql = "select *, (ds_marking+si_mod_marking+ci_mod_marking) as total from ".DB_PREFIX."marking WHERE course_id = '".$course_id."' AND syndicate_id = '".$syndicate_id."' AND term_id = '".$term_id."' AND exercise_id = '".$exercise_id."' AND status = '1' GROUP BY mrk.student_id ORDER BY total desc, student_id ";
			}else{
				$sql = "select mrk.* from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."student as std WHERE mrk.course_id = '".$course_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.term_id = '".$term_id."' AND mrk.exercise_id = '".$exercise_id."' AND mrk.status = '1' AND mrk.student_id = std.id GROUP BY mrk.student_id ORDER BY std.student_id";
			}//else
		}else if($cur_user_group_id == '4'){
			
			if($_POST['submit'] == 'Sort By Position'){
				if($syndicate_id == '0'){
					$sql = "select *, (ds_marking+si_mod_marking+ci_mod_marking) as total from ".DB_PREFIX."marking WHERE course_id = '".$course_id."' AND term_id = '".$term_id."' AND exercise_id = '".$exercise_id."' AND status = '1' GROUP BY student_id ORDER BY total desc, student_id ";
				}else{
					$sql = "select *, (ds_marking+si_mod_marking+ci_mod_marking) as total from ".DB_PREFIX."marking WHERE course_id = '".$course_id."' AND term_id = '".$term_id."' AND exercise_id = '".$exercise_id."' AND syndicate_id = '".$syndicate_id."' AND status = '1' GROUP BY student_id ORDER BY total desc, student_id ";
				}//else
			}else if($_POST['submit'] == 'Sort By C/N'){
				if($syndicate_id == '0'){
					$sql = "select mrk.* from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."student as std WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.exercise_id = '".$exercise_id."' AND mrk.status = '1' AND mrk.student_id = std.id GROUP BY mrk.student_id ORDER BY std.student_id asc";
				}else{
					$sql = "select mrk.* from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."student as std WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.exercise_id = '".$exercise_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.status = '1' AND mrk.student_id = std.id GROUP BY mrk.student_id ORDER BY std.student_id";
				}//else
			}else{
				if($syndicate_id == '0'){
					$sql = "select mrk.* from ".DB_PREFIX."marking as mrk,  ".DB_PREFIX."syndicate as syn, ".DB_PREFIX."student as s WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.exercise_id = '".$exercise_id."' AND mrk.status = '1' AND mrk.syndicate_id = syn.id AND mrk.student_id = s.id GROUP BY mrk.student_id  ORDER BY syn.name asc, s.student_id asc ";
				}else{
					$sql = "select m.* from ".DB_PREFIX."marking as m, ".DB_PREFIX."student as s WHERE m.course_id = '".$course_id."' AND m.term_id = '".$term_id."' AND m.exercise_id = '".$exercise_id."' AND m.syndicate_id = '".$syndicate_id."' AND m.status = '1' AND m.student_id = s.id GROUP BY m.student_id ORDER BY s.student_id";
					//echo $sql;exit;
				}//else
			}//else if
		}//else if
		$studentArr = $dbObj->selectDataObj($sql);
		//echo '<pre>';
		//print_r($studentArr);exit;
		$total_student = sizeof($studentArr);
		
		//Build Position Array
		if($cur_user_group_id == '5'){
			$sql = " SELECT distinct (ds_marking+si_mod_marking+ci_mod_marking) as total from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' AND syndicate_id = '".$syndicate_id."' ORDER BY total DESC";
		}else if($cur_user_group_id == '4'){
			if($syndicate_id == '0' || $syndicate_id == ""){
				$add_syndicate = "";
			}else{
				$add_syndicate = " AND syndicate_id = '".$syndicate_id."'";
			}
			 
			$sql = "SELECT distinct (ds_marking+si_mod_marking+ci_mod_marking) as total from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' AND status = '1' ".$add_syndicate." ORDER BY total DESC";
		}
		$numberArray = $dbObj->selectDataObj($sql);
		
		if(!empty($numberArray)){
			$i = 0;
			foreach($numberArray as $item){
				$numberArray[$i]->position = $i+1;
				$i++; 
			}//foreach
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
				<h1><?php echo EXAM_REPORTS; ?></h1>
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
		if($cur_user_group_id == '4'){
			$ind = 1;
		}else if($cur_user_group_id == '5'){
			$ind = 0;
		}
	if((($cur_user_group_id == 5) && (empty($syndicate_id))) || (($cur_user_group_id == 4) && (empty($courseArray)))){ ?>
		<table cellpadding="0" cellspacing="0" border="0" width="100%" class="module_content">
				<tr>
					<td height="30" colspan="2">
						<?php echo ERROR_MSG; ?>
					</td>
				</tr>
			</table>
	<?php }else{ ?>
		<div id="exr_result_container">
			<div id="ds_result_input">
			<form action="exam_rep.php" method="post" name="exam_rep" id="exam_rep" onsubmit="return validateExerciseResult(<?php echo $ind; ?>);">
				<table cellpadding="0" cellspacing="0" border="0" width="250" class="module_content">
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
									echo $courseList_opt;
									echo '<span class="required_field"> *</span>';
								}
							?>
							
							</strong>
						</td>
					</tr>
					<tr>
						<td height="30" width="30%">
							<?php echo TERM; ?>:
						</td>
						<td width="70%">
						<div id="term_display">
							<strong>
							<?php 	
								if($cur_user_group_id == '5'){
									$term = getNameById('term', $term_id);
									echo $term->name;
									echo '<input type="hidden" name="term_id" id="term_id" value="'.$term_id.'" />';
								}else if($cur_user_group_id == '4'){
									echo $termList_opt;
									echo '<span class="required_field"> *</span>';
								}
							?>
							</strong>
						</div>
						</td>
					</tr>
					<tr>
						<td height="30" width="30%">
							<?php echo SYNDICATE; ?>:
						</td>
						<td width="70%">
							<div id="syndicate_display">
								<strong>
								<?php 	
									if($cur_user_group_id == '5'){
										$syndicate = getNameById('syndicate', $syndicate_id);
										echo $syndicate->name;
									}else if($cur_user_group_id == '4'){
										echo $synList_opt;
									}
								?>
								</strong>
							</div>
						</td>
					</tr>
					<tr>
						<td height="30" width="30%">
							<?php echo EXERCISE; ?>:
						</td>
						<td width="70%">
						<div id="loaderContainer"></div>
						<div id="exr_display">
							<?php echo $exerciseList_opt; ?>
							<span class="required_field"> *</span>
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
			</div>
		
		</div>
		<?php 
		}//if not empty activeCourse or activeTerm
		if (isset($_POST['submit'])){ ?>
			<table cellpadding="0" cellspacing="0" border="0" class="module_content" style="padding-top:25px;width:700px;">
				<tr>
					<td>
					<?php 
						if(!empty($studentArr)){ 
						
						?>
						<table cellpadding="0" cellspacing="0" border="0" width="100%">
							<tr>
								<td width="80%" height="30">
								<?php 
									if($cur_user_group_id == '4'){ ?>
										<a href="download_reports.php"><img src="images/xls.ico" title="Download Report in MS-Excel Format" /></a>
										<a href="download.php?action=getpdf"><img src="images/pdf.png" title="Download Report in PDF Format" height="16" width="16" /></a>
										<?php 
											if($syndicate_id == '0'){
												$syndt = '';
											}else{
												$syndt = '&syndicate_id='.$syndicate_id;
											}
										?>
										<a class="print_button" href="exam_rep_view.php?course_id=<?php echo $course_id;?>&term_id=<?php echo $term_id; echo $syndt;?>&exercise_id=<?php echo $exercise_id;?>&sort=<?php echo $sort;?>" target="_blank"><img src="images/print.png" title="Print" height="16" width="16" /></a>
									<?php }else{ ?>
										<a class="print_button" href="exam_rep_view.php?course_id=<?php echo $course_id;?>&term_id=<?php echo $term_id;?>&syndicate_id=<?php echo $syndicate_id;?>&exercise_id=<?php echo $exercise_id;?>&sort=<?php echo $sort;?>" target="_blank"><img src="images/print.png" title="Print" height="16" width="16" /></a>
									<?php } ?>
								</td>
								<td align="right" width="10%">
								<form action="exam_rep.php" method="post" name="exam_rep" id="exam_rep">
									<input type="submit" name="submit" id="submit" value="Sort By C/N" />
									<input type="hidden" name="course_id" id="course_id" value="<?php echo $course_id; ?>" />
									<input type="hidden" name="term_id" id="term_id" value="<?php echo $term_id; ?>" />
									<input type="hidden" name="syndicate_id" id="syndicate_id" value="<?php echo $syndicate_id; ?>" />
									<input type="hidden" name="exercise_id" id="exercise_id" value="<?php echo $exercise_id; ?>" />
									<input type="hidden" name="action" value="view" />
								</form>
								</td>
								<td align="right" width="10%">
								<form action="exam_rep.php" method="post" name="exam_rep" id="exam_rep">
									<input type="submit" name="submit" id="submit" value="Sort By Position" />
									<input type="hidden" name="course_id" id="course_id" value="<?php echo $course_id; ?>" />
									<input type="hidden" name="term_id" id="term_id" value="<?php echo $term_id; ?>" />
									<input type="hidden" name="syndicate_id" id="syndicate_id" value="<?php echo $syndicate_id; ?>" />
									<input type="hidden" name="exercise_id" id="exercise_id" value="<?php echo $exercise_id; ?>" />
									<input type="hidden" name="action" value="view" />
								</form>
								</td>
							</tr>
						</table>
				<?php }// if not empty student Arr ?>
						<table cellpadding="0" cellspacing="0" border="0" align="center" width="700">
							<tr align="center">
								<td height="40" colspan="2"><strong><?php echo CONFIDENTIAL;?></strong></td>
							</tr>
							<tr align="center">
								<td height="20" colspan="2">
								<strong>
									<?php 
									$crsName = getNameById('course', $course_id);
									echo strtoupper($crsName->name);
									?>
								</strong>
								</td>
							</tr>
							<tr align="center">
								<td height="20" colspan="2"><strong><?php 
								$exrName = getNameById('exercise', $exercise_id);
								//echo '<pre>';
								//print_r($exrName);
								$joinExercise = $exrName->join_course;
								if($joinExercise == 1 && $cur_user_wing_id == 2){
									$exrName->weight = $exrName->air_weight;
								}else if($joinExercise == 1 && $cur_user_wing_id == 3){
									$exrName->weight = $exrName->navy_weight;
								}else if($joinExercise == 1 && $cur_user_wing_id == 1){
									$exrName->weight = $exrName->weight;
								}else{
									$exrName->weight = $exrName->weight;
								}
								echo strtoupper(EXERCISE_RES_SHT.$exrName->name);?></strong></td>
							</tr>
							<tr align="center">
								<td height="20" colspan="2"><strong><?php
								$trmName = getNameById('term', $term_id);
								echo strtoupper($trmName->name).$syndicate_name; ?></strong></td>
							</tr>
							<tr>
								<td height="30" align="left"><?php echo EXERCISE_ID.': <strong>'.$exrName->exercise_id; ?></strong></td>
								<td align="right"><?php $wngName = getNameById('wing', $cur_user_wing_id);
								echo WING.': <strong>'.$wngName->name; ?></strong></td>
							</tr>
							<tr>
								<td height="30" align="left"><?php echo EXERCISE_NAME.': <strong>'.$exrName->name; ?></strong></td>
								<td align="right"><?php if($marking_type == '2'){echo MARKS.': <strong>'.$exrName->mark;} ?></strong></td>
							</tr>
							<tr>
								<td height="30" align="left"><?php 
								$type = getNameByid('exercise_type', $exrName->type_id);
								if($exrName->marking_type == 1){
									$ass_sys = PERCENT_BASED;
								}else if($exrName->marking_type == 2){
									$ass_sys = MARK_BASED;
								}
								$joinExercise = $exrName->join_course;
								if($joinExercise == 1 && $cur_user_wing_id == 2){
									$exrName->weight = $exrName->air_weight;
								}else if($joinExercise == 1 && $cur_user_wing_id == 3){
									$exrName->weight = $exrName->navy_weight;
								}else if($joinExercise == 1 && $cur_user_wing_id == 1){
									$exrName->weight = $exrName->weight;
								}else{
									$exrName->weight = $exrName->weight;
								}
								echo EXERCISE_TYPE.': <strong>'.$type->name.' ('.$ass_sys.')'; ?></strong></td>
								<td align="right"><?php echo WEIGHT.': <strong>'.$exrName->weight; ?></strong></td>
							</tr>
						</table>
						<table cellpadding="0" cellspacing="0" border="0" class="datagrid">
							<tr class="head">
								<td align="center" width="5%" rowspan="2">
									<strong><?php echo SER_NO; ?></strong>
								</td>
								<td align="center" width="10%" rowspan="2">
									<strong><?php echo CN; ?></strong>
								</td>
								
								
								<?php 
								
								$crsNam = getNameById("course", $course_id);
								$crsName = $crsNam->name;
								$exrNam = getNameById("exercise", $exercise_id);
								$exrName = $exrNam->name;
								$termNam = getNameById("term", $term_id);
								$termName = $termNam->name;
								
								$downloadTitle[0] = CONFIDENTIAL."\n";
								$downloadTitle[1] = 'Course ID: '.$crsName."\n";
								$downloadTitle[2] = EXERCISE_RES_SHT.' '.$exrName."\n";
								$downloadTitle[3] = 'Term: '.$termName."\n";
								$downloadTitle[4] = 'Exercise ID: '.$exrNam->exercise_id."\n";
								$downloadTitle[5] = 'Exercise Name: '.$exrNam->name."\n";
								$downloadTitle[6] = 'Exercise Type: '.$type->name.' ('.$ass_sys.")\n";
								$downloadTitle[7] = 'Wing: '.$wngName->name."\n";
								if($marking_type == '2'){
									$downloadTitle[8] = 'Mks: '.$exrNam->mark."\n";
									$has_marks = 'Mks: '.$exrNam->mark;
								}
								$joinExercise = $exrNam->join_course;
								if($joinExercise == 1 && $cur_user_wing_id == 2){
									$exrNam->weight = $exrNam->air_weight;
								}else if($joinExercise == 1 && $cur_user_wing_id == 3){
									$exrNam->weight = $exrNam->navy_weight;
								}else if($joinExercise == 1 && $cur_user_wing_id == 1){
									$exrNam->weight = $exrName->weight;
								}else{
									$exrNam->weight = $exrNam->weight;
								}
								
								
								$downloadTitle[9] = 'Wt: '.$exrNam->weight."\n";

								$str_pad = '
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr height="50">
										<td align="center">'.CONFIDENTIAL.'</td>
									</tr>
									<tr>
										<td align="center">'.$crsName.'</td>
									</tr>
									<tr>
										<td align="center">'.EXERCISE_RES_SHT.': '.$exrName.'</td>
									</tr>
									<tr>
										<td align="center">'.$termName.'</td>
									</tr>
								</table>
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr height="50">
										<td align="left" width="50%">Exercise ID : '.$exrNam->exercise_id.'</td>
										<td align="right" width="50%">Wing : '.$wngName->name.'</td>
									</tr>
									<tr>
										<td align="left" width="50%">Exercise Name : '.$exrNam->name.'</td>
										<td align="right" width="50%">'.$has_marks.'</td>
									</tr>
									<tr>
										<td align="left" width="50%">Exercise Type : '.$type->name.' ('.$ass_sys.')</td>
										<td align="right" width="50%">Wt : '.$exrNam->weight.'</td>
									</tr>
								</table>
								<table border="1" cellpadding="0" cellspacing="0" width="1000">
									<tr>
										<td align="center" width="5%">
											'.SER_NO.'
										</td>
										<td align="center" width="10%">
											'.CN.'
										</td>';
										
								if($cur_user_group_id == '4' && $syndicate_id == 0){ ?>
								<td align="center" width="10%" rowspan="2">
									<strong><?php echo SYNDICATE; ?></strong>
								</td>
								<?php 
									$str_pad .= '
										<td align="center" width="10%">
											'.SYNDICATE.'
										</td>';
								} 
								?>
								<td width="5%" rowspan="2">
									<strong><?php echo RANK; ?></strong>
								</td>
								<td width="10%" rowspan="2">
									<strong><?php echo STUDENT_NAME; ?></strong>
								</td>
								<td colspan="2" width="10%" align="center">
									<strong><?php echo DS_FROM; ?></strong>
								</td>
								<td colspan="2" align="center" width="10%">
									<strong><?php echo SI_NUMBER_PERCENT; ?></strong>
								</td>
								<td colspan="2" align="center" width="10%">
									<strong><?php echo CI_NUMBER_PERCENT; ?></strong>
								</td>
								<td colspan="2" width="10%" align="center">
									<strong><?php echo MOD; ?></strong>
								</td>
								<td rowspan="2" width="5%">
									<strong><?php echo GRADE; ?></strong>
								</td>
								<td rowspan="2" width="5%">
									<strong><?php echo POSITION; ?></strong>
								</td>
							</tr>
							<tr class="head">
								<td align="center">
									<strong><?php echo PERCENT; ?></strong>
								</td>
								<td align="center">
									<strong><?php echo WT; ?></strong>
								</td>
								<td align="center">
									<strong><?php echo PERCENT; ?></strong>
								</td>
								<td align="center">
									<strong><?php echo WT; ?></strong>
								</td>
								<td align="center">
									<strong><?php echo PERCENT; ?></strong>
								</td>
								<td align="center">
									<strong><?php echo WT; ?></strong>
								</td>
								<td align="center">
									<strong><?php echo PERCENT; ?></strong>
								</td>
								<td align="center">
									<strong><?php echo WT.' ('.$weight.')'; ?></strong>
								</td>
							</tr>
						<?php 
						$str_pad .= '
								<td width="10%">'.RANK.'</td>
								<td width="15%">'.STUDENT_NAME.'</td>
								<td width="10%" align="center">'.DS_FROM.' ('.PERCENT.')'.'</td>
								<td width="10%" align="center">'.DS_FROM.' ('.WT.')</td>
								<td align="center" width="10%">'.SI_NUMBER_PERCENT.'('.PERCENT.')</td>
								<td align="center" width="10%">'.SI_NUMBER_PERCENT.'('.WT.')</td>
								<td align="center" width="10%">'.CI_NUMBER_PERCENT.'('.PERCENT.')</td>
								<td align="center" width="10%">'.CI_NUMBER_PERCENT.'('.WT.')</td>
								<td width="10%" align="center">'.MOD.'('.PERCENT.')</td>
								<td width="10%" align="center">'.MOD.'('.WT.')</td>
								<td width="5%">'.GRADE.'</td>
								<td width="5%">'.POSITION.'</td>
							</tr>';
						//For downloading Reports as XLS format
						//if group_id == 4 --->>> Only SI can download the reprot
						if($cur_user_group_id == '4'){
							$_SESSION['report_type'] = 'exercise';
							$arr[0]['sl'] = 'Ser No';
							$arr[0]['student_id'] = 'C/N';
							if($syndicate_id == 0){ 
								$arr[0]['syndicate_name'] = 'Syndicate';
							}//if
							$arr[0]['rank_id'] = 'Rank';
							$arr[0]['student_name'] = 'Student Name';
							$arr[0]['ds_percent'] = 'DS (%)';		
							$arr[0]['ds_wt'] = 'DS (Wt)';		
							$arr[0]['si_percent'] = 'SI (%)';
							$arr[0]['si_wt'] = 'SI (Wt)';
							$arr[0]['ci_percent'] = 'CI (%)';
							$arr[0]['ci_wt'] = 'CI (Wt)';
							$arr[0]['mod_percent'] = 'Mod (%)';
							$arr[0]['mod_wt'] = 'Mod (Wt)';
							$arr[0]['grade'] = 'Grade';
							$arr[0]['position'] = 'Posn';
						}//if	
							
						if(!empty($studentArr)){
							$rownum = 0;
							$sl = 0;
							$total_number = 0;
							
							foreach($studentArr as $student){
								$sl = $rownum + 1;
								if(($rownum%2)==0){//even
									$class = ' class="even"';									
								}else{//odd
									$class = ' class="odd"';									
								}
							
							//Find number of each student exercise if already inserted in same term & course
							if($cur_user_group_id == '5'){
								$sql = "select * from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise_id."' AND student_id = '".$student->student_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' AND status = '1' order by id desc";
							}else if($cur_user_group_id == '4'){
								$sql = "select * from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise_id."' AND student_id = '".$student->student_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' AND status = '1' order by id desc";
							}
							
							$number = $dbObj->selectDataObj($sql);
							$student_number = $number[0];
							//echo '<pre>';
							//print_r($student_number);
							
							$from_ds = $student_number->ds_marking;
							//$from_ds_weight = $student_number->ds_student_weight;
							$from_ds_weight = ($from_ds*$weight)/100;
							$si_mod = $student_number->si_mod_marking;
							$si_sign = ($student_number->si_sign == '+')?$student_number->si_sign:'';
							$conv_si_mod = $student_number->si_student_weight;
							$ci_mod = $student_number->ci_mod_marking;
							$ci_sign = ($student_number->ci_sign == '+')?$student_number->ci_sign:'';
							$conv_ci_mod = $student_number->ci_student_weight;
							$total_mod = ($student_number->ds_marking+$student_number->si_mod_marking+$student_number->ci_mod_marking);
							$total_mod_weight = ($total_mod*$weight)/100;
							
							//Find Info of this student
							$sql = "select * from ".DB_PREFIX."student WHERE id = '".$student->student_id."' ";
							$stuInfoArr = $dbObj->selectDataObj($sql);
							$stuInfo = $stuInfoArr[0];
							$student_name = $stuInfo->official_name;
							$rnk_name = getNameById('rank', $stuInfo->rank_id);
							$rnk_name = $rnk_name->short_name;
							
							//Find syndicate of student
							$sql = "select syndicate_id from ".DB_PREFIX."student_to_syndicate WHERE student_id = '".$student->student_id."' AND term_id = '".$term_id."' ";
							$synArr = $dbObj->selectDataObj($sql);
							$syndicate = $synArr[0];
							$syndicateName = getNameById('syndicate', $syndicate->syndicate_id);
							
							?>
							<tr <?php echo $class; ?>>
								<td align="center">
									<?php echo $sl; 
										$arr[$sl]['sl'] = $sl;
									?>
								</td>
								<td align="center">
									<?php echo $stuInfo->student_id;
										$arr[$sl]['student_id'] = $stuInfo->student_id;
									?>
								</td>
								<?php 
								$str_pad .= '						
								<tr>
									<td>'.$sl.'</td>
									<td>'.$stuInfo->student_id.'</td>';
								if($cur_user_group_id == '4' && $syndicate_id == 0){ ?>
								<td align="center">
									<?php echo $syndicateName->name; 
										$arr[$sl]['syndicate_name'] = $syndicateName->name; 
									?>
								</td>
								<?php 
								$str_pad .= '						
									<td width="10%">'.$syndicateName->name.'</td>';
								} ?>
								<td class="general_padding">
									<?php echo $rnk_name; 
										$arr[$sl]['rank_id'] = $rnk_name; 
									?>
								<td class="general_padding">			
									<?php echo $student_name; 
										$arr[$sl]['student_name'] = $student_name; 
									?>
								</td>
								<td align="center">
									<?php echo view_number_two($from_ds); 
										$arr[$sl]['ds_percent'] = view_number_two($from_ds);
									?>
								</td>
								<td align="center">
									<?php echo view_number_two($from_ds_weight);
										$arr[$sl]['ds_wt'] = view_number_two($from_ds_weight);
									?> 
								</td>
								<td align="center">
									<?php echo $si_sign.view_number_two($si_mod);
										$arr[$sl]['si_percent'] = $si_sign.view_number_two($si_mod);
									?> 
								</td>
								<td align="center">
									<?php echo $si_sign.view_number_two($conv_si_mod);
										$arr[$sl]['si_wt'] = $si_sign.view_number_two($conv_si_mod);
									?> 
								</td>
								<td align="center">
									<?php echo $ci_sign.view_number_two($ci_mod);
										$arr[$sl]['ci_percent'] = $ci_sign.view_number_two($ci_mod);
									?> 
								</td>
								<td align="center">
									<?php echo $ci_sign.view_number_two($conv_ci_mod);
										$arr[$sl]['ci_wt'] = $ci_sign.view_number_two($conv_ci_mod);
									?> 
								</td>
								<td align="center">
									<?php echo view_number_two($total_mod);
										$arr[$sl]['mod_percent'] = view_number_two($total_mod);
										$total_number += view_number_two($total_mod);
									?> 
								</td>
								<td align="center">
									<?php echo view_number_two($total_mod_weight);
										$arr[$sl]['mod_wt'] = view_number_two($total_mod_weight);
									?> 
								</td>
								<td style="padding-left:15px;">
									<?php echo findGrade($total_mod);
										$arr[$sl]['grade'] = findGrade($total_mod);
									?>
								</td>
								<td align="center">
									<?php echo findPosition($numberArray, $total_mod);
										$arr[$sl]['position'] = findPosition($numberArray, $total_mod);
									?>
								</td>
							</tr>
							<?php	
								$str_pad .= 
								'<td class="general_padding">'.$rnk_name.'</td>
								<td class="general_padding">'.$student_name.'</td>
								<td align="center">'.view_number_two($from_ds).'</td>
								<td align="center">'.view_number_two($from_ds_weight).'</td>
								<td align="center">'.$si_sign.view_number_two($si_mod).'</td>
								<td align="center">'.$si_sign.view_number_two($conv_si_mod).'</td>
								<td align="center">'.$ci_sign.view_number_two($ci_mod).'</td>
								<td align="center">'.$ci_sign.view_number_two($conv_ci_mod).'</td>
								<td align="center">'.view_number_two($total_mod).'</td>
								<td align="center">'.view_number_two($total_mod_weight).'</td>
								<td style="padding-left:15px;">'.findGrade($total_mod).'</td>
								<td align="center">'.findPosition($numberArray, $total_mod).'</td>
							</tr>';
								$rownum++;
							}//foreach
							
							$avg_number = $total_number/$total_student;
							$colspan = ($syndicate_id == 0) ? 11 : 10;
							$hrcolspan = $colspan+4;
						?>
							<tr>
								<td colspan="<?php echo $hrcolspan; ?>+"><hr /></td>
							</tr>
							<tr>
								<td height="20" colspan="<?php echo $colspan; ?>" align="right" style="padding-right:15px;">
									<strong><?php echo AVG_MARKS; ?></strong>
								</td>
								<td colspan="4" style="padding-left:8px;">
									<strong><?php echo view_number_two($avg_number); ?> %</strong>
								</td>
							</tr>
							<tr>
								<td colspan="<?php echo $hrcolspan; ?>+"><hr /></td>
							</tr>					
						<?php 
						$str_pad .= '
									<tr>
										<td height="30" colspan="'.$colspan.'">
											'.AVG_MARKS.'
										</td>
										<td colspan="4">
											'.view_number_two($avg_number).'
										</td>
									</tr>
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
					}else{	//if empty studentArr ?>
							<tr>
								<td height="30" colspan="9"><?php if($cur_user_group_id == '4'){echo EMPTY_DATA;}else if($cur_user_group_id == '5'){echo EXERCISE_NOT_FORWARDED;} ?></td>
							</tr>
					<?php }	?>
						</table>
					</td>
				</tr>
			</table>
		
		<?php  } //if submited 
		
		//For assinging Term Report in XLS Format 
		$_SESSION['exercise'] = '';
		$_SESSION['exercise'][0] = $downloadTitle;
		$_SESSION['exercise'][1] = $arr;
		$_SESSION['exercise'][2] = $avg_number;
		$_SESSION['report_name'] = 'Report of ';
		
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
		$pdf->Output($pdf_invoice_name);

		$_SESSION['pdf'] = '';
		$_SESSION['pdf'] = $pdf_invoice_name;

	}//if action == view ?>	
</div>
			
<?php
require_once("includes/footer.php");
?>
