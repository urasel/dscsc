<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Assign SI';
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
		$courseList_opt = formSelectElement($courseId, $course_id, 'course_id', 'onchange = processFunction("assign_si")');
		
		$action = 'view';
		$msg = $_REQUEST['msg'];
		break;
		
	case 'save':	
		$course_id = $_POST['course_id'];
		$si_id = $_POST['si_id'];
		$crs_name = getNameById('course', $course_id);
		
		//Check for not inserting any blank entry
		if($course_id == '0'){
			$msg = PARAM_MISSING;
			if(empty($id)){
				$url = 'assign_si.php?action=create&msg='.$msg;
			}else{
				$url = 'assign_si.php?action=update&id='.$id.'&msg='.$msg;
			}
			redirect($url);
		}
		
		//Delete Existing SI From si_to_course Table
		$sql = 'DELETE from '.DB_PREFIX.'si_to_course where course_id='.$course_id;	
			
		if($dbObj->executeData($sql)){	
			//Insert New SI to si_to_course Table
			if(!empty($si_id)){
				$sql = 'INSERT INTO '.DB_PREFIX.'si_to_course (`wing_id`,`course_id`, `si_id`) VALUES';
				foreach($si_id as $si){
					$sql .= "('".$cur_user_wing_id."','".$course_id."','".$si."'),";						
				}
				$sql = rtrim($sql, ",");
				$sql = $sql.';';
				//save to Database	
				$update_status = $dbObj->executeData($sql);		
			}
		}
		
		if(!$update_status){
			$msg = ALL_FIELD_EMPTY;		
		}else{
			$msg = SI_SUCCESSFULLY_ASSIGNED_IN.$crs_name->name;
		}
					
		$url = 'assign_si.php?action=view&msg='.$msg;
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
				<h1><?php echo ASSIGN_SI; ?></h1>
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
	?>		<form action="assign_si.php" method="post" name="assign_si" id="assign_si" onsubmit="return validateAssignSI();">
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
						<td colspan="2">
							<div id="loaderContainer"></div>
							<div id="si_display"></div>
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
			</form>
				
	<?php } ?>
				
					
</div>
			
<?php
require_once("includes/footer.php");
?>