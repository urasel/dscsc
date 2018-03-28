<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Unlock DS Requests';
$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$action = $_REQUEST['action'];
$msg = '';

//Chek if this user is valid for this file
if($cur_user_group_id != '4'){	//only SI can unlock DS requests
	header("Location: dashboard.php");	
}

switch($action){
	case 'view':	
	default:
	
		//Find Currently Active Course/Courses which is assigned to this SI from si_to_course
		$sql = "select stc.course_id from ".DB_PREFIX."si_to_course as stc,  ".DB_PREFIX."course as crs where stc.si_id = ".$cur_user_id." AND stc.wing_id = '".$cur_user_wing_id."' AND crs.status = '0' AND crs.id = stc.course_id";
		$SIcourseArray = $dbObj->selectDataObj($sql);
		$SIcourse = $SIcourseArray[0];
		
		//Find Active Term
		$sql = "select * from ".DB_PREFIX."term where wing_id = '".$cur_user_wing_id."' AND status = '0'";
		$activeTermArr = $dbObj->selectDataObj($sql);
		$activeTerm = $activeTermArr[0];
		
		$sql = "select * from ".DB_PREFIX."marking_lock where status = '2' AND course_id = '".$SIcourse->course_id."' AND term_id = '".$activeTerm->id."'";
		$lockList = $dbObj->selectDataObj($sql);
		
		$sql = "select * from ".DB_PREFIX."impression_marking_lock where status = '2' AND course_id = '".$SIcourse->course_id."' AND term_id = '".$activeTerm->id."'";
		$termLockList = $dbObj->selectDataObj($sql);
		
		$action = 'view';
		$msg = $_REQUEST['msg'];
		break;
	
	case 'deny':	
		$id = $_REQUEST['id'];
		
		$sql = "select * from ".DB_PREFIX."marking_lock where id = '".$id."'";
		$primStatArr = $dbObj->selectDataObj($sql);
		$primtStat = $primStatArr[0];
		$exer = getNameById('exercise', $primtStat->exercise_id);
		$requer = getNameById('user', $primtStat->locked_by);
		
		$where = " id = '".$id."'";
		$fields = array('status' => '1', 'unlocked_by' => $cur_user_id );
		$deny = $dbObj->updateTableData("marking_lock", $fields, $where);
		
		if($deny){
			$msg = 'Unlock request from '.$requer->official_name.' for '.$exer->name.' has been denied';
			$url = 'unlock_ds_req.php?action=view&msg='.$msg;
			redirect($url);
		}else{
			$msg = 'Unlock request from '.$requer->official_name.' for '.$exer->name.' could not be denied';
			$url = 'unlock_ds_req.php?action=view&msg='.$msg;
			redirect($url);
		}
		
		break;
		
	case 'unlock':	
		$id = $_REQUEST['id'];
		
		$sql = "select * from ".DB_PREFIX."marking_lock where id = '".$id."'";
		$primStatArr = $dbObj->selectDataObj($sql);
		$primtStat = $primStatArr[0];
		
		$sql = "select * from ".DB_PREFIX."ci_marking_lock where course_id = '".$primtStat->course_id."' AND term_id = '".$primtStat->term_id."' AND exercise_id = '".$primtStat->exercise_id."'";
		$targetStatArr = $dbObj->selectDataObj($sql);
		$targetStat = $targetStatArr[0];
		$target_status = $targetStat->status;
		
		if(($target_status == 1) || ($target_status == 2)){
			$msg = 'The Request could not be Unlock because it is Locked from CI level';
			$url = 'unlock_ds_req.php?action=view&msg='.$msg;
			redirect($url);
		}
		
		$where = " id = '".$id."'";
		$fields = array('status' => '0', 'unlocked_by' => $cur_user_id );
		
		$unlock = $dbObj->updateTableData("marking_lock", $fields, $where);		
		
		if($unlock){
			$sql = "select * from ".DB_PREFIX."marking_lock where id = '".$id."'";
			$lockList = $dbObj->selectDataObj($sql);
			$lock = $lockList[0];
			$exr_nam = getNameById('exercise', $lock->exercise_id);
			
			$fields = array(
						'status' => '0',
						'si_mod_marking' => '0',
						'si_sign' => '',
						'ds_student_weight' => '0',
						'ci_mod_marking' => '0',
						'ci_student_weight' => '0',
						'ci_sign' => ''
						);
			$where = " course_id = '".$lock->course_id."' AND term_id = '".$lock->term_id."' AND exercise_id = '".$lock->exercise_id."' AND syndicate_id = '".$lock->syndicate_id."'";
			
			$unlock = $dbObj->updateTableData("marking", $fields, $where);	

			$msg = $exr_nam->name.RESULT_UNLOCKED;
			$url = 'unlock_ds_req.php?action=view&msg='.$msg;
			redirect($url);
		}else{
			$msg = RESULT_NOT_UNLOCKED;		
			$url = 'unlock_ds_req.php?action=view&msg='.$msg;
			redirect($url);
		}

		break;
		
	case 'term_deny':	
		$id = $_REQUEST['id'];
		
		//Get Requested IDed Information
		$sql = "select * from ".DB_PREFIX."impression_marking_lock where id = '".$id."'";
		$termLockList = $dbObj->selectDataObj($sql);
		$lock = $termLockList[0];
		$trm_nam = getNameById('term', $lock->term_id);
		$requer = getNameById('user', $lock->locked_by);
		
		$where = " id = '".$id."'";
		$fields = array('status' => '1', 'unlocked_by' => $cur_user_id);
		$term_deny = $dbObj->updateTableData("impression_marking_lock", $fields, $where);		
		
		if($term_deny){
			$msg = 'Unlock request from '.$requer->official_name.' for '.$trm_nam->name.' has been denied';
		}else{
			$msg = 'Unlock request from '.$requer->official_name.' for '.$trm_nam->name.' could not be denied';
		}//else
		
		$url = 'unlock_ds_req.php?action=view&msg='.$msg;
		redirect($url);
		
		break;
	
	case 'term_unlock':	
		$id = $_REQUEST['id'];
		$where = " id = '".$id."'";
		$fields = array('status' => '0', 'unlocked_by' => $cur_user_id );
		$unlock = $dbObj->updateTableData("impression_marking_lock", $fields, $where);		
		
		if($unlock){
			$sql = "select * from ".DB_PREFIX."impression_marking_lock where id = '".$id."'";
			$termLockList = $dbObj->selectDataObj($sql);
			$lock = $termLockList[0];
			$trm_nam = getNameById('term', $lock->term_id);
			
			$where = " course_id = '".$lock->course_id."' AND term_id = '".$lock->term_id."' AND syndicate_id = '".$lock->syndicate_id."'";
			$fields = array('status' => '0');
			$unlock = $dbObj->updateTableData("impression_marking", $fields, $where);	

			$msg = $trm_nam->name.RESULT_UNLOCKED;
			$url = 'unlock_ds_req.php?action=view&msg='.$msg;
			redirect($url);
		}else{
			$msg = RESULT_NOT_UNLOCKED;		
			$url = 'unlock_ds_req.php?action=view&msg='.$msg;
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
				<h1><?php echo UNLOCK_REQUEST_LOCK; ?></h1>
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
										$lock_by = getNameById('user', $lock->locked_by);
										$requester_rank = getNameById('rank', $lock_by->rank_id);
							?>
									<tr <?php echo $class; ?>>
										<td height="30"><?php echo $exercise->name; ?></td>
										<td><?php echo $course->name; ?></td>
										<td><?php echo $term->name; ?> </td>
										<td><?php echo $lock->comment;?> </td>
										<td><?php echo $requester_rank->short_name.' '.$lock_by->official_name; ?></td>				
										<td>								
											<a href="unlock_ds_req.php?action=unlock&id=<?php echo $lock->id; ?>" onclick="return confirm('SI & CI Moderation marks will be deleted if you Unlock this Result. Continue?');"><?php echo UNLOCK; ?></a>
											<a href="unlock_ds_req.php?action=deny&id=<?php echo $lock->id; ?>" onclick="return confirm('Are you sure you want to Deny the Unlock Request?');"><?php echo DENY; ?></a>
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
			
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="module_content">
				<tr>
					<td>
						<h3><?php echo REQ_FOR_UNLOCK_TRM_RES; ?></h3>
					</td>
				</tr>
				<tr>
					<td>
						<table width="100%" cellpadding="0" cellspacing="0" border="0" class="datagrid">
							<tr class="head">
								<td height="30" width="15%"><strong><?php echo COURSE; ?></strong></td>
								<td width="15%"><strong><?php echo TERM; ?></strong></td>
								<td width="30%"><strong><?php echo COMMENTS; ?></strong></td>
								<td width="15%"><strong><?php echo REQUEST_BY; ?></strong></td>
								<td width="5%"><strong><?php echo ACTION; ?></strong></td>
							</tr>
							<?php			
								$rownum = 0;
								if(!empty($termLockList)){							
									foreach($termLockList as $termLock){		
										$class = (($rownum%2)==0) ? ' class="even"' : ' class="odd"';
										$course = getNameById('course', $termLock->course_id);
										$term = getNameById('term', $termLock->term_id);
										$lock_by = getNameById('user', $termLock->locked_by); 
										$requester_rank = getNameById('rank', $lock_by->rank_id);
							?>
									<tr <?php echo $class; ?>>
										<td height="30"><?php echo $course->name; ?>  </td>
										<td><?php echo $term->name; ?></td>
										<td><?php echo $termLock->comment;?> </td>
										<td><?php echo $requester_rank->short_name.' '.$lock_by->official_name; ?> </td>				
										<td>								
											<a href="unlock_ds_req.php?action=term_unlock&id=<?php echo $termLock->id; ?>" onclick="return confirm('Are you sure you want Unlock this result?');"><?php echo UNLOCK; ?></a>
											<a href="unlock_ds_req.php?action=term_deny&id=<?php echo $termLock->id; ?>" onclick="return confirm('Are you sure you want Deny the Unlock Request?');"><?php echo DENY; ?></a>
										</td>
									</tr>
								<?php 
										$rownum++;
									}//foreach
								}else{ ?>
								<tr height="50">
									<td colspan="5"><?php echo NO_UNLOCK_REQUEST; ?></td>
								</tr>
								<?php } ?>	
								<tr class="footer">
									<td colspan="5">&nbsp;</td>
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