<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Search Panel';
$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$action = $_REQUEST['action'];
$msg = '';
$path_view = 'attach_file/';
$token = $_REQUEST['token'];


//Chek if this user is valid for this file
if($cur_user_group_id > '3'){
	header("Location: dashboard.php");	
}

//Get User Info
if($cur_user_group_id == 1){
	$sql = "select * from ".DB_PREFIX."user where ( ba_no like'%".$token."%' || full_name like'%".$token."%' || username like'%".$token."%' || official_name like'%".$token."%') order by username asc";
}else if($cur_user_group_id == 2){
	$sql = "select * from ".DB_PREFIX."user where group_id > 1 AND wing_id = '".$cur_user_wing_id."' && ( ba_no like'%".$token."%' || full_name like'%".$token."%' || username like'%".$token."%' || official_name like'%".$token."%') order by username asc";
}else if($cur_user_group_id == 3){
	$sql = "select * from ".DB_PREFIX."user where group_id > 2 AND wing_id = '".$cur_user_wing_id."' && ( ba_no like'%".$token."%' || full_name like'%".$token."%' || username like'%".$token."%' || official_name like'%".$token."%') order by username asc";
}

$userList = $dbObj->selectDataObj($sql);		
$total_user = sizeof($userList);

//Get Student Info
if($cur_user_group_id == 3){
	$sql = "select * from ".DB_PREFIX."student where ( student_id like'%".$token."%' || ba_no like'%".$token."%' || full_name like'%".$token."%' || official_name like'%".$token."%') order by student_id asc";
}

/*else if($cur_user_group_id == 4){
	$sql = "select * from ".DB_PREFIX."user where group_id > 1 AND wing_id = '".$cur_user_wing_id."' && ( ba_no like'%".$token."%' || full_name like'%".$token."%' || username like'%".$token."%' || official_name like'%".$token."%') order by username asc";
}else if($cur_user_group_id == 3){
	$sql = "select * from ".DB_PREFIX."user where group_id > 2 AND wing_id = '".$cur_user_wing_id."' && ( ba_no like'%".$token."%' || full_name like'%".$token."%' || username like'%".$token."%' || official_name like'%".$token."%') order by username asc";
}*/

$studentList = $dbObj->selectDataObj($sql);		
$total_student = sizeof($studentList);

//Get Exercise Info
if($cur_user_group_id == 2 || $cur_user_group_id == 3){
	$sql = "select * from ".DB_PREFIX."exercise where wing_id = '".$cur_user_wing_id."' && ( exercise_id like'%".$token."%' || name like'%".$token."%') order by name asc";
}

$exerciseList = $dbObj->selectDataObj($sql);		
$total_exercise = sizeof($exerciseList);

if($cur_user_group_id == 1){
	$total_result = $total_user;
}else if($cur_user_group_id == 2){
	$total_result = $total_user + $total_exercise;
}else if($cur_user_group_id == 3){
	$total_result = $total_user + $total_student + $total_exercise;
}else if($cur_user_group_id > 3){
	$total_result = $total_student;
}


require_once("includes/templates.php");
require_once("templates/top_menu.php");
require_once("templates/left_menu.php");
?>
<div id="right_column">

		<table width="100%" cellspacing="0" cellpadding="0" border="0" class="module_header">
			<tr>
				<td>
					<h1><?php echo SEARCH_RESULT; ?></h1>
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
		<table id="search_count">
			<tr >
				<td class="search_count_pad">
					<?php
						if($total_result == 1 || $total_result == 0){
							$result = ' result found';
						}else{
							$result = ' results found';
						}
						echo SEARCH_KEYWORD.'<b>'.$token.'</b>'.'<br><br>'.TTL.' : <b>'.$total_result.'</b>'.$result;
						if(!empty($userList)){ echo ', <b>'.$total_user.'</b>'. IN_USER; }
						if(!empty($studentList) && $cur_user_group_id > 2){ echo ', <b>'.$total_student.'</b>'. IN_STUDENT; }
						if(!empty($exerciseList) && ($cur_user_group_id == 2 || $cur_user_group_id == 3)){ echo ', <b>'.$total_exercise.'</b>'. IN_EXERCISE; }
					?>
				</td>
			</tr>
		</table>
	<?php if(!empty($userList)){?>
	<div id="userContainer" style="width:100%; <?php if($total_user > 7){ echo "height:500px; overflow-y:scroll;"; } ?> margin-top:10px;">
		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="module_content">
			<tr>
				<td>
					<h3 class="highlight"><?php echo USER_MANAGEMENT; ?></h3>
				</td>			
			</tr>
			<tr>
				<td>
					<table width="100%" cellpadding="0" cellspacing="0" border="0" class="datagrid">
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
							<?php if($cur_user_group_id == 1){ ?>
							<td height="30" width="15%">
								<strong><?php echo WING; ?></strong>
							</td>
							<?php } ?>
							<td height="30" width="15%">
								<strong><?php echo GROUP; ?></strong>
							</td>
							<td height="30" width="15%">
								<strong><?php echo RANK; ?></strong>
							</td>
							<td height="30" width="15%">
								<strong><?php echo PHOTO; ?></strong>
							</td>
							<td height="30" width="10%">
								<strong><?php echo ACTION; ?></strong>
							</td>
						</tr>
						<?php
						$rownum = 0;							
						if(!empty($userList)){
							foreach($userList as $user){			
								if(($rownum%2)==0){//even
									$class = ' class="even"';									
								}else{//odd
									$class = ' class="odd"';									
								}									
						?>
						<tr <?php echo $class; ?>>
							<td style="padding-left:15px;">
								<?php echo $rownum+1; ?>																					
							</td>
							<td>
								<?php echo $user->username; ?>
							</td>
							<td>
								<?php echo $user->official_name; ?>
							</td>
							<?php if($cur_user_group_id == 1){ ?>
							<td>
								<?php 
								$wing = getNameById("wing", $user->wing_id);
								echo $wing->name;
								?> 																						
							</td>
							<?php } ?>	
							<td>
								<?php 
								$group = getNameById("user_group", $user->group_id);
								echo $group->name;
								?> 																						
							</td>
							<td>
								<?php 
								$rank = getNameById("rank", $user->rank_id);
								echo $rank->name;
								?> 
							</td>
							<td>
								<?php if($user->photo == ""){?>
									<img height="50" width="60" src="attach_file/unknown.png" title="<?php echo $user->full_name;?>" />
								<?php }else { ?>
									<a id="example4" href="<?php echo $path_view.$user->photo ;?>" ><img height="50" width="60" src="<?php echo $path_view.$user->photo ;?>"  title="<?php echo $user->full_name;?>" /></a>
								<?php } ?>
							</td>
							<td>
								<a href="user.php?action=detail&id=<?php echo $user->id; ?>"><?php echo DETAILS; ?></a>
							<?php if(($cur_user_group_id == '1') ||($cur_user_group_id == '2' && ($user->group_id > '2')) ||($cur_user_group_id == '3' && ($user->group_id > '3'))){ ?>
								<a href="user.php?action=update&id=<?php echo $user->id; ?>"><?php echo UPDATE; ?></a>
								<a href="user.php?action=delete&id=<?php echo $user->id; ?>" onclick="return confirm('Are you sure you want to delete?');"><?php echo DELETE; ?></a>
							<?php } ?>																					
							</td>
						</tr>
					<?php 
							$rownum++;
						}//foreach 
					} ?>					
					</table>
				</td>
			</tr>
		</table>
	</div>
	<?php }//if userList is not empty
	if(!empty($studentList) && $cur_user_group_id == 3){?>
	<div id="studentContainer" style="width:100%; <?php if($total_student > 7){ echo "height:500px; overflow-y:scroll;"; } ?> margin-top:10px;">
		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="module_content">
			<tr>
				<td>
					<h3 class="highlight"><?php echo STUDENT_MANAGEMENT; ?></h3>
				</td>			
			</tr>
			<tr>
				<td>
					<table width="100%" cellpadding="0" cellspacing="0" border="0" class="datagrid">
						<tr class="head">
							<td height="30" width="15%">
								<strong><?php echo SER_NO; ?></strong>
							</td>
							<td height="30" width="20%">
								<strong><?php echo COURSE; ?></strong>
							</td>
							<td height="30" width="20%">
								<strong><?php echo STUDENT_ID; ?></strong>
							</td>								
							<td height="30" width="15%">
								<strong><?php echo BA_NO; ?></strong>
							</td>
							<td height="30" width="15%">
								<strong><?php echo OFFICIAL_NAME; ?></strong>
							</td>
							<td height="30" width="15%">
								<strong><?php echo RANK; ?></strong>
							</td>
							<td height="30" width="15%">
								<strong><?php echo PHOTO; ?></strong>
							</td>
							<td height="30" width="15%">
								<strong><?php echo ACTION; ?></strong>
							</td>
						</tr>
						<?php
						if(!empty($studentList)){
							$rownum = 0;							
							foreach($studentList as $student){			
								if(($rownum%2)==0){//even
									$class = ' class="even"';									
								}else{//odd
									$class = ' class="odd"';									
								}									
						?>
						<tr <?php echo $class; ?>>
							<td style="padding-left:15px;">
								<?php echo $rownum+1; ?>																					
							</td>
							<td>
								<?php 
								$course = getNameById("course", $student->course_id);
								echo $course->name;
								?>																					
							</td>
							<td>
								<?php echo $student->student_id; ?>
							</td>
							<td>
								<?php echo $student->ba_no; ?>
							</td>	
							<td>
								<?php echo $student->official_name; ?>																					
							</td>
							<td>
								<?php 
								$rank = getNameById("rank", $student->rank_id);
								echo $rank->name;
								?>																					
							</td>
							<td>
								<?php if($student->photo == ""){?>
									<img height="50" width="60" src="attach_file/unknown.png" title="<?php echo $student->full_name;?>" />
								<?php }else { ?>
									<a id="example4" href="<?php echo $path_view.$student->photo ;?>" ><img height="50" width="60" src="<?php echo $path_view.$student->photo ;?>"  title="<?php echo $student->full_name;?>" /></a>
								<?php } ?>
							</td>
							<td>
								<a href="student.php?action=update&id=<?php echo $student->id; ?>"><?php echo UPDATE; ?></a>
								<a href="student.php?action=detail&id=<?php echo $student->id; ?>"><?php echo DETAILS; ?></a>
								<a href="student.php?action=delete&id=<?php echo $student->id; ?>" onclick="return confirm('Are you sure you want to delete?');"><?php echo DELETE; ?></a>
							</td>
						</tr>
						<?php 
								$rownum++;
							}//foreach	
						} ?>
					</table>
				</td>
			</tr>
		</table>
	</div>
	<?php }//if not empty Student List 
	if(!empty($exerciseList) && ($cur_user_group_id == 2 || $cur_user_group_id == 3)){ ?>
	<div id="exerciseContainer" style="width:100%; <?php if($total_exercise > 7){ echo "height:500px; overflow-y:scroll;"; } ?> margin-top:10px;">
		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="module_content">
			<tr>
				<td>
					<h3 class="highlight"><?php echo EXERCISE_MANAGEMENT; ?></h3>
				</td>			
			</tr>
			<tr>
				<td>
					<table width="100%" cellpadding="0" cellspacing="0" border="0" class="datagrid">
						<tr class="head">
							<td height="30" width="10%">
								<strong><?php echo SER_NO; ?></strong>
							</td>
							<td height="30" width="10%">
								<strong><?php echo EXERCISE_ID; ?></strong>
							</td>
							<td height="30" width="20%">
								<strong><?php echo NAME; ?></strong>
							</td>
							<td height="30" width="10%">
								<strong><?php echo TYPE; ?></strong>
							</td>
							<td height="30" width="20%">
								<strong><?php echo ASSESSMENT_SYSTEM; ?></strong>
							</td>
							<td height="30" width="5%">
								<strong><?php echo MARKS; ?></strong>
							</td>
							<td height="30" width="5%">
								<strong><?php echo WEIGHT; ?></strong>
							</td>
							<td height="30" width="10%" align="center">
								<strong><?php echo ACTION; ?></strong>
							</td>
						</tr>
						<?php
						if(!empty($exerciseList)){
							$rownum = 0;							
							foreach($exerciseList as $exercise){			
								if(($rownum%2)==0){//even
									$class = ' class="even"';									
								}else{//odd
									$class = ' class="odd"';									
								}									
						?>
						<tr <?php echo $class; ?>>
							<td style="padding-left:15px;">
								<?php echo $rownum+1; ?>																					
							</td>
							<td>
								<?php echo $exercise->exercise_id; ?>												
							</td>	
							<td>
								<?php echo $exercise->name; ?> 																						
							</td>
							<td>
								<?php 
								$type = getNameById("exercise_type", $exercise->type_id);
								echo $type->name;
								?>																						
							</td>
							<td>
								<?php 
									if($exercise->marking_type == 1){
										echo PERCENT_BASED;
									}else{
										echo MARK_BASED;
									}
								?> 	
							</td>
							<td align="center">
								<?php if($exercise->mark == ""){
										echo NOT_SELECTED;
									}else{
										echo $exercise->mark;
									} ?> 
							</td>
							<td align="center">
								<?php if($exercise->weight == ""){
										echo NOT_SELECTED;
									}else{
										echo $exercise->weight;
									} ?> 
							</td>
							<td align="center">
							<?php if($cur_user_group_id == 2){ ?>
								<a href="marknweight.php?action=assign&id=<?php echo $exercise->id; ?>"><?php echo ASSIGN_MARK; ?></a> 
							<?php } else if($cur_user_group_id == 3){ ?>
								<a href="exercise.php?action=update&id=<?php echo $exercise->id; ?>"><?php echo UPDATE; ?></a>
								<a href="exercise.php?action=delete&id=<?php echo $exercise->id; ?>" onclick="return confirm('Are you sure you want to delete?');"><?php echo DELETE; ?></a>
							<?php } ?>
							</td>
						</tr>
						<?php 
								$rownum++;
							}//foreach
						?>		
					</table>
				</td>
			</tr>
		</table>
	<?php }//if not empty exerciseList   ?>
	</div>
<?php } ?>
</div>
			
<?php
require_once("includes/footer.php");
?>