<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Assigned Student';
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
$trmId = $_REQUEST['term_id'];
$syndId = $_REQUEST['syndicate_id'];

	$param = "&course_id=".$crsId;
	if($trmId != '0'){
		$param .= "&term_id=".$trmId;
	}
	if($syndId != '0'){
		$param .= "&syndicate_id=".$syndId;
	}

switch($action){
	case 'view':	
	default:
		
		if($page > 0){
			$_POST['show'] = true;
		}
		
		if(isset($_POST['show'])){
			$course_id = $_REQUEST['course_id'];
			$term_id = $_REQUEST['term_id'];
			$syndicate_id = $_REQUEST['syndicate_id'];
			
			
			if($term_id == 0 || $term_id == ""){
				$add_term = "";
			}else{
				$add_term = " AND stc.term_id = '".$term_id."' ";
			}
			
			if($syndicate_id == 0 || $syndicate_id == ""){
				$add_syndicate = "";
			}else{
				$add_syndicate = " AND stc.syndicate_id = '".$syndicate_id."' ";
			}
			
			$syndicateQuery = "select id, syndicate_type from ".DB_PREFIX."syndicate WHERE id = '".$syndicate_id."'";
			$syndicate = $dbObj->selectDataObj($syndicateQuery);
			$syndicateType = $syndicate[0]->syndicate_type;
			if($syndicateType == 1){
				$courseIdArr = '';
				$courseQuery = "select session from ".DB_PREFIX."course WHERE id = '".$course_id."'";
				$coursesSession = $dbObj->selectDataObj($courseQuery);
				$coursesSession = $coursesSession[0]->session;
				$courseArrQuery = "select id from ".DB_PREFIX."course WHERE session = '".$coursesSession."'";
				$coursesArr = $dbObj->selectDataObj($courseArrQuery);
				foreach($coursesArr as $course){
					$courseIdArr[] = $course->id;
				}
				$courseIdArr = join(', ', $courseIdArr);
				
				
				$query = "select std.id, std.student_id,wng.name wng, stc.term_id, stc.syndicate_id,stc.wing_id, std.ba_no, std.rank_id, std.official_name from ".DB_PREFIX."student as std, ".DB_PREFIX."student_to_syndicate as stc, ".DB_PREFIX."wing as wng where stc.course_id IN (".$courseIdArr.") AND std.wing_id = wng.id  AND stc.student_id = std.id ".$add_term. $add_syndicate." order by stc.student_id asc,wng.name asc";
			}else{
				$query = "select std.id, std.student_id,wng.name wng, stc.term_id, stc.syndicate_id, std.ba_no, std.rank_id, std.official_name from ".DB_PREFIX."student as std, ".DB_PREFIX."student_to_syndicate as stc, ".DB_PREFIX."wing as wng  where stc.course_id = '".$course_id."' AND std.wing_id = wng.id   AND stc.student_id = std.id ".$add_term. $add_syndicate." order by stc.student_id asc";
			}
			
			//Build Student List Array	
			
			$studentArr = $dbObj->selectDataObj($query);
			//echo '<pre>';
			//print_r($studentArr);
			
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
		$courseList_opt = formSelectElement($courseId, $course_id, 'course_id', 'onchange = processFunction("assigned_student")');
		
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
		
		//Build Syndicate List Array	
		$query = "select syn.id, syn.name from ".DB_PREFIX."syndicate as syn, ".DB_PREFIX."syndicate_to_course as stc where stc.course_id = '".$course_id."' AND stc.wing_id = '".$cur_user_wing_id."' AND stc.syndicate_id = syn.id order by syn.name asc";
		$synArr = $dbObj->selectDataObj($query);
		$synId = array();
		$synId[0] = SELECT_SYNDICATE_OPT;
		if(!empty($synArr)){			
			foreach($synArr as $item){
				$synId[$item->id] = $item->name;
			}	
		}			
		$syndicateList_opt = formSelectElement($synId, $syndicate_id, 'syndicate_id');
		
		
		$action = 'view';
		$msg = $_REQUEST['msg'];
		
		//Pagination 
		if(!empty($studentArr)){
			$total_rows = sizeof($studentArr);
		}else{
			$total_rows =0;
		}
		//find start
		$s = ($page - 1) * $limit;
		$total_page = $total_rows/$limit;
		
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
				<h1><?php echo ASSIGNED_STUDENT; ?></h1>
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
		<form action="assigned_student.php" method="post" name="assigned_student" id="assigned_student" onsubmit="return validateAssignedStudent();">
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
					<td height="30" width="20%">
						<?php echo SELECT_SYNDICATE; ?>:
					</td>
					<td width="80%">
						<div id="loaderContainer"></div>
						<div id="syndicate_display">
							<?php echo $syndicateList_opt;	?>
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
							<td height="30" width="10%">
								<strong><?php echo STUDENT_ID; ?></strong>
							</td>
							<td height="30" width="10%">
								<strong><?php echo WING; ?></strong>
							</td>
							<td height="30" width="15%">
								<strong><?php echo TERM; ?></strong>
							</td>
							<td height="30" width="15%">
								<strong><?php echo SYNDICATE; ?></strong>
							</td>
							<td height="30" width="15%">
								<strong><?php echo BA_NO; ?></strong>
							</td>
							<td height="30" width="15%">
								<strong><?php echo RANK; ?></strong>
							</td>
							<td height="30" width="20%">
								<strong><?php echo OFFICIAL_NAME; ?></strong>
							</td>
						</tr>
						<?php			
						if(!empty($studentArr)){	
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
								
							if($term_id == 0 || $term_id == "")	{
								$new_cond = "";
							}else{
								$new_cond = " AND term_id = '".$term_id."'";
							}
								
						?>
						<tr <?php echo $class; ?>>
							<td style="padding-left:15px;">
								<?php echo $rownum+1; ?> 
							</td>
							<td>
								<?php echo $studentArr[$rownum]->student_id; ?> 
							</td>
							<td>
								<?php echo $studentArr[$rownum]->wng; ?> 
							</td>
							<td >
								<?php 	$term = getNameById('term', $studentArr[$rownum]->term_id);
									echo $term->name;?>
							</td>
							<td >
								<?php 	$syndicate = getNameById('syndicate', $studentArr[$rownum]->syndicate_id);
									echo $syndicate->name;?>
							</td>	
							<td >
								<?php echo $studentArr[$rownum]->ba_no; ?> 
							</td>	
							<td >								
								<?php 	$rank = getNameById('rank', $studentArr[$rownum]->rank_id);
									echo $rank->short_name;?>
							</td>
							<td>								
								<?php echo $studentArr[$rownum]->official_name; ?>
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
						}
						if($total_page > 1){ ?>
						<tr height="50">
							<td colspan="7">
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
		}
	} 
	?>
</div>
			
<?php
require_once("includes/footer.php");
?>
