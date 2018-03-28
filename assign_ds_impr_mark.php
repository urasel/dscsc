<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'DS Impression Marks';
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
		$sql = "select id, name from ".DB_PREFIX."course WHERE wing_id = '".$cur_user_wing_id."' AND status = '0' order by name asc";
		$courseArr = $dbObj->selectDataObj($sql);
		
		$courseId = array();
		$courseId[0] = SELECT_COURSE_OPT;
		if(!empty($courseArr)){			
			foreach($courseArr as $item){
				$courseId[$item->id] = $item->name;
			}	
		}			
		$courseList_opt = formSelectElement($courseId, $course_id, 'course_id', 'onchange = processFunction("assign_term_ds_mark")');
		
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
		
	case 'save':	
		$term_id = $_POST['term_id'];
		$trm_nam = getNameById('term', $term_id);
		$course_id = $_POST['course_id'];
		$crs_nam = getNameById('course', $course_id);
		$ds_impr_mark = $_POST['ds_impr_mark'];
		$ds_impr_mark_limit = $_POST['ds_impr_mark_limit'];
		
		//Check for not inserting any blank entry
		if($course_id == '0' || $term_id == '0' || $ds_impr_mark == "" || $ds_impr_mark_limit == ""){
			$msg = PARAM_MISSING;
			if(empty($id)){
				$url = 'assign_ds_impr_mark.php?action=create&msg='.$msg;
			}else{
				$url = 'assign_ds_impr_mark.php?action=update&id='.$id.'&msg='.$msg;
			}
			redirect($url);
		}
		
		$fields = array('ds_impr_mark' => $ds_impr_mark,
					'ds_impr_mark_limit' => $ds_impr_mark_limit
					);
					
		$where = "id = '".$term_id."'";
		
		$update_status = $dbObj->updateTableData("term", $fields, $where);	
		
		if(!$update_status){
			$msg = MARKS_COULD_NOT_UPDATED_FOR.$trm_nam->name.', '.$crs_nam->name;
			$action = 'view';
		}else{
			$msg = MARKS_UPDATED_FOR.$trm_nam->name.', '.$crs_nam->name;
			$url = 'assign_ds_impr_mark.php?action=view&msg='.$msg;
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
				<h1><?php echo DS_IMPR_MARK; ?></h1>
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
		<form action="assign_ds_impr_mark.php" method="post" name="assign_ds_impr_mark" id="assign_ds_impr_mark" onsubmit="return validateDsImprMark();">
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
							<span class="required_field">*</span>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<div id="loaderContainer"></div>
						<div id="ds_impr_mark_display"></div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
					<input type="submit" name="Submit" class="button" value="Save" />
						<a href="<?php echo $_SERVER['HTTP_REFERER']; ?>"><input type="button" onclick="window.location='<?php echo $_SERVER['HTTP_REFERER']; ?>'"  name="cancel" class="cancel" value="<?php echo CANCEL; ?>" /></a>
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