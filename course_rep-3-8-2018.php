<?php
require_once("includes/header.php");
//check for loggedin
$usr = $user->getUser();

if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Course Progressive Results';
$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$cur_user_off_nam = $usr[0]->official_name;
$cur_user_rank = getNameById('rank',$usr[0]->rank_id);
$cur_user_rank = $cur_user_rank->name;
$action = $_REQUEST['action'];

//Chek if this user is valid for this file
if($cur_user_group_id != '4'){
	header("Location: dashboard.php");	
}

switch($action){
	case 'view':	
	default:
		
		if(isset($_POST['submit'])){
			$course_id = $_REQUEST['course_id'];
			$term_id = $_REQUEST['term_id'];
		}
		if($term_id != '0' || !empty($term_id)){
			$term_con = '&term_id='.$term_id;
		}else{
			$term_con = '';
		}
		
		if($_POST['submit'] == 'Sort By Position'){
			$sort = 'posn';
		}else{
			$sort = 'general';
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
			$courseList_opt = formSelectElement($courseId, $course_id, 'course_id', 'onchange = processFunction("progressive_term_list")');
			
			//Build Term List Array	
			$query = "select id, name from ".DB_PREFIX."term WHERE course_id = '".$course_id."' order by order_id asc";
			$termArray = $dbObj->selectDataObj($query);
			$termId = array();
			$termId[0] = SELECT_TERM_OPT;
			if(!empty($termArray)){			
				foreach($termArray as $item){
					$termId[$item->id] = $item->name;
				}	
			}			
			$termList_opt = formSelectElement($termId, $term_id, 'term_id');
			
			if($term_id == '0' || empty($term_id)){
				if($_POST['submit'] == 'Sort By Position'){
					$sql = "select * from ".DB_PREFIX."si_impression_marking WHERE course_id = '".$course_id."' ORDER BY total_weight desc";
				}else{
					$sql = "select * from ".DB_PREFIX."si_impression_marking WHERE course_id = '".$course_id."' ORDER BY student_id asc";
				}
				$studentArr = $dbObj->selectDataObj($sql);
				
				$sql = "select * from ".DB_PREFIX."term WHERE course_id = '".$course_id."' ORDER BY order_id";
				$termArr = $dbObj->selectDataObj($sql);
				$total_term = sizeof($termArr);
				
				$sql = "select * from ".DB_PREFIX."course WHERE id = '".$course_id."'";
				$courseArr = $dbObj->selectDataObj($sql);
				$course = $courseArr[0];
				$si_impr_mark = $course->si_impr_mark;
				//echo 'test1';
				//exit;
			}else{
				//Find Info of this term
				$course_id = $_POST['course_id'];
				$term_id = $_POST['term_id'];

				$sql = "SELECT * FROM ".DB_PREFIX."term where id = '".$term_id."'";
				$termInfoArr = $dbObj->selectDataObj($sql);
				$termInfo = $termInfoArr[0];
				$order = $termInfo->order_id;
			
				$sql = "SELECT * FROM ".DB_PREFIX."term where course_id = '".$course_id."' AND order_id <= '".$order."' ORDER BY order_id";
				$termArr = $dbObj->selectDataObj($sql);
				
				//if($_POST['submit'] == 'Sort By Position'){
					//$sql = " SELECT distinct mrk.student_id, (SUM(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)+impr.ds_impr_weight) as total from ".DB_PREFIX."term as trm, ".DB_PREFIX."impression_marking as impr, ".DB_PREFIX."marking as mrk WHERE mrk.term_id = trm.id AND trm.order_id <= '".$order."' AND mrk.course_id = '".$course_id."' AND mrk.term_id <= trm.id  AND impr.course_id = '".$course_id."' AND impr.term_id <= trm.id AND impr.term_id  <= trm.id AND mrk.student_id = impr.student_id GROUP BY mrk.student_id ORDER BY total desc";
					//$studentArr = $dbObj->selectDataObj($sql);
					//$studentArr2 = $studentArr;
				//}else{
					//$sql = "select distinct m.student_id from ".DB_PREFIX."marking as m, ".DB_PREFIX."student as s WHERE m.course_id = '".$course_id."' AND s.id = m.student_id ORDER BY s.student_id";
					//$studentArr = $dbObj->selectDataObj($sql);
				//}
				
				$sql = "select distinct m.student_id from ".DB_PREFIX."marking as m, ".DB_PREFIX."student as s WHERE m.course_id = '".$course_id."' AND s.id = m.student_id ORDER BY s.student_id";
				$studentArr2 = $studentArr = $dbObj->selectDataObj($sql);				
			}//else
		}//if not empty courseArray
		
		//This code find position of a student in a course	
		$sql = " SELECT distinct total_weight as total from ".DB_PREFIX."si_impression_marking WHERE course_id = '".$course_id."' ORDER BY total desc";
		$numberArrayForCourse = $dbObj->selectDataObj($sql);
		
		if(!empty($numberArrayForCourse)){
			$i = 0;
			foreach($numberArrayForCourse as $item){
				$numberArrayForCourse[$i]->position = $i+1;
				$i++; 
			}//foreach
		}//if
		
		if($term_id != 0){
			//Find out the order_id of this term
			$order_arr = getNameById('term', $term_id);
			$order_id = $order_arr->order_id;
			
			//This code find position of a student in aggregate of term	
			$sql = " SELECT distinct (SUM(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)) as total, mrk.student_id from ".DB_PREFIX."marking as mrk WHERE mrk.course_id = '".$course_id."' AND (";			
			if(!empty($termArr)){
				foreach($termArr as $item){
					$sql .= " mrk.term_id = '".$item->id."' || ";
				}
				$sql = rtrim($sql, "|| ");
				$sql .= " )";
			}

			$sql .= "GROUP BY mrk.student_id ORDER BY mrk.student_id asc";
			$mark_arr = $dbObj->selectDataObj($sql);
			
			$sql = " SELECT distinct (SUM(impr.ds_impr_weight)) as total, impr.student_id from ".DB_PREFIX."impression_marking as impr WHERE impr.course_id = '".$course_id."' AND (";			
			if(!empty($termArr)){
				foreach($termArr as $item){
					$sql .= " impr.term_id = '".$item->id."' || ";
				}
				$sql = rtrim($sql, "|| ");
				$sql .= " )";
			}
			
			$sql .= "GROUP BY impr.student_id ORDER BY impr.student_id asc";
			$impr_arr = $dbObj->selectDataObj($sql);
			
			$numberArrayForTermAggregateTemp =array();
			$numberArrayForTermAggregateTemp2 =array();
			$std_with_num =array();
			
			$ind = 0; 
			foreach($mark_arr as $item){
				$numberArrayForTermAggregateTemp[$ind] = $item->total + $impr_arr[$ind]->total;
				$std_with_num[$item->student_id] = $item->total+ $impr_arr[$ind]->total;
				$ind++; 
			}//foreach
			
			
			arsort($std_with_num);
			rsort($numberArrayForTermAggregateTemp);
			
			$numberArrayForTermAggregate2 = array_unique($numberArrayForTermAggregateTemp);
			rsort($numberArrayForTermAggregate2);
			
			$i = 0; 
			foreach($numberArrayForTermAggregate2 as $item){
				$arr_obj = array();
				$arr_obj = (object)$arr_obj;
				$arr_obj->total =  $item;
				$arr_obj->position =  $i+1;
				$numberArrayForTermAggregate[$i] = $arr_obj;
				$i++; 
			}//foreach
			
			if($_POST['submit'] == 'Sort By Position'){
				$in = 0;
				foreach($std_with_num as $k => $v){
					$studentArr[$in]->student_id = $k;
					$in++;
				}
			}
		}//if
		
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
				<h1><?php echo COURSE_RESULTS; ?></h1>
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
		if(empty($courseArray)){ ?>
		<table cellpadding="0" cellspacing="0" border="0" width="100%" class="module_content">
			<tr>
				<td height="30" colspan="2">
					<?php echo ERROR_MSG; ?>
				</td>
			</tr>
		</table>
	<?php }else{ ?>
			
			<form action="course_rep.php" method="post" name="course_rep" id="course_rep" >
				<table cellpadding="0" cellspacing="0" border="0" width="250" class="module_content">
					<tr>
						<td height="30" width="30%">
							<?php echo COURSE; ?>:
						</td>
						<td width="70%">
							<?php echo $courseList_opt.'<span class="required_field"> *</span>'; ?>
						</td>
					</tr>
					<tr>
						<td height="30" width="30%">
							<?php echo TERM; ?>:
						</td>
						<td width="70%">
							<div id="loaderContainer"></div>
							<div id="term_display">
							<?php echo $termList_opt.'<span class="required_field"> *</span>';?>
							</div>
						</td>
					</tr>
					<tr height="50">
						<td colspan="2">
							<input type="submit" name="submit" id="submit" value="Show Reports" onclick="return validateCourseRep();" />
						</td>
					</tr>
				</table>
				<input type="hidden" name="action" value="view" />
			</form>
			
		<?php 
			}//if not empty courseArray
		if(isset($_POST['submit'])){ ?>
		<div style="width:700px; overflow-x:scroll;" >
			<table cellpadding="0" cellspacing="0" border="0" class="module_content" style="width:700px;">
				<tr>
					<td>
						<table cellpadding="0" cellspacing="0" border="0">
							<tr height="30">
								<td width="99%">
									<a href="download_reports.php"><img src="images/xls.ico" title="Download Report in MS-Excel Format" /></a>
									<a href="download.php?action=getpdf"><img src="images/pdf.png" title="Download Report in PDF Format" height="16" width="16" /></a>
									<a class="print_button" href="course_rep_view.php?course_id=<?php echo $course_id.$term_con;?>&sort=<?php echo $sort; ?>" target="_blank"><img src="images/print.png" title="Print" height="16" width="16" /></a>
								</td>
								<td align="right" width="1%">
								<form action="course_rep.php" method="post" name="course_rep" id="course_rep">
									<input type="submit" name="submit" id="submit" value="Sort By C/N" />
									<input type="hidden" name="course_id" id="course_id" value="<?php echo $course_id; ?>" />
									<input type="hidden" name="term_id" id="term_id" value="<?php echo $term_id; ?>" />
									<input type="hidden" name="action" value="view" />
								</form>
								</td>
								<td align="right" width="1%">
								<form action="course_rep.php" method="post" name="course_rep" id="course_rep">
									<input type="submit" name="submit" id="submit" value="Sort By Position" />
									<input type="hidden" name="course_id" id="course_id" value="<?php echo $course_id; ?>" />
									<input type="hidden" name="term_id" id="term_id" value="<?php echo $term_id; ?>" />
									<input type="hidden" name="action" value="view" />
								</form>
								</td>
							</tr>
						</table>
						<table cellpadding="0" cellspacing="0" border="0" class="datagrid">

							<tr class="head">
								<td align="center" width="5%" rowspan="3">
									<strong><?php echo SER_NO; ?></strong>
								</td>
								<td align="center" width="10%" rowspan="3">
									<strong><?php echo CN; ?></strong>
								</td>
								<td align="center" width="20%" rowspan="3">
									<strong><?php echo RANK; ?></strong>
								</td>
								<td align="center" width="20%" rowspan="3">
									<strong><?php echo STUDENT_NAME; ?></strong>
								</td>
								<?php 
								$crsNam = getNameById("course", $course_id);
								$crsName = $crsNam->name;
								
								$downloadTitle[0] = CONFIDENTIAL."\n";
								$downloadTitle[1] = COURSE.' '.$crsName."\n";
								
								$str_pad = '
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr height="50">
										<td align="center">'.CONFIDENTIAL.'</td>
									</tr>
									<tr>
										<td align="center">'.$crsName.'</td>
									</tr>
								</table>
									<table cellpadding="0" cellspacing="0" border="1" width="700">
										<tr>
											<td align="center" width="5%">'.SER_NO.'</td>
											<td align="center" width="8%">'.CN.'</td>
											<td align="center" width="5%">'.RANK.'</td>
											<td align="center" width="10%">'.STUDENT_NAME.'</td>';
								$total_term_weight = 0;
								foreach($termArr as $term){
									$sql = "select exr.weight from ".DB_PREFIX."exercise as exr,  ".DB_PREFIX."exercise_to_term as ett WHERE ett.exercise_id = exr.id AND ett.term_id = '".$term->id."'";
									$TexerciseArr = $dbObj->selectDataObj($sql);
									$exercise_weight = 0;
										foreach($TexerciseArr as $Texercise){
											$exercise_weight += $Texercise->weight;
										}
									$ds_impr_mark = $term->ds_impr_mark;
									$term_weight = $exercise_weight+$ds_impr_mark;
									$total_term_weight += $term_weight;
								 ?>
								<td align="center" width="30%" colspan="5">
									<strong><?php echo $term->name; ?></strong>
								</td>
								<?php 
									$str_pad .= '
										<td align="center" width="5%">'.$term->name.' ('.SYNDICATE.')</td>
										<td align="center" width="5%">'.$term->name.' ('.PERCENT.')</td>
										<td align="center" width="5%">'.$term->name.' ('.WT.')</td>
										<td align="center" width="5%">'.$term->name.' ('.GRADE.')</td>
										<td align="center" width="5%">'.$term->name.' ('.POSITION.')</td>';
										}//foreach term Arr 
								if($term_id == '0' || empty($term_id)){
								?>
								<td align="center" width="5%" rowspan="2">
									<strong><?php echo TERM_TOT_WT; ?></strong>
								</td>
								<td align="center" width="5%" rowspan="3">
									<strong><?php echo TERM_TOT_PERCENT; ?></strong>
								</td>
								<td align="center" width="5%" rowspan="2" colspan="2">
									<strong><?php echo SI_IMPRES_MARK; ?></strong>
								</td>
								<td align="center" width="5%" rowspan="2">
									<strong><?php echo TOTAL_WT; ?></strong>
								</td>
								<td align="center" width="5%" rowspan="3">
									<strong><?php echo TOTAL_PERCENT; ?></strong>
								</td>
								<td align="center" width="5%" rowspan="3">
									<strong><?php echo GRADE; ?></strong>
								</td>
								<td align="center" width="5%" rowspan="3">
									<strong><?php echo POSITION; ?></strong>
								</td>
								<?php 
								$str_pad .= '
									<td align="center" width="5%">'.TERM_TOT_WT.'</td>
									<td align="center" width="5%">'.TERM_TOT_PERCENT.'</td>
									<td align="center" width="5%">'.SI_IMP.' ('.PERCENT.')</td>
									<td align="center" width="5%">'.SI_IMP.' ('.WT.')</td>
									<td align="center" width="5%">'.TOTAL_WT.'</td>
									<td align="center" width="5%">'.TOTAL_PERCENT.'</td>
									<td align="center" width="5%">'.GRADE.'</td>
									<td align="center" width="5%">'.POSITION.'</td>
								</tr>';
									
									}else{ ?>
								<td align="center" width="5%" rowspan="3">
									<strong><?php echo TOTAL_WT.'<hr />'.$total_term_weight; ?></strong>
								</td>
								<td align="center" width="5%" rowspan="3">
									<strong><?php echo TOTAL_PERCENT; ?></strong>
								</td>
								<td align="center" width="5%" rowspan="3">
									<strong><?php echo GRADE; ?></strong>
								</td>
								<td align="center" width="5%" rowspan="3">
									<strong><?php echo POSITION; ?></strong>
								</td>
								<?php }//else ?>
							</tr>
							<tr class="head">
								<?php foreach($termArr as $term){ ?>
								<td align="center" width="20%" rowspan="2">
									<strong><?php echo SYNDICATE; ?></strong>
								</td>
								<td align="center" width="20%" rowspan="2">
									<strong><?php echo PERCENT; ?></strong>
								</td>
								<td align="center" width="20%">
									<strong><?php echo WT; ?></strong>
								</td>
								<td align="center" width="20%" rowspan="2">
									<strong><?php echo GRADE; ?></strong>
								</td>
								<td align="center" width="20%" rowspan="2">
									<strong><?php echo POSITION; ?></strong>
								</td>
								<?php } ?>
							</tr>
							<tr class="head">
								<?php
								$total_term_weight = 0;
								foreach($termArr as $term){
									$sql = "select exr.weight from ".DB_PREFIX."exercise as exr,  ".DB_PREFIX."exercise_to_term as ett WHERE ett.exercise_id = exr.id AND ett.term_id = '".$term->id."'";
									$TexerciseArr = $dbObj->selectDataObj($sql);
									$exercise_weight = 0;
										foreach($TexerciseArr as $Texercise){
											$exercise_weight += $Texercise->weight;
										}
									$ds_impr_mark = $term->ds_impr_mark;
									$term_weight = $exercise_weight+$ds_impr_mark;
									$total_term_weight += $term_weight;
									$total_weight = $total_term_weight + $si_impr_mark;
								?>
								<td align="center" width="20%">
									<strong><?php echo $term_weight; ?></strong>
								</td>
								<?php }//foreach
								if($term_id == '0' || empty($term_id)){
								?>
								<td align="center" width="20%">
									<strong><?php echo $total_term_weight; ?></strong>
								</td>
								<td align="center" width="20%">
									<strong><?php echo PERCENT; ?></strong>
								</td>
								<td align="center" width="20%">
									<strong><?php echo WT.'<br />'.$si_impr_mark; ?></strong>
								</td>
								<td align="center" width="20%">
									<strong><?php echo $total_weight; ?></strong>
								</td>
								<?php }//if term_id = '0' ?>
							</tr>
							<?php 
							if($cur_user_group_id == '4'){
								$_SESSION['report_type'] = 'course';
								$arr[0]['sl'] = 'Ser No';
								$arr[0]['student_id'] = 'C/N';
								$arr[0]['rank_id'] = 'Rank';
								$arr[0]['student_name'] = 'Student Name';
								foreach($termArr as $term){
									$arr[0][$term->id.'_syn'] = 'Syndicate';
									$arr[0][$term->id.'_percent'] = $term->name.'(%)';
									$arr[0][$term->id.'_wt'] = $term->name.' Wt';
									$arr[0][$term->id.'_grade'] = $term->name.' Grade';
									$arr[0][$term->id.'_posn'] = $term->name.' Position';
								}//foreach
								
							if(empty($term_id)){
								$arr[0]['term_total_wt'] = 'Term Total Wt';
								$arr[0]['term_total_percent'] = 'Term Total (%)';
								$arr[0]['si_imp_percent'] = 'SI Imp (%)' ;
								$arr[0]['si_imp_wt'] = 'SI Imp Wt';
							}	
								$arr[0]['total_wt'] = 'Total Wt';
								$arr[0]['total_percent'] = 'Total (%)';
								$arr[0]['grade'] = 'Grade';
								$arr[0]['position'] = 'Posn';
							}//if	
							
							if(!empty($studentArr)){
								$rownum = 0;
								$sl = 0;
								foreach($studentArr as $student){
									$sl = $rownum + 1;
									if(($rownum%2)==0){//even
										$class = ' class="even"';									
									}else{//odd
										$class = ' class="odd"';									
									}
								
								//Find Info of this Student
								$sql = "select * from ".DB_PREFIX."student WHERE id = '".$student->student_id."'";
								$studentInfoArr = $dbObj->selectDataObj($sql);
								$studentInfo = $studentInfoArr[0];
							?>
							<tr <?php echo $class; ?>>
								<td align="center">
									<?php echo $sl; 
										$arr[$sl]['sl'] = $sl;
									?>
								</td>
								<td align="center">
									<?php echo $studentInfo->student_id;
										$arr[$sl]['student_id'] = $studentInfo->student_id;
									?>
								</td>
								<td>
									<?php 	$rank = getNameById('rank', $studentInfo->rank_id);
											$arr[$sl]['rank'] = $rank->short_name;
											echo $rank->short_name; ?>
								</td>
								<td>			
									<?php echo $studentInfo->official_name; 
											$arr[$sl]['student_name'] = $studentInfo->official_name; 
									?>
								</td>
								<?php
								$str_pad .= '
									<tr>
										<td align="center">'.$sl.'</td>
										<td align="center">'.$student->student_id.'</td>
										<td>'.$rank->short_name.'</td>
										<td>'.$studentInfo->official_name.'</td>';
								$term_total_weight = 0;
								$total_term_weight = 0;
								foreach($termArr as $term){
									//Find student's syndicate of this term
									$sql = "select syndicate_id from ".DB_PREFIX."student_to_syndicate WHERE student_id = '".$student->student_id."' AND term_id = '".$term->id."' AND course_id = '".$course_id."' ";
									$synArr = $dbObj->selectDataObj($sql);
									$syndicate = $synArr[0];
									$syndicateName = getNameById('syndicate', $syndicate->syndicate_id);
									if($syndicateName->name == ''){
										$syndicate_name = NOT_SELECTED;
									}else{
										$syndicate_name = $syndicateName->name;
									}
									
									//Find total exercise of this term --- thus get term weightage marks
									//$sql = "select exr.id, exr.mark, exr.weight from ".DB_PREFIX."exercise as exr,  ".DB_PREFIX."exercise_to_term as ett WHERE exr.course_id = '".$course_id."' AND ett.exercise_id = exr.id AND ett.term_id = '".$term->id."'";
									
									$sql = "select exr.id, exr.mark, exr.weight from ".DB_PREFIX."exercise as exr,  ".DB_PREFIX."exercise_to_term as ett WHERE ett.exercise_id = exr.id AND ett.term_id = '".$term->id."'";
									$totalExerciseArr = $dbObj->selectDataObj($sql);
									
									$total_exr_weight = 0;
									foreach($totalExerciseArr as $exercise){
										//Find student's marks of this term
										$sql = "select * from ".DB_PREFIX."marking WHERE term_id = '".$term->id."' AND course_id = '".$course_id."' AND student_id = '".$student->student_id."' AND exercise_id = '".$exercise->id."' AND status = '1'";
										$markArr = $dbObj->selectDataObj($sql);
										$mark = $markArr[0];
										$exr_total_weight = $mark->ds_marking+$mark->si_mod_marking+$mark->ci_mod_marking;
										
										//Find Exercise Info
										$sql = "select * from ".DB_PREFIX."exercise WHERE id = '".$exercise->id."'";
										$exrArr = $dbObj->selectDataObj($sql);
										$exercise = $exrArr[0];
										$exrWeight = $exercise->weight;
										
										$converted_exr_weight = $mark->ds_student_weight+$mark->si_student_weight+$mark->ci_student_weight;
										$total_exr_weight += $converted_exr_weight;
										//echo $total_exr_weight.'<br />';
									}//foreach --- totalExerciseArr

								$sql = "select * from ".DB_PREFIX."term WHERE id = '".$term->id."'";
								$termInfoArr = $dbObj->selectDataObj($sql);
								$termInfo = $termInfoArr[0];	
								$ds_impress_mark = $termInfo->ds_impr_mark;
								
								//Find Term Impression Marks for Student
								$sql = "select * from ".DB_PREFIX."impression_marking WHERE course_id = '".$course_id."' AND term_id = '".$term->id."' AND student_id = '".$student->student_id."' AND status = '1'";
								$stdTermArr = $dbObj->selectDataObj($sql);
								$stdTerm = $stdTermArr[0];	
								$student_term_mark = $stdTerm->ds_impr_marking;
								$student_term_weight = $stdTerm->ds_impr_weight;
								$std_term_total_weight = $total_exr_weight + $student_term_weight;
								
								//Find total weight of this term of Examinations
								$sql = "select exr.weight from ".DB_PREFIX."exercise as exr,  ".DB_PREFIX."exercise_to_term as ett WHERE ett.exercise_id = exr.id AND ett.term_id = '".$term->id."'";
								$TexerciseArr = $dbObj->selectDataObj($sql);
								$exercise_weight = 0;
									foreach($TexerciseArr as $Texercise){
										$exercise_weight += $Texercise->weight;
									}
								$term_weight = $exercise_weight+$ds_impress_mark;
								$term_percent = ($std_term_total_weight*100)/$term_weight;

								$term_total_weight += $std_term_total_weight;
								$total_term_weight += $term_weight;
								$term_total_percent_mark = (100*$term_total_weight)/$total_term_weight;
								$total_weight = $total_term_weight + $si_impr_mark;
								
								//This code find position of a student in this Term	
								$sql = " SELECT distinct (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)+impr.ds_impr_weight) as total from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND mrk.course_id = '".$course_id."' AND impr.term_id = '".$term->id."' AND mrk.term_id = '".$term->id."' AND mrk.status = '1' AND impr.status = '1' group by mrk.student_id ORDER BY total desc";
								$numberArrayForTerm = $dbObj->selectDataObj($sql);
								
								if(!empty($numberArrayForTerm)){
									$i = 0;
									foreach($numberArrayForTerm as $item){
										$numberArrayForTerm[$i]->position = $i+1;
										$i++; 
									}//foreach
								}//if
								?>
								<td align="center">
									<?php echo $syndicate_name;
											$arr[$sl][$term->id.'_syn'] = $syndicate_name;
									?>
								</td>
								<td align="right">
									<?php echo view_number_two($term_percent);
											$arr[$sl][$term->id.'_percent'] = view_number_two($term_percent);
									?>
								</td>
								<td align="right">
									<?php echo view_number_two($std_term_total_weight); 
											$arr[$sl][$term->id.'_wt'] = view_number_two($std_term_total_weight);
									?>
								</td>
								<td  style="padding-left:20px;">
									<?php echo findGrade($term_percent); 
											$arr[$sl][$term->id.'_grade'] = findGrade($term_percent);
									?>
								</td>
								<td align="center">
									<?php echo findPosition($numberArrayForTerm, $std_term_total_weight);
											$arr[$sl][$term->id.'_posn'] = findPosition($numberArrayForTerm, $std_term_total_weight);
									?>
								</td>
								<?php 
								$str_pad .= '
									<td align="center">'.$syndicate_name.'</td>
									<td align="right">'.view_number_two($term_percent).'</td>
									<td align="right">'.view_number_two($std_term_total_weight).'</td>
									<td align="center">'.findGrade($term_percent).'</td>
									<td align="center">'.findPosition($numberArrayForTerm, $std_term_total_weight).'</td>';
									}//foreach -- termArr
								if($term_id == '0' || empty($term_id)){
								//Find SI impression mark of this student in this course
								$sql = "select * from ".DB_PREFIX."si_impression_marking WHERE course_id = '".$course_id."' AND student_id = '".$student->student_id."'";
								$imprMarkingArr = $dbObj->selectDataObj($sql);
								$imprMarking = $imprMarkingArr[0];
								$SIimprMark = $imprMarking->si_impr_mark;
								$converted_si_impr_mark = $imprMarking->si_impr_weight;
								$student_total_weight = $converted_si_impr_mark + $term_total_weight;
								$total_percent_mark = (100*$student_total_weight)/$total_weight;
								
								?>
								<td align="right">
									<?php echo view_number_two($term_total_weight);
											$arr[$sl]['term_total_wt'] = view_number_two($term_total_weight);
									?>
								</td>
								<td align="right">
									<?php echo view_number_two($term_total_percent_mark);
											$arr[$sl]['term_total_percent'] = view_number_two($term_total_percent_mark);
									?> 
								</td>
								<td align="right">
									<?php echo view_number_two($SIimprMark); 
											$arr[$sl]['si_imp_percent'] = view_number_two($SIimprMark); 
									?> 
								</td>
								<td align="right">
									<?php echo view_number_two($converted_si_impr_mark);
											$arr[$sl]['si_imp_wt'] = view_number_two($converted_si_impr_mark);
									?> 
								</td>
								<td align="right">
									<?php echo view_number_two($student_total_weight);
											$arr[$sl]['total_wt'] = view_number_two($student_total_weight);
									?> 
								</td>
								<td align="right">
									<?php echo view_number_two($total_percent_mark);
											$arr[$sl]['total_percent '] = view_number_two($total_percent_mark);
									?>
								</td>
								<td style="padding-left:20px;">
									<?php echo findGrade($total_percent_mark);
											$arr[$sl]['grade'] = findGrade($total_percent_mark);
									?>
								</td>
								<td align="center">
									<?php echo findPosition($numberArrayForCourse, $student_total_weight);
											$arr[$sl]['position'] = findPosition($numberArrayForCourse, $student_total_weight);
									?>
								</td>
								<?php 
								$str_pad .= '
									<td align="right">'.view_number_two($term_total_weight).'</td>
									<td align="right">'.view_number_two($term_total_percent_mark).'</td>
									<td align="right">'.view_number_two($SIimprMark).'</td>
									<td align="right">'.view_number_two($converted_si_impr_mark).'</td>
									<td align="right">'.view_number_two($student_total_weight).'</td>
									<td align="right">'.view_number_two($total_percent_mark).'</td>
									<td align="center">'.findGrade($total_percent_mark).'</td>
									<td align="center">'.findPosition($numberArrayForCourse, $student_total_weight).'</td>
								</tr>';
									}else{ ?>
								<td align="center">
									<?php echo view_number_two($term_total_weight); 
										$arr[$sl]['total_wt'] = view_number_two($term_total_weight);
									?>
								</td>
								<td align="center">
									<?php echo view_number_two($term_total_percent_mark); 
										$arr[$sl]['total_percent '] = view_number_two($term_total_percent_mark);
										
										//Find out postion of student in aggregate of selected terms
										$stud_pos['mark'][$sl] = view_number_two($term_total_percent_mark);
										$stud_pos['student_id'][$sl] = $student->student_id;
									?>
								</td>
								<td align="center">
									<?php echo findGrade($term_total_percent_mark);
										$arr[$sl]['grade'] = findGrade($term_total_percent_mark); ?>
								</td>
								<td align="center">
									<?php echo findPosition($numberArrayForTermAggregate, $term_total_weight);
											$arr[$sl]['position'] = findPosition($numberArrayForTermAggregate, $term_total_weight); ?>
								</td>
								<?php }//else ?>
							</tr>
							<?php	
									$rownum++;
								}//foreach
								$str_pad .= '
								</table>
								<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center">
									<tr>
										<td height="30">
											'.GEN_BY.$cur_user_rank.' '.$cur_user_off_nam.ON. date('d M Y').AT.date('h:m:i a').'
										</td>
									</tr>
									<tr height="50">
										<td align="center">'.CONFIDENTIAL.'</td>
									</tr>
								</table>';
								
							}else{//if empty Student Array ?>
							<tr>
								<td colspan="9" height="30"><?php echo EMPTY_DATA; ?></td>
							</tr>
							<?php } ?>
						</table>
					</td>
				</tr>
			</table>
		</div>	
	<?php
		}
		
		/*echo '<pre>';
		print_r($stud_pos);*/
		
		$_SESSION['course'] = '';
		$_SESSION['course'][0] = $downloadTitle; //For assinging Course Report Title
		$_SESSION['course'][1] = $arr; //For assinging Course Report
		
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
		
		$pdf->Output($pdf_invoice_name);
		$_SESSION['pdf'] = '';
		$_SESSION['pdf'] = $pdf_invoice_name;
	}//if
	?>	
</div>
			
<?php
require_once("includes/footer.php");
?>
