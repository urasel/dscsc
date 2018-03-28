<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Exercise Type Management';
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
		
		$sql = "select * from ".DB_PREFIX."exercise_type WHERE wing_id = '".$cur_user_wing_id."' order by name asc";
		$exerciseTypeList = $dbObj->selectDataObj($sql);
		$action = 'view';
		$msg = $_REQUEST['msg'];
		
		//Pagination 
		if(!empty($exerciseTypeList)){
			$total_rows = sizeof($exerciseTypeList);
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
			$sql = "select * from ".DB_PREFIX."exercise_type WHERE id='".$id."'";	
			$exerciseTypeList = $dbObj->selectDataObj($sql);
			$exerciseType = $exerciseTypeList[0];
			
			//Make Sure wing manager of different wing cannot change an exercise_type by force from URL
			if($exerciseType->wing_id != $cur_user_wing_id){
				header("Location: dashboard.php");
				exit;
			}
			
			$name = $exerciseType->name;
		}else{
			$id = '';
			$wing_id = '';
			$name = '';
		}
		
		$action = 'insert';
		break;
		
	case 'save':	
		$id = $_POST['id'];
		$wing_id = $_POST['wing_id'];
		$name = $_POST['name'];
		$created_date_time = $last_updated_time = date('Y-m-d H:i:s');
		
		//Check for not inserting any blank entry
		if($name == ""){
			$msg = PARAM_MISSING;
			if(empty($id)){
				$url = 'exercise_type.php?action=create&msg='.$msg;
			}else{
				$url = 'exercise_type.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
			}
			redirect($url);
		}
		
		//Check if Exercise Type Name already exists in the db in same wing
		if(empty($id)){
			$sql = "select name from ".DB_PREFIX."exercise_type WHERE name = '".$name."' AND wing_id = '".$cur_user_wing_id."' limit 1";
			$exerciseTypeList = $dbObj->selectDataObj($sql);		
			
			if(!empty($exerciseTypeList)){
				$msg = EXERCISE_TYPE.' '.$name.ALREADY_EXISTS;
				$url = 'exercise_type.php?action=create&msg='.$msg;
				redirect($url);
			}			
		}else if(!empty($id)){
			$sql = "select name from ".DB_PREFIX."exercise_type WHERE id!='".$id."' AND name = '".$name."' AND wing_id = '".$cur_user_wing_id."' limit 1";
			$exerciseTypeList = $dbObj->selectDataObj($sql);		
			
			if(!empty($exerciseTypeList)){
				$msg = EXERCISE_TYPE.' '.$name.ALREADY_EXISTS;
				$url = 'exercise_type.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
				redirect($url);
			}
		}
		
		if(!empty($id)){
			$fields = array('wing_id' => $wing_id,
						'name' => $name,
						'updated_by' => $cur_user_id,
						'updated_datetime' => $last_updated_time,
						);
						
			$where = "id = '".$id."'";
			
			$update_status = $dbObj->updateTableData("exercise_type", $fields, $where);	
			
			if(!$update_status){
				$msg = EXERCISE_TYPE.' '.$name.COULD_NOT_BE_UPDATED;
				$action = 'insert';
			}else{
				$msg = EXERCISE_TYPE.' '.$name.HAS_BEEN_UPDATED;
				$url = 'exercise_type.php?action=view&page='.$page.'&msg='.$msg;
				redirect($url);
			}
		}else{
			$fields = array('wing_id' => $wing_id,
						'name' => $name,
						'created_by' => $cur_user_id,
						'created_datetime' => $created_date_time,
						'updated_by' => $cur_user_id,
						'updated_datetime' => $last_updated_time,
						);
			
			$inserted = $dbObj->insertTableData("exercise_type", $fields);	
			if(!$inserted){
				$msg = EXERCISE_TYPE.' '.$name.COULD_NOT_BE_CREATED;
				$action = 'insert';
			}else{
				$msg = EXERCISE_TYPE.' '.$name.CREATED_SUCCESSFULLY;
				$url = 'exercise_type.php?action=view&msg='.$msg;
				redirect($url);
			}
		}
		break;

	case 'delete':	
		$id = $_REQUEST['id'];	
		$sql = "select * from ".DB_PREFIX."exercise_type WHERE id='".$id."'";	
		$exerciseTypeList = $dbObj->selectDataObj($sql);
		$exerciseType = $exerciseTypeList[0];
		
		//Make Sure wing manager of different wing cannot change an exercise_type by force from URL
		if($exerciseType->wing_id != $cur_user_wing_id){
			header("Location: dashboard.php");
			exit;
		}
		
		$name = $exerciseType->name;
		$where = "id='".$id."'";	
		$success = $dbObj->deleteTableData("exercise_type", $where);	
		
		if(!$success){
			$msg = EXERCISE_TYPE.' '.$name.COULD_NOT_BE_DELETED;
		}else{
			$msg = EXERCISE_TYPE.' '.$name.HAS_BEEN_DELETED;
		}
		
		$url = 'exercise_type.php?action=view&page='.$page.'&msg='.$msg;
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
				<h1><?php echo EXERCISE_TYPE_MANAGEMENT; ?></h1>
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
										<b><a href="exercise_type.php?action=create"><?php echo CREATE; ?></a></b>
									</td>
								</tr>				
							<tr class="head">
								<td height="30" width="20%">
									<strong><?php echo EXERCISE_TYPE; ?></strong>
								</td>
								<td height="30" width="20%">
									<strong><?php echo ACTION; ?></strong>
								</td>
							</tr>
							<?php			
							if(!empty($exerciseTypeList)){	
								
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
									<tr <?php echo $class; ?>>
										<td width="20%">
											<?php echo $exerciseTypeList[$rownum]->name; ?> 
										</td>
										<td width="20%">								
											<a href="exercise_type.php?action=update&page=<?php echo $page; ?>&id=<?php echo $exerciseTypeList[$rownum]->id; ?>"><?php echo UPDATE; ?></a>
											<a href="exercise_type.php?action=delete&page=<?php echo $page; ?>&id=<?php echo $exerciseTypeList[$rownum]->id; ?>" onclick="return confirm('Are you sure you want to delete?');"><?php echo DELETE; ?></a>
										</td>
									</tr>
								<?php 
									}//for
								}else{ ?>
								<tr height="30">
									<td colspan="2">
										<?php echo EMPTY_DATA; ?>
									</td>
								</tr>
								<?php 
								}
								if($total_page > 1){ ?>
								<tr height="50">
									<td colspan="2">
										<?php 
										echo pagination($total_rows,$limit,$page,''); ?>
									</td>
								</tr>
								<?php } ?>
								<tr class="footer">
									<td colspan="2">
										<b><a href="exercise_type.php?action=create"><?php echo CREATE; ?></a></b>
									</td>
								</tr>				
						</table>
					</td>
				</tr>
			</table>
				
	<?php 
		}elseif($action=="insert"){ 
	?>
				<form action="exercise_type.php" method="post" name="exercise_type" id="exercise_type" onsubmit="return validateExerciseType();">
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
								<?php echo EXERCISE_TYPE; ?>:
							</td>
							<td width="80%">
								<input name="name" id="name" type="text" class="inputbox" alt="Exercise Type" size="36" value="<?php echo $name; ?>" />
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;
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