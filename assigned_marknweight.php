<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Assigned Marks & Weightage';
$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$action = $_REQUEST['action'];
$msg = '';

//Chek if this user is valid for this file
if($cur_user_group_id != '2'){
	header("Location: dashboard.php");	
}

switch($action){
	case 'view':	
	default:
		
		if(isset($_POST['show'])){
			$course_id = $_REQUEST['course_id'];
			$term_id = $_REQUEST['term_id'];
			
			if($term_id == '0' || $term_id == ""){
				$add_term = "";
			}else{
				$add_term = " AND term_id = '".$term_id."'";
			}//else
			
			if($term_id == '0' || $term_id == ""){
				$mark_of_term = "";
			}else{
				$mark_of_term = " AND id = '".$term_id."'";
			}//else
			
			//Exercise List is selected Course
			$sql = "select ett.* from ".DB_PREFIX."exercise_to_term as ett, ".DB_PREFIX."exercise as e WHERE e.id = ett.exercise_id AND ett.wing_id = '".$cur_user_wing_id."' AND ett.course_id = '".$course_id."' ".$add_term." order by ett.term_id, e.exercise_id asc";
			$exerciseList = $dbObj->selectDataObj($sql);
			
			//Term List & Info of this selected course
			$sql = "select * from ".DB_PREFIX."term WHERE wing_id = '".$cur_user_wing_id."' ".$mark_of_term." AND course_id = '".$course_id."'";
			$termList = $dbObj->selectDataObj($sql);
			
			//Course Info of this selected course
			$sql = "select * from ".DB_PREFIX."course WHERE wing_id = '".$cur_user_wing_id."' AND id = '".$course_id."'";
			$courseList = $dbObj->selectDataObj($sql);
			$courseInfo = $courseList[0];
		}//if
		
		//Build Course Array
		$sql = "select id, name from ".DB_PREFIX."course where wing_id = '".$cur_user_wing_id."' AND status = '0' order by name asc";
		$courseArr = $dbObj->selectDataObj($sql);
		
		$courseId = array();
		$courseId[0] = SELECT_COURSE_OPT;
		if(!empty($courseArr)){			
			foreach($courseArr as $item){
				$courseId[$item->id] = $item->name;
			}	
		}			
		$courseList_opt = formSelectElement($courseId, $course_id, 'course_id', 'onchange = processFunction("assigned_exercise")');
		
		//Build Term List Array	
		$query = "select id, name from ".DB_PREFIX."term where course_id = '".$course_id."' order by name asc";
		$termArr = $dbObj->selectDataObj($query);
		$termId = array();
		$termId[0] = SELECT_TERM_OPT;
		if(!empty($termArr)){			
			foreach($termArr as $item){
				$termId[$item->id] = $item->name;
			}	
		}			
		$termList_opt = formSelectElement($termId, $term_id, 'term_id');
		
		$action = 'view';
		$msg = $_REQUEST['msg'];
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
				<h1><?php echo ASSIGNED_MARKS; ?></h1>
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
	?>
		<form action="assigned_marknweight.php" method="post" name="marknweight" id="marknweight" onsubmit="return validateMarkWeightView();">
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="module_content">
				<tr>
					<td height="30" width="20%">
						<?php echo SELECT_COURSE; ?>:
					</td>
					<td width="80%">
						<?php echo $courseList_opt;	?>
						<span class="required_field">*</span>
					</td>
				</tr>
				<tr>
					<td height="30" width="20%">
						<?php echo SELECT_TERM; ?>:
					</td>
					<td width="80%">
						<div id="loaderContainer"></div>
						<div id="term_display">
							<?php echo $termList_opt;	?>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
					<input type="submit" name="show" class="button" value="<?php echo SHOW; ?>" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="action" value="view" />
		</form>
	
		<?php if((isset($_POST['show'])) || (!empty($_REQUEST['course_id']))){ ?>
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="module_content">
				<tr>
					<td>
						<table width="100%" cellpadding="0" cellspacing="0" border="0" class="datagrid">
							<tr class="head">
								<td height="30" width="10%">
									<strong><?php echo SER_NO; ?></strong>
								</td>
								<td height="30" width="15%">
									<strong><?php echo EXERCISE_ID; ?></strong>
								</td>
								<td height="30" width="15%">
									<strong><?php echo EXERCISE_NAME; ?></strong>
								</td>
								<td height="30" width="15%">
									<strong><?php echo TYPE; ?></strong>
								</td>
								<td height="30" width="15%">
									<strong><?php echo ASSESSMENT_SYSTEM; ?></strong>
								</td>
								<td height="30" width="15%" align="center">
									<strong><?php echo TERM; ?></strong>
								</td>
								<td  height="30" width="15%" align="right" style="padding-right:20px;">
									<strong><?php echo WEIGHT; ?></strong>
								</td>
							</tr>
							<?php			
							$rownum = 0;
							$sl = 1;
							$total_exercise_mark = 0;					
							foreach($exerciseList as $exercise){		
								if(($rownum%2)==0){//even
									$class = ' class="even"';									
								}else{//odd
									$class = ' class="odd"';									
								}

								$sql = "select * from ".DB_PREFIX."exercise WHERE id='".$exercise->exercise_id."'";
								$exrInfoArray = $dbObj->selectDataObj($sql);								
								$exrInfo = $exrInfoArray[0];
								
								$joinExercise = $exrInfo->join_course;
								if($joinExercise == 1 && $cur_user_wing_id == 2){
									$exrInfo->weight = $exrInfo->air_weight;
								}else if($joinExercise == 1 && $cur_user_wing_id == 3){
									$exrInfo->weight = $exrInfo->navy_weight;
								}else if($joinExercise == 1 && $cur_user_wing_id == 1){
									$exrInfo->weight = $exrInfo->weight;
								}else{
									$exrInfo->weight = $exrInfo->weight;
								}
								
								$total_exercise_mark += $exrInfo->weight; 
							?>
							<tr <?php echo $class; ?>>
								<td style="padding-left:15px;">
									<?php echo $sl++; ?> 
								</td>
								<td>
									<?php echo $exrInfo->exercise_id; ?> 
								</td>	
								<td>
									<?php echo $exrInfo->name; ?> 
								</td>
								<td>
									<?php 
										$type = getNameById('exercise_type', $exrInfo->type_id);
										echo $type->name;
									?>
								</td>
								<td>
									<?php 
										if($exrInfo->marking_type == '1'){
											echo PERCENT_BASED;
										}else if($exrInfo->marking_type == '2'){
											echo MARK_BASED;
										}
									?>
								</td>
								<td align="center">
									<?php 
										$term = getNameById('term', $exercise->term_id);
										echo $term->name;
									?> 
								</td>
								<td align="right" style="padding-right:20px;">
									<?php if($exrInfo->weight == ""){
												echo NOT_SELECTED;
											}else{
												echo $exrInfo->weight;
									} ?> 
								</td>				
							</tr>
							<?php 
								$rownum++;
							}//foreach 
							?>
							<tr>
								<td height="30" colspan="6" align="right">
									<strong><?php echo TOTAL_EXAM_MARK; ?></strong>
								</td>
								<td height="30" colspan="2" style="padding-right:20px;" align="right">
									<strong><?php echo $total_exercise_mark; ?></strong>
								</td>
							</tr>
							<?php 
							$total_ds_impression_marks = 0;
							foreach($termList as $term){ 
								$total_ds_impression_marks += $term->ds_impr_mark;
							?>
							<tr height="30">
								<td colspan="6" align="right">
									<?php echo $term->name.' '.DS_IMPR_WT; ?>
								</td>
								<td align="right" colspan="2" style="padding-right:20px;">
									<?php echo $term->ds_impr_mark; ?>
								</td>
							</tr>
							<?php } 
								$sub_total_marks = $total_ds_impression_marks+$total_exercise_mark;
								$total_marks = $sub_total_marks+$courseInfo->si_impr_mark;
								if($term_id == 0){
									$total_or_sub = 'Sub Total Wt';
								}else{
									$total_or_sub = 'Total Wt';
								}
							?>
							<tr>
								<td colspan="4">
								</td>
								<td colspan="3">
									<hr />
									<hr />
								</td>
							</tr>
							<tr>
								<td height="30" colspan="6" align="right">
									<strong><?php echo $total_or_sub.' :'; ?></strong>
								</td>
								<td height="30" colspan="2" style="padding-right:20px;" align="right">
									<strong><?php echo $sub_total_marks; ?></strong>
								</td>
							</tr>
							<?php if($term_id == 0){ ?>
							<tr>
								<td height="30" colspan="6" align="right">
									<?php echo SI_IMPR_WT; ?>
								</td>
								<td height="30" colspan="2" style="padding-right:20px;" align="right">
									<?php echo $courseInfo->si_impr_mark; ?>
								</td>
							</tr>
							<tr>
								<td colspan="4">
								</td>
								<td colspan="3">
									<hr />
									<hr />
								</td>
							</tr>
							<tr>
								<td height="30" colspan="6" align="right">
									<strong><?php echo TOTAL_WT.' :'; ?></strong>
								</td>
								<td height="30" colspan="2" style="padding-right:20px;" align="right">
									<strong><?php echo $total_marks; ?></strong>
								</td>
							</tr>
							<?php } ?>
						</table>
					</td>
				</tr>
			</table>
			
	<?php 		}//if POSTED or REQEST['course_id']
		} ?>
</div>
			
<?php
require_once("includes/footer.php");
?>