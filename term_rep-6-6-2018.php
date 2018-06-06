<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Term Results';
$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$cur_user_off_nam = $usr[0]->official_name;
$cur_user_rank = getNameById('rank',$usr[0]->rank_id);
$cur_user_rank = $cur_user_rank->name;
$action = $_REQUEST['action'];
$msg = '';

//Chek if this user is valid for this file
if($cur_user_group_id != '4' && $cur_user_group_id != '5'){	//only DS & SI can view Term report
	header("Location: dashboard.php");	
}

switch($action){
	case 'view':	
	default:
		
		if($cur_user_group_id == '5'){
			//Find Currently Active Course
			$sql = "SELECT id from ".DB_PREFIX."course WHERE wing_id = '".$cur_user_wing_id."' AND status = '0'";
			$activeCourseArr = $dbObj->selectDataObj($sql);
			$course_id = $activeCourseArr[0]->id;
			
			//Find Currently Active Term which is assigned to this DS
			$sql = "select id from ".DB_PREFIX."term WHERE wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."' AND status = '0'";
			$activeTermArr = $dbObj->selectDataObj($sql);
			$term_id = $activeTermArr[0]->id;
			
			//Find syndicate of the DS
			$sql = "select syndicate_id from ".DB_PREFIX."ds_to_course WHERE wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."' AND term_id = '".$term_id."' AND ds_id = '".$cur_user_id."' AND syndicatetype = 0";
			$activeSynArr = $dbObj->selectDataObj($sql);
			$syndicate_id = $activeSynArr[0]->syndicate_id;
			
			$sql = "SELECT * from ".DB_PREFIX."impression_marking_lock WHERE course_id = '".$course_id."' AND term_id = '".$term_id."' AND syndicate_id = '".$syndicate_id."' AND status = '1'";
			$imp_forwarded = $dbObj->selectDataObj($sql);
			
			$forwarded = (!empty($imp_forwarded)) ? 'true' : 'false';

		}else if($cur_user_group_id == '4'){
		
			if(isset($_POST['submit'])){
				$course_id = $_REQUEST['course_id'];
				$term_id = $_REQUEST['term_id'];
				$syndicate_id = $_REQUEST['syndicate_id'];
			}
		
			//Find Currently Active Course/Courses which is assigned to this SI from si_to_course
			$sql = "select stc.course_id from ".DB_PREFIX."si_to_course as stc,  ".DB_PREFIX."course as crs where stc.si_id = ".$cur_user_id." AND stc.wing_id = '".$cur_user_wing_id."' AND crs.status = '0' AND crs.id = stc.course_id ORDER BY crs.name";
			$courseArray = $dbObj->selectDataObj($sql);
			
			if(!empty($courseArray)){
				//Build Course Selection Array
				$sql = "select id, name from ".DB_PREFIX."course where wing_id = '".$cur_user_wing_id."' AND (";
										foreach($courseArray as $course){
											$sql .= " id = '".$course->course_id."' || ";						
										}
				$sql = rtrim($sql, "|| ");
				$sql = $sql.');';
				$course_list = $dbObj->selectDataObj($sql);
				
				$courseId = array();
				$courseId[0] = SELECT_COURSE_OPT;
				if(!empty($course_list)){			
					foreach($course_list as $item){
						$courseId[$item->id] = $item->name;
					}	
				}			
				$courseList_opt = formSelectElement($courseId, $course_id, 'course_id', 'onchange = processFunction("si_report_term")');
				
				//Build Term List Array	
				$query = "select id, name from ".DB_PREFIX."term WHERE course_id = '".$course_id."' order by name asc";
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
				$query = "select syn.id, syn.name from ".DB_PREFIX."syndicate as syn, ".DB_PREFIX."syndicate_to_course as stc where syn.wing_id = ".$cur_user_wing_id." AND stc.course_id = '".$course_id."' AND syn.id = stc.syndicate_id order by syn.name asc";
				$synArr = $dbObj->selectDataObj($query);
		
				$synId = array();
				$synId[0] = SELECT_SYNDICATE_OPT;
				if(!empty($synArr)){			
					foreach($synArr as $item){
						$synId[$item->id] = $item->name;
					}	
				}			
				$synList_opt = formSelectElement($synId, $syndicate_id, 'syndicate_id');
			}//if not empty courseArray
		}//else if
		
		//Build Exercise List Array	
		//$query = "select exr.id, exr.name, exr.weight, exr.mark from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$course_id."' AND ett.term_id = '".$term_id."' AND exr.id = ett.exercise_id order by exr.name asc";
		
		$query = "select exr.id, exr.name,exr.join_course, exr.weight, exr.air_weight, exr.navy_weight, exr.mark from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where ett.term_id = ".$term_id." AND ett.exercise_id = exr.id order by exr.name asc";
		
		$exrArr = $dbObj->selectDataObj($query);
		//echo '<pre>';
		//print_r($exrArr);exit;
		$totalExercise = sizeof($exrArr);
		
		//Find out totall Syndicat assigned in this term
		//$query = "select * from ".DB_PREFIX."syndicate_to_course where wing_id = ".$cur_user_wing_id." AND course_id = '".$course_id."'";
		$query = "select syn.id, syn.name, syn.syndicate_id from ".DB_PREFIX."syndicate as syn, ".DB_PREFIX."syndicate_to_course as stc where syn.wing_id = ".$cur_user_wing_id." AND stc.course_id = '".$course_id."' AND syn.syndicate_type = 0 AND syn.id = stc.syndicate_id order by syn.name asc";
		$syndArr = $dbObj->selectDataObj($query);
		$totalSyndicate = sizeof($syndArr);
		
		$total_forwarded = 0;
		foreach($syndArr as $synt){
			//print_r($synt);exit;
			$sql = "SELECT * FROM ".DB_PREFIX."impression_marking_lock WHERE course_id = ".$course_id." AND term_id = '".$term_id."' AND syndicate_id = '".$synt->syndicate_id."' AND status = '1'";
			$allForwardedArr = $dbObj->selectDataObj($sql);
			//echo $sql;
			//echo '<pre>';
			//print_r($allForwardedArr);exit;
			if(empty($allForwardedArr)){
				$forwarded_counter = 0;
			}else{
				$forwarded_counter = 1;
			}//else
			$total_forwarded += $forwarded_counter;
		}//foreach
		//echo $totalSyndicate.'<br>'.$total_forwarded;


		if($cur_user_group_id == '4'){
			$forwarded = ($totalSyndicate == $total_forwarded) ? 'true' : 'false';
		}
		
		//Find DS Impression mark Info of this term
		$sql = "select * from ".DB_PREFIX."term WHERE id = '".$term_id."'";
		$termInfoArry = $dbObj->selectDataObj($sql);
		$termInfo = $termInfoArry[0];
		$ds_impr_mark = $termInfo->ds_impr_mark;
		//Find the impression mark of this term has been forwarded by the DS
		if($cur_user_group_id == '4'){
			$sql = "select * from ".DB_PREFIX."impression_marking_lock WHERE term_id = '".$term_id."' AND status != '0'";
		}else if($cur_user_group_id == '5'){
			$sql = "select * from ".DB_PREFIX."impression_marking_lock WHERE term_id = '".$term_id."' AND syndicate_id = '".$syndicate_id."' AND status != '0'";
		}
		$imprMarkForwardeArr = $dbObj->selectDataObj($sql);
		
		//Find Student info  -- the student who are assigned in this same term//Syndicate
		
		if($cur_user_group_id == '5'){
			if($_POST['submit'] == 'Sort By Position'){
				if(!empty($imp_forwarded)){
					$sql = "SELECT mrk.student_id, (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)+impr.ds_impr_weight) as total from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr WHERE impr.student_id = mrk.student_id AND mrk.course_id = '".$course_id."' AND impr.course_id = '".$course_id."' AND impr.term_id = '".$term_id."' AND mrk.term_id = '".$term_id."' AND impr.syndicate_id = '".$syndicate_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.status = '1' AND mrk.ci_status = '1' AND impr.status = '1' group by mrk.student_id ORDER BY total desc";
				}else{
					$sql = "SELECT mrk.student_id, (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)) as total from ".DB_PREFIX."marking as mrk WHERE mrk.syndicate_id = '".$syndicate_id."' AND mrk.term_id = '".$term_id."' AND mrk.course_id = '".$course_id."' AND mrk.status = '1' AND mrk.ci_status = '1' group by mrk.student_id ORDER BY total desc";
				}
			}else{
				$sql = "SELECT mrk.student_id FROM ".DB_PREFIX."marking as mrk, ".DB_PREFIX."student as std WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.status = '1' AND mrk.student_id = std.id GROUP BY mrk.student_id ORDER BY std.student_id";
			}//else 
		}else if($cur_user_group_id == '4'){
			if($total_forwarded == $totalSyndicate){
				if($_POST['submit'] == 'Sort By Position'){
					
					if($syndicate_id == '0'){
						$sql = "SELECT mrk.student_id, (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)+impr.ds_impr_weight) as total from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND mrk.course_id = '".$course_id."' AND impr.term_id = '".$term_id."' AND mrk.term_id = '".$term_id."' AND mrk.status = '1' AND mrk.ci_status = '1' AND impr.status = '1' group by mrk.student_id ORDER BY total desc";
						
					}else{
						$sql = "SELECT mrk.student_id, (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)+impr.ds_impr_weight) as total from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND mrk.course_id = '".$course_id."' AND impr.term_id = '".$term_id."' AND mrk.term_id = '".$term_id."' AND impr.syndicate_id = '".$syndicate_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.status = '1' AND mrk.ci_status = '1' AND impr.status = '1' group by mrk.student_id ORDER BY total desc";
					}//else 
				}else if($_POST['submit'] == 'Sort By C/N'){
					if($syndicate_id == '0'){
						$sql = "SELECT mrk.student_id from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr, ".DB_PREFIX."student as std WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND impr.term_id = '".$term_id."' AND mrk.status = '1' AND impr.status = '1' AND mrk.student_id = std.id AND impr.student_id = std.id group by mrk.student_id ORDER BY std.student_id asc";
					}else{
						$sql = "SELECT mrk.student_id from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr, ".DB_PREFIX."student as std WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND impr.term_id = '".$term_id."' AND impr.syndicate_id = '".$syndicate_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.status = '1' AND impr.status = '1' AND mrk.student_id = std.id AND impr.student_id = std.id group by mrk.student_id ORDER BY std.student_id asc";
					}//else 
				}else{
					if($syndicate_id == '0'){
						$sql = "SELECT mrk.student_id from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr, ".DB_PREFIX."syndicate as syn WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND impr.term_id = '".$term_id."' AND mrk.status = '1' AND impr.status = '1' AND syn.id = mrk.syndicate_id AND syn.id = impr.syndicate_id group by mrk.student_id ORDER BY syn.name asc, mrk.student_id asc";
					}else{
						$sql = "SELECT mrk.student_id from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND impr.term_id = '".$term_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND impr.syndicate_id = '".$syndicate_id."' AND mrk.status = '1' AND impr.status = '1' group by mrk.student_id ORDER BY mrk.student_id asc";
					}//else 
				}//else
			}else{
				if($_POST['submit'] == 'Sort By Position'){
					if($syndicate_id == '0'){
						$sql = "SELECT mrk.student_id, (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)) as total from ".DB_PREFIX."marking as mrk WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.status = '1' AND mrk.ci_status = '1' group by mrk.student_id ORDER BY total desc";
					}else{
						$sql = "SELECT mrk.student_id, (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)) as total from ".DB_PREFIX."marking as mrk  WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.status = '1' AND mrk.ci_status = '1' group by mrk.student_id ORDER BY total desc";
					}//else 
				}else if($_POST['submit'] == 'Sort By C/N'){
					if($syndicate_id == '0'){
						$sql = "SELECT mrk.student_id from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."student as std WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.status = '1' AND mrk.student_id = std.id group by mrk.student_id ORDER BY std.student_id asc";
					}else{
						$sql = "SELECT mrk.student_id from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."student as std WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.status = '1' AND mrk.student_id = std.id group by mrk.student_id ORDER BY std.student_id asc";
					}//else 
				}else{
					if($syndicate_id == '0'){
						$sql = "SELECT mrk.student_id from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."syndicate as syn WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.status = '1' AND syn.id = mrk.syndicate_id group by mrk.student_id ORDER BY syn.name asc, mrk.student_id asc";
					}else{
						$sql = "SELECT mrk.student_id from ".DB_PREFIX."marking as mrk WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.status = '1' group by mrk.student_id ORDER BY mrk.student_id asc";
					}//else 
				}//else
			}//else
		}//else if
		

		$studentArr = $dbObj->selectDataObj($sql);
		$studentArrForArray = $dbObj->selectDataObj($sql);
		$totalStudent = sizeof($studentArr);
		
		if($_POST['submit'] == 'Sort By Position'){
			$sort = 'posn';
		}else if($_POST['submit'] == 'Sort By C/N'){
			$sort = 'cn';
		}else{
			$sort = 'general';
		}
		
		//This code find position of a student
		if($cur_user_group_id == '5'){
				if(!empty($imp_forwarded)){
					$sql = "SELECT distinct (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)+impr.ds_impr_weight) as total from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND mrk.course_id = '".$course_id."' AND impr.term_id = '".$term_id."' AND mrk.term_id = '".$term_id."' AND impr.syndicate_id = '".$syndicate_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.ci_status = '1' AND mrk.status = '1' group by mrk.student_id ORDER BY total desc";
				}else{
					$sql = "SELECT distinct (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)) as total from ".DB_PREFIX."marking as mrk WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.syndicate_id = '".$syndicate_id."' AND mrk.ci_status = '1' AND mrk.status = '1' group by mrk.student_id ORDER BY total desc";
				}
		}else if($cur_user_group_id == '4'){
			
			if($total_forwarded == $totalSyndicate){
				if($syndicate_id == '0' || $syndicate_id == ""){
					$add_syndicate = "";
				}else{
					$add_syndicate = " AND mrk.syndicate_id = '".$syndicate_id."' AND impr.syndicate_id = '".$syndicate_id."'";
				}
				$sql = "SELECT distinct (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)+impr.ds_impr_weight) as total from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND mrk.course_id = '".$course_id."' AND impr.term_id = '".$term_id."'  AND mrk.term_id = '".$term_id."' AND mrk.status = '1' AND mrk.ci_status = '1' ".$add_syndicate." group by mrk.student_id ORDER BY total desc"; //AND impr.status = '1'
			}else{
				if($syndicate_id == '0' || $syndicate_id == ""){
					$add_syndicate = "";
				}else{
					$add_syndicate = " AND mrk.syndicate_id = '".$syndicate_id."'";
				}
				$sql = "SELECT distinct (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)) as total from ".DB_PREFIX."marking as mrk WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.status = '1' AND mrk.ci_status = '1' ".$add_syndicate." group by mrk.student_id ORDER BY total desc"; //AND impr.status = '1'
			}
		}//else if
		//echo $sql;exit;
		$numberArray = $dbObj->selectDataObj($sql);
		
		
		
		/*********************************New Array generate***************************/
		
		$NewnumberArray = '';
		$totalexrsql = "select exr.id, exr.name,exr.join_course, exr.weight, exr.air_weight, exr.navy_weight, exr.mark from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where ett.term_id = ".$term_id." AND ett.exercise_id = exr.id order by exr.name asc";
		
		$totalExerciseArforCount = $dbObj->selectDataObj($totalexrsql);
		//echo '<pre>';
		//print_r($totalExerciseAr);
		
		foreach($studentArrForArray as $student){
		
				$total_exr_weight_new = 0;
				
				foreach($totalExerciseArforCount as $exercise){
									
					//Find student's marks of this term
					//$mark_sql = "select * from ".DB_PREFIX."marking WHERE term_id = '".$term_id."' AND course_id = '".$course_id."' AND student_id = '".$student->student_id."' AND exercise_id = '".$exercise->id."' AND status = '1' order by id desc";
					$mark_sql = "select * from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise->id."' AND student_id = '".$student->student_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' AND status = '1' AND ci_status = '1' order by id desc";
					
					$markArrResult = $dbObj->selectDataObj($mark_sql);
					
					$array_count_mark = $markArrResult[0];
					
					$exr_total_weight = $array_count_mark->ds_marking+$array_count_mark->si_mod_marking+$array_count_mark->ci_mod_marking;
					
					//Find Exercise Info
					$exrsql = "select * from ".DB_PREFIX."exercise WHERE id = '".$exercise->id."'";
					$count_exrArr = $dbObj->selectDataObj($exrsql);
					
					$exercise = $count_exrArr[0];
						$joinExercise = $exercise->join_course;
						if($joinExercise == 1 && $cur_user_wing_id == 2){
							$exercise->weight = $exercise->air_weight;
						}else if($joinExercise == 1 && $cur_user_wing_id == 3){
							$exercise->weight = $exercise->navy_weight;
						}else if($joinExercise == 1 && $cur_user_wing_id == 1){
							$exercise->weight = $exercise->weight;
						}else{
							$exercise->weight = $exercise->weight;
						}
					$exrWeight = $exercise->weight;
					
					
					$from_ds_weight = ($array_count_mark->ds_marking*$exercise->weight)/100;
					$converted_exr_weight = $from_ds_weight+$array_count_mark->si_student_weight+$array_count_mark->ci_student_weight;
					$total_exr_weight_new += $converted_exr_weight;
					
					if(($cur_user_group_id == 4) && ($total_forwarded == $totalSyndicate)){
						//Find term impression mark of Student
						$sql = "select * from ".DB_PREFIX."impression_marking WHERE student_id = '".$student->student_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' AND status = '1'";
						$stuImprMarkArry = $dbObj->selectDataObj($sql);
						$stuImprMark = $stuImprMarkArry[0];
						
						$stu_ds_impression_mark = $stuImprMark->ds_impr_marking;
						$converted_ds_impr_mark = ($stu_ds_impression_mark*$ds_impr_mark)/100;
					}else if(($cur_user_group_id == 5) && ($forwarded == 'true')){
						//Find term impression mark of Student
						$sql = "select * from ".DB_PREFIX."impression_marking WHERE student_id = '".$student->student_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."'";
						$stuImprMarkArry = $dbObj->selectDataObj($sql);
						$stuImprMark = $stuImprMarkArry[0];
						$stu_ds_impression_mark = $stuImprMark->ds_impr_marking;
						$converted_ds_impr_mark = ($stu_ds_impression_mark*$ds_impr_mark)/100;

					}
					
					
					
				}
				$total_number = $total_exr_weight_new+$converted_ds_impr_mark;
				
				$NewnumberArray[] = $total_number;
		
		}
		rsort($NewnumberArray);
		
		
		if(!empty($NewnumberArray)){
			$i = 0;
			foreach($NewnumberArray as $item){
				$numberArray[$i]->total = $item;
				$numberArray[$i]->position = $i+1;
				$i++; 
			}
		}
		
		
		/*********************************New Array generate***************************/
		
		/*
		if(!empty($numberArray)){
			$i = 0;
			foreach($numberArray as $item){
				$numberArray[$i]->position = $i+1;
				$i++; 
			}//foreach
		}//if
		*/
		
		if($syndicate_id == 0 || empty($syndicate_id)){
			$colspan = ($totalExercise*2)+10;
			$hrcolspan = ($totalExercise*2)+14;
		}else{
			$colspan = ($totalExercise*2)+9;
			$hrcolspan = ($totalExercise*2)+13;
		}
		
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
				<h1><?php echo TERM_RESULTS; ?></h1>
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
		if((($cur_user_group_id == 5) && (empty($syndicate_id))) || (($cur_user_group_id == 4) && (empty($courseArray)))){ ?>
			<table cellpadding="0" cellspacing="0" border="0" width="100%" class="module_content">
				<tr>
					<td height="30" colspan="2">
						<?php echo ERROR_MSG; ?>
					</td>
				</tr>
			</table>
	<?php }else{ ?>
			<div id="term_result_input">
			<form action="term_rep.php" method="post" name="term_rep" id="term_rep" onsubmit="return validateTermResult();">
				<table cellpadding="0" cellspacing="0" border="0" width="250" class="module_content">
					<tr>
						<td height="30" width="30%">
							<?php echo COURSE; ?>:
						</td>
						<td width="70%">
							<strong>
							<?php 
								if($cur_user_group_id == '5'){
								 	$course_name = getNameById('course', $course_id);
									echo $course_name->name;
									echo '<input type="hidden" name="course_id" id="course_id" value="'.$course_id.'" />';
								}else if($cur_user_group_id == '4'){
									echo $courseList_opt;
									echo '<span class="required_field"> *</span>';
								}
							?>
							
							</strong>
						</td>
					</tr>
					<tr>
						<td height="30" width="30%">
							<?php echo TERM; ?>:
						</td>
						<td width="70%">
						<div id="loaderContainer"></div>
						<div id="term_display">
							<strong>
							<?php 	
								if($cur_user_group_id == '5'){
									$term = getNameById('term', $term_id);
									echo $term->name;
									echo '<input type="hidden" name="term_id" id="term_id" value="'.$term_id.'" />';
								}else if($cur_user_group_id == '4'){
									echo $termList_opt;
									echo '<span class="required_field"> *</span>';
								}
							?>
							</strong>
						</div>
							
						</td>
					</tr>
					<?php if($cur_user_group_id == '5'){ ?>
					<tr>
						<td height="30" width="30%">
							<?php echo SYNDICATE; ?>:
						</td>
						<td width="70%">
							<strong>
							<?php 	$syndicate = getNameById('syndicate', $syndicate_id);
									echo $syndicate->name; ?>
							</strong>
						</td>
					</tr>
					<?php } 
						if($cur_user_group_id == '4'){
					?>
					<tr>
						<td height="30" width="30%">
							<?php echo SYNDICATE; ?>:
						</td>
						<td width="70%">
							<div id="syndicate_display">
								<?php echo $synList_opt; ?>
							</div>
						</td>
					</tr>
					<tr height="50">
						<td colspan="2">
							<input type="submit" name="submit" id="submit" value="Show Reports" />
						</td>
					</tr>
					<?php } ?>
				</table>
				<input type="hidden" name="action" value="view" />
			</form>
			</div>
		<?php if ($cur_user_group_id == '5' || ($cur_user_group_id == '4' && isset($_POST['submit']))){ ?>
		<div id="term_reports" style="width:700px; overflow-x:scroll;" >
			<table cellpadding="0" cellspacing="0" border="0" class="module_content" style="padding-top:25px;width:700px;">
				<tr>
					<td>
					<?php if(!empty($studentArr)){ ?>
						<table cellpadding="0" cellspacing="0" border="0">
							<tr height="30">
								<td width="80%">
								<?php 
									if($cur_user_group_id == 4){ ?>
										<a href="download_reports.php"><img src="images/xls.ico" title="Download Report in MS-Excel Format" /></a>
										<!--<a href="download.php?action=getpdf"><img src="images/pdf.png" title="Download Report in PDF Format" height="16" width="16" /></a>-->
										<?php }
										if($syndicate_id == 0 || $syndicate_id == ''){
													$syndt = '';
												}else{
													$syndt = '&syndicate_id='.$syndicate_id;
												} ?>
										<a class="print_button" href="term_rep_view.php?course_id=<?php echo $course_id;?>&term_id=<?php echo $term_id; echo $syndt;?>&sort=<?php echo $sort; ?>&forward=<?php echo $forwarded.'&colspan='.$colspan; ?>" target="_blank"><img src="images/print.png" title="Print" height="16" width="16" /></a>
										<a id="toggle_fullscreen" href="#"><img src="images/fullscreen.png" title="Full Screen" height="16" width="16" /></a>
								</td>
								<td align="right" width="10%">
								<form action="term_rep.php" method="post" name="term_rep" id="term_rep">
									<input type="submit" name="submit" id="submit" value="Sort By C/N" />
									<input type="hidden" name="course_id" id="course_id" value="<?php echo $course_id; ?>" />
									<input type="hidden" name="term_id" id="term_id" value="<?php echo $term_id; ?>" />
									<input type="hidden" name="syndicate_id" id="syndicate_id" value="<?php echo $syndicate_id; ?>" />
									<input type="hidden" name="action" value="view" />
								</form>
								</td>
								<td align="right" width="10%">
								<form action="term_rep.php" method="post" name="term_rep" id="term_rep">
									<input type="submit" name="submit" id="submit" value="Sort By Position" />
									<input type="hidden" name="course_id" id="course_id" value="<?php echo $course_id; ?>" />
									<input type="hidden" name="term_id" id="term_id" value="<?php echo $term_id; ?>" />
									<input type="hidden" name="syndicate_id" id="syndicate_id" value="<?php echo $syndicate_id; ?>" />
									<input type="hidden" name="action" value="view" />
								</form>
								</td>
							</tr>
						</table>
					<?php } ;?>
						<table cellpadding="0" cellspacing="0" border="0" class="datagrid">
							<tr class="head">
								<td align="center" width="5%" rowspan="2">
									<strong><?php echo SER_NO; ?></strong>
								</td>
								<td align="center" width="10%" rowspan="2">
									<strong><?php echo CN; ?></strong>
								</td>
								<td align="center" width="20%" rowspan="2">
									<strong><?php echo BANO; ?></strong>
								</td>
								<td align="center" width="20%" rowspan="2">
									<strong><?php echo RANK; ?></strong>
								</td>
								<td align="center" width="20%" rowspan="2">
									<strong><?php echo STUDENT_NAME; ?></strong>
								</td>
								<?php
								$crsNam = getNameById("course", $course_id);
								$crsName = $crsNam->name;
								$termNam = getNameById("term", $term_id);
								$termName = $termNam->name;
								
								$downloadTitle[0] = CONFIDENTIAL."\n";
								$downloadTitle[1] = COURSE.': '.$crsName."\n";
								$downloadTitle[2] = TERM.': '.$termName."\n";
								
								$str_pad = '
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr height="50">
										<td align="center">'.CONFIDENTIAL.'</td>
									</tr>
									<tr>
										<td align="center">'.$crsName.'</td>
									</tr>
									<tr>
										<td align="center">'.$termName.'</td>
									</tr>
								</table>
								<table border="1" cellpadding="0" width="700" cellspacing="0">
									<tr>
										<td align="center" width="5%">'.SER_NO.'</td>
										<td align="center" width="8%">'.CN.'</td>
										<td align="center" width="5%">'.BANO.'</td>
										<td align="center" width="5%">'.RANK.'</td>
										<td align="center" width="10%">'.STUDENT_NAME.'</td>';
								if($cur_user_group_id == '4' && $syndicate_id == 0){ ?>
								<td align="center" width="25%" rowspan="2">
									<strong><?php echo SYNDICATE; ?></strong>
								</td>
								<?php 
									$str_pad .= '
										<td align="center" width="5%">'.SYNDICATE.'</td>';
								}//if cur_user_group_id == 4 --->> syndicate will be viewed
										foreach($exrArr as $exercise){ ?>
								<td align="center" width="20%" colspan="2">
									<strong><?php echo $exercise->name; ?></strong>
								</td>
								<?php 
									$str_pad .= '
										<td align="center" width="8%">'.$exercise->name.' ('.PERCENT.')</td>
										<td align="center" width="8%">'.$exercise->name.' ('.WT.')</td>';
										}//foreach ?>
								<td align="center" width="10%">
									<strong><?php echo EXAM_WT; ?></strong>
								</td>
								<td align="center" width="20%" rowspan="2">
									<strong><?php echo EX_TOTAL_PERCENT; ?></strong>
								</td>
								<td align="center" width="10%" colspan="2">
									<strong><?php echo DS_IMPRES_MARK; ?></strong>
								</td>
								<td align="center" width="10%">
									<strong><?php echo TOTAL_WT; ?></strong>
								</td>
								<td align="center" width="10%" rowspan="2">
									<strong><?php echo TOTAL; ?></strong>
								</td>
								<td align="center" width="10%" rowspan="2">
									<strong><?php echo GRADE; ?></strong>
								</td>
								<td align="center" width="10%" rowspan="2">
									<strong><?php echo POSITION; ?></strong>
								</td>
								<td align="center" width="20%" rowspan="2">
									<strong><?php echo STUDENT_NAME; ?></strong>
								</td>
								<td align="center" width="20%" rowspan="2">
									<strong><?php echo BANO; ?></strong>
								</td>
								<td align="center" width="20%" rowspan="2">
									<strong><?php echo RANK; ?></strong>
								</td>
								
							</tr>
							<?php 
								$str_pad .= '
									<td align="center" width="5%">'.EXAM_WT.'</td>
									<td align="center" width="8%">'.EX_TOTAL_PERCENT.'</td>
									<td align="center" width="8%">'.DS_IMP.' ('.PERCENT.')</td>
									<td align="center" width="8%">'.DS_IMPRES_MARK.'</td>
									<td align="center" width="5%">'.TOTAL_WT.'</td>
									<td align="center" width="5%">'.TOTAL.'</td>
									<td align="center" width="5%">'.GRADE.'</td>
									<td align="center" width="5%">'.POSITION.'</td>
									<td align="center" width="5%">'.STUDENT_NAME.'</td>
									<td align="center" width="5%">'.BANO.'</td>
									<td align="center" width="5%">'.RANK.'</td>
								</tr>';
								
							?>
							<tr class="head">
								<?php 
								$sub_total = 0;
								$exam_total_weight = 0;
								foreach($exrArr as $exercise){ ?>
								<td align="center" width="20%">
									<strong><?php echo PERCENT; ?></strong>
								</td>
								<td align="center" width="20%">
									<?php
									$joinExercise = $exercise->join_course;
									if($joinExercise == 1 && $cur_user_wing_id == 2){
										$exercise->weight = $exercise->air_weight;
									}else if($joinExercise == 1 && $cur_user_wing_id == 3){
										$exercise->weight = $exercise->navy_weight;
									}else if($joinExercise == 1 && $cur_user_wing_id == 1){
										$exercise->weight = $exercise->weight;
									}else{
										$exercise->weight = $exercise->weight;
									}
									
									?>
									<strong><?php echo WT.'<br />'.$exercise->weight; ?></strong>
								</td>
								<?php 
									//echo '<pre>';
									//print_r($exercise);
									$sub_total += $exercise->weight;
									$exam_total_weight += $exercise->weight;
									if($cur_user_group_id == '4'){
										$total = $sub_total+$ds_impr_mark;
									}else{
										$total = $sub_total+$ds_impr_mark;
									}
									
									
								} ?>
								<td align="center" width="20%">
									<strong><?php echo $exam_total_weight; ?></strong>
								</td>
								<td align="center" width="20%">
									<strong><?php echo PERCENT; ?></strong>
								</td>
								<td align="center" width="20%">
									<strong><?php echo WT.' ('.$ds_impr_mark.')'; ?></strong>
								</td>
								<td align="center" width="20%">
									<strong><?php echo $total; ?></strong>
								</td>
							</tr>
							<?php 
							//For downloading Reports as XLS format
							//if group_id == 4 --->>> Only SI can download the reprot
							if($cur_user_group_id == '4'){
								$_SESSION['report_type'] = 'term';
								$arr[0]['sl'] = 'Ser No';
								$arr[0]['student_id'] = 'C/N';
								$arr[0]['bano'] = 'BA NO';
								$arr[0]['rank_id'] = 'Rank';
								$arr[0]['student_name'] = 'Student Name';
								if($cur_user_group_id == '4' && $syndicate_id == 0){
									$arr[0]['syndicate_name'] = 'Syndicate';
								}
								foreach($exrArr as $exercise){
									$arr[0][$exercise->id.'_'.$exercise->name] = $exercise->name;		//$exercise->id
									$arr[0][$exercise->id.'_'.$exercise->weight] = 'Wt ('.$exercise->weight.')';		
								}
								$arr[0]['total_exam_weight'] = 'Exam Wt';
								$arr[0]['total_exam_percent'] = 'Exam Total (%)';
								$arr[0]['ds_impr_percent'] = 'DS Imp (%)';
								$arr[0]['ds_impr_weight'] = 'DS Imp Wt';
								$arr[0]['total_weight'] = 'Total Wt';
								$arr[0]['total_weight'] = 'Total (%)';
								$arr[0]['grade'] = 'Grade';
								$arr[0]['position'] = 'Posn';
								$arr[0]['student_name2'] = 'Student Name';
								$arr[0]['bano2'] = 'BA NO';
								$arr[0]['rank_id2'] = 'Rank';
							}//if	
							
							if(!empty($studentArr)){
								$rownum = 0;
								$sl = 0;
								$totals_number = 0;
								foreach($studentArr as $student){
									$sl = $rownum + 1;
									$class = (($rownum%2)==0) ? ' class="even"' : ' class="odd"';
							
									//Find Student Info
									$sql = "select * from ".DB_PREFIX."student WHERE id = '".$student->student_id."'";
									$stuInfoArr = $dbObj->selectDataObj($sql);
									$stuInfo = $stuInfoArr[0];
									$bano = $stuInfo->ba_no;
									$student_name = $stuInfo->official_name;
									$rnkName = getNameById('rank', $stuInfo->rank_id);
									$rnkName = $rnkName->short_name;
									
									//Find syndicate of student
									$sql = "select syndicate_id from ".DB_PREFIX."student_to_syndicate WHERE student_id = '".$student->student_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' ";
									$synArr = $dbObj->selectDataObj($sql);
									$syndicate = $synArr[0];
									$syndicateName = getNameById('syndicate', $syndicate->syndicate_id);
								
							?>
							<tr <?php echo $class; ?>>
								<td align="center" width="10%">
									<?php echo $sl;
										$arr[$sl]['sl'] = $sl;
									?>
								</td>
								<td align="center" width="20%">
									<?php echo $stuInfo->student_id;
										$arr[$sl]['student_id'] = $stuInfo->student_id;
									?>
								</td>
								<td align="center" width="20%">
									<?php echo $bano;
										$arr[$sl]['bano'] = $bano;
									?>
								</td>
								<td align="center" width="20%">
									<?php echo $rnkName;
										$arr[$sl]['rank_id'] = $rnkName;
									?>
								</td>
								<td>			
									<?php echo $student_name; 
									$arr[$sl]['student_name'] = $student_name;
									?>
								</td>
								<?php 
								
								$str_pad .= 
								'<tr>
									<td align="center">'.$sl.'</td>
									<td align="center">'.$stuInfo->student_id.'</td>
									<td align="center">'.$bano.'</td>
									<td align="center">'.$rnkName.'</td>
									<td>'.$student_name.'</td>';

									if($cur_user_group_id == '4' && $syndicate_id == 0){ ?>
								<td align="center" width="20%">
									<?php echo $syndicateName->name; 
										$arr[$sl]['syndicate_name'] = $syndicateName->name; 
									?>
								</td>
								<?php 
									$str_pad .= '
									<td align="center">'.$syndicateName->name.'</td>';
									}//if cur_user_group_id == '4' ---> show Syndicate
								
								$term_exam_weight = 0;
								foreach($exrArr as $exercise){
									//Find info of exercise
									$sql = "select * from ".DB_PREFIX."exercise WHERE id = '".$exercise->id."'";
									$exary = $dbObj->selectDataObj($sql);
									$exer = $exary[0];
									$joinExercise = $exer->join_course;
									if($joinExercise == 1 && $cur_user_wing_id == 2){
										$weight = $exer->air_weight;
										$exercise->weight = $exercise->air_weight;
									}else if($joinExercise == 1 && $cur_user_wing_id == 3){
										$weight = $exer->navy_weight;
										$exercise->weight = $exercise->navy_weight;
									}else if($joinExercise == 1 && $cur_user_wing_id == 1){
										$weight = $exer->weight;
										$exercise->weight = $exercise->weight;
									}else{
										$weight = $exer->weight;
										$exercise->weight = $exercise->weight;
									}
									
									
									//Find marking of students of different exams
									//if($cur_user_group_id == 5){
										$sql = "select * from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise->id."' AND student_id = '".$student->student_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' AND status = '1' AND ci_status = '1'";
									/*}else{
										$sql = "select * from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise->id."' AND student_id = '".$student->student_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' AND status = '1'";
									}*/

									$markArr = $dbObj->selectDataObj($sql);
									$mark = $markArr[0];
									
									$s_mark = $mark->ds_marking+$mark->si_mod_marking+$mark->ci_mod_marking;
									//$converted_s_mark = $mark->ds_student_weight+$mark->si_student_weight+$mark->ci_student_weight;
									$from_ds_weight = ($mark->ds_marking*$exercise->weight)/100;
									$converted_s_mark = $from_ds_weight+$mark->si_student_weight+$mark->ci_student_weight;
									$term_exam_weight += $converted_s_mark;
									$term_exam_percent = ($term_exam_weight*100)/$exam_total_weight;
									//echo 'forwarded = '.$total_forwarded.'<br/>';
									//echo 'total = '.$totalSyndicate;exit;
									if(($cur_user_group_id == 4) && ($total_forwarded == $totalSyndicate)){
										//Find term impression mark of Student
										$sql = "select * from ".DB_PREFIX."impression_marking WHERE student_id = '".$student->student_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' AND status = '1'";
										$stuImprMarkArry = $dbObj->selectDataObj($sql);
										$stuImprMark = $stuImprMarkArry[0];
										
										$stu_ds_impression_mark = $stuImprMark->ds_impr_marking;
										$converted_ds_impr_mark = ($stu_ds_impression_mark*$ds_impr_mark)/100;
									}else if(($cur_user_group_id == 5) && ($forwarded == 'true')){
										//Find term impression mark of Student
										$sql = "select * from ".DB_PREFIX."impression_marking WHERE student_id = '".$student->student_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."'";
										$stuImprMarkArry = $dbObj->selectDataObj($sql);
										$stuImprMark = $stuImprMarkArry[0];
										$stu_ds_impression_mark = $stuImprMark->ds_impr_marking;
										$converted_ds_impr_mark = ($stu_ds_impression_mark*$ds_impr_mark)/100;
	
									}
										
									$total_number = $term_exam_weight+$converted_ds_impr_mark;
									$total_percent = ($total_number*100)/$total;
								?>
								<td align="right">
									<?php echo view_number_two($s_mark); 
										$arr[$sl][$exercise->id.'_'.$exercise->name] = view_number_two($s_mark);
									?>
								</td>
								<td align="right">
									<?php 
									
									echo view_number_two($converted_s_mark);
										$arr[$sl][$exercise->id.'_'.$exercise->weight] = view_number_two($converted_s_mark);
									?>
								</td>
								<?php 
								$str_pad .= '
									<td align="right">'.view_number_two($s_mark).'</td>
									<td align="right">'.view_number_two($converted_s_mark).'</td>';
										}//foreach exrArr 
										?>
								<td align="right">
									<?php echo view_number_two($term_exam_weight);
										$arr[$sl]['total_exam_weight'] = view_number_two($term_exam_weight);
									?>
								</td>
								<td align="right">
									<?php echo view_number_two($term_exam_percent);
										$arr[$sl]['total_exam_percent'] = view_number_two($term_exam_percent);
									?> 
								</td>
								<td align="right">
									<?php 
										echo view_number_two($stu_ds_impression_mark);
										$arr[$sl]['ds_impr_percent'] = view_number_two($stu_ds_impression_mark);
									?> 
								</td>
								<td align="right">
									<?php echo view_number_two($converted_ds_impr_mark);
										$arr[$sl]['ds_impr_weight'] = view_number_two($converted_ds_impr_mark);
									?> 
								</td>
								<td align="right">
									<?php echo view_number_two($total_number);
										$arr[$sl]['total_weight'] = view_number_two($total_number);
									?> 
								</td>
								<td align="right">
									<?php echo view_number_two($total_percent);
										$arr[$sl]['total_weight'] = view_number_two($total_percent);
										$totals_number += view_number_two($total_percent);
									?> 
								</td>
								<td align="left" style="padding-left:10px;">
									<?php echo findGrade($total_percent);
										$arr[$sl]['grade'] = findGrade($total_percent);;									
									?>
								</td>
								<td align="center">
									<?php echo findPosition($numberArray, $total_number);
										$arr[$sl]['position'] = findPosition($numberArray, $total_number);
									?>
								</td>
								<td>			
									<?php echo $student_name;
									$arr[$sl]['student_name2'] = $student_name;
									?>
								</td>
								<td align="center" width="20%">
									<?php echo $bano;
									$arr[$sl]['bano2'] = $bano;
									?>
								</td>
								<td align="center" width="20%">
									<?php echo $rnkName;
									$arr[$sl]['rank_id2'] = $rnkName;
									?>
								</td>
							</tr>
							<?php	
								$str_pad .= '
									<td align="right">'.view_number_two($term_exam_weight).'</td>
									<td align="right">'.view_number_two($term_exam_percent).'</td>
									<td align="right">'.view_number_two($stu_ds_impression_mark).'</td>
									<td align="right">'.view_number_two($converted_ds_impr_mark).'</td>
									<td align="right">'.view_number_two($total_number).'</td>
									<td align="right">'.view_number_two($total_percent).'</td>
									<td align="center">'.findGrade($total_percent).'</td>
									<td align="center">'.findPosition($numberArray, $total_number).'</td>
									<td>'.$student_name.'</td>
									<td align="center">'.$bano.'</td>
									<td align="center">'.$rnkName.'</td>
								</tr>';
								$rownum++;
							}//foreach
								$avg_marks = $totals_number/$totalStudent;
						?>
							<tr>
								<td colspan="<?php echo $hrcolspan; ?>+"><hr /></td>
							</tr>
							<tr>
								<td align="right" colspan="<?php echo $colspan;?>" style="padding-right:15px;">
									<strong><?php echo AVG_MARKS; ?></strong>
								</td>
								<td align="left" colspan="3">
									<strong><?php echo view_number_two($avg_marks).'&nbsp;%'; ?></strong>
								</td>
							</tr>
							<tr>
								<td colspan="<?php echo $hrcolspan; ?>+"><hr /></td>
							</tr>
						<?php 
							$str_pad .= '
									<tr>
										<td height="30" colspan="'.$colspan.'">
											'.AVG_MARKS.'
										</td>
										<td colspan="3">
											'.view_number_two($avg_marks).'
										</td>
									</tr>
								</table>
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td height="30">
											'.GEN_BY.$cur_user_rank.' '.$cur_user_off_nam.ON. date('d M Y').AT.date('h:m:i a').'
										</td>
									</tr>
									<tr height="50">
										<td align="center">'.CONFIDENTIAL.'</td>
									</tr>
								</table>';
							
							}else{ 	//if empty StudentArr ?>
								<tr>
									<td colspan="<?php echo 10+($totalExercise*2); ?>" height="30"><?php if($cur_user_group_id == '4'){
																								echo EMPTY_DATA;
																								}else if($cur_user_group_id == '5'){
																									if($total_forwarded != $totalExercise){
																										echo NOT_FORWARDED_MESSAGE;
																									}else{
																										echo IMPRESSION_MARK_NOT_FORWARDED;
																									}//else
																								}//else if ?>
								</td>
							</tr>
						<?php } ?>
						</table>
					</td>
				</tr>
			</table>
		</div>	
	<?php
			}//if posted course_id, term_id
		}//if not empty activeCourse or Term
		//For assinging Term Report in XLS Format 
		$_SESSION['term'] = '';
		$_SESSION['term'][0] = $downloadTitle; 
		$_SESSION['term'][1] = $arr;
		$_SESSION['term'][2] = view_number_two($avg_marks);
		
		//For assinging Term Report in PDF Format 
		$content = $str_pad;
		require_once("html2fpdf/html2fpdf.php");		
		
		//set path and file name of desired PDF	
		$pdf_dir_name = 'report/';	
		$report_id = $cur_user_id.'_'.date('ymdhis');	
		$pdf_file_name = $report_id.'.pdf';		
		$pdf_invoice_name = $pdf_dir_name.$pdf_file_name;
		
		//Create the object and Save PDF file
		$pdf=new HTML2FPDF();
		$pdf->SetFont('Helvetica','',6);
		$pdf->AddPage();
		$pdf->WriteHTML($content);
		/*echo '<pre>';
		print_r(htmlspecialchars($content));
		exit;*/
		$pdf->Output($pdf_invoice_name);
		$_SESSION['pdf'] = '';
		$_SESSION['pdf'] = $pdf_invoice_name;
	}//view ends here
	?>	
</div>
			
<?php require_once("includes/footer.php"); ?>
