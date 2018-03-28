<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Exercise Management';
$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$action = $_REQUEST['action'];
$msg = '';

//Chek if this user is valid for this file
if($cur_user_group_id != '3'){
	header("Location: dashboard.php");	
}

//Pagination
$limit = PAGE_LIMIT_DEFAULT;

//Get Page Number 
if(empty($_REQUEST['page'])) {
	$page=1;
}else{
	$page = $_REQUEST['page']; 
}

switch($action){
	case 'view':	
	default:
		
		$sql = "select * from ".DB_PREFIX."exercise WHERE wing_id = '".$cur_user_wing_id."' or join_course = 1 order by course_id, exercise_id asc";
		$exerciseList = $dbObj->selectDataObj($sql);
		$action = 'view';
		$msg = $_REQUEST['msg'];
		
		//Pagination 
		if(!empty($exerciseList)){
			$total_rows = sizeof($exerciseList);
		}else{
			$total_rows =0;
		}
		//find start
		$s = ($page - 1) * $limit;
		
		$total_page = $total_rows/$limit;
		
		break;
		
	case 'update':
	case 'create':
	
		$msg = $_REQUEST['msg'];
		if(!empty($_REQUEST['id'])){
			$id = $_REQUEST['id'];
			$sql = "select * from ".DB_PREFIX."exercise WHERE id='".$id."'";	
			$exerciseList = $dbObj->selectDataObj($sql);
			$exercise = $exerciseList[0];
			
			//Make Sure wing manager of different wing cannot change an Exercise by force from URL
			if($exercise->wing_id != $cur_user_wing_id){
				header("Location: dashboard.php");
				exit;
			}
			
			$wing_id = $exercise->wing_id;
			$course_id = $exercise->course_id;
			$exercise_id = $exercise->exercise_id;
			$type_id = $exercise->type_id;
			$marking_type = $exercise->marking_type;
			$join_course = $exercise->join_course;
			$name = $exercise->name;
		}else{
			$id = '';
			$wing_id = '';
			$course_id = '';
			$exercise_id = '';
			$type_id = '';
			$marking_type = '';
			$join_course = '';
			$name = '';
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
		$courseList_opt = formSelectElement($courseId, $course_id, 'course_id');
		
		//Build Exercise Type Array
		$sql = "select id, name from ".DB_PREFIX."exercise_type where wing_id = '".$cur_user_wing_id."' order by name asc";
		$exerciseTypeArr = $dbObj->selectDataObj($sql);
		
		$exerciseTypeId = array();
		$exerciseTypeId[0] = SELECT_EXERCISE_TYPE_OPT;
		if(!empty($exerciseTypeArr)){			
			foreach($exerciseTypeArr as $item){
				$exerciseTypeId[$item->id] = $item->name;
			}	
		}			
		$exerciseTypeList_opt = formSelectElement($exerciseTypeId, $type_id, 'type_id');
		
		//Marking Type Array
		$markingArray = array(
			'1' => 'Percent Based',
			'2' => 'Mark Based',
		);
		
		$markingList = array();
		$markingList[0] = SELECT_MARKING_OPT;
		if(!empty($markingArray)){
			foreach($markingArray as $key => $val){
				$markingList[$key] = $val;
			}	
		}			
		$marking_opt = formSelectElement($markingList, $marking_type, 'marking_type');
		
		$action = 'insert';
		break;
		
	case 'save':	
		$id = $_POST['id'];
		$wing_id = $_POST['wing_id'];
		$course_id = $_POST['course_id'];
		$exercise_id = $_POST['exercise_id'];
		$type_id = $_POST['type_id'];
		$name = $_POST['name'];
		$marking_type = $_POST['marking_type'];
		$join_course = ($_POST['join_course'] == 'on' ? 1:0);
		$created_date_time = $last_updated_time = date('Y-m-d H:i:s');
		
		//Check for not inserting any blank entry
		if($course_id == '0' || $type_id == '0' || $exercise_id == "" || $name == ""){
			$msg = PARAM_MISSING;
			if(empty($id)){
				$url = 'exercise.php?action=create&msg='.$msg;
			}else{
				$url = 'exercise.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
			}
			redirect($url);
		}
		
		/*//Check if Exercise ID already exists in the db in same course
		if(empty($id)){
			$sql = "select exercise_id from ".DB_PREFIX."exercise WHERE exercise_id = '".$exercise_id."' AND course_id = '".$course_id."' AND type_id = '".$type_id."' limit 1";
			$exerciseList = $dbObj->selectDataObj($sql);		
			
			if(!empty($exerciseList)){
				$msg = EXERCISE_ID.' '.$name.ALREADY_EXISTS;
				$url = 'exercise.php?action=create&msg='.$msg;
				redirect($url);
			}			
		}else if(!empty($id)){
			$sql = "select exercise_id from ".DB_PREFIX."exercise WHERE id!='".$id."' AND exercise_id = '".$exercise_id."' AND course_id = '".$course_id."' AND type_id = '".$type_id."' limit 1";
			$exerciseList = $dbObj->selectDataObj($sql);		
			
			if(!empty($exerciseList)){
				$msg = EXERCISE_ID.' '.$name.ALREADY_EXISTS;
				$url = 'exercise.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
				redirect($url);
			}
		}
		
		//Check if Exercise Name already exists in the db in same course
		if(empty($id)){
			$sql = "select name from ".DB_PREFIX."exercise WHERE name = '".$name."' AND course_id = '".$course_id."' AND type_id = '".$type_id."' limit 1";
			$exerciseList = $dbObj->selectDataObj($sql);		
			
			if(!empty($exerciseList)){
				$msg = EXERCISE.' '.$name.ALREADY_EXISTS;
				$url = 'exercise.php?action=create&msg='.$msg;
				redirect($url);
			}			
		}else if(!empty($id)){
			$sql = "select name from ".DB_PREFIX."exercise WHERE id!='".$id."' AND name = '".$name."' AND course_id = '".$course_id."' AND type_id = '".$type_id."' limit 1";
			$exerciseList = $dbObj->selectDataObj($sql);		
			
			if(!empty($exerciseList)){
				$msg = EXERCISE.' '.$name.ALREADY_EXISTS;
				$url = 'exercise.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
				redirect($url);
			}
		}*/
		
		
		if(!empty($id)){
			$fields = array('wing_id' => $wing_id,
						'course_id' => $course_id,
						'exercise_id' => $exercise_id,
						'type_id' => $type_id,
						'marking_type' => $marking_type,
						'name' => $name,
						'updated_by' => $cur_user_id,
						'join_course' => $join_course,
						'updated_datetime' => $last_updated_time,
						);
						
			$where = "id = '".$id."'";
			
			$update_status = $dbObj->updateTableData("exercise", $fields, $where);	
			
			if(!$update_status){
				$msg = EXERCISE.' '.$name.COULD_NOT_BE_UPDATED;	
				$action = 'insert';
			}else{
				$msg = EXERCISE.' '.$name.HAS_BEEN_UPDATED;
				$url = 'exercise.php?action=view&page='.$page.'&msg='.$msg;
				redirect($url);
			}
		}else{
			$fields = array('wing_id' => $wing_id,
						'course_id' => $course_id,
						'exercise_id' => $exercise_id,
						'type_id' => $type_id,
						'marking_type' => $marking_type,
						'name' => $name,
						'join_course' => $join_course,
						'created_by' => $cur_user_id,
						'created_datetime' => $created_date_time,
						'updated_by' => $cur_user_id,
						'updated_datetime' => $last_updated_time,
						);
			
			$inserted = $dbObj->insertTableData("exercise", $fields);	
			if(!$inserted){
				$msg = EXERCISE.' '.$name.COULD_NOT_BE_CREATED;
				$action = 'insert';
			}else{
				$msg = EXERCISE.' '.$name.CREATED_SUCCESSFULLY;
				$url = 'exercise.php?action=view&msg='.$msg;
				redirect($url);
			}
		}
		break;

	case 'delete':	
		$id = $_REQUEST['id'];	
		$sql = "select * from ".DB_PREFIX."exercise WHERE id='".$id."'";	
		$exerciseList = $dbObj->selectDataObj($sql);
		$exercise = $exerciseList[0];
		
		//Make Sure wing manager of different wing cannot change an Exercise by force from URL
		if($exercise->wing_id != $cur_user_wing_id){
			header("Location: dashboard.php");
			exit;
		}
		
		$name = $exercise->name;
		$where = "id='".$id."'";	
		
		$success = $dbObj->deleteTableData("exercise", $where);	
		
		if(!$success){
			$msg = EXERCISE.' '.$name.COULD_NOT_BE_DELETED;
		}else{
			$msg = EXERCISE.' '.$name.HAS_BEEN_DELETED;
		}
		
		$url = 'exercise.php?action=view&page='.$page.'&msg='.$msg;
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
				<h1><?php echo EXERCISE_MANAGEMENT; ?></h1>
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
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="module_content">
				<tr>
					<td>
						<table width="100%" cellpadding="0" cellspacing="0" border="0" class="datagrid">
								<tr class="footer">
									<td colspan="5" style=" background:#EEEEEE;">
										<b><a href="exercise.php?action=create"><?php echo CREATE; ?></a></b>
									</td>
								</tr>				
							<tr class="head">
								<td height="30" width="10%">
									<strong><?php echo SER_NO; ?></strong>
								</td>
								<td height="30" width="20%">
									<strong><?php echo EXERCISE_ID; ?></strong>
								</td>
								<td height="30" width="20%">
									<strong><?php echo EXERCISE_NAME; ?></strong>
								</td>
								<td height="30" width="20%">
									<strong><?php echo COURSE; ?></strong>
								</td>
								<td height="30" width="10%">
									<strong><?php echo TYPE; ?></strong>
								</td>
								<td height="30" width="10%">
									<strong><?php echo ASSESSMENT_SYSTEM; ?></strong>
								</td>
								<td height="30" width="10%">
									<strong><?php echo ACTION; ?></strong>
								</td>
							</tr>
							<?php			
							if(!empty($exerciseList)){	
								
								if(($s+$limit) > $total_rows){
									$maxPageLimit = $total_rows;
								}else{
									$maxPageLimit = $s+$limit;
								}		
								
								$sl = ($limit*$page)-($limit-1);
								for($rownum = $s; $rownum <$maxPageLimit; $rownum++){	
									if(($rownum%2)==0){//even
										$class = ' class="even"';									
									}else{//odd
										$class = ' class="odd"';									
									}									
							?>
							<tr <?php echo $class; ?>>
								<td style="padding-left:15px;">
									<?php echo $sl++; ?> 
								</td>
								<td>
									<?php echo $exerciseList[$rownum]->exercise_id; ?> 
								</td>	
								<td>
									<?php echo $exerciseList[$rownum]->name; ?> 
								</td>
								<td>
									<?php 
										$course = getNameById('course', $exerciseList[$rownum]->course_id);
										echo $course->name;
									?>
								</td>
								<td>
									<?php 
										$type = getNameById('exercise_type', $exerciseList[$rownum]->type_id);
										echo $type->name;
									?>
								</td>	
								<td>
									<?php 
										if($exerciseList[$rownum]->marking_type == '1'){
											echo PERCENT_BASED;
										}else if($exerciseList[$rownum]->marking_type == '2'){
											echo MARK_BASED;
										}
									?>
								</td>				
								<td>								
									<a href="exercise.php?action=update&page=<?php echo $page; ?>&id=<?php echo $exerciseList[$rownum]->id; ?>"><?php echo UPDATE; ?></a>
									<a href="exercise.php?action=delete&page=<?php echo $page; ?>&id=<?php echo $exerciseList[$rownum]->id; ?>" onclick="return confirm('Are you sure you want to delete?');"><?php echo DELETE; ?></a>
								</td>
							</tr>
							<?php 
								}//for
							}else{ ?>
							<tr height="30">
								<td colspan="7">
									<?php echo EMPTY_DATA; ?>
								</td>
							</tr>
							<?php 
							}
							if($total_page > 1){ ?>
							<tr height="50">
								<td colspan="7">
									<?php 
									echo pagination($total_rows,$limit,$page,''); ?>
								</td>
							</tr>
							<?php } ?>
							<tr class="footer">
								<td colspan="7">
									<b><a href="exercise.php?action=create"><?php echo CREATE; ?></a></b>
								</td>
							</tr>				
						</table>
					</td>
				</tr>
			</table>
				
	<?php 
		}elseif($action=="insert"){ 
	?>
				<form action="exercise.php" method="post" name="exercise" id="exercise" onsubmit="return validateExercise();">
					<table width="100%" cellpadding="0" cellspacing="0" border="0" class="module_content">
						<tr>
							<td height="30" width="20%">
								<?php echo WING_NAME; ?>:
							</td>
							<td width="80%">
								<strong>
								<?php 
									$wing = getNameById('wing', $cur_user_wing_id);
									echo $wing->name;
								?>
								</strong>
								<input type="hidden" name="wing_id" id="wing_id" value="<?php echo $cur_user_wing_id; ?>" />
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo SELECT_COURSE; ?>:
							</td>
							<td width="80%">
								<?php echo $courseList_opt; ?>
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo SELECT_EXERCISE_TYPE; ?>:
							</td>
							<td width="80%">
								<?php echo $exerciseTypeList_opt; ?>
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo EXERCISE_ID; ?>:
							</td>
							<td width="80%">
								<input name="exercise_id" id="exercise_id" type="text" class="inputbox" alt="Exercise ID" size="36" value="<?php echo $exercise_id; ?>" />
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo EXERCISE_NAME; ?>:
							</td>
							<td width="80%">
								<input name="name" id="name" type="text" class="inputbox" alt="Exercise Name" size="36" value="<?php echo $name; ?>" />
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo ASSESSMENT_SYSTEM; ?>:
							</td>
							<td width="80%">
								<?php echo $marking_opt; ?>
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo JOIN_EXCERCISE; ?>:
							</td>
							<td width="80%">
								<?php 
								
								if($join_course == 1){
									$join_type = ' checked="checked"';
								}
								echo '<input type="checkbox" name="join_course" '.$join_type .' />'
								?>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<input type="submit" name="Submit" class="button" value="Save" />
								<a href="<?php echo $_SERVER['HTTP_REFERER']; ?>"><input type="button" onclick="window.location='<?php echo $_SERVER['HTTP_REFERER']; ?>'"  name="cancel" class="cancel" value="<?php echo CANCEL; ?>" /></a>
							</td>
						</tr>		
					</table>	
					<input type="hidden" name="id" value="<?php echo $id; ?>" />
					<input type="hidden" name="action" value="save" />
					<input type="hidden" name="page" id="page" value="<?php echo $page; ?>" />
				</form>
			
	<?php }?>
</div>
			
<?php
require_once("includes/footer.php");
?>