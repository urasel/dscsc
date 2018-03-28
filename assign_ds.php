<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Assign DS';
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
		$courseList_opt = formSelectElement($courseId, $course_id, 'course_id', 'onchange = processFunction("course_to_term_syn")');
		
		//Build Term Array	
		$query = "select id, name from ".DB_PREFIX."term where course_id = '".$course_id."' AND status = '0' order by name asc";
		$termArr = $dbObj->selectDataObj($query);
		
		$termId = array();
		$termId[0] = SELECT_TERM_OPT;
		if(!empty($termArr)){			
			foreach($termArr as $item){
				$termId[$item->id] = $item->name;
			}	
		}			
		$termList_opt = formSelectElement($termId, $term_id, 'term_id');
		
		//Build Syndicate Array	
		$query = "select syn.id, syn.name from ".DB_PREFIX."syndicate as syn, ".DB_PREFIX."syndicate_to_course as stc where stc.course_id = '".$course_id."' order by syn.name asc";
		$synArr = $dbObj->selectDataObj($query);
		
		$synId = array();
		$synId[0] = SELECT_SYNDICATE_OPT;
		if(!empty($synArr)){			
			foreach($synArr as $item){
				$synId[$item->id] = $item->name;
			}	
		}			
		$syndicateList_opt = formSelectElement($synId, $syndicate_id, 'syndicate_id');
			
		//set Action	
		$action = 'view';
		$msg = $_REQUEST['msg'];
		break;
		
	case 'save':	
		$course_id = $_POST['course_id'];
		$crs_name = getNameById('course', $course_id);
		$term_id = $_POST['term_id'];
		$trm_name = getNameById('term', $term_id);
		$syndicate_id = $_POST['syndicate_id'];
		$syn_name = getNameById('syndicate', $syndicate_id);
		$ds_id = $_POST['ds_id'];
		$wing_id = $_POST['wing_id'];
		
		//Check for not inserting any blank entry
		if($course_id == '0' || $term_id == '0' || $syndicate_id == '0'){
			$msg = PARAM_MISSING;
			if(empty($id)){
				$url = 'assign_ds.php?action=create&msg='.$msg;
			}else{
				$url = 'assign_ds.php?action=update&id='.$id.'&msg='.$msg;
			}
			redirect($url);
		}
		
		//Delete All data from ds_to_course Table of selected Syndicate/Term if no ds is selected
		$sql = 'DELETE from '.DB_PREFIX.'ds_to_course where syndicate_id='.$syndicate_id.' AND term_id = '.$term_id;
			
		if($dbObj->executeData($sql)){	
			//Insert New DS to ds_to_course Table
			if(!empty($ds_id)){
				$syndicateQuery = "select id, syndicate_type from ".DB_PREFIX."syndicate WHERE id = '".$syndicate_id."'";
				$syndicate = $dbObj->selectDataObj($syndicateQuery);
				$syndicateType = $syndicate[0]->syndicate_type;
				if($syndicateType == 1){
					
				}
				$sql = 'INSERT INTO '.DB_PREFIX.'ds_to_course (`wing_id`,`course_id`,`term_id`,`syndicate_id`,`syndicatetype`, `ds_id`) VALUES';
				foreach($ds_id as $ds){
					$wingid = $wing_id[$ds];
					if($syndicateType == 1){
						$sql .= "('".$wingid."','".$course_id."','".$term_id."','".$syndicate_id."','1','".$ds."'),";
					}else{
						$sql .= "('".$wingid."','".$course_id."','".$term_id."','".$syndicate_id."','0','".$ds."'),";
					}
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
			$msg = DS_SUCCESSFULLY_ASSIGNED_IN.$crs_name->name.', '.$trm_name->name.', '.$syn_name->name;
		}
					
		$url = 'assign_ds.php?action=view&msg='.$msg;
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
				<h1><?php echo ASSIGN_DS; ?></h1>
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
	?>		<form action="assign_ds.php" method="post" name="assign_ds" id="assign_ds" onsubmit="return validateAssignDS();">
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
							<?php echo SELECT_TERM; ?>:
						</td>
						<td width="80%">
							<div id="term_display">
							<?php echo $termList_opt; ?>
							<span class="required_field">*</span>
							</div>
						</td>
					</tr>
					<tr>
						<td height="30" width="20%">
							<?php echo SELECT_SYNDICATE; ?>:
						</td>
						<td width="80%">
							<div id="syndicate_display">
							<?php echo $syndicateList_opt; ?>
							<span class="required_field">*</span>
							</div>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<div id="loaderContainer"></div>
							<div id="ds_display"></div>
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