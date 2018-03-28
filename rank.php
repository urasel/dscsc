<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Rank Management';
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
		
		$sql = "select * from ".DB_PREFIX."rank WHERE wing_id = '".$cur_user_wing_id."' order by weight asc";
		$rankList = $dbObj->selectDataObj($sql);
		$action = 'view';
		$msg = $_REQUEST['msg'];
		
		//Pagination 
		if(!empty($rankList)){
			$total_rows = sizeof($rankList);
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
			$sql = "select * from ".DB_PREFIX."rank WHERE id='".$id."'";	
			$rankList = $dbObj->selectDataObj($sql);
			$rank = $rankList[0];
			
			//Make Sure wing manager of different wing cannot change a rank by force from URL
			if($rank->wing_id != $cur_user_wing_id){
				header("Location: dashboard.php");
				exit;
			}
			
			$wing_id = $rank->wing_id;
			$name = $rank->name;
			$short_name = $rank->short_name;
			$weight = $rank->weight;
		}else{
			$id = '';
			$wing_id = '';
			$name = '';
			$short_name = '';
			$weight = '';
		}
		
		$action = 'insert';
		break;
		
	case 'save':	
		$id = $_POST['id'];
		$wing_id = $_POST['wing_id'];
		$name = $_POST['name'];
		$short_name = $_POST['short_name'];
		$weight = $_POST['weight'];
		$created_date_time = $last_updated_time = date('Y-m-d H:i:s');
		
		//Check for not inserting any blank entry
		if($name == "" || $short_name == "" || $weight == ""){
			$msg = PARAM_MISSING;
			if(empty($id)){
				$url = 'rank.php?action=create&msg='.$msg;
			}else{
				$url = 'rank.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
			}
			redirect($url);
		}
		
		//Check if Rank Name already exists in the db in same wing
		if(empty($id)){
			$sql = "select name from ".DB_PREFIX."rank WHERE name = '".$name."' AND wing_id = '".$cur_user_wing_id."' limit 1";
			$rankList = $dbObj->selectDataObj($sql);		
			
			if(!empty($rankList)){
				$msg = RANK.' '.$name.ALREADY_EXISTS;
				$url = 'rank.php?action=create&msg='.$msg;
				redirect($url);
			}			
		}else if(!empty($id)){
			$sql = "select name from ".DB_PREFIX."rank WHERE id!='".$id."' AND name = '".$name."' AND wing_id = '".$cur_user_wing_id."' limit 1";
			$rankList = $dbObj->selectDataObj($sql);		
			
			if(!empty($rankList)){
				$msg = RANK.' '.$name.ALREADY_EXISTS;
				$url = 'rank.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
				redirect($url);
			}
		}
		
		//Check if Rank Short Name already exists in the db in same wing
		if(empty($id)){
			$sql = "select short_name from ".DB_PREFIX."rank WHERE short_name = '".$short_name."' AND wing_id = '".$cur_user_wing_id."' limit 1";
			$rankList = $dbObj->selectDataObj($sql);		
			
			if(!empty($rankList)){
				$msg = RANK_SHORT_NAME.' '.$short_name.ALREADY_EXISTS;
				$url = 'rank.php?action=create&msg='.$msg;
				redirect($url);
			}			
		}else if(!empty($id)){
			$sql = "select short_name from ".DB_PREFIX."rank WHERE id!='".$id."' AND short_name = '".$short_name."' AND wing_id = '".$cur_user_wing_id."' limit 1";
			$rankList = $dbObj->selectDataObj($sql);		
			
			if(!empty($rankList)){
				$msg = RANK_SHORT_NAME.' '.$short_name.ALREADY_EXISTS;
				$url = 'rank.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
				redirect($url);
			}
		}
		
		//Check if Rank Weight already exists in the db in same wing
		if(empty($id)){
			$sql = "select weight from ".DB_PREFIX."rank WHERE weight = '".$weight."' AND wing_id = '".$cur_user_wing_id."' limit 1";
			$rankList = $dbObj->selectDataObj($sql);		
			
			if(!empty($rankList)){
				$msg = RANK_ORDER.' '.$weight.ALREADY_EXISTS;
				$url = 'rank.php?action=create&msg='.$msg;
				redirect($url);
			}			
		}else if(!empty($id)){
			$sql = "select weight from ".DB_PREFIX."rank WHERE id!='".$id."' AND weight = '".$weight."' AND wing_id = '".$cur_user_wing_id."' limit 1";
			$rankList = $dbObj->selectDataObj($sql);		
			
			if(!empty($rankList)){
				$msg = RANK_ORDER.' '.$weight.ALREADY_EXISTS;
				$url = 'rank.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
				redirect($url);
			}
		}
		
		if(!empty($id)){
			$fields = array('wing_id' => $wing_id,
						'name' => $name,
						'short_name' => $short_name,
						'weight' => $weight,
						'updated_by' => $cur_user_id,
						'updated_datetime' => $last_updated_time
						);
						
			$where = "id = '".$id."'";
			
			$update_status = $dbObj->updateTableData("rank", $fields, $where);	
			
			if(!$update_status){
				$msg = RANK.' '.$name.COULD_NOT_BE_UPDATED;	
				$action = 'insert';
			}else{
				$msg = RANK.' '.$name.HAS_BEEN_UPDATED;
				$url = 'rank.php?action=view&page='.$page.'&msg='.$msg;
				redirect($url);
			}
		}else{
			$fields = array('wing_id' => $wing_id,
						'name' => $name,
						'short_name' => $short_name,
						'weight' => $weight,
						'created_by' => $cur_user_id,
						'created_datetime' => $created_date_time,
						'updated_by' => $cur_user_id,
						'updated_datetime' => $last_updated_time
						);
			
			$inserted = $dbObj->insertTableData("rank", $fields);	
			if(!$inserted){
				$msg = RANK.' '.$name.COULD_NOT_BE_CREATED;
				$action = 'insert';
			}else{
				$msg = RANK.' '.$name.CREATED_SUCCESSFULLY;
				$url = 'rank.php?action=view&msg='.$msg;
				redirect($url);
			}
		}
		break;

	case 'delete':	
		$id = $_REQUEST['id'];	
		$sql = "select * from ".DB_PREFIX."rank WHERE id='".$id."'";	
		$rankList = $dbObj->selectDataObj($sql);
		$rank = $rankList[0];
		
		//Make Sure wing manager of different wing cannot change a rank by force from URL
		if($rank->wing_id != $cur_user_wing_id){
			header("Location: dashboard.php");
			exit;
		}
			
		$name = $rank->name;
		$where = "id='".$id."'";	
		
		$success = $dbObj->deleteTableData("rank", $where);	
		
		if(!$success){
			$msg = RANK.' '.$name.COULD_NOT_BE_DELETED;
		}else{
			$msg = RANK.' '.$name.HAS_BEEN_DELETED;
		}
		
		$url = 'rank.php?action=view&page='.$page.'&msg='.$msg;
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
				<h1><?php echo RANK_MANAGEMENT; ?></h1>
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
										<b><a href="rank.php?action=create"><?php echo CREATE; ?></a></b>
									</td>
								</tr>				
							<tr class="head">
								<td height="30" width="20%">
									<strong><?php echo RANK_NAME; ?></strong>
								</td>
								<td height="30" width="20%">
									<strong><?php echo SHORT_NAME; ?></strong>
								</td>
								<td height="30" width="20%">
									<strong><?php echo ORDER; ?></strong>
								</td>
								<td height="30" width="20%">
									<strong><?php echo ACTION; ?></strong>
								</td>
							</tr>
							<?php			
							if(!empty($rankList)){	
								
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
											<?php echo $rankList[$rownum]->name; ?> 
										</td>
										<td width="20%">
											<?php echo $rankList[$rownum]->short_name; ?> 
										</td>
										<td width="20%">
											<?php echo $rankList[$rownum]->weight; ?> 
										</td>				
										<td width="20%">								
											<a href="rank.php?action=update&page=<?php echo $page; ?>&id=<?php echo $rankList[$rownum]->id; ?>"><?php echo UPDATE; ?></a>
											<a href="rank.php?action=delete&page=<?php echo $page; ?>&id=<?php echo $rankList[$rownum]->id; ?>" onclick="return confirm('Are you sure you want to delete?');"><?php echo DELETE; ?></a>
										</td>
									</tr>
								<?php 
									}//for
								}else{ ?>
								<tr height="30">
									<td colspan="4">
										<?php echo EMPTY_DATA; ?>
									</td>
								</tr>
								<?php 
								}
								if($total_page > 1){ ?>
								<tr height="50">
									<td colspan="4">
										<?php 
										echo pagination($total_rows,$limit,$page,''); ?>
									</td>
								</tr>
								<?php } ?>	
								<tr class="footer">
									<td colspan="4">
										<b><a href="rank.php?action=create"><?php echo CREATE; ?></a></b>
									</td>
								</tr>				
						</table>
					</td>
				</tr>
			</table>
				
	<?php 
		}elseif($action=="insert"){ 
	?>
				<form action="rank.php" method="post" name="rank" id="rank" onsubmit="return validateRank();">
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
								<?php echo RANK_NAME; ?>:
							</td>
							<td width="80%">
								<input name="name" id="name" type="text" class="inputbox" alt="Rank Name" size="36" value="<?php echo $name; ?>" />
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo SHORT_NAME; ?>:
							</td>
							<td width="80%">
								<input name="short_name" id="short_name" type="text" class="inputbox" alt="Rank Short Name" size="36" value="<?php echo $short_name; ?>" />
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo ORDER; ?>:
							</td>
							<td width="80%">
								<input name="weight" id="weight" type="text" class="inputbox" alt="Weight" size="36" maxlength="2" value="<?php echo $weight; ?>" onkeyup="return isNUM('weight')" />
								<span class="required_field">*</span>
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