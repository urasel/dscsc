<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Term Management';
$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$action = $_REQUEST['action'];
$msg = '';

//Chek if this user is valid for this file
if($cur_user_group_id != '4'){
	header("Location: dashboard.php");	
}

switch($action){
	case 'view':	
	default:
		
		$sql = "select * from ".DB_PREFIX."course where wing_id = '".$cur_user_wing_id."' AND status = '0'";
		$activeCourse = $dbObj->selectDataObj($sql);
		
		$action = 'view';
		$msg = $_REQUEST['msg'];
		
		break;
		
	case 'lock':	
		$id = $_REQUEST['id'];
		$val = $_REQUEST['val'];
		$sql = "select * from ".DB_PREFIX."term where id = '".$id."'";
		$termArr = $dbObj->selectDataObj($sql);
		
		//Make Sure wing admin of different wing cannot change a term by force from URL
		if($termArr[0]->wing_id != $cur_user_wing_id){
			header("Location: dashboard.php");
			exit;
		}
			
		$fields = array('lock_status' => $val);
		$where = "id='".$id."'";
		
		$success = $dbObj->updateTableData("term", $fields, $where);	
		
		if($success){
			if($val == '1'){
				$msg = $termArr[0]->name.' has been locked successfully';
			}else if($val == '2'){
				$msg = 'A message has been sent to unlock result for '.$termArr[0]->name; 
			}
		}else{
			$msg = $termArr[0]->name.' could not be changed';
		}
		
		$url = 'lock_term.php?action=view&msg='.$msg;
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
				<h1><?php echo TERM_MANAGEMENT; ?></h1>
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
							<tr>
								<td colspan="3" align="center" height="50">
									<strong><?php echo $activeCourse[0]->name; ?></strong>
								</td>
							</tr>				
							<tr class="head">
								<td height="30" width="30%">
									<strong><?php echo TERM_NAME; ?></strong>
								</td>
								<td height="30" width="30%">
									<strong><?php echo STATUS; ?></strong>
								</td>
								<td height="30" width="40%">
									<strong><?php echo ACTION; ?></strong>
								</td>
							</tr>
							<?php			
							if(!empty($activeCourse)){
								
								$sql = "select * from ".DB_PREFIX."term where course_id = '".$activeCourse[0]->id."' order by order_id";
								$termList = $dbObj->selectDataObj($sql);
								
								
								
								$rownum = 0;							
								foreach($termList as $term){		
									if(($rownum%2)==0){//even
										$class = ' class="even"';									
									}else{//odd
										$class = ' class="odd"';									
									}//else 
									
									if($term->lock_status == '' || $term->lock_status == '0'){
										$status = 'Open';
										$action_status = 'Lock';
										$action_msg = 'Are you sure you want to Lock this Term?';
										$val = '1';
									}else if($term->lock_status == '1'){
										$status = 'Locked';
										$action_status = 'Request to Unlock';
										$action_msg = 'Are you sure you want to Request to Unlock this Term?';
										$val = '2';
									}else if($term->lock_status == '2'){
										$status = 'Waiting to Unlock';
										$action_status = '';
										$action_msg = '';
									}//else if
								
							?>
									<tr <?php echo $class; ?>>
										<td>
											<?php echo $term->name; ?>
										</td>
										<td>
											<?php echo $status; ?>
										</td>
										<td>
											<?php if($term->lock_status == '2'){ 
													 echo NOT_SELECTED;
													}else{?>
												<a href="lock_term.php?action=lock&id=<?php echo $term->id; ?>&val=<?php echo $val; ?>" onclick="return confirm('<?php echo $action_msg; ?>');"><?php echo $action_status; ?></a>
											<?php }//else  ?>
										</td>
									</tr>
								<?php 
									$rownum++;
									}//for 
								}//if not empty activeCourseArray
								?>
						</table>
					</td>
				</tr>
			</table>
	<?php }// if action == view ?>
</div>
			
<?php
require_once("includes/footer.php");
?>