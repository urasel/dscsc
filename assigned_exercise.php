<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Assigned Exercise';
$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$action = $_REQUEST['action'];
$msg = '';

//Chek if this user is valid for this file
if($cur_user_group_id != '3'){
	header("Location: dashboard.php");	
}

switch($action){
	case 'view':	
	default:
		
		if(isset($_POST['show'])){
			$course_id = $_POST['course_id'];
			$term_id = $_POST['term_id'];
			
			if($term_id == 0 || $term_id == ""){
				$add_term = "";
			}else{
				$add_term = " AND ett.term_id = '".$term_id."' ";
			}
			
			//Build Student List Array	
			$query = "select ett.exercise_id, ett.term_id, ett.course_id from ".DB_PREFIX."exercise_to_term as ett, ".DB_PREFIX."exercise as exr where ett.course_id = '".$course_id."'  AND ett.exercise_id = exr.id ".$add_term." order by ett.term_id, exr.name asc";
			$exerciseArr = $dbObj->selectDataObj($query);
		}
		
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
				<h1><?php echo ASSIGNED_EXERCISE; ?></h1>
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
		<form action="assigned_exercise.php" method="post" name="assigned_exercise" id="assigned_exercise" onsubmit="return validateAssignedExercise();">
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
		
	<?php if(isset($_POST['show'])){ ?>
		
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
							<td height="30" width="20%">
								<strong><?php echo NAME; ?></strong>
							</td>
							<td height="30" width="15%">
								<strong><?php echo TERM; ?></strong>
							</td>
							<td height="30" width="15%">
								<strong><?php echo EXERCISE_TYPE; ?></strong>
							</td>
							<td height="30" width="15%">
								<strong><?php echo ASSESSMENT_SYSTEM; ?></strong>
							</td>
							<td height="30" width="10%">
								<strong><?php echo WEIGHT; ?></strong>
							</td>
						</tr>
						<?php	
						if(!empty($exerciseArr)){
							$rownum = 0;							
							foreach($exerciseArr as $exercise){		
								if(($rownum%2)==0){//even
									$class = ' class="even"';									
								}else{//odd
									$class = ' class="odd"';									
								}
							
							$sql = "select * from ".DB_PREFIX."exercise where id = '".$exercise->exercise_id."'";
							$ExrArr = $dbObj->selectDataObj($sql);
							$exrList = $ExrArr[0];
							$exrInfo = getNameById('exercise', $exrList->id);
		
						?>
								<tr <?php echo $class; ?>>
									<td style="padding-left:15px;">
										<?php echo $rownum+1; ?> 
									</td>
									<td>
										<?php echo $exrInfo->exercise_id; ?> 
									</td>
									<td>
										<?php echo $exrInfo->name;;?>
									</td>
									<td >
										<?php 	$term = getNameById('term', $exercise->term_id);
											echo $term->name;?>
									</td>	
									<td >
										<?php 	$type = getNameById('exercise_type', $exrInfo->type_id);
											echo $type->name;?>
									</td>	
									<td >								
										<?php 	if($exrInfo->marking_type == '1'){
													echo PERCENT_BASED;
												}else if($exrInfo->marking_type == '2'){
													echo MARK_BASED;
												} ?>
									</td>
									<td>								
										<?php echo $exrInfo->weight; ?>
									</td>
								</tr>
							<?php 
									$rownum++;
								}//foreach 
							}else{
							?>	
							<tr height="30">
								<td colspan="7">
									<?php echo EMPTY_DATA; ?>
								</td>
							</tr>
							<?php } ?>
					</table>
				</td>
			</tr>
		</table>
			
	<?php 
		}
	} 
	?>
</div>
			
<?php
require_once("includes/footer.php");
?>