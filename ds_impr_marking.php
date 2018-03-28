<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Assign Impression Marks';
$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$action = $_REQUEST['action'];
$msg = '';

//Chek if this user is valid for this file
if($cur_user_group_id != '5'){			
	header("Location: dashboard.php");		//Only DS can give DS Impression Marks
}
	$sql = "SELECT id from ".DB_PREFIX."course WHERE wing_id = '".$cur_user_wing_id."' AND status = '0'";
		$activeCourseArr = $dbObj->selectDataObj($sql);
		$course_id = $activeCourseArr[0]->id;
		
		//Find Currently Active Term which is assigned to this DS
		$sql = "select id, ds_impr_mark, ds_impr_mark_limit from ".DB_PREFIX."term WHERE wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."' AND status = '0'";
		$activeTermArr = $dbObj->selectDataObj($sql);
		$term_id = $activeTermArr[0]->id;
		$ds_impr_mark_limit = $activeTermArr[0]->ds_impr_mark_limit;
		$ds_impr_mark = $activeTermArr[0]->ds_impr_mark;
switch($action){
	case 'view':	
	default:
		
		//Find Currently Active Course
		$sql = "SELECT id from ".DB_PREFIX."course WHERE wing_id = '".$cur_user_wing_id."' AND status = '0'";
		$activeCourseArr = $dbObj->selectDataObj($sql);
		$course_id = $activeCourseArr[0]->id;
		
		//Find Currently Active Term which is assigned to this DS
		$sql = "select id, ds_impr_mark, ds_impr_mark_limit from ".DB_PREFIX."term WHERE wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."' AND status = '0'";
		$activeTermArr = $dbObj->selectDataObj($sql);
		$term_id = $activeTermArr[0]->id;
		$ds_impr_mark_limit = $activeTermArr[0]->ds_impr_mark_limit;
		$ds_impr_mark = $activeTermArr[0]->ds_impr_mark;
		
		//Find syndicate of the DS
		$sql = "select syndicate_id from ".DB_PREFIX."ds_to_course WHERE wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."' AND term_id = '".$term_id."' AND ds_id = '".$cur_user_id."' AND syndicatetype = 0";
		$activeSynArr = $dbObj->selectDataObj($sql);
		/*echo '<pre>';
		print_r($activeSynArr);
		echo $syndicate_id = $activeSynArr[0]->syndicate_id;
		exit;*/
		$syndicate_id = $activeSynArr[0]->syndicate_id;
		//Build Exercise List Array	
		//$query = "select exr.id, exr.name, exr.weight, exr.mark from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$course_id."' AND ett.term_id = '".$term_id."' AND exr.id = ett.exercise_id order by exr.name asc";
		
		$query = "select exr.id, exr.name, exr.join_course, exr.weight, exr.air_weight, exr.navy_weight, exr.mark from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where ett.term_id = ".$term_id." AND ett.exercise_id = exr.id order by exr.name asc";
		
		$exrArr = $dbObj->selectDataObj($query);
		
		$totalExercise = sizeof($exrArr);
		$total_forwarded = 0;
		foreach($exrArr as $exerc){
			$sql = "SELECT * FROM ".DB_PREFIX."marking WHERE course_id = ".$course_id." AND term_id = '".$term_id."' AND syndicate_id = '".$syndicate_id."' AND exercise_id = '".$exerc->id."' AND status = '1' AND ci_status = '1'";
			$allForwardedArr = $dbObj->selectDataObj($sql);
			
			if(empty($allForwardedArr)){
				$forwarded_counter = 0;
			}else{
				$forwarded_counter = 1;
			}//else
			$total_forwarded += $forwarded_counter;
		}//foreach
		
		$exrId = array();
		$exrId[0] = SELECT_EXERCISE_OPT;
		if(!empty($exrArr)){			
			foreach($exrArr as $item){
				$exrId[$item->id] = $item->name;
			}//foreach
		}//if
		
		if($totalExercise == $total_forwarded){
			$query = "select std.official_name, std.student_id, std.id, std.rank_id from ".DB_PREFIX."student as std,  ".DB_PREFIX."student_to_syndicate as sts WHERE std.wing_id = '".$cur_user_wing_id."' AND std.course_id = '".$course_id."' AND sts.syndicate_id = '".$syndicate_id."' AND sts.term_id = '".$term_id."' AND sts.student_id = std.id ORDER BY std.student_id";
			$studentArr = $dbObj->selectDataObj($query);
		}else{
			$studentArr = array();
		}//else
		
		$totalNumberStudent = sizeof($studentArr);
	
		$studentId = array();
		if(!empty($studentArr)){
			foreach($studentArr as $item){
				$studentId[$item->id] = $item->student_id;
			}//foreach
		}//if
		
		//This code find position of a student		
		$sql = " SELECT distinct (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)+impr.ds_impr_weight) as total from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND mrk.course_id = '".$course_id."' AND impr.term_id = '".$term_id."' AND mrk.term_id = '".$term_id."' AND impr.syndicate_id = '".$syndicate_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.status = '1' group by mrk.student_id ORDER BY total desc";
		$numberArray = $dbObj->selectDataObj($sql);
		
		if(empty($numberArray)){
			$sql = " SELECT distinct (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)) as total from ".DB_PREFIX."marking as mrk WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.status = '1' group by mrk.student_id ORDER BY total desc";
			$numberArray = $dbObj->selectDataObj($sql);
		}
		
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
		
	case 'save':	
		
		$course_id = $_POST['course_id'];
		$syndicate_id = $_POST['syndicate_id'];
		$term_id = $_POST['term_id'];
		$trm_nam = getNameById('term', $term_id);
		$student_id = $_POST['student_id'];
		$comment = $_POST['comment'];
		$locked_datetime = date('Y-m-d H:i:s');
		
		//status indicates whether the DS gets the assign_mark field as read-only or not
		if($_POST['Submit'] == 'Forward' || $_POST['Submit'] == 'Request to Unlock'){
			$status = '1';			//  - 1 -means that the ds can not enter marks further, it is already forwarded
		}else{
			$status = '0';			//  - 0 -means it has only been saved for further update
		}
		
		if($_POST['Submit'] == 'Request to Unlock'){
			$lock_status = '2';		//  -2 - request sent to SI for unlock this marks
		}else if($_POST['Submit'] == 'Forward'){
			$lock_status = '1';		// -  1 - has been forwarded to SI 
		}else{
			$lock_status = '0';		// -  0 - has been saved for further update
		}//else
		
		if($_POST['Submit'] == 'Forward'){
		
			$query = "select * from ".DB_PREFIX."impression_marking_lock WHERE term_id = '".$term_id."' AND course_id = '".$course_id."' AND syndicate_id = '".$syndicate_id."'";
			$forwarded = $dbObj->selectDataObj($query);
			
			if(empty($forwarded)){
				$fields = array('course_id' => $course_id,
							'term_id' => $term_id,
							'syndicate_id' => $syndicate_id,
							'locked_by' => $cur_user_id,
							'locked_datetime' => $locked_datetime,
							'status' => $lock_status
							);
				$inserted = $dbObj->insertTableData("impression_marking_lock", $fields);	
			}else{
				$fields = array(
						'status' => $lock_status,
						'locked_datetime' => $locked_datetime,
						'locked_by' => $cur_user_id
						);
				$where = " course_id = '".$course_id."' AND term_id = '".$term_id."' AND syndicate_id = '".$syndicate_id."'";
				
				$update_status = $dbObj->updateTableData("impression_marking_lock", $fields, $where);	
			}//else
			
		}else if($_POST['Submit'] == 'Request to Unlock'){
			$fields = array(
						'comment' => $comment,
						'status' => $lock_status
						);
			$where = " course_id = '".$course_id."' AND term_id = '".$term_id."' AND syndicate_id = '".$syndicate_id."'";
			
			$update_status = $dbObj->updateTableData("impression_marking_lock", $fields, $where);	
		}//else
		
		//Find Student info
		$query = "select * from ".DB_PREFIX."student WHERE wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."'";
		$studentArr = $dbObj->selectDataObj($query);
		
		$number = array();
		foreach($studentArr as $student){
			$key = $student->id;
			$val = "number_".$student->id;				//number_11
			$number[$key] = $_POST[$val];				//$_POST['number_11']
		}
		
		//Find Ds_impression mark of this term 
		$query = "select * from ".DB_PREFIX."term WHERE id = '".$term_id."' AND course_id = '".$course_id."'";
		$dsImprWeightArray = $dbObj->selectDataObj($query);
		$dsImprWeight = $dsImprWeightArray[0];
		$ds_impr_weight = $dsImprWeight->ds_impr_mark;
		
		//Delete All data from ds_to_course Table of selected Syndicate/Term if no ds is selected
		$sql = 'DELETE from '.DB_PREFIX.'impression_marking where course_id='.$course_id.' AND term_id = '.$term_id.' AND syndicate_id = '.$syndicate_id;
		$delete = $dbObj->executeData($sql);
								
		//Now Save into database as a new entry
		if(!empty($student_id)){
			$sql = 'INSERT INTO '.DB_PREFIX.'impression_marking (`course_id`,`term_id`,`ds_id`,`status`,`syndicate_id`,`student_id`,`ds_impr_marking`,`ds_impr_weight`) VALUES';
					foreach($student_id as $student){
						foreach($number as $key => $value){
							if($key == $student){
								$student_number = $value;
							}//if
						}//foreach
						//now convert the ds_impression_percent_mark into impression_weight
						$converted_ds_impr_weight = ($ds_impr_weight*$student_number)/100;
						$converted_ds_impr_weight = view_number_two($converted_ds_impr_weight);
						$sql .= "('".$course_id."','".$term_id."','".$cur_user_id."','".$status."','".$syndicate_id."','".$student."','".$student_number."','".$converted_ds_impr_weight."'),";						
					}//foreach
						$sql = rtrim($sql, ",");
						$sql = $sql.';';
		}//if
			
		$inserted = $dbObj->executeData($sql);		
		if(!$inserted){
			$msg = ADD_FAILED;		
			$action = 'view';
		}else{
			if($_POST['Submit'] == 'Save'){
				$msg = $trm_nam->name.SUCCESSFULLY_SAVED;
			}else if($_POST['Submit'] == 'Forward'){
				$msg = $trm_nam->name.FORWARDED_RESULT_SI;
			}else if($_POST['Submit'] == 'Request to Unlock'){
				$msg = REQUEST_SENT.$trm_nam->name;
			}
			$url = 'ds_impr_marking.php?action=view&msg='.$msg;
			redirect($url);
		}
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
	<?php if($action=="view"){ ?>
	
		<?php if(empty($syndicate_id)){ ?>
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
						<td height="30" width="50%">
							<?php echo COURSE; ?>:
						</td>
						<td width="50%">
							<strong>
							<?php 	$course_name = getNameById('course', $course_id);
									echo $course_name->name; ?>
							</strong>
						</td>
					</tr>
					<tr>
						<td height="30" width="50%">
							<?php echo TERM; ?>:
						</td>
						<td width="50%">
							<strong>
							<?php 	$termName = getNameById('term', $term_id);
									echo $termName->name; 
									?>
							</strong>
						</td>
					</tr>
					<tr>
						<td height="30" width="50%">
							<?php echo SYNDICATE; ?>:
						</td>
						<td width="50%">
							<strong>
							<?php 	$syndicate = getNameById('syndicate', $syndicate_id);
									echo $syndicate->name; ?>
							</strong>
						</td>
					</tr>
					<tr>
						<td height="30" width="50%">
							<?php echo MARK_LIMIT; ?>:
						</td>
						<td width="50%">
							<strong>
							<?php 	echo $ds_impr_mark_limit.'%'; ?>
							</strong>
						</td>
					</tr>
				</table>
		<div id="ds_impres_mark" style="height:auto; width:700px; overflow-x:scroll;">
			<form action="ds_impr_marking.php" method="post" name="ds_impr_marking" id="ds_impr_marking" >
					<table cellpadding="0" cellspacing="0" border="0" class="module_content">
						<tr>
							<td>
								<table cellpadding="0" cellspacing="0" border="0" class="datagrid">
									<tr class="head">
										<td align="center" rowspan="2" >
											<strong><?php echo SER_NO; ?></strong>
										</td>
										<td align="center" rowspan="2">
											<strong><?php echo CN; ?></strong>
										</td>
										<td rowspan="2">
											<strong><?php echo RANK; ?></strong>
										</td>
										<td rowspan="2">
											<strong><?php echo STUDENT_NAME; ?></strong>
										</td>										
								<?php foreach($exrArr as $exercise){ ?>
										<td align="center" colspan="2">
											<strong><?php echo $exercise->name; ?></strong>
										</td>
								<?php } ?>
										<td align="center">
											<strong><?php echo SUB_TOTAL_WT; ?></strong>
										</td>
										<td align="center" rowspan="2">
											<strong><?php echo SUB_TOTAL_PERCENT; ?></strong>
										</td>
										<td align="center" colspan="2">
											<strong><?php echo DS_IMPR_MKS; ?></strong>
										</td>
										<td align="center">
											<strong><?php echo TOTAL_WT; ?></strong>
										</td>
										<td align="center" rowspan="2">
											<strong><?php echo TOTAL; ?></strong>
										</td>
										<td align="center" rowspan="2">
											<strong><?php echo GRADE; ?></strong>
										</td>
										<td align="center" rowspan="2">
											<strong><?php echo POSITION; ?></strong>
										</td>
									</tr>
									<tr class="head">									
									<?php 
										$sub_total = 0;
										foreach($exrArr as $exercise){ ?>
										
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
												
											?>
											<td align="center">
												<strong><?php echo PERCENT; ?></strong>
											</td>
											<td align="center">
												<strong><?php echo WT.'<br>'.$exercise->weight; ?></strong>
											</td>
									<?php 
											$sub_total += $exercise->weight;
										}//foreach
											$total_weight = $sub_total + $ds_impr_mark;
									?>	
										<td align="center">
											<strong><?php echo $sub_total; ?></strong>
										</td>
										<td align="center">
											<strong><?php echo PERCENT; ?></strong>
										</td>
										<td align="center">
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
												
											?>
											<strong><?php echo WT.'<br />'.$ds_impr_mark; ?></strong>
										</td>
										<td align="center">
											<strong><?php echo $total_weight; ?></strong>
										</td>
									</tr>
								<?php
								if(!empty($studentArr)){
									$rownum = 0;
									$sl = 0;
									foreach($studentArr as $student){
										$sl = $rownum + 1;
										if(($rownum%2)==0){//even
											$class = ' class="even"';									
										}else{//odd
											$class = ' class="odd"';									
										}	
									$sql = "select * from ".DB_PREFIX."rank WHERE id = '".$student->rank_id."'";
									$rankArr = $dbObj->selectDataObj($sql);
									$rank = $rankArr[0];
									$rankName = getNameById('rank', $rank->id);
								?>
								<tr <?php echo $class?>>
										<td align="center" >
											<?php echo $sl; ?>
										</td>
										<td align="center">
											<?php echo $student->student_id; ?>
											<input type="hidden" name="cn_<?php echo $sl; ?>" id="cn_<?php echo $sl; ?>" value="<?php echo $student->student_id; ?>" />
										</td>
										<td>
											<?php echo $rankName->name; ?>
										</td>
										<td>
											<?php echo $student->official_name; ?>
										</td>
										<?php 
										$sub_total_mark = 0;
										$first_impr_valid = 0;
										$sub_total_by_weight = 0;
											foreach($exrArr as $exercise){ 
										?>
										<td class="center">
										<?php
											$query = "select *, (ds_marking+si_mod_marking+ci_mod_marking) as total from ".DB_PREFIX."marking WHERE course_id = '".$course_id."' AND exercise_id = '".$exercise->id."' AND term_id = '".$term_id."' AND syndicate_id = '".$syndicate_id."' AND student_id = '".$student->id."' AND status = '1' order by id desc";
											$dsMark = $dbObj->selectDataObj($query);
											$mark = $dsMark[0];
												$impr_valid = 0;
												foreach($dsMark as $mark){
													$sub_total_mark += $mark->total;
												}//foreach
												
											$mark_ds_student_weight	= ($mark->ds_marking*$exercise->weight)/100;
											//$num_by_weight = $mark->ds_student_weight+$mark->si_student_weight+$mark->ci_student_weight;
											$num_by_weight = $mark_ds_student_weight+$mark->si_student_weight+$mark->ci_student_weight;
											
											if($num_by_weight > 0){
												$find_zero = 1;
											}else{
												$find_zero = 0;
											}//else
											
											$impr_valid = ($num_by_weight+$find_zero);
											$sub_total_by_weight += $num_by_weight;
											$first_impr_valid += $impr_valid;
											$second_impr_valid = $sub_total_by_weight+$totalExercise;
											echo view_number_two($mark->total);
										?>
										</td>
										<td class="center">
										<?php echo view_number_two($num_by_weight); ?>
										</td>
										<?php 
												//$total_mark_number += $mark_number;
											}//foreach
											$std_mark = ($sub_total_by_weight*100)/$sub_total;
											
											//Find out HIGHEST & LOWEST Possible marks for all student
											$mlimit =  view_number_two((view_number_two($std_mark) * $ds_impr_mark_limit)/100);
											$highest_val = view_number_two(view_number_two($std_mark) + $mlimit);
											$lowest_val = view_number_two(view_number_two($std_mark) - $mlimit);
										 ?>
										<td>
											<input class="input_number" type="text" name="weight_number<?php echo $student->id; ?>" id="weight_number<?php echo $sl; ?>" value="<?php echo view_number_two($sub_total_by_weight); ?>"size="3" readonly="readonly" />
										</td>
										<td>
											<input class="input_number" type="text" name="sub_student_number<?php echo $sl; ?>" id="sub_student_number<?php echo $sl; ?>" value="<?php echo view_number_two($std_mark); ?>" readonly="readonly" size="3" />
										</td>
										<td>
											<input style="border:1px solid #000000; text-align:right" type="text" name="number_<?php echo $student->id; ?>" id="number<?php echo $sl; ?>" title="Highest : <?php echo $highest_val; ?>           Lowest : <?php echo $lowest_val;?>" value="<?php 
											
											$sql = "select * from ".DB_PREFIX."impression_marking WHERE student_id = '".$student->id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."'";
											$number = $dbObj->selectDataObj($sql);
											$student_number = $number[0];
											$impr_mark = $student_number->ds_impr_marking;
											
											$converted_impr_mark = ($impr_mark*$ds_impr_mark)/100;
											$student_total_weight = $converted_impr_mark+$sub_total_by_weight;
											$student_total_percent = ($student_total_weight*100)/$total_weight;
											echo view_number_two($impr_mark);
									
											?>" size="3" onkeyup="return validateImpressionMarking('number<?php echo $sl?>');" maxlength="5" 
											
											<?php 
												if($student_number->status == '1' ){//|| $first_impr_valid != $second_impr_valid
													echo 'readonly = "readonly" class="input_number"';									
												}//if												
											?> />
										</td>
										<td>
											<input class="input_number" type="text" name="ds_number<?php echo $student->id; ?>" id="ds_number<?php echo $sl; ?>" value="<?php echo view_number_two($converted_impr_mark)?>" size="3" readonly="readonly" />
										</td>
										<td>
											<input class="input_number" type="text" name="total_number<?php echo $student->id; ?>" id="total_number<?php echo $sl; ?>" value="<?php echo view_number_two($student_total_weight); ?>" size="3" readonly="readonly" />
										</td>
										<td>
											<input class="input_number" type="text" name="total_percent_number<?php echo $student->id; ?>" id="total_percent_number<?php echo $sl; ?>" value="<?php echo view_number_two($student_total_percent); ?>" size="3" readonly="readonly" />
										</td>
										<td>
											<input class="center_input" type="text" name="grade_number<?php echo $student->id; ?>" id="grade_number<?php echo $sl; ?>" value="<?php echo findGrade($student_total_percent)?>" size="3" readonly="readonly" />
										</td>
										<td align="center">
											<input class="center_input" type="text" name="position<?php echo $student->id; ?>" id="position<?php echo $sl; ?>" value="<?php echo findPosition($numberArray, $student_total_weight); ?>" size="3" readonly="readonly" />
										</td>
									</tr>
								<?php 
										$rownum++;
									}//foreach
								?>
									<tr class="head">
										<td colspan="<?php echo ($totalExercise*2)+12; ?>">
						<?php $sql = "select * from ".DB_PREFIX."impression_marking_lock WHERE course_id = '".$course_id."' AND term_id = '".$term_id."' AND syndicate_id = '".$syndicate_id."' ";
						$submitStatus = $dbObj->selectDataObj($sql);
						$submit = $submitStatus[0];
						$locked_datetime = $submit->locked_datetime;
						$locked_date_time = explode(' ', $locked_datetime);
						$locked_date = $locked_date_time[0];
						$locked_time = $locked_date_time[1];

						$DSName = getNameById('user', $submit->locked_by);
						if(($submit->status == '0') || empty($submitStatus)){ ?>
											<input type="submit" name="Submit" class="button" value="Save"  onclick="return validateStudentImprOfDS(0);" />
											<input type="submit" name="Submit" class="button" value="Forward" onclick="return validateStudentImprOfDS(1);"/>
						<?php }else if($submit->status == '1'){	?>
											<input type="button" value="<?php echo REQUEST_TO_UNLOCK; ?>" onclick="return submitRequest();">
											<span class="sentMsg"><?php echo TERM_SENT_MESSAGE.$DSName->official_name.' on '.dateConvertion($locked_date).' at '.$locked_time; ?></span>
											<div id="submitRequest">
												<form name="submit_req" id="submit_req" action="ds_impr_marking.php" method="post">
													<textarea name="comment" id="comment" rows="5" cols="27" class="inputbox" alt="Comment"></textarea>
													<br  />
													<input type="hidden" name="action" value="save"  />
													<input type="submit" name="Submit" id="Submit"  value="Request to Unlock"  />
													<input type="button" name="btnclose" id="btnclose"  value="<?php echo CANCEL; ?>"  onclick="Close('submitRequest')" />											
												</form>						
											</div>
						<?php }else{ echo REQUEST_MESSAGE.$DSName->official_name; } ?>
										</td>
									</tr>
						<?php }else{ //if empty studentArr ?>
									<tr>
										<td colspan="<?php echo ($totalExercise*2)+12; ?>" height="30"><?php echo NOT_FORWARDED_MESSAGE; ?></td>
									</tr>
						<?php } ?>
								</table>
							</td>
						</tr>
					</table>
					<?php foreach($studentId as $id => $num_count){ ?>
						<input type="hidden" name="student_id[]" id="student_id<?php echo $num_count; ?>" value="<?php echo $id; ?>" />
					<?php } ?>		
						<input type="hidden" name="sl" id="sl" value="<?php echo $sl; ?>" />
						<input type="hidden" name="course_id" id="course_id" value="<?php echo $course_id; ?>" />
						<input type="hidden" name="syndicate_id" id="syndicate_id" value="<?php echo $syndicate_id; ?>" />
						<input type="hidden" name="term_id" id="term_id" value="<?php echo $term_id; ?>" />
						<input type="hidden" name="impr_mark" id="impr_mark" value="<?php echo $ds_impr_mark; ?>" />
						<input type="hidden" name="impr_mark_limit" id="impr_mark_limit" value="<?php echo $ds_impr_mark_limit; ?>" />
						<input type="hidden" name="t_mark" id="t_mark" value="<?php echo $sub_total+$ds_impr_mark; ?>" />
						<input type="hidden" name="total_count" id="total_count" value="<?php echo $totalNumberStudent; ?>" />
						<input type="hidden" name="action" value="save" />
					</form>
		</div>
		
	<?php }//else - active course & term		
		}//else - action = view
	?>
</div>
			
<?php require_once("includes/footer.php"); ?>