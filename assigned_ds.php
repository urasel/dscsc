<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Assigned Exercise';
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
		
		if(isset($_POST['show'])){
			$course_id = $_POST['course_id'];
			$term_id = $_POST['term_id'];
			
			if($term_id == 0 || $term_id == ""){
				$add_term = "";
			}else{
				$add_term = " AND dtc.term_id = '".$term_id."' ";
			}
			
			//Build Student List Array	
			$query = "select usr.id, usr.username, usr.official_name, usr.rank_id, usr.appointment_id, dtc.syndicate_id, dtc.term_id from ".DB_PREFIX."ds_to_course as dtc, ".DB_PREFIX."user as usr, ".DB_PREFIX."syndicate as syn where dtc.course_id = '".$course_id."' AND dtc.syndicate_id = syn.id AND dtc.ds_id = usr.id ".$add_term." order by dtc.term_id, syn.name asc";
			$dsArr = $dbObj->selectDataObj($query);
		}
		
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
		$courseList_opt = formSelectElement($courseId, $course_id, 'course_id', 'onchange = processFunction("assigned_exercise")');
		
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
		
}//switch


require_once("includes/templates.php");
require_once("templates/top_menu.php");
require_once("templates/left_menu.php");
?>

<div id="right_column">
	<table width="100%" cellspacing="0" cellpadding="0" border="0" class="module_header">
		<tr>
			<td>
				<h1><?php echo ASSIGNED_DS; ?></h1>
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
		<form action="assigned_ds.php" method="post" name="assigned_ds" id="assigned_ds" onsubmit="return validateAssignedExercise();">
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
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
					<input type="submit" name="show" class="button" value="<?php echo SHOW; ?>" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="action" value="view" />
		</form>
		
	<?php if(isset($_POST['show'])){ ?>
		
		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="module_content">
			<tr>
				<td>
					<table width="100%" cellpadding="0" cellspacing="0" border="0" class="datagrid">
						<tr class="head">
							<td height="30" width="10%">
								<strong><?php echo SER_NO; ?></strong>
							</td>
						<?php if($term_id == '0'){ ?>
							<td height="30" width="10%">
								<strong><?php echo SER_NO; ?></strong>
							</td>
						<?php }//if ?>	
							<td height="30" width="10%">
								<strong><?php echo USERNAME; ?></strong>
							</td>
							<td height="30" width="10%">
								<strong><?php echo SYNDICATE; ?></strong>
							</td>
							<td height="30" width="10%">
								<strong><?php echo RANK; ?></strong>
							</td>
							<td height="30" width="20%">
								<strong><?php echo OFFICIAL_NAME; ?></strong>
							</td>
							<td height="30" width="40%">
								<strong><?php echo APPOINTMENT; ?></strong>
							</td>
						</tr>
						<?php	
						if(!empty($dsArr)){
							$rownum = 0;							
							foreach($dsArr as $ds){
								if(($rownum%2)==0){//even
									$class = ' class="even"';									
								}else{//odd
									$class = ' class="odd"';									
								}//else 
						?>
								<tr <?php echo $class; ?>>
									<td style="padding-left:15px;">
										<?php echo $rownum+1; ?> 
									</td>
								<?php if($term_id == '0'){ ?>
									<td>
										<?php 	$term = getNameById('term', $ds->term_id);
											echo $term->name;?>
									</td>
								<?php }//if ?>
									<td>
										<?php echo $ds->username; ?> 
									</td>
									<td>
										<?php 	$syndicate = getNameById('syndicate', $ds->syndicate_id);
											echo $syndicate->name;?>
									</td>
									<td>
										<?php 	$rank = getNameById('rank', $ds->rank_id);
											echo $rank->short_name;?>
									</td>
									<td>
										<?php echo $ds->official_name; ?> 
									</td>
									<td >
										<?php 	$appointment = getNameById('appointment', $ds->appointment_id);
											echo $appointment->name;?>
									</td>
								</tr>
							<?php 
									$rownum++;
								}//foreach 
							}else{
							?>	
							<tr height="30">
								<td colspan="7">
									<?php echo EMPTY_DATA; ?>
								</td>
							</tr>
							<?php } ?>
					</table>
				</td>
			</tr>
		</table>
			
	<?php 
		}
	} 
	?>
</div>
			
<?php
require_once("includes/footer.php");
?>