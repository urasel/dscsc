<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}

$title = 'User Group Management';
$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$action = $_REQUEST['action'];
$msg = '';

//Chek if this user is valid for this file
if($cur_user_group_id != '1'){
	header("Location: dashboard.php");	
}

switch($action){
	case 'view':	
	default:
		
		$sql = "select * from ".DB_PREFIX."user_group order by name asc";
		$userGroupList = $dbObj->selectDataObj($sql);
		$action = 'view';
		$msg = $_REQUEST['msg'];
		break;
		
	case 'update':
	case 'create':
	
		$msg = $_REQUEST['msg'];
		if(!empty($_REQUEST['id'])){
			$id = $_REQUEST['id'];
			$sql = "select * from ".DB_PREFIX."user_group WHERE id='".$id."'";	
			$groupList = $dbObj->selectDataObj($sql);
			$group = $groupList[0];
			$name = $group->name;
			$info = $group->info;
		}else{
			$id = '';
			$name = '';
			$info = '';
		}
		
		$action = 'insert';
		break;
		
	case 'save':	
		$id = $_POST['id'];
		$name = $_POST['name'];
		$info = $_POST['info'];
		$created_date_time = $last_updated_time = date('Y-m-d H:i:s');
		
		//Check for not inserting any blank entry
		if($name == ""){
			$msg = PARAM_MISSING;
			if(empty($id)){
				$url = 'user_group.php?action=create&msg='.$msg;
			}else{
				$url = 'user_group.php?action=update&id='.$id.'&msg='.$msg;
			}
			redirect($url);
		}
		
		//check repeation of User Group
		if(empty($id)){
			$sql = "select name from ".DB_PREFIX."user_group WHERE name = '".$name."' limit 1";
			$userGroupList = $dbObj->selectDataObj($sql);		
			
			if(!empty($userGroupList)){
				$msg = USER_GROUP.' '.$name.ALREADY_EXISTS;
				$url = 'user_group.php?action=create&msg='.$msg;
				redirect($url);
			}			
		}else if(!empty($id)){
			$sql = "select name from ".DB_PREFIX."user_group WHERE id!='".$id."' AND name = '".$name."' limit 1";
			$userGroupList = $dbObj->selectDataObj($sql);		
			
			if(!empty($userGroupList)){
				$msg = USER_GROUP.' '.$name.ALREADY_EXISTS;
				$url = 'user_group.php?action=update&id='.$id.'&msg='.$msg;
				redirect($url);
			}
		}
		
		if(!empty($id)){
			$fields = array('name' => $name,
						'info' => $info,
						'updated_by' => $cur_user_id,
						'updated_datetime' => $last_updated_time,
						);
						
			$where = "id = '".$id."'";
			
			$update_status = $dbObj->updateTableData("user_group", $fields, $where);	
			
			if(!$update_status){
				$msg = USER_GROUP.' '.$name.COULD_NOT_BE_UPDATED;		
				$action = 'insert';
			}else{
				$msg = USER_GROUP.' '.$name.HAS_BEEN_UPDATED;
				$url = 'user_group.php?action=view&msg='.$msg;
				redirect($url);
			}
		}else{
			$fields = array('name' => $name,
						'info' => $info,
						'created_by' => $cur_user_id,
						'created_datetime' => $created_date_time,
						'updated_by' => $cur_user_id,
						'updated_datetime' => $last_updated_time,
						);
			
			$inserted = $dbObj->insertTableData("user_group", $fields);	
			if(!$inserted){
				$msg = USER_GROUP.' '.$name.COULD_NOT_BE_CREATED;	
				$action = 'insert';
			}else{
				$msg = USER_GROUP.' '.$name.CREATED_SUCCESSFULLY;
				$url = 'user_group.php?action=view&msg='.$msg;
				redirect($url);
			}
		}
		break;

	/*case 'delete':	
		$id = $_REQUEST['id'];
		$sql = "select * from ".DB_PREFIX."user_group WHERE id='".$id."'";	
		$groupList = $dbObj->selectDataObj($sql);
		$group = $groupList[0];
		$name = $group->name;
			
		$where = "id='".$id."'";	
		
		$success = $dbObj->deleteTableData("user_group", $where);	
		
		if(!$success){
			$msg = USER_GROUP.' '.$name.COULD_NOT_BE_DELETED;
		}else{
			$msg = USER_GROUP.' '.$name.DELETED_SUCCESSFULLY;
		}
		
		$url = 'user_group.php?action=view&msg='.$msg;
		redirect($url);
		break;*/

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
				<h1><?php echo USER_GROUP_MANAGEMENT; ?></h1>
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
										<!--<b><a href="user_group.php?action=create"><?php //echo CREATE; ?></a></b>-->
									</td>
								</tr>				
							<tr class="head">
								<td height="30" width="20%">
									<strong><?php echo GROUP_NAME; ?></strong>
								</td>
								<td height="30" width="50%">
									<strong><?php echo GROUP_INFO; ?></strong>
								</td>
								<td height="30" width="20%">
									<strong><?php echo ACTION; ?></strong>
								</td>
							</tr>
							<?php			
								$rownum = 0;							
								foreach($userGroupList as $group){		
									if(($rownum%2)==0){//even
										$class = ' class="even"';									
									}else{//odd
										$class = ' class="odd"';									
									}									
							?>
									<tr <?php echo $class; ?>>
										<td width="20%">
											<?php echo $group->name; ?> 
										</td>					
										<td width="50%">
											<?php 
												if($group->info == ''){
													echo NOT_SELECTED;
												}else {
													echo $group->info;
												}
											?>
										</td>
										<td width="20%">								
											<a href="user_group.php?action=update&id=<?php echo $group->id; ?>"><?php echo UPDATE; ?></a>
											<!--<a href="user_group.php?action=delete&id=<?php //echo $group->id; ?>" onclick="return confirm('Are you sure you want to delete?');"><?php //echo DELETE; ?></a>-->
										</td>
									</tr>
								<?php 
										$rownum++;
									}//foreach 
								?>	
								<tr class="footer">
									<td colspan="4">
										<!--<b><a href="user_group.php?action=create"><?php //echo CREATE; ?></a></b>
-->									</td>
								</tr>				
						</table>
					</td>
				</tr>
			</table>
				
	<?php 
		}elseif($action=="insert"){ 
	?>
				<form action="user_group.php" method="post" name="user_group" id="user_group" onsubmit="return validateUserGroup();">
					<table width="100%" cellpadding="0" cellspacing="0" border="0" class="module_content">
						<tr>
							<td height="30" width="20%">
								<?php echo GROUP_NAME; ?>:
							</td>
							<td width="80%">
								<input name="name" id="name" type="text" class="inputbox" alt="Group Name" size="36" value="<?php echo $name; ?>" />
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo GROUP_INFO; ?>:
							</td>
							<td width="80%">
								<input name="info" id="info" type="text" class="inputbox" alt="Group Info" size="36" value="<?php echo $info; ?>" />
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
			
	<?php }?>
</div>
			
<?php
require_once("includes/footer.php");
?>