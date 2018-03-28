<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'My Account';
$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$action = $_REQUEST['action'];
$msg = '';

switch($action){
	case 'view':	
	default:
		
		
		$sql = "select * from ".DB_PREFIX."user where id = '".$cur_user_id."'";
		$userList = $dbObj->selectDataObj($sql);
		
		if($cur_user_group_id == '5'){
			//'0'means active course & term
			$sql = "select dtc.course_id, dtc.term_id, dtc.syndicate_id from ".DB_PREFIX."course as crs, ".DB_PREFIX."term as trm, ".DB_PREFIX."ds_to_course as dtc where dtc.ds_id = '".$cur_user_id."' AND crs.id = dtc.course_id AND crs.status = '0' AND trm.status = '0' AND trm.id = dtc.term_id ";
			$activeInfoArr = $dbObj->selectDataObj($sql);
			$activeInfo = $activeInfoArr[0];
			
			if(empty($activeInfo)){
				$status = NON_TEACHING_DS;
			}else{
				$status = TEACHING_DS;
			}//if
			
			$sql = "select count(*) as total_student from ".DB_PREFIX."student_to_syndicate where syndicate_id = '".$activeInfo->syndicate_id."' AND term_id = '".$activeInfo->term_id."' AND course_id = '".$activeInfo->course_id."' ";
			$studentInfoArr = $dbObj->selectDataObj($sql);
			$studentInfo = $studentInfoArr[0];
			$total_student = $studentInfo->total_student;
			
			$sql = "select count(*) as total_exercise from ".DB_PREFIX."exercise_to_term where term_id = '".$activeInfo->term_id."' AND course_id = '".$activeInfo->course_id."' ";
			$exerciseInfoArr = $dbObj->selectDataObj($sql);
			$exerciseInfo = $exerciseInfoArr[0];
			$total_exercise = $exerciseInfo->total_exercise;
			
		}else if($cur_user_group_id > 0 && $cur_user_group_id < 5){
			//Find total Number of Super Administrator 
			if($cur_user_group_id == 1){
				$sql = "select count(*) as total_sa from ".DB_PREFIX."user WHERE group_id = '1'";
			}
			$totalSaArr = $dbObj->selectDataObj($sql);
			$totalSa = $totalSaArr[0];
			$total_sa = $totalSa->total_sa;
			
			
			//Find Total user of this wing
			if($cur_user_group_id == 1){
				$sql = "select count(*) as total_user from ".DB_PREFIX."user";
			}else{
				$sql = "select count(*) as total_user from ".DB_PREFIX."user where wing_id = '".$cur_user_wing_id."' AND group_id != '1'";
			}
			$totalUserArr = $dbObj->selectDataObj($sql);
			$totalUser = $totalUserArr[0];
			$total_user = $totalUser->total_user;
			
			//Find total number of WIng Admin of this Wing
			if($cur_user_group_id == 1){
				$sql = "select count(*) as total_wa from ".DB_PREFIX."user where group_id = '2'";
			}else{
				$sql = "select count(*) as total_wa from ".DB_PREFIX."user where wing_id = '".$cur_user_wing_id."' AND group_id = '2'";
			}
			$totalWaArr = $dbObj->selectDataObj($sql);
			$totalWa = $totalWaArr[0];
			$total_wa = $totalWa->total_wa;
			
			//Find total number of Wing Manager of this wing
			if($cur_user_group_id == 1){
				$sql = "select count(*) as total_wm from ".DB_PREFIX."user where group_id = '3'";
			}else{
				$sql = "select count(*) as total_wm from ".DB_PREFIX."user where wing_id = '".$cur_user_wing_id."' AND group_id = '3'";
			}
			$totalWmArr = $dbObj->selectDataObj($sql);
			$totalWm = $totalWmArr[0];
			$total_wm = $totalWm->total_wm;
			
			//Find total Number of SI of this wing
			if($cur_user_group_id == 1){
				$sql = "select count(*) as total_si from ".DB_PREFIX."user where group_id = '4'";
			}else{
				$sql = "select count(*) as total_si from ".DB_PREFIX."user where wing_id = '".$cur_user_wing_id."' AND group_id = '4'";
			}
			$totalSiArr = $dbObj->selectDataObj($sql);
			$totalSi = $totalSiArr[0];
			$total_si = $totalSi->total_si;
			
			//Find total Number of DS of this wing
			if($cur_user_group_id == 1){
				$sql = "select count(*) as total_ds from ".DB_PREFIX."user where group_id = '5'";
			}else{
				$sql = "select count(*) as total_ds from ".DB_PREFIX."user where wing_id = '".$cur_user_wing_id."' AND group_id = '5'";
			}
			$totalDsArr = $dbObj->selectDataObj($sql);
			$totalDs = $totalDsArr[0];
			$total_ds = $totalDs->total_ds;
			
			//Find Active Course
			$sql = "select * from ".DB_PREFIX."course where status = '0' AND wing_id = '".$cur_user_wing_id."'";
			$ActiveCourseArr = $dbObj->selectDataObj($sql);
			$activeCourse = $ActiveCourseArr[0];
			$active_course = $activeCourse->name;
			
			//Find Active Term
			$sql = "select * from ".DB_PREFIX."term where wing_id = '".$cur_user_wing_id."' AND status = '0'";
			$activeTermArr = $dbObj->selectDataObj($sql);
			$activeTerm = $activeTermArr[0];
			$active_term = $activeTerm->name;
			
			if($cur_user_group_id == 4){
				//Find Currently Active Course/Courses which is assigned to this SI from si_to_course
				$sql = "select stc.course_id from ".DB_PREFIX."si_to_course as stc,  ".DB_PREFIX."course as crs where stc.si_id = ".$cur_user_id." AND stc.wing_id = '".$cur_user_wing_id."' AND crs.status = '0' AND crs.id = stc.course_id";
				$SIcourseArray = $dbObj->selectDataObj($sql);
				$SIcourse = $SIcourseArray[0];
				
				//Find Exercise Marking Unlock Request 
				$sql = "select * from ".DB_PREFIX."marking_lock where course_id = '".$SIcourse->course_id."' AND term_id = '".$activeTerm->id."' AND status = '2'";
				$exrUnlckArr = $dbObj->selectDataObj($sql);
				$total_exercise_to_unlock = sizeof($exrUnlckArr);
				
				//Find Impression Marking Unlock Request 
				$sql = "select * from ".DB_PREFIX."impression_marking_lock where course_id = '".$SIcourse->course_id."' AND term_id = '".$activeTerm->id."' AND status = '2'";
				$imprUnlckArr = $dbObj->selectDataObj($sql);
				$total_impress_to_unlock = sizeof($imprUnlckArr);
				
				if($total_exercise_to_unlock != 0 && $total_impress_to_unlock != 0){
					$unlock_msg = "You have ".$total_exercise_to_unlock." Unlock Exercise Results & ".$total_impress_to_unlock." Unlock Impression Marks Request";
				}else if($total_exercise_to_unlock == 0 && $total_impress_to_unlock != 0){
					$unlock_msg = "You have ".$total_impress_to_unlock." Unlock Impression Marks Request";
				}else if($total_exercise_to_unlock != 0 && $total_impress_to_unlock == 0){
					$unlock_msg = "You have ".$total_exercise_to_unlock." Unlock Exercise Results Request";
				}else if($total_exercise_to_unlock == 0 && $total_impress_to_unlock == 0){
					$unlock_msg = "";
				}
			
			}
			
		}

		$action = 'view';
		$path_view = 'attach_file/';
		$msg = $_REQUEST['msg'];
		break;
					
	case 'detail':	
		$id = $_REQUEST['id'];	
		
		if($id != $cur_user_id){
			header('Location:dashboard.php');
		}
		
		$sql = "select * from ".DB_PREFIX."user where id = '".$id."'";	
		$userList = $dbObj->selectDataObj($sql);		
		
		$action = 'detail';
		$msg = $_REQUEST['msg'];
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
				<h1><?php echo MY_ACCOUNT; ?></h1>
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
					<td colspan="2">
						<table width="100%" cellpadding="0" cellspacing="0" border="0" class="datagrid">
							<tr class="head">
								<td height="30" width="15%">
									<strong><?php echo NAME; ?></strong>
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
								$rownum = 0;							
								foreach($userList as $user){		
									if(($rownum%2)==0){//even
										$class = ' class="even"';									
									}else{//odd
										$class = ' class="odd"';									
									}									
							?>
									<tr <?php echo $class; ?>>
										<td width="15%">
											<?php echo $user->username; ?> 
										</td>
										<?php if($cur_user_group_id == '1'){ ?>
										<td width="15%">
											<?php
											$wing = getNameById("wing", $user->wing_id);
											echo $wing->name;
											?>
										</td>
										<?php } ?>
										<td width="20%">
											<?php
											$group = getNameById("user_group", $user->group_id);
											echo $group->name;
											?>
										</td>
										<td width="15%">
											<?php 
											$rank = getNameById("rank", $user->rank_id);
											echo $rank->name;
											?>
										</td>
										<td width="20%">
											<?php if($user->photo == ""){?>
												<img height="50" width="60" src="attach_file/unknown.png" title="<?php echo $user->full_name;?>" />
											<?php }else { ?>
												<a id="example4" href="<?php echo $path_view.$user->photo ;?>" ><img height="50" width="60" src="<?php echo $path_view.$user->photo ;?>"  title="<?php echo $user->full_name;?>" /></a>
											<?php } ?>
										</td>			
										<td width="10%">								
											<a href="dashboard.php?action=detail&id=<?php echo $user->id; ?>"><?php echo DETAILS; ?></a>
											<!--<a href="dashboard.php?action=update&id=<?php //echo $user->id; ?>"><?php //echo UPDATE; ?></a>-->
										</td>
									</tr>
								<?php 
										$rownum++;
									}//foreach 
								?>	
						</table>
					</td>
				</tr>
				<tr height="30">
					<td colspan="2">
						<h3><?php echo PROF_INFO; ?></h3><hr />
					</td>
				</tr>
				<?php if($cur_user_group_id > 0 && $cur_user_group_id < 4){ ?>
				<tr>
					<td height="30" width="20%">
						<?php echo TOTAL_USER; ?>:
					</td>
					<td width="80%">
						<strong><?php echo $total_user; ?></strong>
					</td>
				</tr>
				<?php if($cur_user_group_id == 1){ ?>
				<tr>
					<td height="30" width="20%" style="padding-left:15px;">
						<?php echo SUPER_ADMIN; ?>:
					</td>
					<td width="80%">
						<strong><?php echo $total_sa; ?></strong>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<td height="30" width="20%" style="padding-left:15px;">
						<?php echo WING_ADMIN; ?>:
					</td>
					<td width="80%">
						<strong><?php echo $total_wa; ?></strong>
					</td>
				</tr>
				<tr>
					<td height="30" width="20%" style="padding-left:15px;">
						<?php echo WING_MANAGER; ?>:
					</td>
					<td width="80%">
						<strong><?php echo $total_wm; ?></strong>
					</td>
				</tr>
				<tr>
					<td height="30" width="20%" style="padding-left:15px;">
						<?php echo SI; ?>:
					</td>
					<td width="80%">
						<strong><?php echo $total_si; ?></strong>
					</td>
				</tr>
				<tr>
					<td height="30" width="20%" style="padding-left:15px;">
						<?php echo DS; ?>:
					</td>
					<td width="80%">
						<strong><?php echo $total_ds; ?></strong>
					</td>
				</tr>
				<?php } 
				if($cur_user_group_id != 5){
					if($cur_user_group_id == 4 && $unlock_msg != ""){ ?>
				<tr>
					<td height="30" colspan="2">
						<a href="unlock_ds_req.php" style="color:#FF0000; text-decoration:blink;"><?php echo $unlock_msg; ?></a>
					</td>
				</tr>
				<?php }//if there is any unlock msg
				if($cur_user_group_id == '4' && empty($SIcourseArray)){ ?>
				<tr>
					<td height="30" width="20%">
						<?php echo STATUS; ?>:
					</td>
					<td width="80%">
						<strong> <?php echo INACTIVE; ?> </strong>
					</td>
				</tr>
				<?php }//empty Active SI ?>
				<tr>
					<td height="30" width="20%">
						<?php echo ACTIVE_COURSE; ?>:
					</td>
					<td width="80%">
						<strong> <?php echo $active_course; ?> </strong>
					</td>
				</tr>
				<tr>
					<td height="30" width="20%">
						<?php echo ACTIVE_TERM; ?>:
					</td>
					<td width="80%">
						<strong> <?php echo $active_term; ?> </strong>
					</td>
				</tr>
				<?php } //end of Wing Admin
					if($cur_user_group_id == '5'){ ?>
				<tr>
					<td height="30" width="20%">
						<?php echo STATUS; ?>:
					</td>
					<td width="80%">
						<strong><?php echo $status; ?></strong>
					</td>
				</tr>
				<?php if(!empty($activeInfo)){?>
				<tr>
					<td height="30" width="20%">
						<?php echo ACTIVE_COURSE; ?>:
					</td>
					<td width="80%">
						<strong>
						<?php 
						$course = getNameById("course", $activeInfo->course_id);
						echo $course->name; ?>
						</strong>
					</td>
				</tr>
				<tr>
					<td height="30" width="20%">
						<?php echo ACTIVE_TERM; ?>:
					</td>
					<td width="80%">
						<strong>
						<?php 
						$term = getNameById("term", $activeInfo->term_id);
						echo $term->name; ?>
						</strong>
					</td>
				</tr>
				<tr>
					<td height="30" width="20%">
						<?php echo CURRENT_SYND; ?>:
					</td>
					<td width="80%">
						<strong>
						<?php 
						$synd = getNameById("syndicate", $activeInfo->syndicate_id);
						echo $synd->name; ?>
						</strong>
					</td>
				</tr>
				<tr>
					<td height="30" width="20%">
						<?php echo NO_OF_STUDENT; ?>:
					</td>
					<td width="80%">
						<strong><?php echo $total_student; ?></strong>
					</td>
				</tr>
				<tr>
					<td height="30" width="20%">
						<?php echo NO_OF_EXAM; ?>:
					</td>
					<td width="80%">
						<strong><?php echo $total_exercise; ?></strong>
					</td>
				</tr>
				<?php 	}//end activeInfo
					}//end of DS AND SI part
				?>
			</table>
	<?php }elseif($action=="detail"){ ?>
	
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="module_content">
				<?php		
					$rownum = 0;								
					foreach($userList as $user){										
				?>
						<tr>
							<td colspan="2">
								<a href="dashboard.php?action=view"><?php echo BACK; ?></a>
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
								<a href="dashboard.php?action=view"><?php echo BACK; ?></a>
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