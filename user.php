<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}

$title = 'User Management';
$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$action = $_REQUEST['action'];
$msg = '';

//Chek if this user is valid for this file
if($cur_user_group_id > '3'){
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
		
		if($cur_user_group_id == '1'){
			$sql = "select * from ".DB_PREFIX."user order by group_id, wing_id, username asc";
		}else if ($cur_user_group_id == '2' || $cur_user_group_id == '3'){
			$sql = "select * from ".DB_PREFIX."user where wing_id = '".$cur_user_wing_id."' AND group_id > '1' order by group_id, wing_id, username asc";
		}
		
		$userList = $dbObj->selectDataObj($sql);
		$action = 'view';
		$path_view = 'attach_file/';
		$msg = $_REQUEST['msg'];
		
		//Pagination 
		if(!empty($userList)){
			$total_rows = sizeof($userList);
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
			$sql = "select * from ".DB_PREFIX."user WHERE id='".$id."'";	
			$userList = $dbObj->selectDataObj($sql);
			$user = $userList[0];
			if(($cur_user_group_id == 2) && ($user->wing_id != $cur_user_wing_id || $user->group_id < $cur_user_group_id)){
				echo UNKNOWN_INPUT;
				exit;
			}
			$wing_id = $user->wing_id;
			$group_id = $user->group_id;
			$rank_id = $user->rank_id;
			$appointment_id = $user->appointment_id;
			$ba_no = $user->ba_no;
			$full_name = $user->full_name;
			$username = $user->username;
			$official_name = $user->official_name;
			$photo = $user->photo;
			$sec_key = $user->sec_key;
			
		}else{
			$id = '';
			$wing_id = '';
			$group_id = '';
			$rank_id = '';
			$appointment_id = '';
			$ba_no = '';
			$full_name = '';
			$username = '';
			$password = '';
			$official_name = '';
			$photo = '';
			$sec_key = generateSecKey();
		}
		
		//Build Group Array
		if($cur_user_group_id == '1'){
			$sql = "select id, name from ".DB_PREFIX."user_group order by id asc";
		}else if($cur_user_group_id == '2'){
			$sql = "select id, name from ".DB_PREFIX."user_group where id > '2' order by id asc";
		}else if($cur_user_group_id == '3'){
			$sql = "select id, name from ".DB_PREFIX."user_group where id > '3' order by id asc";
		}
		$groupArr = $dbObj->selectDataObj($sql);
		
		$wingId = array();
		$groupId[0] = SELECT_GROUP_OPT;
		if(!empty($groupArr)){			
			foreach($groupArr as $item){
				$groupId[$item->id] = $item->name;
			}	
		}			
		$groupList_opt = formSelectElement($groupId, $group_id, 'group_id');
		
		//Build Wing Array
		$sql = "select id, name from ".DB_PREFIX."wing order by name asc";
		$wingArr = $dbObj->selectDataObj($sql);
		
		$wingId = array();
		$wingId[0] = SELECT_WING_OPT;
		if(!empty($wingArr)){			
			foreach($wingArr as $item){
				$wingId[$item->id] = $item->name;
			}	
		}			
		$wingList_opt = formSelectElement($wingId, $wing_id, 'wing_id', 'onchange = processFunction("assign_rank_appoint")');
		
		//Build Rank Array
		if($cur_user_group_id == '1'){
			$sql = "select id, name from ".DB_PREFIX."rank where wing_id = '".$wing_id."' order by weight asc";
		}else if($cur_user_group_id == '2' || $cur_user_group_id == '3'){
			$sql = "select id, name from ".DB_PREFIX."rank where wing_id = '".$cur_user_wing_id."' order by weight asc";
		}
		$rankArr = $dbObj->selectDataObj($sql);
		
		$rankId = array();
		$rankId[0] = SELECT_RANK_OPT;
		if(!empty($rankArr)){			
			foreach($rankArr as $item){
				$rankId[$item->id] = $item->name;
			}	
		}			
		$rankList_opt = formSelectElement($rankId, $rank_id, 'rank_id');
		
		//Build Appointment Array
		if($cur_user_group_id == '1'){
			$sql = "select id, name from ".DB_PREFIX."appointment where wing_id = '".$wing_id."' order by `order` asc";
		}else if($cur_user_group_id == '2' || $cur_user_group_id == '3'){
			$sql = "select id, name from ".DB_PREFIX."appointment where wing_id = '".$cur_user_wing_id."' order by `order` asc";
		}
		$appointmentArr = $dbObj->selectDataObj($sql);
		
		$appointmentId = array();
		$appointmentId[0] = SELECT_APPOINTMENT_OPT;
		if(!empty($appointmentArr)){			
			foreach($appointmentArr as $item){
				$appointmentId[$item->id] = $item->name;
			}	
		}			
		$appointmentList_opt = formSelectElement($appointmentId, $appointment_id, 'appointment_id');
		
		
		$action = 'insert';
		break;
		
	case 'save':
	
		$id = $_POST['id'];
		$wing_id = $_POST['wing_id'];
		$group_id = $_POST['group_id'];
		$rank_id = $_POST['rank_id'];
		$appointment_id = $_POST['appointment_id'];
		$ba_no = $_POST['ba_no'];
		$full_name = $_POST['full_name'];
		$username = $_POST['username'];
		$password = $_POST['password'];
		$sec_key = $_POST['sec_key'];
		$official_name = $_POST['official_name'];
		$photo = $_POST['photo'];
		$created_date_time = $last_updated_time = date('Y-m-d H:i:s');

		//Check for not inserting any blank entry
		if($group_id == '0' || $wing_id == '0' || $ba_no == "" || $full_name == "" || $official_name == "" || $username == "" || $sec_key == ""){
			$msg = PARAM_MISSING;
			if(empty($id)){
				$url = 'user.php?action=create&msg='.$msg;
			}else{
				$url = 'user.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
			}
			redirect($url);
		}
		
		//check repeation of Username
		if(empty($id)){
			$sql = "select username from ".DB_PREFIX."user WHERE username = '".$username."' limit 1";
			$userList = $dbObj->selectDataObj($sql);		
			
			if(!empty($userList)){
				$msg = USERNAME.' '.$username.ALREADY_EXISTS;
				$url = 'user.php?action=create&msg='.$msg;
				redirect($url);
			}			
		}else if(!empty($id)){
			$sql = "select username from ".DB_PREFIX."user WHERE id!='".$id."' AND username = '".$username."' limit 1";
			$userList = $dbObj->selectDataObj($sql);		
			
			if(!empty($userList)){
				$msg = USERNAME.' '.$username.ALREADY_EXISTS;
				$url = 'user.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
				redirect($url);
			}
		}
		
		//Upload file
		$path = './attach_file/';
		
		$uploaded = upload_file($_FILES, $path, $cur_user_id);	
		
		if($uploaded['error_counter'] != '0'){
			$msg = implode("<br />",$uploaded['error']);
			$url = 'user.php?action=create&msg='.$msg;
			redirect($url);
		}
		
		if(!empty($id)){
			$fields = array('wing_id' => $wing_id,
						'group_id' => $group_id,
						'rank_id' => $rank_id,
						'appointment_id' => $appointment_id,
						'ba_no' => $ba_no,
						'full_name' => $full_name,
						'username' => $username,
						'sec_key' => $sec_key,
						'official_name' => $official_name,
						'updated_by' => $cur_user_id,
						'updated_datetime' => $last_updated_time
						);
			
			if(!empty($password)){
				$fields['password'] = md5($password);
			}
			
			if($uploaded['uploaded'][0] != ''){
				$fields['photo'] = ($uploaded['uploaded'][0] == '')?'':$uploaded['uploaded'][0];		
			}
						
			$where = "id = '".$id."'";
			
			$update_status = $dbObj->updateTableData("user", $fields, $where);	
			
			if(!$update_status){
				$msg = USER.' '.$username.COULD_NOT_BE_UPDATED;		
				$action = 'insert';
			}else{
				$msg = USER.' '.$username.HAS_BEEN_UPDATED;
				$url = 'user.php?action=view&page='.$page.'&msg='.$msg;
				redirect($url);
			}
		}else{
			$fields = array('wing_id' => $wing_id,
						'group_id' => $group_id,
						'rank_id' => $rank_id,
						'appointment_id' => $appointment_id,
						'ba_no' => $ba_no,
						'full_name' => $full_name,
						'username' => $username,
						'sec_key' => $sec_key,
						'password' => md5($password),
						'official_name' => $official_name,
						'photo' => ($uploaded['uploaded'][0] == '')?'':$uploaded['uploaded'][0],
						'created_by' => $cur_user_id,
						'created_datetime' => $created_date_time,
						'updated_by' => $cur_user_id,
						'updated_datetime' => $last_updated_time
						);
			
			$inserted = $dbObj->insertTableData("user", $fields);	
			if(!$inserted){
				$msg = USER.' '.$username.COULD_NOT_BE_CREATED;	
				$action = 'insert';
			}else{
				$msg = USER.' '.$username.CREATED_SUCCESSFULLY;
				$url = 'user.php?action=view&msg='.$msg;
				redirect($url);
			}
		}
		break;

	case 'detail':	
		$id = $_REQUEST['id'];	
		
		$sql = "select * from ".DB_PREFIX."user where id = '".$id."'";	
		$userList = $dbObj->selectDataObj($sql);		
		
		$action = 'detail';
		$msg = $_REQUEST['msg'];
		break;
		
	case 'delete':	
		$id = $_REQUEST['id'];	
		$sql = "select * from ".DB_PREFIX."user WHERE id='".$id."'";	
		$userList = $dbObj->selectDataObj($sql);
		$user = $userList[0];
		$username = $user->username;
		
		$where = "id='".$id."'";	
		
		$success = $dbObj->deleteTableData("user", $where);	
		
		if(!$success){
			$msg = USER.' '.$username.COULD_NOT_BE_DELETED;
		}else{
			$msg = USER.' '.$username.HAS_BEEN_DELETED;
		}
		
		$url = 'user.php?action=view&page='.$page.'&msg='.$msg;
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
				<h1><?php echo USER_MANAGEMENT; ?></h1>
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
						<table width="100%" cellpadding="0" cellspacing="0" border="0" class="datagrid">
								<tr class="footer">
									<td colspan="5" style="background:#EEEEEE;">
										<b><a href="user.php?action=create"><?php echo CREATE; ?></a></b>
									</td>
								</tr>				
							<tr class="head">
								<td height="30" width="10%">
									<strong><?php echo SER_NO; ?></strong>
								</td>
								<td height="30" width="15%">
									<strong><?php echo USERNAME; ?></strong>
								</td>
								<td height="30" width="15%">
									<strong><?php echo OFFICIAL_NAME; ?></strong>
								</td>
								<?php if($cur_user_group_id == '1'){ ?>
								<td height="30" width="15%">
									<strong><?php echo WING; ?></strong>
								</td>
								<?php } ?>
								<td height="30" width="20%">
									<strong><?php echo GROUP; ?></strong>
								</td>
								<td height="30" width="15%">
									<strong><?php echo RANK; ?></strong>
								</td>
								<td height="30" width="20%">
									<strong><?php echo PHOTO; ?></strong>
								</td>
								<td height="30" width="10%">
									<strong><?php echo ACTION; ?></strong>
								</td>
							</tr>
							<?php
							if(!empty($userList)){	
								
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
								<td style="padding-left:15px;">
									<?php echo $sl++; ?> 
								</td>
								<td>
									<?php echo $userList[$rownum]->username; ?> 
								</td>
								<td>
									<?php echo $userList[$rownum]->official_name; ?> 
								</td>
								<?php if($cur_user_group_id == '1'){ ?>
								<td>
									<?php
									$wing = getNameById("wing", $userList[$rownum]->wing_id);
									echo $wing->name;
									?>
								</td>
								<?php } ?>
								<td>
									<?php
									$group = getNameById("user_group", $userList[$rownum]->group_id);
									echo $group->name;
									?>
								</td>
								<td>
									<?php 
									$rank = getNameById("rank", $userList[$rownum]->rank_id);
									echo $rank->name;
									?>
								</td>
								<td>
									<?php if($userList[$rownum]->photo == ""){?>
										<img height="50" width="60" src="attach_file/unknown.png" title="<?php echo $userList[$rownum]->full_name;?>" />
									<?php }else { ?>
										<a id="example4" href="<?php echo $path_view.$userList[$rownum]->photo ;?>" ><img height="50" width="60" src="<?php echo $path_view.$userList[$rownum]->photo ;?>"  title="<?php echo $userList[$rownum]->full_name;?>" /></a>
									<?php } ?>
								</td>
								<td>								
									<a href="user.php?action=detail&page=<?php echo $page; ?>&id=<?php echo $userList[$rownum]->id; ?>"><?php echo DETAILS; ?></a>
									<?php if(($cur_user_group_id == '1') ||($cur_user_group_id == '2' && ($userList[$rownum]->group_id > '2')) ||($cur_user_group_id == '3' && ($userList[$rownum]->group_id > '3'))){ ?>
									<a href="user.php?action=update&page=<?php echo $page; ?>&id=<?php echo $userList[$rownum]->id; ?>"><?php echo UPDATE; ?></a>
									<a href="user.php?action=delete&page=<?php echo $page; ?>&id=<?php echo $userList[$rownum]->id; ?>" onclick="return confirm('Are you sure you want to delete?');"><?php echo DELETE; ?></a>
									<?php } ?>
								</td>
							</tr>
							<?php 
								}//for
							}else{ ?>
							<tr height="30">
								<td colspan="7">
									<?php echo EMPTY_DATA; ?>
								</td>
							</tr>
							<?php 
							} ?>
							<tr height="50">
								<td colspan="8">
									<?php 
									echo pagination($total_rows,$limit,$page,''); ?>
								</td>
							</tr>
							<tr class="footer">
								<td colspan="8">
									<b><a href="user.php?action=create"><?php echo CREATE; ?></a></b>
								</td>
							</tr>				
						</table>
					</td>
				</tr>
			</table>
				
	<?php }elseif($action=="insert"){ 
		
		if(empty($id)){
			$token = 0;		//token has been set for password repeatation in case of update
		}else{
			$token = 1;
		}
	?>
	
				<form action="user.php" method="post" name="user" id="user" onsubmit="return validateUserCreate(<?php echo $token; ?>);" enctype="multipart/form-data">
					<table width="100%" cellpadding="0" cellspacing="0" border="0" class="module_content">
						<?php if($cur_user_group_id == '1'){ ?>
						<tr>
							<td height="30" width="20%">
								<?php echo GROUP; ?>:
							</td>
							<td width="80%">
								<?php echo $groupList_opt;	?>
								<span class="required_field">*</span>
							</td>
						</tr>
						<?php } ?>
						<tr>
							<td height="30" width="20%">
								<?php echo WING; ?>:
							</td>
							<td width="80%">
								<?php 
								if($cur_user_group_id == '1'){
									echo $wingList_opt;	?>
									<span class="required_field">*</span>
								<?php 
								}else if($cur_user_group_id == '2' || $cur_user_group_id == '3'){ 
									$wing = getNameById('wing', $cur_user_wing_id);
									echo '<b>'.$wing->name.'</b>'; ?>
								<input type="hidden" name="wing_id" id="wing_id" value="<?php echo $cur_user_wing_id; ?>" />
								<?php }//else if ?>
									
							</td>
						</tr>
						<?php if($cur_user_group_id == '2' || $cur_user_group_id == '3'){ ?>
						<tr>
							<td height="30" width="20%">
								<?php echo GROUP; ?>:
							</td>
							<td width="80%">
							<?php
								if($id == $cur_user_id){								
								 $group = getNameById('user_group', $cur_user_group_id);
									echo '<b>'.$group->name.'</b>'; ?>	
								<input type="hidden" name="group_id" id="group_id" value="<?php echo $cur_user_group_id; ?>" />
								<?php
								}else{
									echo $groupList_opt; ?>
								<span class="required_field">*</span>
							<?php } ?>
							</td>
						</tr>
						<?php } ?>
						<tr>
							<td height="30" width="20%">
								<?php echo RANK; ?>:
							</td>
							<td width="80%">
							<div id="rank_display">
								<?php echo $rankList_opt;	?>
							</div>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo APPOINTMENT; ?>:
							</td>
							<td width="80%">
							<div id="appointment_display">
								<?php echo $appointmentList_opt;	?>
							</div>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo BA_NO; ?>:
							</td>
							<td width="80%">
								<input name="ba_no" id="ba_no" type="text" class="inputbox" alt="BA/Service No." size="36" value="<?php echo $ba_no; ?>" />
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo FULL_NAME; ?>:
							</td>
							<td width="80%">
								<input name="full_name" id="full_name" type="text" class="inputbox" alt="Full Name" size="36" value="<?php echo $full_name; ?>" />
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo OFFICIAL_NAME; ?>:
							</td>
							<td width="80%">
								<input name="official_name" id="official_name" type="text" class="inputbox" alt="Official Name" size="36" value="<?php echo $official_name; ?>" />
								<span class="required_field">*</span>
								<!--<input type="button" name="checkOfficeName" id="checkOfficeName" value="Check" onclick="return getOfficeName();" />-->
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo SECURITY_KEY; ?>:
							</td>
							<td width="40%">
								<div id="sec_key_display">
									<input name="sec_key" id="sec_key" type="text" readonly="readonly" class="inputbox" alt="Security Key" size="36" value="<?php echo $sec_key; ?>" />
								</div>
								<div id="testing">								
									<input id="generate_sec_key" type="button" onclick="return getSecKey();" value="Regenerate" title="Regenerate Security Key"  />
</div>						</td>
						</tr>
						<!--<tr>
							<td style="padding-left:140px;" colspan="2">
								<div id="loaderContainer"></div>
								<div id="officename_display"></div>
							</td>
						</tr>-->
						<tr>
							<td height="30" width="20%">
								<?php echo USERNAME; ?>:
							</td>
							<td width="80%">
								<input name="username" id="username" type="text" class="inputbox" alt="Username" size="36" value="<?php echo $username; ?>" />
								<span class="required_field">*</span>
								<input type="button" name="checkUsername" id="checkUsername" value="Check" onclick="return getUsername();" />
							</td>
						</tr>
						<tr>
							<td style="padding-left:140px;" colspan="2">
								<div id="loaderContainer"></div>
								<div id="username_display"></div>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo PASSWORDLABEL; ?>:
							</td>
							<td width="80%">
								<input name="password" id="password" type="password" class="inputbox" alt="Password" size="36" value="<?php echo $password; ?>" />
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo CONFIRM_PASSWORD; ?>:
							</td>
							<td width="80%">
								<input name="retype_password" id="retype_password" type="password" class="inputbox" alt="Retype Password" size="36" value="<?php echo $retype_password; ?>" />
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo PHOTO; ?>:
							</td>
							<td width="80%">
								<input name="photo" id="photo" type="file" class="inputbox" alt="Photo" size="23" value="<?php echo $photo; ?>" />
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
			
	<?php }elseif($action=="detail"){ ?>
	
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="module_content">
				<?php		
					$rownum = 0;								
					foreach($userList as $user){										
				?>
						<tr>
							<td colspan="2">
								<a href="user.php?action=view&page=<?php echo $page; ?>"><?php echo BACK; ?></a>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo USERNAME; ?>:
							</td>
							<td width="80%">
								<?php echo $user->username; ?>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo OFFICIAL_NAME; ?>:
							</td>
							<td width="80%">
								<?php 
								echo $user->official_name;
								?>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo FULL_NAME; ?>:
							</td>
							<td width="80%">
								<?php 
								echo $user->full_name;
								?>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo WING; ?>:
							</td>
							<td width="80%">
								<?php 
									$wing = getNameById("wing", $user->wing_id);
									echo $wing->name;
								?> 							
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo GROUP; ?>:
							</td>
							<td width="80%">
								<?php 
								$group = getNameById("user_group", $user->group_id);
								echo $group->name;
								?>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo RANK; ?>:
							</td>
							<td width="80%">
								<?php 
								$rank = getNameById("rank", $user->rank_id);
								echo $rank->name;
								?>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo APPOINTMENT; ?>:
							</td>
							<td width="80%">
								<?php 
								$appointment = getNameById("appointment", $user->appointment_id);
								echo $appointment->name;
								?>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo BA_NO; ?>:
							</td>
							<td width="80%">
								<?php 
								echo $user->ba_no;
								?>
							</td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<a href="user.php?action=view&page=<?php echo $page; ?>"><?php echo BACK; ?></a>
							</td>
						</tr>
						
				<?php
					}//foreach
			}//elseif
				?>
			</table>	
</div>

<?php
require_once("includes/footer.php");
?>
