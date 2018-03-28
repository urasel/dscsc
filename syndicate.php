<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Syndicate Management';
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
		
		$sql = "select * from ".DB_PREFIX."syndicate WHERE wing_id = '".$cur_user_wing_id."' or syndicate_type = 1 order by name asc";
		$syndicateList = $dbObj->selectDataObj($sql);
		$action = 'view';
		$msg = $_REQUEST['msg'];
		
		//Pagination 
		if(!empty($syndicateList)){
			$total_rows = sizeof($syndicateList);
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
			$sql = "select * from ".DB_PREFIX."syndicate WHERE id='".$id."'";	
			$syndicateList = $dbObj->selectDataObj($sql);
			$syndicate = $syndicateList[0];
			
			//Make Sure wing manager of different wing cannot change an Syndicate by force from URL
			if($syndicate->wing_id != $cur_user_wing_id){
				header("Location: dashboard.php");
				exit;
			}
			
			$name = $syndicate->name;
			$syndicate_type = $syndicate->syndicate_type;
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
		$syndicate_type = ($_POST['syndicate_type'] == 'on' ? 1:0);
		$created_date_time = $last_updated_time = date('Y-m-d H:i:s');
		
		//Check for not inserting any blank entry
		if($name == ""){
			$msg = PARAM_MISSING;
			if(empty($id)){
				$url = 'syndicate.php?action=create&msg='.$msg;
			}else{
				$url = 'syndicate.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
			}
			redirect($url);
		}
		
		//Check if Syndicate Name already exists in the db in same wing
		if(empty($id)){
			$sql = "select name from ".DB_PREFIX."syndicate WHERE name = '".$name."' AND wing_id = '".$cur_user_wing_id."' limit 1";
			$syndicateList = $dbObj->selectDataObj($sql);		
			
			if(!empty($syndicateList)){
				$msg = SYNDICATE.' '.$name.ALREADY_EXISTS;
				$url = 'syndicate.php?action=create&msg='.$msg;
				redirect($url);
			}			
		}else if(!empty($id)){
			$sql = "select name from ".DB_PREFIX."syndicate WHERE id!='".$id."' AND name = '".$name."' AND wing_id = '".$cur_user_wing_id."' limit 1";
			$syndicateList = $dbObj->selectDataObj($sql);		
			
			if(!empty($syndicateList)){
				$msg = SYNDICATE.' '.$name.ALREADY_EXISTS;
				$url = 'syndicate.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
				redirect($url);
			}
		}
		
		if(!empty($id)){
			$fields = array('wing_id' => $wing_id,
						'name' => $name,
						'syndicate_type' => $syndicate_type,
						'updated_by' => $cur_user_id,
						'updated_datetime' => $last_updated_time,
						);
						
			$where = "id = '".$id."'";
			
			$update_status = $dbObj->updateTableData("syndicate", $fields, $where);	
			
			if(!$update_status){
				$msg = SYNDICATE.' '.$name.COULD_NOT_BE_UPDATED;
				$action = 'insert';
			}else{
				$msg = SYNDICATE.' '.$name.HAS_BEEN_UPDATED;
				$url = 'syndicate.php?action=view&page='.$page.'&msg='.$msg;
				redirect($url);
			}
		}else{
			$fields = array('wing_id' => $wing_id,
						'name' => $name,
						'syndicate_type' => $syndicate_type,
						'created_by' => $cur_user_id,
						'created_datetime' => $created_date_time,
						'updated_by' => $cur_user_id,
						'updated_datetime' => $last_updated_time,
						);
			
			$inserted = $dbObj->insertTableData("syndicate", $fields);	
			if(!$inserted){
				$msg = SYNDICATE.' '.$name.COULD_NOT_BE_CREATED;
				$action = 'insert';
			}else{
				$msg = SYNDICATE.' '.$name.CREATED_SUCCESSFULLY;
				$url = 'syndicate.php?action=view&msg='.$msg;
				redirect($url);
			}
		}
		break;

	case 'delete':	
		$id = $_REQUEST['id'];
		$sql = "select * from ".DB_PREFIX."syndicate WHERE id='".$id."'";	
		$syndicateList = $dbObj->selectDataObj($sql);
		$syndicate = $syndicateList[0];
		
		//Make Sure wing manager of different wing cannot change an Syndicate by force from URL
		if($syndicate->wing_id != $cur_user_wing_id){
			header("Location: dashboard.php");
			exit;
		}
		
		$name = $syndicate->name;
		$where = "id='".$id."'";	
		
		$success = $dbObj->deleteTableData("syndicate", $where);	
		
		if(!$success){
			$msg = SYNDICATE.' '.$name.COULD_NOT_BE_DELETED;
		}else{
			$msg = SYNDICATE.' '.$name.HAS_BEEN_DELETED;
		}
		
		$url = 'syndicate.php?action=view&page='.$page.'&msg='.$msg;
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
				<h1><?php echo SYNDICATE_MANAGEMENT; ?></h1>
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
										<b><a href="syndicate.php?action=create"><?php echo CREATE; ?></a></b>
									</td>
								</tr>				
							<tr class="head">
								<td height="30" width="20%">
									<strong><?php echo SYNDICATE_NAME; ?></strong>
								</td>
								<td height="30" width="20%">
									<strong><?php echo IS_JOIN_SYNDICATE; ?></strong>
								</td>
								<td height="30" width="20%">
									<strong><?php echo ACTION; ?></strong>
								</td>
							</tr>
							<?php			
							if(!empty($syndicateList)){	
								
								if(($s+$limit) > $total_rows){
									$maxPageLimit = $total_rows;
								}else{
									$maxPageLimit = $s+$limit;
								}		
								
								$sl = ($limit*$page)-($limit-1);
								for($rownum = $s; $rownum <$maxPageLimit; $rownum++){		
									if(($rownum%2)==0){//even
										$class = ' class="even"';									
									}else{//odd
										$class = ' class="odd"';									
									}									
							?>
									<tr <?php echo $class; ?>>
										<td width="20%">
											<?php echo $syndicateList[$rownum]->name; ?> 
										</td>
										<td width="20%">
											<?php echo ($syndicateList[$rownum]->syndicate_type == 1 ? 'Yes': 'No') ?> 
										</td>
										<td width="20%">								
											<a href="syndicate.php?action=update&page=<?php echo $page; ?>&id=<?php echo $syndicateList[$rownum]->id; ?>"><?php echo UPDATE; ?></a>
											<a href="syndicate.php?action=delete&page=<?php echo $page; ?>&id=<?php echo $syndicateList[$rownum]->id; ?>" onclick="return confirm('Are you sure you want to delete?');"><?php echo DELETE; ?></a>
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
										<b><a href="syndicate.php?action=create"><?php echo CREATE; ?></a></b>
									</td>
								</tr>				
						</table>
					</td>
				</tr>
			</table>
				
	<?php 
		}elseif($action=="insert"){ 
	?>
				<form action="syndicate.php" method="post" name="syndicate" id="syndicate" onsubmit="return validateSyndicate();">
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
								<?php echo SYNDICATE_NAME; ?>:
							</td>
							<td width="80%">
								<input name="name" id="name" type="text" class="inputbox" alt="Exercise Type" size="36" value="<?php echo $name; ?>" />
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo IS_JOIN_SYNDICATE; ?>:
							</td>
							<td width="80%">
								<?php 
								
								if($syndicate_type == 1){
									$syndicate_type = ' checked="checked"';
								}
								echo '<input type="checkbox" name="syndicate_type" '.$syndicate_type .' />'
								?>
							</td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<input type="submit" name="submit" class="button" value="Save" />
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