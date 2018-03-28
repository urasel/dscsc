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
if($cur_user_group_id != '4' && $cur_user_group_id != '5'){	//only DS & SI can view Term report
	header("Location: dashboard.php");	
}

switch($action){
	case 'view':	
	default:
		
		$course_id = $_REQUEST['course_id'];
		$term_id = $_REQUEST['term_id'];
		$syndicate_id = intval($_REQUEST['syndicate_id']);
		$sort = $_REQUEST['sort'];
		$forward = $_REQUEST['forward'];
		$colspan = $_REQUEST['colspan'];
		
		//Build Exercise List Array	
		//$query = "select exr.id, exr.name, exr.weight, exr.mark from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$course_id."' AND ett.term_id = '".$term_id."' AND exr.id = ett.exercise_id order by exr.name asc";
		
		$query = "select exr.id, exr.name,exr.join_course, exr.weight, exr.air_weight, exr.navy_weight, exr.mark from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where ett.term_id = '".$term_id."' AND exr.id = ett.exercise_id order by exr.name asc";
		$exrArr = $dbObj->selectDataObj($query);
		$total_exercise  = sizeof($exrArr);
		
		//Find DS Impression mark Info of this term
		$sql = "select * from ".DB_PREFIX."term WHERE id = '".$term_id."'";
		$termInfoArry = $dbObj->selectDataObj($sql);
		$termInfo = $termInfoArry[0];
		$ds_impr_mark = $termInfo->ds_impr_mark;
		
		if($cur_user_group_id == '5'){
			if($sort == 'posn'){
				if($forward == 'true'){
					$sql = "SELECT mrk.student_id, (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)+impr.ds_impr_weight) as total from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND mrk.course_id = '".$course_id."' AND impr.term_id = '".$term_id."' AND mrk.term_id = '".$term_id."' AND impr.syndicate_id = '".$syndicate_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.status = '1' AND mrk.ci_status = '1' AND impr.status = '1' group by mrk.student_id ORDER BY total desc";
				}else{
					$sql = "SELECT mrk.student_id, (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)) as total from ".DB_PREFIX."marking as mrk WHERE mrk.syndicate_id = '".$syndicate_id."' AND mrk.term_id = '".$term_id."' AND mrk.course_id = '".$course_id."' AND mrk.status = '1' AND mrk.ci_status = '1' group by mrk.student_id ORDER BY total desc";
				}
			}else{
				$sql = "SELECT mrk.student_id FROM ".DB_PREFIX."marking as mrk, ".DB_PREFIX."student as std WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.student_id = std.id GROUP BY std.student_id ORDER BY std.student_id";
			}//else 
		}else if($cur_user_group_id == '4'){
			if($forward == 'true'){
				if($sort == 'posn'){
					if($syndicate_id == '0'){
						$sql = "SELECT mrk.student_id, (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)+impr.ds_impr_weight) as total from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND mrk.course_id = '".$course_id."' AND impr.term_id = '".$term_id."' AND mrk.term_id = '".$term_id."' AND mrk.status = '1' AND mrk.ci_status = '1' AND impr.status = '1' group by mrk.student_id ORDER BY total desc";
					}else{
						$sql = "SELECT mrk.student_id, (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)+impr.ds_impr_weight) as total from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND mrk.course_id = '".$course_id."' AND impr.term_id = '".$term_id."' AND mrk.term_id = '".$term_id."' AND impr.syndicate_id = '".$syndicate_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.status = '1' AND mrk.ci_status = '1' AND impr.status = '1' group by mrk.student_id ORDER BY total desc";
					}//else 
				}else if($sort == 'cn'){
					if($syndicate_id == '0'){
						$sql = "SELECT mrk.student_id from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr, ".DB_PREFIX."student as std WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND mrk.course_id = '".$course_id."' AND impr.term_id = '".$term_id."' AND mrk.term_id = '".$term_id."' AND mrk.status = '1' AND impr.status = '1' AND mrk.student_id = std.id AND impr.student_id = std.id group by mrk.student_id ORDER BY std.student_id asc";
					}else{
						$sql = "SELECT mrk.student_id from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr, ".DB_PREFIX."student as std WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND mrk.course_id = '".$course_id."' AND impr.term_id = '".$term_id."' AND mrk.term_id = '".$term_id."' AND impr.syndicate_id = '".$syndicate_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.status = '1' AND impr.status = '1' AND mrk.student_id = std.id AND impr.student_id = std.id group by mrk.student_id ORDER BY std.student_id asc";
					}//else 
				}else{
					if($syndicate_id == '0'){
						$sql = "SELECT mrk.student_id from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr, ".DB_PREFIX."syndicate as syn WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND mrk.course_id = '".$course_id."' AND impr.term_id = '".$term_id."' AND mrk.term_id = '".$term_id."' AND mrk.status = '1' AND impr.status = '1' AND syn.id = mrk.syndicate_id AND syn.id = impr.syndicate_id group by mrk.student_id ORDER BY syn.name asc, mrk.student_id asc";
					}else{
						$sql = "SELECT mrk.student_id from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND mrk.course_id = '".$course_id."' AND impr.term_id = '".$term_id."' AND mrk.term_id = '".$term_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND impr.syndicate_id = '".$syndicate_id."' AND mrk.status = '1' AND impr.status = '1' group by mrk.student_id ORDER BY mrk.student_id asc";
					}//else 
				}//else
			}else{
				if($sort == 'posn'){
					if($syndicate_id == '0'){
						$sql = "SELECT mrk.student_id, (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)) as total from ".DB_PREFIX."marking as mrk WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.status = '1' AND mrk.ci_status = '1' group by mrk.student_id ORDER BY total desc";
					}else{
						$sql = "SELECT mrk.student_id, (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)) as total from ".DB_PREFIX."marking as mrk  WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.status = '1' AND mrk.ci_status = '1' group by mrk.student_id ORDER BY total desc";
					}//else 
				}else if($sort == 'cn'){
					if($syndicate_id == '0'){
						$sql = "SELECT mrk.student_id from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."student as std WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.status = '1' AND mrk.student_id = std.id group by mrk.student_id ORDER BY std.student_id asc";
					}else{
						$sql = "SELECT mrk.student_id from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."student as std WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.status = '1' AND mrk.student_id = std.id group by mrk.student_id ORDER BY std.student_id asc";
					}//else 
				}else{
					if($syndicate_id == '0'){
						$sql = "SELECT mrk.student_id from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."syndicate as syn WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.status = '1' AND syn.id = mrk.syndicate_id group by mrk.student_id ORDER BY syn.name asc, mrk.student_id asc";
					}else{
						$sql = "SELECT mrk.student_id from ".DB_PREFIX."marking as mrk WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.status = '1' group by mrk.student_id ORDER BY mrk.student_id asc";
					}//else 
				}//else
			}//else
		}//else if
		$studentArr = $dbObj->selectDataObj($sql);
				
		//This code find position of a student
		if($cur_user_group_id == '5'){
				if($forward == 'true'){
					$sql = "SELECT distinct (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)+impr.ds_impr_weight) as total from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND mrk.course_id = '".$course_id."' AND impr.term_id = '".$term_id."' AND mrk.term_id = '".$term_id."' AND impr.syndicate_id = '".$syndicate_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.ci_status = '1' group by mrk.student_id ORDER BY total desc";
				}else{
					$sql = "SELECT distinct (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)) as total from ".DB_PREFIX."marking as mrk WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.ci_status = '1' group by mrk.student_id ORDER BY total desc";
				}
		}else if($cur_user_group_id == '4'){
			if($forward == 'true'){
				if($syndicate_id == '0' || $syndicate_id == ""){
					$add_syndicate = "";
				}else{
					$add_syndicate = " AND mrk.syndicate_id = '".$syndicate_id."' AND impr.syndicate_id = '".$syndicate_id."'";
				}
				$sql = "SELECT distinct (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)+impr.ds_impr_weight) as total from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND mrk.course_id = '".$course_id."' AND impr.term_id = '".$term_id."'  AND mrk.term_id = '".$term_id."' AND mrk.status = '1' AND mrk.ci_status = '1' ".$add_syndicate." group by mrk.student_id ORDER BY total desc"; //AND impr.status = '1'
			}else{
				if($syndicate_id == '0' || $syndicate_id == ""){
					$add_syndicate = "";
				}else{
					$add_syndicate = " AND mrk.syndicate_id = '".$syndicate_id."'";
				}
				$sql = "SELECT distinct (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)) as total from ".DB_PREFIX."marking as mrk WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.status = '1' AND mrk.ci_status = '1' ".$add_syndicate." group by mrk.student_id ORDER BY total desc"; //AND impr.status = '1'
			}
		}//else if
		
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
<title><?php echo YARDSTICK.'Print Term Results'; ?></title>
<body onLoad="javascript:this.print();" style="width:1300px; margin-left:auto; margin-right:auto; font-size:10px;"><!---->
	<table cellpadding="0" cellspacing="0" border="0" align="center" width="900">
		<tr align="center">
			<td height="40" colspan="2"><strong><?php echo CONFIDENTIAL;?></strong></td>
		</tr>
		<tr align="center">
			<td height="40" colspan="2">
			<strong>
				<?php 
				$crsName = getNameById('course', $course_id);
				echo strtoupper($crsName->name);
				?>
			</strong>
			</td>
		</tr>
		<tr align="center">
			<td height="40" colspan="2"><strong><?php
			$trmName = getNameById('term', $term_id);
			echo strtoupper(TERM_RES_SHT.$trmName->name).$syndicate_name; ?></strong></td>
		</tr>
		<tr align="center">
			<td height="40">
			<strong>
				<?php 
				$wngName = getNameById('wing', $cur_user_wing_id);
				echo strtoupper($wngName->name);
				?>
			</strong>
			</td>
		</tr>
	</table>
	<table cellpadding="0" cellspacing="0" border="1">
		<tr class="head">
			<td align="center" rowspan="2">
				<strong><?php echo SER_NO; ?></strong>
			</td>
			<td align="center" rowspan="2">
				<strong><?php echo CN; ?></strong>
			</td>
			<td align="center" rowspan="2">
				<strong><?php echo RANK; ?></strong>
			</td>
			<td align="center" rowspan="2">
				<strong><?php echo STUDENT_NAME; ?></strong>
			</td>
			<?php if($cur_user_group_id == '4' && $syndicate_id == 0){ ?>
			<td align="center" rowspan="2">
				<strong><?php echo SYNDICATE; ?></strong>
			</td>
			<?php } 
					foreach($exrArr as $exercise){ ?>
			<td align="center" colspan="2">
				<strong><?php echo $exercise->name; ?></strong>
			</td>
			<?php } ?>
			<td align="center">
				<strong><?php echo EXAM_WT; ?></strong>
			</td>
			<td align="center" rowspan="2">
				<strong><?php echo EX_TOTAL_PERCENT; ?></strong>
			</td>
			<td align="center" colspan="2">
				<strong><?php echo DS_IMPRES_MARK; ?></strong>
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
			$exam_total_weight = 0;
			foreach($exrArr as $exercise){ 
			
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
				<strong><?php echo WT.'<br />'.$exercise->weight; ?></strong>
			</td>
			<?php 
				$sub_total += $exercise->weight;
				$exam_total_weight += $exercise->weight;
				if($cur_user_group_id == '4'){
					$total = $sub_total+$ds_impr_mark;
				}else{
					$total = $sub_total+$ds_impr_mark;
				}
				
			} ?>
			<td align="center">
				<strong><?php echo $exam_total_weight; ?></strong>
			</td>
			<td align="center">
				<strong><?php echo PERCENT; ?></strong>
			</td>
			<td align="center">
				<strong><?php echo WT.' ('.$ds_impr_mark.')'; ?></strong>
			</td>
			<td align="center">
				<strong><?php echo $total; ?></strong>
			</td>
		</tr>
		<?php 
			$rownum = 0;
			$sl = 0;
			$totals_number = 0;
			foreach($studentArr as $student){
				$sl = $rownum + 1;
				if(($rownum%2)==0){//even
					$class = ' class="even"';									
				}else{//odd
					$class = ' class="odd"';									
				}
		
		//Find Student Info
		$sql = "select * from ".DB_PREFIX."student WHERE id = '".$student->student_id."'";
		$stuInfoArr = $dbObj->selectDataObj($sql);
		$stuInfo = $stuInfoArr[0];
		$student_name = $stuInfo->official_name;
		$rnkName = getNameById('rank', $stuInfo->rank_id);
		$rnkName = $rnkName->short_name;
		
		//Find syndicate of student
		$sql = "select syndicate_id from ".DB_PREFIX."student_to_syndicate WHERE student_id = '".$student->student_id."' AND course_id = '".$course_id."' AND term_id = '".$term_id."'";
		$synArr = $dbObj->selectDataObj($sql);
		$syndicate = $synArr[0];
		$syndicateName = getNameById('syndicate', $syndicate->syndicate_id);
			
		?>
		<tr <?php echo $class; ?> nobr="true">
			<td align="center">
				<?php echo $sl; ?>
			</td>
			<td align="center">
				<?php echo $stuInfo->student_id; ?>
			</td>
			<td align="center">
				<?php echo $rnkName; ?>
			<td>			
				<?php echo $student_name; ?>
			</td>
			<?php if($cur_user_group_id == '4' && $syndicate_id == 0){ ?>
			<td align="center">
				<?php echo $syndicateName->name; ?>
			</td>
			<?php }
			
			$term_exam_weight = 0;
			foreach($exrArr as $exercise){
				//Find info of exercise
				$sql = "select * from ".DB_PREFIX."exercise WHERE id = '".$exercise->id."'";
				$exary = $dbObj->selectDataObj($sql);
				$exer = $exary[0];
				
				$joinExercise = $exercise->join_course;
				if($joinExercise == 1 && $cur_user_wing_id == 2){
					$exercise->weight = $exer->air_weight;
				}else if($joinExercise == 1 && $cur_user_wing_id == 3){
					$exercise->weight = $exer->navy_weight;
				}else if($joinExercise == 1 && $cur_user_wing_id == 1){
					$exercise->weight = $exer->weight;
				}else{
					$exercise->weight = $exer->weight;
				}
				
				$weight = $exercise->weight;
				
				//Find marking of students of different exams
				$sql = "select * from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise->id."' AND student_id = '".$student->student_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' AND status = '1' AND ci_status = '1'";
				$markArr = $dbObj->selectDataObj($sql);
				$mark = $markArr[0];
				$s_mark = $mark->ds_marking+$mark->si_mod_marking+$mark->ci_mod_marking;
				$from_ds_weight = ($mark->ds_marking*$exercise->weight)/100;
				//$converted_s_mark = $mark->ds_student_weight+$mark->si_student_weight+$mark->ci_student_weight;
				$converted_s_mark = $from_ds_weight+$mark->si_student_weight+$mark->ci_student_weight;
				$term_exam_weight += $converted_s_mark;
				$term_exam_percent = ($term_exam_weight*100)/$exam_total_weight;
				
				if($forward == 'true'){
					//Find term impression mark of Student
					$sql = "select * from ".DB_PREFIX."impression_marking WHERE student_id = '".$student->student_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' AND status = '1'";
					$stuImprMarkArry = $dbObj->selectDataObj($sql);
					$stuImprMark = $stuImprMarkArry[0];
					$stu_ds_impression_mark = $stuImprMark->ds_impr_marking;
					$converted_ds_impr_mark = ($stu_ds_impression_mark*$ds_impr_mark)/100;
				}
				$total_number = $term_exam_weight+$converted_ds_impr_mark;
				$total_percent = ($total_number*100)/$total;
			?>
			<td align="right">
				<?php echo view_number_two($s_mark); ?></strong>
			</td>
			<td align="right">
				<?php echo view_number_two($converted_s_mark); ?>
			</td>
			<?php } ?>
			<td align="right">
				<?php echo view_number_two($term_exam_weight); ?>
			</td>
			<td align="right">
				<?php echo view_number_two($term_exam_percent); ?> 
			</td>
			<td align="right">
				<?php echo view_number_two($stu_ds_impression_mark); ?> 
			</td>
			<td align="right">
				<?php echo view_number_two($converted_ds_impr_mark); ?> 
			</td>
			<td align="right">
				<?php echo view_number_two($total_number); ?> 
			</td>
			<td align="right">
				<?php echo view_number_two($total_percent); 
				$totals_number += view_number_two($total_percent);
				?> 
			</td>
			<td align="left" style="padding-left:10px;">
				<?php echo findGrade($total_percent); ?>
			</td>
			<td align="center">
				<?php echo findPosition($numberArray, $total_number); ?>
			</td>
		</tr>
		</tr>
		<?php
			$rownum++;	
		}//foreach
		
		$avg_marks = $totals_number/$sl;
		?>
		<tr>
			<td align="right" colspan="<?php echo $colspan;?>" style="padding-right:15px;">
				<strong><?php echo AVG_MARKS; ?></strong>
			</td>
			<td align="left" colspan="4">
				<strong><?php echo view_number_two($avg_marks).'&nbsp;%'; ?></strong>
			</td>
		</tr>
	</table>
	<table cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr height="30">
			<td>
				<?php echo GEN_BY.$cur_user_rank.' '.$cur_user_off_nam.ON. date('d M Y').AT.date('h:m:i a'); ?>
			</td>
		</tr>
		<tr align="center">
			<td height="40"><strong><?php echo CONFIDENTIAL;?></strong></td>
		</tr>
	</table>
</body>
