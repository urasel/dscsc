<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Unlock SI Requests';
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
	
		$sql = "select * from ".DB_PREFIX."ci_marking_lock where status = '2' AND wing_id = '".$cur_user_wing_id."'";
		$lockList = $dbObj->selectDataObj($sql);
		
		$sql = "select * from ".DB_PREFIX."impression_marking_lock where status = '2' AND course_id = '".$SIcourse->course_id."' AND term_id = '".$activeTerm->id."'";
		$termLockList = $dbObj->selectDataObj($sql);
		
		$sql = "select * from ".DB_PREFIX."si_impr_marking_lock where status = '2'";
		$impressionLockList = $dbObj->selectDataObj($sql);
		
		$sql = "select * from ".DB_PREFIX."term where lock_status = '2'";
		$termLockedArr = $dbObj->selectDataObj($sql);
		
		$action = 'view';
		$msg = $_REQUEST['msg'];
		break;
	
	case 'unlock':	
		$id = $_REQUEST['id'];
		$where = " id = '".$id."'";
		$fields = array('status' => '0', 'unlocked_by' => $cur_user_id);
		$unlock = $dbObj->updateTableData("ci_marking_lock", $fields, $where);		
		
		if($unlock){
			$sql = "select * from ".DB_PREFIX."ci_marking_lock where id = '".$id."'";
			$lockList = $dbObj->selectDataObj($sql);
			$lock = $lockList[0];
			$exr_nam = getNameById('exercise', $lock->exercise_id);
			
			$fields = array('ci_status' => '0');
			$where = " course_id = '".$lock->course_id."' AND term_id = '".$lock->term_id."' AND exercise_id = '".$lock->exercise_id."'";
			$unlock = $dbObj->updateTableData("marking", $fields, $where);	

			$msg = $exr_nam->name.RESULT_UNLOCKED;
			$url = 'unlock_si_req.php?action=view&msg='.$msg;
			redirect($url);
		}else{
			$msg = RESULT_NOT_UNLOCKED;		
			$url = 'unlock_si_req.php?action=view&msg='.$msg;
			redirect($url);
		}

		break;
		
	case 'deny':	
		$id = $_REQUEST['id'];
		
		$sql = "select * from ".DB_PREFIX."ci_marking_lock where id = '".$id."'";
		$lockList = $dbObj->selectDataObj($sql);
		$lock = $lockList[0];
		$exr_nam = getNameById('exercise', $lock->exercise_id);
		$requer = getNameById('user', $lock->locked_by);
			
		$where = " id = '".$id."'";
		$fields = array('status' => '1', 'unlocked_by' => $cur_user_id);
		$unlock = $dbObj->updateTableData("ci_marking_lock", $fields, $where);		
		
		if($unlock){
			$msg = 'Unlock request for '.$exr_nam->name.' from '.$requer->official_name.' has been denied';
		}else{
			$msg = 'Unlock request for '.$exr_nam->name.' from '.$requer->official_name.' could not be denied';
		}
		
		$url = 'unlock_si_req.php?action=view&msg='.$msg;
		redirect($url);

		break;
		
	case 'course_unlock':
		$id = $_GET['id'];
		
		$where = " id = '".$id."'";
		$fields = array('status' => '0', 'unlocked_by' => $cur_user_id);
		$unlock = $dbObj->updateTableData("si_impr_marking_lock", $fields, $where);	
		
		$msg = RESULT_UNLOCKED;
		$url = 'unlock_si_req.php?action=view&msg='.$msg;
		redirect($url);
	break;
	
	case 'course_deny':
		$id = $_GET['id'];
		
		$where = " id = '".$id."'";
		$fields = array('status' => '1', 'unlocked_by' => $cur_user_id);
		$unlock = $dbObj->updateTableData("si_impr_marking_lock", $fields, $where);	
		
		$msg = 'Request to Unlock Course has been denied';
		$url = 'unlock_si_req.php?action=view&msg='.$msg;
		redirect($url);
	break;
	
	case 'term_unlock':
		$id = $_GET['id'];
		
		$where = " id = '".$id."'";
		$fields = array('lock_status' => '0');
		$unlock = $dbObj->updateTableData("term", $fields, $where);	
		
		$msg = 'Term has been unlock successfully';
		$url = 'unlock_si_req.php?action=view&msg='.$msg;
		redirect($url);
	break;
	
	case 'term_deny':
		$id = $_GET['id'];
		
		$where = " id = '".$id."'";
		$fields = array('lock_status' => '1');
		$unlock = $dbObj->updateTableData("term", $fields, $where);	
		
		$msg = 'Request to Unlock Term has been denied';
		$url = 'unlock_si_req.php?action=view&msg='.$msg;
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
				<td><?php echo $msg; ?></td>
			</tr>
		</table>
	<?php
		}
	?>
	<table width="100%" cellspacing="0" cellpadding="0" border="0" class="module_header">
		<tr>
			<td>
				<h1><?php echo UNLOCK_SI_REQ; ?></h1>
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
						<h3><?php echo REQ_FOR_UNLOCK_EXR; ?></h3>
					</td>
				</tr>
				<tr>
					<td>
						<table width="100%" cellpadding="0" cellspacing="0" border="0" class="datagrid">
							<tr class="head">
								<td height="30" width="20%"><strong><?php echo EXERCISE; ?></strong></td>
								<td width="15%"><strong><?php echo COURSE; ?></strong></td>
								<td width="15%"><strong><?php echo TERM; ?></strong></td>
								<td width="25%"><strong><?php echo COMMENTS; ?></strong></td>
								<td width="15%"><strong><?php echo REQUEST_BY; ?></strong></td>
								<td width="10%"><strong><?php echo ACTION; ?></strong></td>
							</tr>
							<?php			
								$rownum = 0;
								if(!empty($lockList)){							
									foreach($lockList as $lock){		
										$class = (($rownum%2)==0) ? ' class="even"' : ' class="odd"';
										$exercise = getNameById('exercise', $lock->exercise_id);
										$course = getNameById('course', $lock->course_id);
										$term = getNameById('term', $lock->term_id);
										$request_by = getNameById('user', $lock->locked_by); 
										$requester_rank = getNameById('rank', $request_by->rank_id); 
							?>
									<tr <?php echo $class; ?>>
										<td height="30"><?php echo $exercise->name; ?> </td>
										<td><?php echo $course->name; ?></td>
										<td><?php echo $term->name; ?> </td>
										<td><?php echo $lock->comment;?> </td>
										<td><?php echo $requester_rank->short_name.'&nbsp;'.$request_by->official_name; ?></td>				
										<td>								
											<a href="unlock_si_req.php?action=unlock&id=<?php echo $lock->id; ?>" onclick="return confirm('Are you sure you want to Unlock this Result?');"><?php echo UNLOCK; ?></a>
											<a href="unlock_si_req.php?action=deny&id=<?php echo $lock->id; ?>" onclick="return confirm('Are you sure you want to Deny the Unlock Result?');"><?php echo DENY; ?></a>
										</td>
									</tr>
								<?php 
										$rownum++;
									}//foreach
								}else{ ?>
								<tr height="50">
									<td colspan="6"><?php echo NO_UNLOCK_REQUEST; ?></td>
								</tr>
								<?php } ?>	
								<tr class="footer">
									<td colspan="6">&nbsp;</td>
								</tr>				
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<h3><?php echo REQUEST_TO_UNLOCK_TERM; ?></h3>
					</td>
				</tr>
				<tr>
					<td>
						<table width="100%" cellpadding="0" cellspacing="0" border="0" class="datagrid">
							<tr class="head">
								<td height="30" width="30%"><strong><?php echo COURSE; ?></strong></td>
								<td width="30%"><strong><?php echo TERM; ?></strong></td>
								<td width="40%"><strong><?php echo ACTION; ?></strong></td>
							</tr>
							<?php			
								$rownum = 0;
								if(!empty($termLockedArr)){							
									foreach($termLockedArr as $term){
										$class = (($rownum%2)==0) ? ' class="even"' : ' class="odd"';
										$course = getNameById('course', $term->course_id); 
							?>
									<tr <?php echo $class; ?>>
										<td><?php echo $course->name; ?> </td>
										<td><?php echo $term->name; ?></td>
										<td>
											<a href="unlock_si_req.php?action=term_unlock&id=<?php echo $term->id; ?>" onclick="return confirm('Are you sure you want to Unlock this Term?');"><?php echo UNLOCK; ?></a><br />
											<a href="unlock_si_req.php?action=term_deny&id=<?php echo $term->id; ?>" onclick="return confirm('Are you sure you want to Deny the Unlock Request?');"><?php echo DENY; ?></a>
										</td>
									</tr>
								<?php 
										$rownum++;
									}//foreach
								}else{ ?>
								<tr height="50">
									<td colspan="3"><?php echo NO_UNLOCK_REQUEST; ?></td>
								</tr>
								<?php } ?>	
								<tr class="footer">
									<td colspan="3">&nbsp;</td>
								</tr>				
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<h3><?php echo REQ_FOR_UNLOCK_CRS; ?></h3>
					</td>
				</tr>
				<tr>
					<td>
						<table width="100%" cellpadding="0" cellspacing="0" border="0" class="datagrid">
							<tr class="head">
								<td height="30" width="15%"><strong><?php echo COURSE; ?></strong></td>
								<td width="25%"><strong><?php echo COMMENTS; ?></strong></td>
								<td width="15%"><strong><?php echo REQUEST_BY; ?></strong></td>
								<td width="10%"><strong><?php echo ACTION; ?></strong></td>
							</tr>
							<?php			
								$rownum = 0;
								if(!empty($impressionLockList)){							
									foreach($impressionLockList as $lock){
										$class = (($rownum%2)==0) ? ' class="even"' : ' class="odd"';
										$course = getNameById('course', $lock->course_id);
										$request_by = getNameById('user', $lock->si_id); 
										$requester_rank = getNameById('rank', $request_by->rank_id);
							?>
									<tr <?php echo $class; ?>>
										<td height="30"><?php echo $course->name;?> </td>
										<td><?php echo $lock->comment;?></td>
										<td><?php echo $requester_rank->short_name.'&nbsp;'.$request_by->official_name; ?></td>				
										<td>								
											<a href="unlock_si_req.php?action=course_unlock&id=<?php echo $lock->id; ?>" onclick="return confirm('Are you sure you want to Unlock this Result?');"><?php echo UNLOCK; ?></a><br />
											<a href="unlock_si_req.php?action=course_deny&id=<?php echo $lock->id; ?>" onclick="return confirm('Are you sure you want to Deny the Unlock Request?');"><?php echo DENY; ?></a>
										</td>
									</tr>
								<?php 
										$rownum++;
									}//foreach
								}else{ ?>
								<tr height="50">
									<td colspan="6"><?php echo NO_UNLOCK_REQUEST; ?></td>
								</tr>
								<?php } ?>	
								<tr class="footer">
									<td colspan="6">&nbsp;</td>
								</tr>				
						</table>
					</td>
				</tr>
			</table>
	<?php }	?>
</div>
			
<?php
require_once("includes/footer.php");
?>