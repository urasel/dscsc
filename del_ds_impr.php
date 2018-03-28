<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Assign Marks';
$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$action = $_REQUEST['action'];
$msg = '';

//Chek if this user is valid for this file
if($cur_user_group_id != '2'){			
	header("Location: dashboard.php");		//Only DS can give DS Marks
}

switch($action){
	case 'view':	
	default:
		
		//Build Course Array	
		$query = "select id, name from ".DB_PREFIX."course where wing_id = ".$cur_user_wing_id." AND status = '0' ORDER BY name";
		$courseArr = $dbObj->selectDataObj($query);
		
		$courseId = array();
		$courseId[0] = SELECT_COURSE_OPT;
		if(!empty($courseArr)){			
			foreach($courseArr as $item){
				$courseId[$item->id] = $item->name;
			}	
		}			
		$courseList_opt = formSelectElement($courseId, $course_id, 'course_id', ' onchange = processFunction("assign_term_for_delete")');
		
		//Build Term Array	
		$query = "select id, name from ".DB_PREFIX."term where wing_id = ".$cur_user_wing_id." AND course_id = '".$course_id."' AND status = '1' ORDER BY order_id asc";
		$termArr = $dbObj->selectDataObj($query);

		$termId = array();
		$termId[0] = SELECT_TERM_OPT;
		if(!empty($termArr)){			
			foreach($termArr as $item){
				$termId[$item->id] = $item->name;
			}	
		}			
		$termList_opt = formSelectElement($termId, $term_id, 'exercise_id', ' onchange = processFunction("assign_exercise_for_delete")');
		
		$action = 'view';
		$msg = $_REQUEST['msg'];
		break;
		
	case 'save':	
		
		$course_id = $_POST['course_id'];
		$term_id = $_POST['term_id'];
		
		$chk_sql = 'DELETE from '.DB_PREFIX.'marking where course_id = '.$course_id.' AND term_id = '.$term_id;
		$chk_delete = $dbObj->executeData($chk_sql);
		
		$sql = 'DELETE from '.DB_PREFIX.'marking_lock where course_id = '.$course_id.' AND term_id = '.$term_id;
		$mark_lock_delete = $dbObj->executeData($sql);
		
		$sql = 'DELETE from '.DB_PREFIX.'ci_marking_lock where course_id = '.$course_id.' AND term_id = '.$term_id;
		$ci_lock_delete = $dbObj->executeData($sql);
		
				$sql = 'DELETE from '.DB_PREFIX.'impression_marking_lock where course_id = '.$course_id.' AND term_id = '.$term_id;
		$lock_delete = $dbObj->executeData($sql);		
				
		$sql = 'DELETE from '.DB_PREFIX.'impression_marking where course_id = '.$course_id.' AND term_id = '.$term_id;
		$delete = $dbObj->executeData($sql);		
		
		if(!$delete){
			$msg = 'Marks could not be delted';		
			$action = 'view';
		}else{
			$msg = 'Marks deleted successfully';
			$url = 'del_ds_impr.php?action=view&msg='.$msg;
			redirect($url);
		}
		break;

}//switch

require_once("includes/templates.php");
require_once("templates/top_menu.php");
require_once("templates/left_menu.php");
?>

<div id="right_column">
	<?php if(!empty($msg)){ ?>
		<table id="system_message">
			<tr>
				<td>
					<?php echo $msg; ?>
				</td>
			</tr>
		</table>
	<?php } ?>
	<table width="100%" cellspacing="0" cellpadding="0" border="0" class="module_header">
		<tr>
			<td>
				<h1><?php echo DELETE_DS_IMPR_MARK; ?></h1>
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
			<form action="del_ds_impr.php" method="post" name="del_ds_impr" id="del_ds_impr" onsubmit="return validateExerciseDelete();">
				<table cellpadding="0" cellspacing="0" border="0" width="700" class="module_content">
					<tr>
						<td height="30" width="20%">
							<?php echo COURSE; ?>:
						</td>
						<td width="80%">
							<?php echo $courseList_opt; ?>
							<span class="required_field"> *</span>
						</td>
					</tr>
					<tr>
						<td height="30">
							<?php echo TERM; ?>:
						</td>
						<td>
							<div id="delete_term_display">
								<?php echo $termList_opt; ?>
								<span class="required_field"> *</span>
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td height="auto" align="left">
							<div id="is_deleteable"></div>
						</td>
					</tr>
				</table>
				<input type="hidden" name="action" value="save" />
			</form>
						
	<?php } ?>
</div>
			
<?php
require_once("includes/footer.php");
?>