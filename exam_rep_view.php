<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}

$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$cur_user_off_nam = $usr[0]->official_name;
$cur_user_rank = getNameById('rank',$usr[0]->rank_id);
$cur_user_rank = $cur_user_rank->name;
$action = $_REQUEST['action'];

//Chek if this user is valid for this file
if($cur_user_group_id != '5' && $cur_user_group_id != '4'){			
	header("Location: dashboard.php");		//Only DS & SI can view Exercise reports
}

switch($action){
	case 'view':	
	default:
		
			$course_id = $_GET['course_id'];
			$term_id = $_REQUEST['term_id'];
			$syndicate_id = $_REQUEST['syndicate_id'];
			$exercise_id = $_REQUEST['exercise_id'];
			$sort = $_REQUEST['sort'];
			
			//Find Exercise Info
			$sql = "select * from ".DB_PREFIX."exercise WHERE id = '".$exercise_id."' ";
			$exrArr = $dbObj->selectDataObj($sql);
			$exercise = $exrArr[0];
			$weight = $exercise->weight;
			$marking_type = $exercise->marking_type;
			
			$synName = getNameById('syndicate', $syndicate_id);
			if($syndicate_id == '' || $syndicate_id == 0){
				$syndicate_name = '';
			}else{
				$syndicate_name = ' ('.$synName->name.')';
			}
		
		//Find student's marks -- marks which has been assigned to the student of this course and this term
		if($cur_user_group_id == '5'){
			if($sort == 'posn'){
				$sql = "select *, (ds_marking+si_mod_marking+ci_mod_marking) as total from ".DB_PREFIX."marking WHERE course_id = '".$course_id."' AND syndicate_id = '".$syndicate_id."' AND term_id = '".$term_id."' AND exercise_id = '".$exercise_id."' AND status = '1' ORDER BY total desc, student_id ";
			}else{
				$sql = "select mrk.* from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."student as std  WHERE mrk.course_id = '".$course_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.term_id = '".$term_id."' AND mrk.exercise_id = '".$exercise_id."' AND mrk.status = '1' AND mrk.student_id = std.id GROUP BY mrk.student_id ORDER BY std.student_id";
			}//else
		}else if($cur_user_group_id == '4'){
			if($sort == 'posn'){
				if($syndicate_id == '0' || $syndicate_id == ""){
					$sql = "select *, (ds_marking+si_mod_marking+ci_mod_marking) as total from ".DB_PREFIX."marking WHERE course_id = '".$course_id."' AND term_id = '".$term_id."' AND exercise_id = '".$exercise_id."' AND status = '1' ORDER BY total desc, student_id ";
				}else{
					$sql = "select *, (ds_marking+si_mod_marking+ci_mod_marking) as total from ".DB_PREFIX."marking WHERE course_id = '".$course_id."' AND term_id = '".$term_id."' AND exercise_id = '".$exercise_id."' AND syndicate_id = '".$syndicate_id."' AND status = '1' ORDER BY total desc, student_id ";
				}//else
			}else if($sort == 'cn'){
				if($syndicate_id == '0' || $syndicate_id == ""){
					$sql = "select mrk.* from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."student as std WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.exercise_id = '".$exercise_id."' AND mrk.status = '1' AND mrk.student_id = std.id GROUP BY mrk.student_id ORDER BY std.student_id asc";
				}else{
					$sql = "select mrk.* from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."student as std WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.exercise_id = '".$exercise_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.status = '1' AND mrk.student_id = std.id GROUP BY mrk.student_id ORDER BY std.student_id";
				}//else
			}else if($sort == 'general'){
				if($syndicate_id == '0' || $syndicate_id == ""){
					$sql = "select mrk.* from ".DB_PREFIX."marking as mrk,  ".DB_PREFIX."syndicate as syn, ".DB_PREFIX."student as s WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.exercise_id = '".$exercise_id."' AND mrk.status = '1' AND mrk.syndicate_id = syn.id AND mrk.student_id = s.id ORDER BY syn.name asc, s.student_id asc";
				}else{
					$sql = "select m.* from ".DB_PREFIX."marking as m, ".DB_PREFIX."student as s WHERE m.course_id = '".$course_id."' AND m.term_id = '".$term_id."' AND m.exercise_id = '".$exercise_id."' AND m.syndicate_id = '".$syndicate_id."' AND m.status = '1' AND m.student_id = s.id GROUP BY m.student_id ORDER BY s.student_id";
				}//else
			}//else if
		}//else if
		$studentArr = $dbObj->selectDataObj($sql);
		$total_student = sizeof($studentArr);
		
		//This code find position of a student in an exercise
		if($cur_user_group_id == '5'){
			$sql = " SELECT distinct (ds_marking+si_mod_marking+ci_mod_marking) as total from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' AND syndicate_id = '".$syndicate_id."' ORDER BY total DESC";
		}else if($cur_user_group_id == '4'){
			if($syndicate_id == '0' || $syndicate_id == ""){
				$add_syndicate = "";
			}else{
				$add_syndicate = " AND syndicate_id = '".$syndicate_id."'";
			}//else no_syndicate
			$sql = "SELECT distinct (ds_marking+si_mod_marking+ci_mod_marking) as total from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' AND status = '1' ".$add_syndicate." ORDER BY total DESC";
		}//else cur_group_id
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

?>
<link rel="shortcut icon" href="images/favicon.ico">
<link href="css/template.css" rel="stylesheet" type="text/css">
<title><?php echo YARDSTICK.'Print Exercise Results'; ?></title>
<body onLoad="javascript:this.print()" style="width:900px; margin-left:auto; margin-right:auto; font-size:10px;" > 
					<table cellpadding="0" cellspacing="0" border="0" align="center" width="900">
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
							
							echo EXERCISE_TYPE.': <strong>'.$type->name.' ('.$ass_sys.')'; ?></strong></td>
							<td align="right"><?php echo WEIGHT.': <strong>'.$exrName->weight; ?></strong></td>
						</tr>
					</table>
					<table cellpadding="0" cellspacing="0" border="1" align="center" width="900">
							<tr class="head">
								<td align="center" rowspan="2">
									<strong><?php echo SER_NO; ?></strong>
								</td>
								<td align="center" rowspan="2">
									<strong><?php echo CN; ?></strong>
								</td>
								<?php if($cur_user_group_id == '4' && $syndicate_id == 0){ ?>
								<td align="center" rowspan="2">
									<strong><?php echo SYNDICATE; ?></strong>
								</td>
								<?php } ?>
								<td rowspan="2" align="center">
									<strong><?php echo RANK; ?></strong>
								</td>
								<td rowspan="2" align="center">
									<strong><?php echo STUDENT_NAME; ?></strong>
								</td>
								<td colspan="2" align="center">
									<strong><?php echo DS_FROM; ?></strong>
								</td>
								<td colspan="2" align="center">
									<strong><?php echo SI_NUMBER_PERCENT; ?></strong>
								</td>
								<td colspan="2" align="center">
									<strong><?php echo CI_NUMBER_PERCENT; ?></strong>
								</td>
								<td colspan="2" align="center">
									<strong><?php echo MOD; ?></strong>
								</td>
								<td rowspan="2" align="center">
									<strong><?php echo GRADE; ?></strong>
								</td>
								<td rowspan="2" align="center">
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
								<td align="right">
									<strong><?php echo PERCENT; ?></strong>
								</td>
								<td align="right">
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
							$sql = "select * from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise_id."' AND student_id = '".$student->student_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' AND status = '1'";
							$number = $dbObj->selectDataObj($sql);
							$student_number = $number[0];
							
							$from_ds = $student_number->ds_marking;
							$from_ds_weight = $student_number->ds_student_weight;
							$si_mod = $student_number->si_mod_marking;
							$conv_si_mod = $student_number->si_student_weight;
							$si_sign = ($student_number->si_sign == '+')?$student_number->si_sign:'';
							$ci_mod = $student_number->ci_mod_marking;
							$conv_ci_mod = $student_number->ci_student_weight;
							$ci_sign = ($student_number->ci_sign == '+')?$student_number->ci_sign:'';
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
							$sql = "select syndicate_id from ".DB_PREFIX."student_to_syndicate WHERE course_id = '".$course_id."' AND term_id = '".$term_id."' AND student_id = '".$student->student_id."' ";
							$synArr = $dbObj->selectDataObj($sql);
							$syndicate = $synArr[0];
							$syndicateName = getNameById('syndicate', $syndicate->syndicate_id);
							
							?>
							<tr <?php echo $class; ?>>
								<td align="center">
									<?php echo $sl; ?>
								</td>
								<td align="center">
									<?php echo $stuInfo->student_id; ?>
								</td>
								<?php if($cur_user_group_id == '4' && $syndicate_id == 0){ ?>
								<td align="center">
									<?php echo $syndicateName->name; ?>
								</td>
								<?php } ?>
								<td class="general_padding">
									<?php echo $rnk_name; ?>
								<td class="general_padding">			
									<?php echo $student_name; ?>
								</td>
								<td align="center">
									<?php echo view_number_two($from_ds); ?>
								</td>
								<td align="center">
									<?php echo view_number_two($from_ds_weight); ?> 
								</td>
								<td align="center">
									<?php echo $si_sign.view_number_two($si_mod); ?> 
								</td>
								<td align="center">
									<?php echo $si_sign.view_number_two($conv_si_mod); ?> 
								</td>
								<td align="center">
									<?php echo $ci_sign.view_number_two($ci_mod); ?> 
								</td>
								<td align="center">
									<?php echo $ci_sign.view_number_two($conv_ci_mod); ?> 
								</td>
								<td align="center">
									<?php echo view_number_two($total_mod);
										$total_number += view_number_two($total_mod);
									?> 
								</td>
								<td align="center">
									<?php echo view_number_two($total_mod_weight); ?> 
								</td>
								<td style="padding-left:15px;">
									<?php echo findGrade($total_mod); ?>
								</td>
								<td align="center">
									<?php echo findPosition($numberArray, $total_mod); ?>
								</td>
							</tr>
						<?php	
							$rownum++;
						}//foreach
						
						$avg_number = $total_number/$total_student;
						$colspan = (empty($syndicate_id)) ? 11 : 10;
						?>
							<tr>
								<td height="40" colspan="<?php echo $colspan; ?>" align="right" style="padding-right:15px;"><?php echo AVG_MARKS; ?></td>
								<td colspan="4" align="left" style="padding-left:8px;"><strong><?php echo view_number_two($avg_number); ?>%</strong></td>
							</tr>
					</table>
					<table cellpadding="0" cellspacing="0" border="0" width="100%">
						<tr>
							<td height="30">
								<?php echo GEN_BY.$cur_user_rank.' '.$cur_user_off_nam.ON. date('d M Y').AT.date('h:m:i a'); ?>
							</td>
						</tr>
						<tr align="center">
							<td height="40" colspan="2"><strong><?php echo CONFIDENTIAL;?></strong></td>
						</tr>
					</table>
</div>
</body>
