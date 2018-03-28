<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Course Management';
$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$action = $_REQUEST['action'];
$msg = '';

//Chek if this user is valid for this file
if($cur_user_group_id != '2'){
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
		
		$sql = "select * from ".DB_PREFIX."course WHERE wing_id = '".$cur_user_wing_id."' order by name asc";
		$courseList = $dbObj->selectDataObj($sql);
		$action = 'view';
		$msg = $_REQUEST['msg'];
		
		//Pagination 
		if(!empty($courseList)){
			$total_rows = sizeof($courseList);
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
			$sql = "select * from ".DB_PREFIX."course WHERE id='".$id."'";	
			$courseList = $dbObj->selectDataObj($sql);
			$course = $courseList[0];
			
			//Make Sure wing admin of different wing cannot change a course by force from URL
			if($course->wing_id != $cur_user_wing_id){
				header("Location: dashboard.php");
				exit;
			}
			
			$name = $course->name;
			$session = $course->session;
			$ini_start_date = $course->start_date;
			$exploded_start_date = explode('-', $ini_start_date);
			$start_date = $exploded_start_date[2].'-'.$exploded_start_date[1].'-'.$exploded_start_date[0];
			$ini_end_date = $course->end_date;
			$exploded_end_date = explode('-', $ini_end_date);
			$end_date = $exploded_end_date[2].'-'.$exploded_end_date[1].'-'.$exploded_end_date[0];
			$weeks = $course->weeks;
		}else{
			$id = '';
			$wing_id = '';
			$name = '';
			$start_date = '';
			$end_date = '';
			$weeks = '';
		}
		
		$action = 'insert';
		break;
		
	case 'save':	
		$id = $_POST['id'];
		$wing_id = $_POST['wing_id'];
		$name = $_POST['name'];
		$session = $_POST['session'];
		$ini_start_date = $_POST['start_date'];
		$exploded_start_date = explode('-', $ini_start_date);
		$start_date = $exploded_start_date[2].'-'.$exploded_start_date[1].'-'.$exploded_start_date[0];
		$ini_end_date = $_POST['end_date'];
		$exploded_end_date = explode('-', $ini_end_date);
		$end_date = $exploded_end_date[2].'-'.$exploded_end_date[1].'-'.$exploded_end_date[0];
		$weeks = $_POST['weeks'];
		$created_date_time = $last_updated_time = date('Y-m-d H:i:s');
		
		//Check for not inserting any blank entry
		if($name == "" || $start_date == "" || $end_date == ""){
			$msg = PARAM_MISSING;
			if(empty($id)){
				$url = 'course.php?action=create&msg='.$msg;
			}else{
				$url = 'course.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
			}
			redirect($url);
		}
		
		//Check if Course Name already exists in the db in same wing
		if(empty($id)){
			$sql = "select name from ".DB_PREFIX."course WHERE name = '".$name."' AND wing_id = '".$cur_user_wing_id."' limit 1";
			$courseList = $dbObj->selectDataObj($sql);		
			
			if(!empty($courseList)){
				$msg = $name.ALREADY_EXISTS;
				$url = 'course.php?action=create&msg='.$msg;
				redirect($url);
			}			
		}else if(!empty($id)){
			$sql = "select name from ".DB_PREFIX."course WHERE id!='".$id."' AND name = '".$name."' AND wing_id = '".$cur_user_wing_id."' limit 1";
			$courseList = $dbObj->selectDataObj($sql);		
			
			if(!empty($courseList)){
				$msg = $name.ALREADY_EXISTS;
				$url = 'course.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
				redirect($url);
			}
		}
		
		if(!empty($id)){
			$fields = array('wing_id' => $wing_id,
						'name' => $name,
						'session' => $session,
						'start_date' => $start_date,
						'end_date' => $end_date,
						'weeks' => $weeks,
						'updated_by' => $cur_user_id,
						'updated_datetime' => $last_updated_time,
						);
						
			$where = "id = '".$id."'";
			
			$update_status = $dbObj->updateTableData("course", $fields, $where);	
			
			if(!$update_status){
				$msg = $name.COULD_NOT_BE_UPDATED;		
				$action = 'insert';
			}else{
				$msg = $name.HAS_BEEN_UPDATED;
				$url = 'course.php?action=view&page='.$page.'&msg='.$msg;
				redirect($url);
			}
		}else{
			$fields = array('wing_id' => $wing_id,
						'name' => $name,
						'session' => $session,
						'start_date' => $start_date,
						'end_date' => $end_date,
						'weeks' => $weeks,
						'weeks' => $weeks,
						'status' => '1',
						'created_by' => $cur_user_id,
						'created_datetime' => $created_date_time,
						'updated_by' => $cur_user_id,
						'updated_datetime' => $last_updated_time,
						);
			
			$inserted = $dbObj->insertTableData("course", $fields);	
			if(!$inserted){
				$msg = $name.COULD_NOT_BE_CREATED;	
				$action = 'insert';
			}else{
				$msg = $name.CREATED_SUCCESSFULLY;
				$url = 'course.php?action=view&msg='.$msg;
				redirect($url);
			}
		}
		break;

	case 'delete':	
		$id = $_REQUEST['id'];	
		$sql = "select * from ".DB_PREFIX."course WHERE id='".$id."'";	
		$courseList = $dbObj->selectDataObj($sql);
		$course = $courseList[0];
		
		//Make Sure wing admin of different wing cannot change a course by force from URL
		if($course->wing_id != $cur_user_wing_id){
			header("Location: dashboard.php");
			exit;
		}
			
		$name = $course->name;
		$session = $course->session;
		
		$where = "id='".$id."'";	
		$success = $dbObj->deleteTableData("course", $where);
		
		//If a course is deleted all data from diffrent tables like term, exercise, student, all results under this course will be deleted
		$where = "course_id='".$id."'";
		$ci_marking_lock_deleted = $dbObj->deleteTableData("ci_marking_lock", $where);
		$ds_to_course_deleted = $dbObj->deleteTableData("ds_to_course", $where);
		$exercise_deleted = $dbObj->deleteTableData("exercise", $where);
		$exercise_to_term_deleted = $dbObj->deleteTableData("exercise_to_term", $where);
		$impression_marking_deleted = $dbObj->deleteTableData("impression_marking", $where);
		$impression_marking_lock_deleted = $dbObj->deleteTableData("impression_marking_lock", $where);
		$marking_deleted = $dbObj->deleteTableData("marking", $where);
		$marking_lock_deleted = $dbObj->deleteTableData("marking_lock", $where);
		$si_impression_marking_deleted = $dbObj->deleteTableData("si_impression_marking", $where);
		$si_impr_marking_lock_deleted = $dbObj->deleteTableData("si_impr_marking_lock", $where);
		$si_to_course_deleted = $dbObj->deleteTableData("si_to_course", $where);
		$student_deleted = $dbObj->deleteTableData("student", $where);
		$student_to_syndicate_deleted = $dbObj->deleteTableData("student_to_syndicate", $where);
		$syndicate_to_course_deleted = $dbObj->deleteTableData("syndicate_to_course", $where);
		$term_deleted = $dbObj->deleteTableData("term", $where);
		
		//if problem arrise to delete, a message will confirm which table is responsible for qeury;
		if(!$ci_marking_lock_deleted){
			$msg = "Could not delete ci_marking_lock";
		}else if(!$ds_to_course_deleted){
			$msg = "Could not delete ds_to_course";
		}else if(!$exercise_deleted){
			$msg = "Could not delete exercise";
		}else if(!$exercise_to_term_deleted){
			$msg = "Could not delete exercise_to_term";
		}else if(!$impression_marking_deleted){
			$msg = "Could not delete impression_marking";
		}else if(!$impression_marking_lock_deleted){
			$msg = "Could not delete impression_marking_lock";
		}else if(!$marking_deleted){
			$msg = "Could not delete marking";
		}else if(!$marking_lock_deleted){
			$msg = "Could not delete marking_lock";
		}else if(!$si_impression_marking_deleted){
			$msg = "Could not delete si_impression_marking";
		}else if(!$si_impr_marking_lock_deleted){
			$msg = "Could not delete si_impr_marking_lock";
		}else if(!$si_to_course_deleted){
			$msg = "Could not delete si_to_course";
		}else if(!$student_to_syndicate_deleted){
			$msg = "Could not delete student_to_syndicate";
		}else if(!$syndicate_to_course_deleted){
			$msg = "Could not delete syndicate_to_course";
		}else if(!$term_deleted){
			$msg = "Could not delete term";
		}
		
		if(!$success){
			$msg = $name.COULD_NOT_BE_DELETED;
		}else{
			$msg = $name.HAS_BEEN_DELETED;
		}
		
		$url = 'course.php?action=view&page='.$page.'&msg='.$msg;
		redirect($url);
		break;
	
	case 'status':	
		$id = $_REQUEST['id'];
		$sql = "select * from ".DB_PREFIX."course where id = '".$id."'";
		$courseArr = $dbObj->selectDataObj($sql);
		$courseId = $courseArr[0];
		
		//Make Sure wing admin of different wing cannot change a course by force from URL
		if($courseId->wing_id != $cur_user_wing_id){
			header("Location: dashboard.php");
			exit;
		}
		
		$name = $courseId->name;
		$session = $courseId->session;
		$courseStatus = $courseId->status;
		
		if($courseStatus == '0'){		
			$fields = array('status' => '1');
			$stat_msg = 'Disabled';
		}else{
			//More than one Courses in a wing can be active simultaniously
			$sql = "select * from ".DB_PREFIX."course  where wing_id = '".$courseId->wing_id."' AND status = '0'";
			$isEnableArr = $dbObj->selectDataObj($sql);
			
			if(!empty($isEnableArr)){
				$msg = 'Please, Disable the Active Course first!';
				$url = 'course.php?action=view&page='.$page.'&msg='.$msg;
				redirect($url);
			}
		
			$fields = array('status' => '0');
			$stat_msg = 'Enabled';
		}
		
		$where = "id='".$id."'";	
		$success = $dbObj->updateTableData("course", $fields, $where);	
		
		if(!$success){
			$msg = $name.COULD_NOT_BE_UPDATED;
		}else{
			$msg = $name.HAS_BEEN.' '.$stat_msg.SUCCESSFULLY;
		}
		
		$url = 'course.php?action=view&page='.$page.'&msg='.$msg;
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
				<h1><?php echo COURSE_MANAGEMENT; ?></h1>
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
								<td colspan="4" style=" background:#EEEEEE;">
									<b><a href="course.php?action=create"><?php echo CREATE; ?></a></b>
								</td>
							</tr>				
							<tr class="head">
								<td height="30" width="15%">
									<strong><?php echo COURSE; ?></strong>
								</td>
								<td height="30" width="15%">
									<strong><?php echo SESSION; ?></strong>
								</td>
								<td height="30" width="40%" align="center">
									<strong><?php echo DURATION; ?></strong>
								</td>
								<td height="30" width="20%">
									<strong><?php echo NUMBER_OF_WEEKS; ?></strong>
								</td>
								<td height="30" width="10%">
									<strong><?php echo STATUS; ?></strong>
								</td>
								<td height="30" width="10%">
									<strong><?php echo ACTION; ?></strong>
								</td>
							</tr>
							<?php
							if(!empty($courseList)){	
								if(($s+$limit) > $total_rows){
									$maxPageLimit = $total_rows;
								}else{
									$maxPageLimit = $s+$limit;
								}
								
								for($rownum = $s; $rownum <$maxPageLimit; $rownum++){	
									if(($rownum%2)==0){//even
										$class = ' class="even"';									
									}else{//odd
										$class = ' class="odd"';									
									}									
							?>
							<tr <?php echo $class; ?> <?php if($courseList[$rownum]->status == '0'){echo 'style="color:#FF3333;"';} ?>>
								<td width="15%">
									<?php echo $courseList[$rownum]->name; ?> 
								</td>
								<td width="15%">
									<?php echo $courseList[$rownum]->session; ?> 
								</td>
								<td width="40%" align="center">
									<?php echo dateConvertion($courseList[$rownum]->start_date).' - '.dateConvertion($courseList[$rownum]->end_date); ?> 
								</td>
								<td width="20%" align="center">
									<?php echo $courseList[$rownum]->weeks; ?> 
								</td>
								<td width="10%">
								<?php
									if($courseList[$rownum]->status == '0'){
										echo ACTIVE;
									}else{
										echo INACTIVE;
									}
								?> 
								</td>				
								<td width="10%">
									<a href="course.php?action=update&page=<?php echo $page; ?>&id=<?php echo $courseList[$rownum]->id; ?>"><?php echo UPDATE; ?></a>
									<a href="course.php?action=delete&page=<?php echo $page; ?>&id=<?php echo $courseList[$rownum]->id; ?>" onclick="return confirmDeleteCourse();"><?php echo DELETE; ?></a>
									<a href="course.php?action=status&page=<?php echo $page; ?>&id=<?php echo $courseList[$rownum]->id; ?>" onclick="return confirm('Are you sure you want to <?php if($courseList[$rownum]->status == '0'){echo INACTIVATE; } else {echo ACTIVATE;}?> this Course?');">
									<?php
										if($courseList[$rownum]->status == '0'){
											echo DISABLE;
										}else{
											echo ENABLE;
										}
									?>
									</a>
								</td>
							</tr>
							<?php 
								}//for
							}else{ ?>
							<tr height="30">
								<td colspan="8">
									<?php echo EMPTY_DATA; ?>
								</td>
							</tr>
							<?php 
							}	
							if($total_page > 1){ ?>
							<tr height="50">
								<td colspan="6">
									<?php echo pagination($total_rows,$limit,$page,''); ?>
								</td>
							</tr>
							<?php } ?>
							<tr class="footer">
								<td colspan="6">
									<b><a href="course.php?action=create"><?php echo CREATE; ?></a></b>
								</td>
							</tr>				
						</table>
					</td>
				</tr>
			</table>
				
	<?php 
		}elseif($action=="insert"){ 
	?>
				<form action="course.php" method="post" name="course" id="course" onsubmit="return validateCourse();" onmousemove="return days_between('start_date','end_date')">
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
								<?php echo COURSE; ?>:
							</td>
							<td width="80%">
								<input name="name" id="name" type="text" class="inputbox" alt="course Name" size="36" value="<?php echo $name; ?>" />
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo SESSION; ?>:
							</td>
							<td width="80%">
								<input name="session" id="name" type="text" class="inputbox" alt="course Name" size="36" value="<?php echo $session; ?>" />
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo INITIAL_DATE; ?>:
							</td>
							<td width="80%">
								<input type="text" id="start_date" name="start_date" readonly="readonly" value="<?php echo $start_date; ?>" />
									<img id="f_rangeStart_trigger" src="date/src/css/img/calendar.gif" title="Pick a Date" />
									<img id="f_clearRangeStart" src="date/src/css/img/no.png" title="Clear Date" onClick="return makeEmpty('start_date')" height="16" width="16"  />
									<script type="text/javascript">
									  RANGE_CAL_1 = new Calendar({
											  inputField: "start_date",
											  dateFormat: "%d-%m-%Y",
											  trigger: "f_rangeStart_trigger",
											  bottomBar: true,
											  onSelect: function() {
													  var date = Calendar.intToDate(this.selection.get());
													  this.hide();
											  }
									  });
									</script>
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo TERMINATION_DATE; ?>:
							</td>
							<td width="80%">
								<input type="text" id="end_date" name="end_date" readonly="readonly" value="<?php echo $end_date; ?>" />
									<img id="f_rangeStart_triggerr" src="date/src/css/img/calendar.gif" title="Pick a Date" />
									<img id="f_clearRangeStart" src="date/src/css/img/no.png" title="Clear Date" onClick="return makeEmpty('end_date')" height="16" width="16"  />
									<script type="text/javascript">
									  RANGE_CAL_1 = new Calendar({
											  inputField: "end_date",
											  dateFormat: "%d-%m-%Y",
											  trigger: "f_rangeStart_triggerr",
											  bottomBar: true,
											  onSelect: function() {
													  var date = Calendar.intToDate(this.selection.get());
													  this.hide();
											  }
									  });
									</script>
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo NUMBER_OF_WEEKS; ?>:
							</td>
							<td width="80%">
								<input name="weeks" id="weeks" type="text" class="inputbox" alt="Number of Weeks" size="20" readonly="readonly" value="<?php echo $weeks; ?>" />
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