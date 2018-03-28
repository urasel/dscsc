<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Student Management';
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

$crsId = $_REQUEST['course_id'];
$param = "&course_id=".$crsId;
//echo $param;

switch($action){
	case 'view':	
	default:
		
		if($page > 0){
			$_POST['submit'] = true;
		}
		
		if(isset($_POST['submit'])){
			$course_id = $_REQUEST['course_id'];
			$sql = "select * from ".DB_PREFIX."student where wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."' order by student_id asc";
			$studentList = $dbObj->selectDataObj($sql);
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
		
		$path_view = 'attach_file/';
		$action = 'view';
		$msg = $_REQUEST['msg'];
		
		//Pagination 
		if(!empty($studentList)){
			$total_rows = sizeof($studentList);
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
			$sql = "select * from ".DB_PREFIX."student WHERE id='".$id."'";	
			$studentList = $dbObj->selectDataObj($sql);
			$student = $studentList[0];
			
			//Make Sure wing manager of different wing cannot change a Student by force from URL
			if($student->wing_id != $cur_user_wing_id){
				header("Location: dashboard.php");
				exit;
			}
			
			$rank_id = $student->rank_id;
			$course_id = $student->course_id;
			$student_id = $student->student_id;
			$ba_no = $student->ba_no;
			$full_name = $student->full_name;
			$official_name = $student->official_name;
			$photo = $student->photo;
		}else{
			$id = '';
			$wing_id = '';
			$rank_id = '';
			$course_id = '';
			$student_id = '';
			$ba_no = '';
			$full_name = '';
			$official_name = '';
			$photo = '';
		}
		
		//Build Rank Array
		$sql = "select id, name from ".DB_PREFIX."rank where wing_id = '".$cur_user_wing_id."' order by weight asc";
		$rankArr = $dbObj->selectDataObj($sql);
		
		$rankId = array();
		$rankId[0] = SELECT_RANK_OPT;
		if(!empty($rankArr)){			
			foreach($rankArr as $item){
				$rankId[$item->id] = $item->name;
			}	
		}			
		$rankList_opt = formSelectElement($rankId, $rank_id, 'rank_id');
		
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
		
		
		$action = 'insert';
		break;
		
	case 'save':
		$id = $_POST['id'];
		$wing_id = $_POST['wing_id'];
		$rank_id = $_POST['rank_id'];
		$course_id = $_POST['course_id'];
		$student_id = $_POST['student_id'];
		$ba_no = $_POST['ba_no'];
		$full_name = $_POST['full_name'];
		$official_name = $_POST['official_name'];
		$photo = $_POST['photo'];
		$created_date_time = $last_updated_time = date('Y-m-d H:i:s');
		
		//Check for not inserting any blank entry
		if($rank_id == '0' || $course_id == '0' || $student_id == "" || $ba_no == "" || $full_name == "" || $official_name == ""){
			$msg = PARAM_MISSING;
			if(empty($id)){
				$url = 'student.php?action=create&msg='.$msg;
			}else{
				$url = 'student.php?action=update&page='.$page.'&course_id='.$crsId.'&id='.$id.'&msg='.$msg;
			}
			redirect($url);
		}
		
		//check repeation of Official Name
		if(empty($id)){
			$sql = "select official_name from ".DB_PREFIX."student WHERE official_name = '".$official_name."' AND course_id = '".$course_id."' limit 1";
			$studentList = $dbObj->selectDataObj($sql);
			
			if(!empty($studentList)){
				$msg = $official_name.ALREADY_EXISTS;
				$url = 'student.php?action=create&msg='.$msg;
				redirect($url);
			}
		}else if(!empty($id)){
			$sql = "select official_name from ".DB_PREFIX."student WHERE id!='".$id."' AND official_name = '".$official_name."' AND course_id = '".$course_id."' limit 1";
			$studentList = $dbObj->selectDataObj($sql);		
			
			if(!empty($studentList)){
				$msg = $official_name.ALREADY_EXISTS;
				$url = 'student.php?action=update&page='.$page.'&course_id='.$crsId.'&id='.$id.'&msg='.$msg;
				redirect($url);
			}
		}
		
		//check repeation of BA-number of student
		if(empty($id)){
			$sql = "select ba_no from ".DB_PREFIX."student WHERE ba_no = '".$ba_no."' limit 1";
			$studentList = $dbObj->selectDataObj($sql);		
			
			if(!empty($studentList)){
				$msg = $ba_no.ALREADY_EXISTS;
				$url = 'student.php?action=create&msg='.$msg;
				redirect($url);
			}			
		}else if(!empty($id)){
			$sql = "select ba_no from ".DB_PREFIX."student WHERE id!='".$id."' AND ba_no = '".$ba_no."' limit 1";
			$studentList = $dbObj->selectDataObj($sql);		
			
			if(!empty($studentList)){
				$msg = $ba_no.ALREADY_EXISTS;
				$url = 'student.php?action=update&page='.$page.'&course_id='.$crsId.'&id='.$id.'&msg='.$msg;
				redirect($url);
			}
		}
		
		//check repeation of Student ID
		if(empty($id)){
			$sql = "select student_id from ".DB_PREFIX."student WHERE student_id = '".$student_id."' AND course_id = '".$course_id."' limit 1";
			$studentList = $dbObj->selectDataObj($sql);		
			
			if(!empty($studentList)){
				$msg = STUDENT_ID.' '.$student_id.ALREADY_EXISTS;
				$url = 'student.php?action=create&msg='.$msg;
				redirect($url);
			}			
		}else if(!empty($id)){
			$sql = "select student_id from ".DB_PREFIX."student WHERE id!='".$id."' AND student_id = '".$student_id."' AND course_id = '".$course_id."' limit 1";
			$studentList = $dbObj->selectDataObj($sql);		
			
			if(!empty($studentList)){
				$msg = STUDENT_ID.' '.$student_id.ALREADY_EXISTS;
				$url = 'student.php?action=update&page='.$page.'&course_id='.$crsId.'&id='.$id.'&msg='.$msg;
				redirect($url);
			}
		}
		
		//Upload file
		$path = './attach_file/';
		
		$uploaded = upload_file($_FILES, $path, $cur_user_id);	
		
		if($uploaded['error_counter'] != '0'){
			$msg = implode("<br />",$uploaded['error']);
			$url = 'student.php?action=create&msg='.$msg;
			redirect($url);
		}
			
		if(!empty($id)){
			$fields = array('wing_id' => $wing_id,
						'rank_id' => $rank_id,
						'course_id' => $course_id,
						'student_id' => $student_id,
						'ba_no' => $ba_no,
						'full_name' => $full_name,
						'official_name' => $official_name,
						'updated_by' => $cur_user_id,
						'updated_datetime' => $last_updated_time
						);
			
			if($uploaded['uploaded'][0] != ''){
				$fields['photo'] = ($uploaded['uploaded'][0] == '')?'':$uploaded['uploaded'][0];		
			}
			
			$where = "id = '".$id."'";
			
			$update_status = $dbObj->updateTableData("student", $fields, $where);	
			
			if(!$update_status){
				$msg = $official_name.COULD_NOT_BE_UPDATED;
				$action = 'insert';
			}else{
				$msg = $official_name.HAS_BEEN_UPDATED;
				$url = 'student.php?action=view&page='.$page.'&course_id='.$course_id.'&msg='.$msg;
				redirect($url);
			}
		}else{
			$fields = array('wing_id' => $wing_id,
						'rank_id' => $rank_id,
						'course_id' => $course_id,
						'student_id' => $student_id,
						'ba_no' => $ba_no,
						'full_name' => $full_name,
						'official_name' => $official_name,
						'photo' => ($uploaded['uploaded'][0] == '')?'':$uploaded['uploaded'][0],
						'created_by' => $cur_user_id,
						'created_datetime' => $created_date_time,
						'updated_by' => $cur_user_id,
						'updated_datetime' => $last_updated_time
						);
			
			$inserted = $dbObj->insertTableData("student", $fields);	
			if(!$inserted){
				$msg = $official_name.COULD_NOT_BE_CREATED;
				$action = 'insert';
			}else{
				$msg = $official_name.CREATED_SUCCESSFULLY;
				$url = 'student.php?action=view&msg='.$msg;
				redirect($url);
			}
		}
		break;

	case 'detail':	
		$id = $_REQUEST['id'];	
		
		//$sql = "select * from ".DB_PREFIX."student where id = '".$id."'";
		$sql = "SELECT DISTINCT ( st.id ), st. * , sd.name as synname FROM dscsc_student st LEFT JOIN dscsc_student_to_syndicate sy ON st.id = sy.student_id LEFT JOIN dscsc_syndicate sd ON sd.id = sy.syndicate_id WHERE st.id = '".$id."'";
			
		$studentList = $dbObj->selectDataObj($sql);		
		
		$action = 'detail';
		$msg = $_REQUEST['msg'];
		break;
		
	case 'delete':	
		$id = $_REQUEST['id'];
		$sql = "select * from ".DB_PREFIX."student WHERE id='".$id."'";	
		$studentList = $dbObj->selectDataObj($sql);
		$student = $studentList[0];
		
		//Make Sure wing manager of different wing cannot change a Student by force from URL
		if($student->wing_id != $cur_user_wing_id){
			header("Location: dashboard.php");
			exit;
		}
		
		$official_name = $student->official_name;
		$where = "id='".$id."'";	
		
		$success = $dbObj->deleteTableData("student", $where);	
		
		if(!$success){
			$msg = $official_name.COULD_NOT_BE_DELETED;
		}else{
			$msg = $official_name.HAS_BEEN_DELETED;
		}
		
		$url = 'student.php?action=view&page='.$page.'&course_id='.$crsId.'&msg='.$msg;
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
				<h1><?php echo STUDENT_MANAGEMENT; ?></h1>
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
			
		<form action="student.php" method="post" name="student" id="student" onsubmit="return showStudent()" >
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="module_content">
				<tr>
					<td height="30" width="20%">
						<?php echo SELECT_COURSE; ?>:
					</td>
					<td width="80%">
						<?php echo $courseList_opt; ?>
						<span class="required_field">*</span>
					</td>
				</tr>
				<tr>
					<td height="30" colspan="2">
						<input type="submit" name="submit" id="submit" value="Show Students" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="action" value="view" />
		</form>
			
		<?php 
			if(isset($_POST['submit'])){ 
				
		?>			
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="module_content">
				<tr>
					<td>
						<table width="100%" cellpadding="0" cellspacing="0" border="0" class="datagrid">
								<tr class="footer">
									<td colspan="5" style=" background:#EEEEEE;">
										<b><a href="student.php?action=create"><?php echo CREATE; ?></a></b>
									</td>
								</tr>				
							<tr class="head">
								<td height="30" width="10%">
									<strong><?php echo SER_NO; ?></strong>
								</td>
								<td height="30" width="10%" align="center">
									<strong><?php echo STUDENT_ID; ?></strong>
								</td>
								<td height="30" width="20%">
									<strong><?php echo BA_NO; ?></strong>
								</td>
								<td height="30" width="20%">
									<strong><?php echo OFFICIAL_NAME; ?></strong>
								</td>
								<td height="30" width="20%">
									<strong><?php echo RANK; ?></strong>
								</td>
								<!--<td height="30" width="10%">
									<strong><?php //echo COURSE; ?></strong>
								</td>-->
								<td height="30" width="20%">
									<strong><?php echo PHOTO; ?></strong>
								</td>
								<td height="30" width="20%">
									<strong><?php echo ACTION; ?></strong>
								</td>
							</tr>
							<?php			
							if(!empty($studentList)){	
								
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
										<td width="10%" style="padding-left:15px;">
											<?php echo $sl++; ?> 
										</td>
										<td width="10%">
											<?php echo $studentList[$rownum]->student_id; ?> 
										</td>
										<td width="20%">
											<?php echo $studentList[$rownum]->ba_no; ?> 
										</td>
										<td width="20%">
											<?php echo $studentList[$rownum]->official_name; ?> 
										</td>
										<td width="20%">
											<?php 
												$rank = getNameById('rank', $studentList[$rownum]->rank_id);
												echo $rank->short_name;
											?>
										</td>
										<!--<td width="10%">
											<?php
											//$course = getNameById("course", $student->course_id);
											//echo $course->name;
											?>
										</td>-->
										<td width="20%">
											<?php if($studentList[$rownum]->photo == ""){?>
												<img height="50" width="60" src="attach_file/unknown.png" />
											<?php }else { ?>
												<a id="example4" href="<?php echo $path_view.$studentList[$rownum]->photo ;?>" ><img height="50" width="60" src="<?php echo $path_view.$studentList[$rownum]->photo ;?>" /></a>
											<?php } ?>
										</td>
										<td width="20%">								
											<a href="student.php?action=update&page=<?php echo $page; ?>&id=<?php echo $studentList[$rownum]->id; ?>"><?php echo UPDATE; ?></a>
											<a href="student.php?action=detail&page=<?php echo $page; ?>&course_id=<?php echo $course_id; ?>&id=<?php echo $studentList[$rownum]->id; ?>"><?php echo DETAILS; ?></a>
											<a href="student.php?action=delete&page=<?php echo $page; ?>&course_id=<?php echo $course_id; ?>&id=<?php echo $studentList[$rownum]->id; ?>" onclick="return confirm('Are you sure you want to delete?');"><?php echo DELETE; ?></a>
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
								<tr class="footer">
									<td colspan="8">
										<b><a href="student.php?action=create"><?php echo CREATE; ?></a></b>
									</td>
								</tr>				
						</table>
					</td>
				</tr>
			</table>
		<?php  } ?>
				
	<?php }elseif($action=="insert"){ ?>
	
				<form action="student.php" method="post" name="student" id="student" onsubmit="return validateStudent();" enctype="multipart/form-data">
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
								<?php echo RANK; ?>:
							</td>
							<td width="80%">
								<?php echo $rankList_opt;	?>
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo COURSE; ?>:
							</td>
							<td width="80%">
								<?php echo $courseList_opt;	?>
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo STUDENT_ID; ?>:
							</td>
							<td width="80%">
								<input name="student_id" id="student_id" type="text" class="inputbox" alt="Student ID" size="36" value="<?php echo $student_id; ?>" />
								<span class="required_field">*</span>
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
								<!--<input type="button" name="checkStudentName" id="checkStudentName" value="Check" onclick="return getStudentName();" />-->
							</td>
						</tr>
						<!--<tr>
							<td style="padding-left:140px;" colspan="2">
								<div id="loaderContainer"></div>
								<div id="studentname_display"></div>
							</td>
						</tr>-->
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
								<input type="submit" name="submit" class="button" value="Save" />
								<a href="<?php echo $_SERVER['HTTP_REFERER']; ?>"><input type="button" onclick="window.location='<?php echo $_SERVER['HTTP_REFERER']; ?>'"  name="cancel" class="cancel" value="<?php echo CANCEL; ?>" /></a>
							</td>
						</tr>		
					</table>	
					<input type="hidden" name="id" value="<?php echo $id; ?>" />
					<input type="hidden" name="action" value="save" />
					<input type="hidden" name="page" id="page" value="<?php echo $page; ?>" />
					<!--<input type="hidden" name="course_id" id="course_id" value="<?php //echo $course_id; ?>" />-->
				</form>
			
	<?php }elseif($action=="detail"){ ?>
	
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="module_content">
				<?php		
					$rownum = 0;							
					foreach($studentList as $student){										
				?>
						<tr>
							<td colspan="2">
								<a href="<?php echo $_SERVER['HTTP_REFERER']; ?>" onclick="window.location='<?php echo $_SERVER['HTTP_REFERER']; ?>'" ><?php echo BACK; ?></a>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo WING; ?>:
							</td>
							<td width="80%">
								<?php 
									$wing = getNameById("wing", $student->wing_id);
									echo $wing->name;
								?> 							
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo STUDENT_ID; ?>:
							</td>
							<td width="80%">
								<?php echo $student->student_id; ?>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo OFFICIAL_NAME; ?>:
							</td>
							<td width="80%">
								<?php 
								echo $student->official_name;
								?>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo FULL_NAME; ?>:
							</td>
							<td width="80%">
								<?php 
								echo $student->full_name;
								?>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo RANK; ?>:
							</td>
							<td width="80%">
								<?php 
								$rank = getNameById("rank", $student->rank_id);
								echo $rank->name;
								?>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo course; ?>:
							</td>
							<td width="80%">
								<?php 
								$course = getNameById("course", $student->course_id);
								echo $course->name;
								?>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo BA_NO; ?>:
							</td>
							<td width="80%">
								<?php 
								echo $student->ba_no;
								?>
							</td>
						</tr>
						
						
				<?php
					break;}//foreach
				echo '<tr><td height="30" width="20%">Syndicate:</td><td width="80%">';
				foreach($studentList as $student){
					echo $student->synname.', ';
				}
				echo '</td></tr>';
			}//elseif
				?>
			<tr>
							<td colspan="2">&nbsp;
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<a href="<?php echo $_SERVER['HTTP_REFERER']; ?>" onclick="window.location='<?php echo $_SERVER['HTTP_REFERER']; ?>'" ><?php echo BACK; ?></a>
							</td>
						</tr>
			</table>	
</div>

<?php
require_once("includes/footer.php");
?>
