<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Appointment Management';
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
		
		$sql = "select * from ".DB_PREFIX."appointment WHERE wing_id = '".$cur_user_wing_id."' order by `order` asc";
		$appointmentList = $dbObj->selectDataObj($sql);
		$action = 'view';
		$msg = $_REQUEST['msg'];
		
		//Pagination 
		if(!empty($appointmentList)){
			$total_rows = sizeof($appointmentList);
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
			$sql = "select * from ".DB_PREFIX."appointment WHERE id='".$id."'";	
			$appointmentList = $dbObj->selectDataObj($sql);
			$appointment = $appointmentList[0];
			
			//Make Sure wing manager of different wing cannot change an appointment by force from URL
			if($appointment->wing_id != $cur_user_wing_id){
				header("Location: dashboard.php");
				exit;
			}
			
			$wing_id = $appointment->wing_id;
			$name = $appointment->name;
			$short_name = $appointment->short_name;
			$order = $appointment->order;
			$office = $appointment->office;
		}else{
			$id = '';
			$wing_id = '';
			$name = '';
			$short_name = '';
			$order = '';
			$office = '';
		}
		
		$action = 'insert';
		break;
		
	case 'save':	
		$id = $_POST['id'];
		$wing_id = $_POST['wing_id'];
		$name = $_POST['name'];
		$short_name = $_POST['short_name'];
		$order = $_POST['order'];
		$office = $_POST['office'];
		$created_date_time = $last_updated_time = date('Y-m-d H:i:s');
		
		//Check for not inserting any blank entry
		if($name == "" || $short_name == "" || $order == ""){
			$msg = PARAM_MISSING;
			if(empty($id)){
				$url = 'appointment.php?action=create&msg='.$msg;
			}else{
				$url = 'appointment.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
			}
			redirect($url);
		}
		
		//Check if Appointment Name already exists in the db in same wing
		if(empty($id)){
			$sql = "select name from ".DB_PREFIX."appointment WHERE name = '".$name."' AND wing_id = '".$cur_user_wing_id."' limit 1";
			$appointmentList = $dbObj->selectDataObj($sql);		
			
			if(!empty($appointmentList)){
				$msg = APPOINTMENT.' '.$name.ALREADY_EXISTS;
				$url = 'appointment.php?action=create&msg='.$msg;
				redirect($url);
			}			
		}else if(!empty($id)){
			$sql = "select name from ".DB_PREFIX."appointment WHERE id!='".$id."' AND name = '".$name."' AND wing_id = '".$cur_user_wing_id."' limit 1";
			$appointmentList = $dbObj->selectDataObj($sql);		
			
			if(!empty($appointmentList)){
				$msg = APPOINTMENT.' '.$name.ALREADY_EXISTS;
				$url = 'appointment.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
				redirect($url);
			}
		}
		
		//Check if Appointment Short Name already exists in the db in same wing
		if(empty($id)){
			$sql = "select short_name from ".DB_PREFIX."appointment WHERE short_name = '".$short_name."' AND wing_id = '".$cur_user_wing_id."' limit 1";
			$appointmentList = $dbObj->selectDataObj($sql);		
			
			if(!empty($appointmentList)){
				$msg = APPOINTMENT_SHORT_NAME.' '.$short_name.ALREADY_EXISTS;
				$url = 'appointment.php?action=create&msg='.$msg;
				redirect($url);
			}			
		}else if(!empty($id)){
			$sql = "select short_name from ".DB_PREFIX."appointment WHERE id!='".$id."' AND short_name = '".$short_name."' AND wing_id = '".$cur_user_wing_id."' limit 1";
			$appointmentList = $dbObj->selectDataObj($sql);		
			
			if(!empty($appointmentList)){
				$msg = APPOINTMENT_SHORT_NAME.' '.$short_name.ALREADY_EXISTS;
				$url = 'appointment.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
				redirect($url);
			}
		}
		
		//Check if Appointment Weight already exists in the db in same wing
		if(empty($id)){
			$sql = "select `order` from ".DB_PREFIX."appointment WHERE `order` = '".$order."' AND wing_id = '".$cur_user_wing_id."' limit 1";
			$appointmentList = $dbObj->selectDataObj($sql);		
			
			if(!empty($appointmentList)){
				$msg = APPOINTMENT_ORDER.' '.$order.ALREADY_EXISTS;
				$url = 'appointment.php?action=create&msg='.$msg;
				redirect($url);
			}			
		}else if(!empty($id)){
			$sql = "select `order` from ".DB_PREFIX."appointment WHERE id!='".$id."' AND `order` = '".$order."' AND wing_id = '".$cur_user_wing_id."' limit 1";
			$appointmentList = $dbObj->selectDataObj($sql);		
			
			if(!empty($appointmentList)){
				$msg = APPOINTMENT_ORDER.' '.$order.ALREADY_EXISTS;
				$url = 'appointment.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
				redirect($url);
			}
		}
		
		if(!empty($id)){
			$fields = array('wing_id' => $wing_id,
						'name' => $name,
						'short_name' => $short_name,
						'`order`' => $order,
						'updated_by' => $cur_user_id,
						'updated_datetime' => $last_updated_time,
						);
						
			$where = "id = '".$id."'";
			
			$update_status = $dbObj->updateTableData("appointment", $fields, $where);	
			
			if(!$update_status){
				$msg = APPOINTMENT.' '.$name.COULD_NOT_BE_UPDATED;
				$action = 'insert';
			}else{
				$msg = APPOINTMENT.' '.$name.HAS_BEEN_UPDATED;
				$url = 'appointment.php?action=view&page='.$page.'&msg='.$msg;
				redirect($url);
			}
		}else{
			$fields = array('wing_id' => $wing_id,
						'name' => $name,
						'short_name' => $short_name,
						'`order`' => $order,
						'updated_by' => $cur_user_id,
						'created_by' => $cur_user_id,
						'created_datetime' => $created_date_time,
						'updated_by' => $cur_user_id,
						'updated_datetime' => $last_updated_time,
						);
			
			$inserted = $dbObj->insertTableData("appointment", $fields);	
			if(!$inserted){
				$msg = APPOINTMENT.' '.$name.COULD_NOT_BE_CREATED;
				$action = 'insert';
			}else{
				$msg = APPOINTMENT.' '.$name.CREATED_SUCCESSFULLY;
				$url = 'appointment.php?action=view&msg='.$msg;
				redirect($url);
			}
		}
		break;

	case 'delete':	
		$id = $_REQUEST['id'];	
		$sql = "select * from ".DB_PREFIX."appointment WHERE id='".$id."'";	
		$appointmentList = $dbObj->selectDataObj($sql);
		$appointment = $appointmentList[0];
		
		//Make Sure wing manager of different wing cannot change an appointment by force from URL
		if($appointment->wing_id != $cur_user_wing_id){
			header("Location: dashboard.php");
			exit;
		}
		
		$name = $appointment->name;
		$where = "id='".$id."'";	
		$success = $dbObj->deleteTableData("appointment", $where);	
		
		if(!$success){
			$msg = APPOINTMENT.' '.$name.COULD_NOT_BE_DELETED;
		}else{
			$msg = APPOINTMENT.' '.$name.HAS_BEEN_DELETED;
		}
		
		$url = 'appointment.php?action=view&page='.$page.'&msg='.$msg;
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
				<h1><?php echo APPOINTMENT_MANAGEMENT; ?></h1>
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
									<td colspan="5" style=" background:#EEEEEE;">
										<b><a href="appointment.php?action=create"><?php echo CREATE; ?></a></b>
									</td>
								</tr>				
							<tr class="head">
								<td height="30" width="20%">
									<strong><?php echo NAME; ?></strong>
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
							if(!empty($appointmentList)){	
								
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
											<?php echo $appointmentList[$rownum]->name; ?> 
										</td>
										<td width="20%">
											<?php echo $appointmentList[$rownum]->short_name; ?> 
										</td>	
										<td width="20%">
											<?php echo $appointmentList[$rownum]->order; ?> 
										</td>	
										<td width="20%">								
											<a href="appointment.php?action=update&page=<?php echo $page; ?>&id=<?php echo $appointmentList[$rownum]->id; ?>"><?php echo UPDATE; ?></a>
											<a href="appointment.php?action=delete&page=<?php echo $page; ?>&id=<?php echo $appointmentList[$rownum]->id; ?>" onclick="return confirm('Are you sure you want to delete?');"><?php echo DELETE; ?></a>
										</td>
									</tr>
								<?php 
									}//for
								}else{ ?>
								<tr height="30">
									<td colspan="5">
										<?php echo EMPTY_DATA; ?>
									</td>
								</tr>
								<?php 
								}
								if($total_page > 1){ ?>
								<tr height="50">
									<td colspan="5">
										<?php 
										echo pagination($total_rows,$limit,$page,''); ?>
									</td>
								</tr>
								<?php } ?>	
								<tr class="footer">
									<td colspan="5">
										<b><a href="appointment.php?action=create"><?php echo CREATE; ?></a></b>
									</td>
								</tr>				
						</table>
					</td>
				</tr>
			</table>
				
	<?php 
		}elseif($action=="insert"){ 
	?>
				<form action="appointment.php" method="post" name="appointment" id="appointment" onsubmit="return validateAppointment();">
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
								<?php echo APPOINTMENT_NAME; ?>:
							</td>
							<td width="80%">
								<input name="name" id="name" type="text" class="inputbox" alt="Appointment Name" size="36" value="<?php echo $name; ?>" />
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo SHORT_NAME; ?>:
							</td>
							<td width="80%">
								<input name="short_name" id="short_name" type="text" class="inputbox" alt="Short Name" size="36" value="<?php echo $short_name; ?>" />
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo ORDER; ?>:
							</td>
							<td width="80%">
								<input name="order" id="order" type="text" class="inputbox" alt="Order" size="36" maxlength="2" value="<?php echo $order; ?>" onkeyup="return isNUM('order')" />
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