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
$msg = '';

//Chek if this user is valid for this file
if($cur_user_group_id != '5' && $cur_user_group_id != '4'){			
	header("Location: dashboard.php");		//Only DS & SI can view Progress reports
}

switch($action){
	case 'view':	
	default:
		
		
		$course_id = $_REQUEST['course_id'];
		$term_id = $_REQUEST['term_id'];
		$student_id = $_REQUEST['student_id'];
		$exercise_id = $_REQUEST['exercise_id'];
		$exercise_type_id = $_REQUEST['exercise_type_id'];
		
		if($cur_user_group_id == '5'){
			//Find Currently Active Course AND Syndicate which is assigned to this DS
			$sql = "select dtc.course_id, dtc.syndicate_id from ".DB_PREFIX."ds_to_course as dtc, ".DB_PREFIX."course as crs WHERE dtc.wing_id = '".$cur_user_wing_id."' AND dtc.ds_id = '".$cur_user_id."' AND crs.status = '0' AND crs.id = dtc.course_id limit 1";
			$dsInfo = $dbObj->selectDataObj($sql);
			$ds = $dsInfo[0];
			
			//Find Currently Active Term which is assigned to this DS
			$sql = "select dtc.term_id from ".DB_PREFIX."ds_to_course as dtc, ".DB_PREFIX."term as trm WHERE dtc.wing_id = '".$cur_user_wing_id."' AND dtc.ds_id = '".$cur_user_id."' AND trm.status = '0' AND trm.id = dtc.term_id limit 1";
			$dsTerm = $dbObj->selectDataObj($sql);
			$ds_term = $dsTerm[0];
			
			//Build Student List Array	
			$query = "select std.id, std.student_id from ".DB_PREFIX."student_to_syndicate as sts, ".DB_PREFIX."student as std where sts.wing_id = ".$cur_user_wing_id." AND sts.course_id = '".$ds->course_id."' AND sts.term_id = '".$ds_term->term_id."' AND sts.syndicate_id = '".$ds->syndicate_id."' AND std.id = sts.student_id ORDER BY std.student_id asc";
			$studentArr = $dbObj->selectDataObj($query);
			
			//Build Exercise Type List Array
			$sql = "select * from ".DB_PREFIX."exercise_type WHERE wing_id = '".$cur_user_wing_id."' ORDER BY name asc";
			$exrTypeArr = $dbObj->selectDataObj($sql);
	
		
					
			//Build Exercise List Array	
			if($exercise_id == '0'){
				$query = "select exr.id, exr.name, exr.weight from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$ds->course_id."' AND ett.term_id = '".$ds_term->term_id."' AND exr.id = ett.exercise_id AND exr.type_id = '".$exercise_type_id."' order by exr.name asc";
			}else{
				$query = "select * from ".DB_PREFIX."exercise where id = '".$exercise_id."'";
			}
			$exerciseListArr = $dbObj->selectDataObj($query);

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
					
					//Build Term List Array	
					$query = "select id, name from ".DB_PREFIX."term WHERE course_id = '".$course_id."' order by name asc";
					$termArr = $dbObj->selectDataObj($query);
					
					//Build Exercise Type List Array
					$sql = "select * from ".DB_PREFIX."exercise_type WHERE wing_id = '".$cur_user_wing_id."' ORDER BY name asc";
					$exrTypeArr = $dbObj->selectDataObj($sql);
			
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

					
					//Build Student List Array
					if($exercise_type_id == '0' || $exercise_type_id == ""){
						$sql = "SELECT distinct std.id, std.student_id FROM ".DB_PREFIX."marking AS dm, ".DB_PREFIX."exercise AS ex, ".DB_PREFIX."student AS std WHERE dm.exercise_id = ex.id AND std.id = dm.student_id ORDER BY std.student_id";
					}else{
						$sql = "SELECT distinct std.id, std.student_id FROM ".DB_PREFIX."marking AS dm, ".DB_PREFIX."exercise_type AS et, ".DB_PREFIX."exercise AS ex, ".DB_PREFIX."student AS std WHERE dm.exercise_id = ex.id AND et.id = ex.type_id AND et.id = '".$exercise_type_id."' AND std.id = dm.student_id  ORDER BY std.student_id";
					}
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
						}
					}	
				$exerciseListArr = $dbObj->selectDataObj($sql);
			}//if not empty courseArray
		}//else if
		
		if(!empty($exercise_id)){
			//Find Exercise Info
			$sql = "select * from ".DB_PREFIX."exercise WHERE id = '".$exercise_id."' ";
			$exrArr = $dbObj->selectDataObj($sql);
			$exercise = $exrArr[0];
			$weight = $exercise->weight;
			$exr_mark = $exercise->mark;
			$marking_type = $exercise->marking_type;
		}else{
			//Build Exercise List Array
			if($exercise_type_id == '0' || $exercise_type_id == ""){
				$sql = "select exr.id, exr.name, exr.weight, exr.mark from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$course_id."' AND ett.term_id = '".$term_id."' AND exr.id = ett.exercise_id order by exr.name asc";
			}else{
				$sql = "select exr.id, exr.name, exr.weight, exr.mark from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett, ".DB_PREFIX."exercise_type as et where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$course_id."' AND ett.term_id = '".$term_id."' AND exr.id = ett.exercise_id AND et.id = '".$exercise_type_id."' AND et.id = exr.type_id order by exr.name asc";
			}
			$exrArr = $dbObj->selectDataObj($sql);
			
			//Find DS Impression mark Info of this term
			$sql = "select * from ".DB_PREFIX."term WHERE id = '".$term_id."'";
			$termInfoArry = $dbObj->selectDataObj($sql);
			$termInfo = $termInfoArry[0];
			$ds_impr_mark = $termInfo->ds_impr_mark;
		}
		
		//Build Term List Array	-- for viewing course report
		$query = "select * from ".DB_PREFIX."term where course_id = '".$course_id."'";
		$courseTermArr = $dbObj->selectDataObj($query);
		
		if($term_id == '0'){
			$sql = "select * from ".DB_PREFIX."course WHERE id = '".$course_id."'";
			$courseInfoArr = $dbObj->selectDataObj($sql);
			$courseInfo = $courseInfoArr[0];
			$si_impr_mark = $courseInfo->si_impr_mark;
		}
							
		$action = 'view';
		$msg = $_REQUEST['msg'];
		break;

}//switch

?>

<link rel="shortcut icon" href="images/favicon.ico">
<link href="css/template.css" rel="stylesheet" type="text/css">
<title><?php echo YARDSTICK.'Print Performance Results'; ?></title>
<body onLoad="javascript:this.print();" style="width:1300px; margin-left:auto; margin-right:auto;">

	<?php if($action=="view"){ ?>

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
	<?php if($term_id != '0'){ ?>
		<tr align="center">
			<td height="40" colspan="2"><strong><?php
			$trmName = getNameById('term', $term_id);
			echo strtoupper($trmName->name).$syndicate_name; ?></strong></td>
		</tr>
	<?php }//if ?>
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
			<td align="center" width="5%" rowspan="2">
				<strong><?php echo SER_NO; ?></strong>
			</td>
			<td align="center" width="5%" rowspan="2">
				<strong><?php echo CN; ?></strong>
			</td>
			<td align="center" width="5%" rowspan="2">
				<strong><?php echo RANK; ?></strong>
			</td>
			<td align="center" width="10%" rowspan="2">
				<strong><?php echo STUDENT_NAME; ?></strong>
			</td>
			<?php
			$examTotalWeight = 0;
			foreach($exerciseListArr as $exercise){ ?>
			<td align="center" width="10%" colspan="2">
				<strong><?php echo $exercise->name; ?></strong>
			</td>
			<?php 
			$examTotalWeight += $exercise->weight;
			} ?>
			<td align="center" width="5%" rowspan="2">
				<strong><?php echo TOTAL_WT.'<br />('.$examTotalWeight.')'; ?></strong>
			</td>
			<td align="center" width="5%" rowspan="2">
				<strong><?php echo TOTAL_PERCENT; ?></strong>
			</td>
		</tr>
		<tr class="head">
			<?php 
			foreach($exerciseListArr as $exercise){ ?>
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
			if(($rownum%2)==0){//even
				$class = ' class="even"';									
			}else{//odd
				$class = ' class="odd"';									
			}
			
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
			$rownum++;
		}//foreach 
		?>
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
<?php }// if action is view?>