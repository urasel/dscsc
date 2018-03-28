<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Marks & Weight Management';
$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$action = $_REQUEST['action'];
$msg = '';

//Chek if this user is valid for this file
if($cur_user_group_id != '2'){
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

$crsId = $_REQUEST['course_id'];
$param = "&course_id=".$crsId;

switch($action){
	case 'view':	
	default:
		//echo $page;
		if($page > 1){
			$_POST['show'] = true;
		}
		
		if(isset($_POST['show']) || (!empty($_REQUEST['course_id']))){
			$course_id = $_REQUEST['course_id'];
			
			$sql = "select * from ".DB_PREFIX."exercise WHERE wing_id = '".$cur_user_wing_id."' OR join_course = 1 OR course_id = '".$course_id."' order by exercise_id, name asc";
			$exerciseList = $dbObj->selectDataObj($sql);
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
		$courseList_opt = formSelectElement($courseId, $course_id, 'course_id');
		
		$action = 'view';
		$msg = $_REQUEST['msg'];
		
		//Pagination 
		if(!empty($exerciseList)){
			$total_rows = sizeof($exerciseList);
		}else{
			$total_rows =0;
		}
		//find start
		$s = ($page - 1) * $limit;
		$total_page = $total_rows/$limit;
		
		break;
	
	case 'assign':
	
		//For picking relevant info
		$id = $_REQUEST['id'];
		$sql = "select * from ".DB_PREFIX."exercise WHERE id='".$id."'";
		$exerciseList = $dbObj->selectDataObj($sql);
		$exercise = $exerciseList[0];
		$joinExercise = $exercise->join_course;	
		if($exercise->wing_id != $cur_user_wing_id && $joinExercise != 1){
			echo UNKNOWN_INPUT;
			exit;
		}
		$mark = $exercise->mark;
		//echo '<pre>';
		//print_r($exercise);
		if($joinExercise == 1 && $cur_user_wing_id == 2){
			$weight = $exercise->air_weight;
		}else if($joinExercise == 1 && $cur_user_wing_id == 3){
			$weight = $exercise->navy_weight;
		}else if($joinExercise == 1 && $cur_user_wing_id == 1){
			$weight = $exercise->weight;
		}else{
			$weight = $exercise->weight;
		}
		
		$marking_type = $exercise->marking_type;

		$msg = $_REQUEST['msg'];
		$action = 'assign';
		break;
			
	case 'save':	
		$id = $_POST['id'];
		$exr_nam = getNameById('exercise', $id);
		$mark = $_POST['mark'];
		$weight = $_POST['weight'];
		$course_id = $_POST['course_id'];
		
		$sql = "select * from ".DB_PREFIX."exercise WHERE id='".$id."'";
		$exerciseList = $dbObj->selectDataObj($sql);
		$exercise = $exerciseList[0];
		$joinExercise = $exercise->join_course;	
		
		//Check for not inserting any blank entry
		if($weight == ""){
			$msg = PARAM_MISSING;
			$url = 'marknweight.php?action=assign&page='.$page.'&id='.$id.'&msg='.$msg;
			redirect($url);
		}
		
		//save in database
		if($joinExercise == 1 && $cur_user_wing_id == 2){
			$fields = array('mark' => $mark,'air_weight' => $weight);
		}else if($joinExercise == 1 && $cur_user_wing_id == 3){
			$fields = array('mark' => $mark,'navy_weight' => $weight);
		}else if($joinExercise == 1 && $cur_user_wing_id == 1){
			$fields = array('mark' => $mark,'weight' => $weight);
		}else{
			$fields = array('mark' => $mark,'weight' => $weight);
		}
		
		
					
		$where = "id = '".$id."'";
		
		$update_status = $dbObj->updateTableData("exercise", $fields, $where);	
		
		if(!$update_status){
			$msg = $exr_nam->name.COULD_NOT_BE_UPDATED;
			$url = 'marknweight.php?action=view&course_id='.$course_id.'&page='.$page.'&msg='.$msg;
			redirect($url);
		}else{
			$msg = $exr_nam->name.HAS_BEEN_UPDATED;
			$url = 'marknweight.php?action=view&course_id='.$course_id.'&page='.$page.'&msg='.$msg;
			redirect($url);
		}
		
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
				<h1><?php echo MARK_WEIGHT_INFO; ?></h1>
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
			<form action="marknweight.php" method="post" name="marknweight" id="marknweight" onsubmit="return validateMarkWeightView();">
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
						<td colspan="2">
						<input type="submit" name="show" class="button" value="<?php echo SHOW; ?>" />
						</td>
					</tr>
				</table>
				<input type="hidden" name="action" value="view" />
			</form>
			<?php if(isset($_POST['show']) || (!empty($_REQUEST['course_id']))){ ?>
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="module_content">
				<tr>
					<td>
						<table width="100%" cellpadding="0" cellspacing="0" border="0" class="datagrid">
							<tr class="head">
								<td height="30" width="10%">
									<strong><?php echo SER_NO; ?></strong>
								</td>
								<td height="30" width="20%">
									<strong><?php echo EXERCISE_ID; ?></strong>
								</td>
								<td height="30" width="20%">
									<strong><?php echo EXERCISE_NAME; ?></strong>
								</td>
								<td height="30" width="10%">
									<strong><?php echo TYPE; ?></strong>
								</td>
								<td height="30" width="10%">
									<strong><?php echo ASSESSMENT_SYSTEM; ?></strong>
								</td>
								<td height="30" width="5%">
									<strong><?php echo MARKS; ?></strong>
								</td>
								<td height="30" width="5%">
									<strong><?php echo WEIGHT; ?></strong>
								</td>
								<td height="30" width="20%" align="center">
									<strong><?php echo ACTION; ?></strong>
								</td>
							</tr>
							<?php			
							if(!empty($exerciseList)){	
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
								<td >
									<?php echo $exerciseList[$rownum]->exercise_id; ?> 
								</td>	
								<td >
									<?php echo $exerciseList[$rownum]->name; ?> 
								</td>
								<td >
									<?php 
										$type = getNameById('exercise_type', $exerciseList[$rownum]->type_id);
										echo $type->name;
									?>
								</td>
								<td width="15%">
									<?php 
										if($exerciseList[$rownum]->marking_type == '1'){
											echo PERCENT_BASED;
										}else if($exerciseList[$rownum]->marking_type == '2'){
											echo MARK_BASED;
										}
									?>
								</td>
								<td >
									<?php if($exerciseList[$rownum]->mark == ""){
												echo NOT_SELECTED;
											}else{
												echo $exerciseList[$rownum]->mark;
									} ?> 
								</td>
								<td >
									<?php if($exerciseList[$rownum]->weight == ""){
												echo NOT_SELECTED;
											}else{
												$joinExercise = $exerciseList[$rownum]->join_course;
												if($joinExercise == 1 && $cur_user_wing_id == 2){
													echo $exerciseList[$rownum]->air_weight;
												}else if($joinExercise == 1 && $cur_user_wing_id == 3){
													echo $exerciseList[$rownum]->navy_weight;
												}else if($joinExercise == 1 && $cur_user_wing_id == 1){
													echo $exerciseList[$rownum]->weight;
												}else{
													echo $exerciseList[$rownum]->weight;
												}
												
												
									} ?> 
								</td>				
								<td align="center">								
									<a href="marknweight.php?action=assign&page=<?php echo $page; ?>&course_id=<?php echo $course_id; ?>&id=<?php echo $exerciseList[$rownum]->id; ?>"><?php echo ASSIGN_WT; ?></a>
								</td>
							</tr>
							<?php 
								}//for
							}else{ ?>
							<tr height="30">
								<td colspan="8">
									<?php echo EMPTY_DATA; ?>
								</td>
							</tr>
							<?php 
							}
							if($total_page > 1){ ?>
							<tr height="50">
								<td colspan="8">
									<?php 
									echo pagination($total_rows,$limit,$page,$param); ?>
								</td>
							</tr>
							<?php } ?>		
						</table>
					</td>
				</tr>
			</table>
	<?php 
			}//if isset post
		}elseif($action=="assign"){ 
	?>
				<form action="marknweight.php" method="post" name="marknweight" id="marknweight" onsubmit="return validateMarkWeight(<?php echo $marking_type; ?>);">
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
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo COURSE; ?>:
							</td>
							<td width="80%">
								<strong>
								<?php
									$course = getNameById('course', $exercise->course_id);
									echo $course->name;
								?>
								</strong>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo EXERCISE_NAME; ?>:
							</td>
							<td width="80%">
								<strong>
								<?php echo $exercise->name; ?>
								</strong>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo EXERCISE_ID; ?>:
							</td>
							<td width="80%">
								<strong>
								<?php echo $exercise->exercise_id; ?>
								</strong>
							</td>
						</tr>
						<?php if($marking_type == '2') {?>
						<tr>
							<td height="30" width="20%">
								<?php echo MARKS; ?>:
							</td>
							<td width="80%">
								<input name="mark" id="mark" type="text" class="inputbox" alt="Marks" size="18" maxlength="3" value="<?php echo $mark; ?>" onkeyup="return isNUM('mark')" />
								<span class="required_field">*</span>
							</td>
						</tr>
						<?php } ?>
						<tr>
							<td height="30" width="20%">
								<?php echo WEIGHT; ?>:
							</td>
							<td width="80%">
								<input name="weight" id="weight" type="text" class="inputbox" alt="Weight" size="18" maxlength="3" value="<?php echo $weight; ?>" onkeyup="return isNUM('weight')" />
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
					<input type="hidden" name="course_id" id="course_id" value="<?php echo $exercise->course_id; ?>" />
					<input type="hidden" name="page" id="page" value="<?php echo $page; ?>" />
				</form>
			
	<?php }?>
</div>
			
<?php
require_once("includes/footer.php");
?>