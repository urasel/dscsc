<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Relate Syn to Course';
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
		$courseList_opt = formSelectElement($courseId, $course_id, 'course_id', 'onchange = processFunction("assign_syndicate")');
		
		$action = 'view';
		$msg = $_REQUEST['msg'];
		break;
		
	case 'save':	
		$course_id = $_POST['course_id'];
		$crs_nam = getNameById('course', $course_id);
		$syndicate_id = $_POST['syndicate_id'];
		
		//Check for not inserting any blank entry
		if($course_id == '0'){
			$msg = PARAM_MISSING;
			$url = 'relate_syn_to_crs.php?action=view&msg='.$msg;
			redirect($url);
		}
		
		//Delete Exixting Syndicate From syndicate_to_course Table
		$sql = 'DELETE from '.DB_PREFIX.'syndicate_to_course where course_id='.$course_id;	
			
		if($dbObj->executeData($sql)){	
			//Insert New Syndicate to syndicate_to_course Table
			if(!empty($syndicate_id)){
				$sql = 'INSERT INTO '.DB_PREFIX.'syndicate_to_course (`wing_id`,`course_id`, `syndicate_id`) VALUES';
				foreach($syndicate_id as $syndicate){
					$sql .= "('".$cur_user_wing_id."','".$course_id."','".$syndicate."'),";						
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
			$msg = SYN_SUCCESSFULLY_ASSIGNED_IN.$crs_nam->name;
		}
					
		$url = 'relate_syn_to_crs.php?action=view&msg='.$msg;
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
				<h1><?php echo RELATE_SYNDICATE_WITH_COURSE; ?></h1>
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
		<form action="relate_syn_to_crs.php" method="post" name="relate_syn_to_crs" id="relate_syn_to_crs" onsubmit="return validateSyndicateCourse();">
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
					<td colspan="2">
						<div id="loaderContainer"></div>
						<div id="syndicate_display"></div>
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
