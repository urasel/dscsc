<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}

$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$cur_user_off_nam = $usr[0]->official_name;
$cur_user_rank = getNameById('rank',$usr[0]->rank_id);
$cur_user_rank = $cur_user_rank->name;
$action = $_REQUEST['action'];
$msg = '';

//Chek if this user is valid for this file
if($cur_user_group_id != '4'){			
	header("Location: dashboard.php");		//Only SI can view course reports
}





switch($action){
	case 'view':	
	default:
		
		$course_id = $_REQUEST['course_id'];
		$term_id = $_REQUEST['term_id'];
		$sort = $_REQUEST['sort'];
		
		if(empty($term_id)){
			if($sort == 'posn'){
				$sql = "select * from ".DB_PREFIX."si_impression_marking WHERE course_id = '".$course_id."' ORDER BY total_weight desc";
			}else{
				$sql = "select * from ".DB_PREFIX."si_impression_marking WHERE course_id = '".$course_id."' ORDER BY student_id";
			}
			$studentArr = $dbObj->selectDataObj($sql);
			
			$sql = "select * from ".DB_PREFIX."term WHERE course_id = '".$course_id."'";
			$termArr = $dbObj->selectDataObj($sql);
			$total_term = sizeof($termArr);
			
			$sql = "select * from ".DB_PREFIX."course WHERE id = '".$course_id."'";
			$courseArr = $dbObj->selectDataObj($sql);
			$course = $courseArr[0];
			$si_impr_mark = $course->si_impr_mark;
			
			
			//New Array Create For Position Calculation Start
			$allStudentNewWeightArraySql = "select * from ".DB_PREFIX."impression_marking WHERE course_id = '".$course_id."' AND term_id = '".$term->id."' AND status = '1'";
			$stdTermArrAll = $dbObj->selectDataObj($allStudentNewWeightArraySql);
			$numberArray = '';
			if(!empty($stdTermArrAll)){
				foreach($stdTermArrAll as $item){
					//print_r($item);exit;
					$numberArray[]= $total_exr_weight + $item->ds_impr_marking;
				}//foreach
			}
			arsort($numberArray);
			$numberArrayForCourse = '';
			if(!empty($numberArray)){
				$i = 0;
				foreach($numberArray as $key => $val){
					$numberArrayForCourse[$i]->total = $val;
					$numberArrayForCourse[$i]->position = $i+1;
					$i++; 
				}//foreach
			}
			
			
		}else{
		
			$sql = "SELECT * FROM ".DB_PREFIX."term where id = '".$term_id."'";
			$termInfoArr = $dbObj->selectDataObj($sql);
			$termInfo = $termInfoArr[0];
			$order = $termInfo->order_id;
			
			$sql = "SELECT * FROM ".DB_PREFIX."term where course_id = '".$course_id."' AND order_id <= '".$order."' ORDER BY order_id";
			$termArr = $dbObj->selectDataObj($sql);
			
			$total_term_weight = 0;
			foreach($termArr as $term){
				$sql = "select exr.weight from ".DB_PREFIX."exercise as exr,  ".DB_PREFIX."exercise_to_term as ett WHERE exr.course_id = '".$course_id."' AND ett.exercise_id = exr.id AND ett.term_id = '".$term->id."'";
				$TexerciseArr = $dbObj->selectDataObj($sql);
				$exercise_weight = 0;
					foreach($TexerciseArr as $Texercise){
						$exercise_weight += $Texercise->weight;
					}
				$ds_impr_mark = $term->ds_impr_mark;
				$term_weight = $exercise_weight+$ds_impr_mark;
				$total_term_weight += $term_weight;
			}//foreach term Arr 
			
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
			
			if($sort == 'posn'){
				$in = 0;
				foreach($std_with_num as $k => $v){
						$studentArr[$in]->student_id = $k;
					$in++;
				}
			}else{
				$sql = "select distinct m.student_id from ".DB_PREFIX."marking as m, ".DB_PREFIX."student as s WHERE m.course_id = '".$course_id."' AND s.id = m.student_id ORDER BY s.student_id";
				$studentArr = $dbObj->selectDataObj($sql);	
			}
			
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
			
			
			
		}//else 
		
		
		$sql = " SELECT distinct total_weight as total from ".DB_PREFIX."si_impression_marking WHERE course_id = '".$course_id."' ORDER BY total desc";
		$numberArrayForCourse = $dbObj->selectDataObj($sql);
		
		if(!empty($numberArrayForCourse)){
			$i = 0;
			foreach($numberArrayForCourse as $item){
				$numberArrayForCourse[$i]->position = $i+1;
				$i++; 
			}//foreach
		}//if
						
		$action = 'view';
		$msg = $_REQUEST['msg'];
		break;
		
		
}//switch

/*********************************New Array generate***************************/
		
		$NewnumberArray = '';
		$totalexrsql = "select exr.id, exr.name,exr.join_course, exr.weight, exr.air_weight, exr.navy_weight, exr.mark from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where ett.term_id = ".$term_id." AND ett.exercise_id = exr.id order by exr.name asc";
		
		$totalExerciseArforCount = $dbObj->selectDataObj($totalexrsql);
		
		//Find Student info  -- the student who are assigned in this same term//Syndicate
		
		$sql = "SELECT mrk.student_id from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."syndicate as syn WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term_id."' AND mrk.status = '1' AND syn.id = mrk.syndicate_id group by mrk.student_id ORDER BY syn.name asc, mrk.student_id asc";
		
		$studentArrForArray = $dbObj->selectDataObj($sql);
		
		/* Aggregate Part Start*/
																
		$NewnumberArrayTotalTerm = '';												
		$studentTotalTermMarkArray = '';
		
		foreach($studentArrForArray as $student){
			$studentTotalTermMark = 0;
					foreach($termArr as $term){
					$total_exr_weight_new = 0;
					$totalexrsql = "select exr.id, exr.name,exr.join_course, exr.weight, exr.air_weight, exr.navy_weight, exr.mark from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where ett.term_id = ".$term->id." AND ett.exercise_id = exr.id order by exr.name asc";
		
					$totalExerciseArforCount = $dbObj->selectDataObj($totalexrsql);
					$total_exr_weight = 0;
					$term_total_weight = 0;
					foreach($totalExerciseArforCount as $exercise){
						
						//Find student's marks of this term
										$sql = "select * from ".DB_PREFIX."marking WHERE term_id = '".$term->id."' AND course_id = '".$course_id."' AND student_id = '".$student->student_id."' AND exercise_id = '".$exercise->id."' AND status = '1' order by id desc";
										
										$markArr = $dbObj->selectDataObj($sql);
										$mark = $markArr[0];
										$exr_total_weight = $mark->ds_marking+$mark->si_mod_marking+$mark->ci_mod_marking;
										
										//Find Exercise Info
										$sql = "select * from ".DB_PREFIX."exercise WHERE id = '".$exercise->id."'";
										$exrArr = $dbObj->selectDataObj($sql);
										$exercise = $exrArr[0];
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
										
										
										/***************************************************/
										
										//$converted_s_mark = $mark->ds_student_weight+$mark->si_student_weight+$mark->ci_student_weight;
										$from_ds_weight = ($mark->ds_marking*$exercise->weight)/100;
										/*$converted_s_mark = $from_ds_weight+$mark->si_student_weight+$mark->ci_student_weight;
										$term_exam_weight += $converted_s_mark;
										$term_exam_percent = ($term_exam_weight*100)/$exam_total_weight;
										/***************************************************/
										
										//$converted_exr_weight = $mark->ds_student_weight+$mark->si_student_weight+$mark->ci_student_weight;
										$converted_exr_weight = $from_ds_weight+$mark->si_student_weight+$mark->ci_student_weight;
										$total_exr_weight += $converted_exr_weight;
										
										//echo 'Std Name='.$studentInfo->official_name.' studen id'.$student->student_id.' exr id'.$exercise->id.' Std Wgt='.$total_exr_weight.'<br/>';
									
									//Find Term Impression Marks for Student
									$sql = "select * from ".DB_PREFIX."impression_marking WHERE course_id = '".$course_id."' AND term_id = '".$term->id."' AND student_id = '".$student->student_id."' AND status = '1'";
									$stdTermArr = $dbObj->selectDataObj($sql);
									$stdTerm = $stdTermArr[0];	
									
									
									$student_term_weight = $stdTerm->ds_impr_weight;
									
									
									
						
					}//Foreach Exercise End
					
					$std_term_total_weight = $total_exr_weight + $student_term_weight;
					$term_total_weight += $std_term_total_weight;
					
					$studentTotalTermMark += $term_total_weight;
					
			
			}//Foreach Term End
			$studentTotalTermMarkArray[] = $studentTotalTermMark;
			$std_with_num[$student->student_id] = $studentTotalTermMark;
		}
		arsort($std_with_num);
		
		if(!empty($std_with_num)){
			$in = 0;
			foreach($std_with_num as $k => $v){
				$studentArr[$in]->student_id = $k;
				$in++;
			}
		}
		
		rsort($studentTotalTermMarkArray);
		
		
		if(!empty($studentTotalTermMarkArray)){
			$i = 0;
			foreach($studentTotalTermMarkArray as $item){
				$numberArrayForTotalTerm[$i]->total = $item;
				$numberArrayForTotalTerm[$i]->position = $i+1;
				$i++; 
			}
		}
		
		
												/* Aggregate Part End*/
		
		/*********************************New Array generate End***************************/
		
		
		
/*NEW TEST Case for Array Start */
		$termWiseArray = '';
		$sql = "SELECT * FROM ".DB_PREFIX."term where course_id = '".$course_id."' ORDER BY order_id ASC";
		$termArr = $dbObj->selectDataObj($sql);
		$terwisestudenttotalmrk = '';
		foreach($termArr as $term){
			
			$ds_imp_term_sql = "select * from ".DB_PREFIX."term WHERE id = '".$term->id."'";
			$termInfoArry = $dbObj->selectDataObj($ds_imp_term_sql);
			$termInfo = $termInfoArry[0];
			$ds_impr_mark = $termInfo->ds_impr_mark;
			
				$sql = "select * from ".DB_PREFIX."exercise as exr,  ".DB_PREFIX."exercise_to_term as ett WHERE ett.exercise_id = exr.id AND ett.term_id = '".$term->id."'";
			$NewnumberArray = '';
			$totalexrsql = "select exr.id, exr.name,exr.join_course, exr.weight, exr.air_weight, exr.navy_weight, exr.mark from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where ett.term_id = ".$term->id." AND ett.exercise_id = exr.id order by exr.name asc";
			
			$totalExerciseArforCount = $dbObj->selectDataObj($totalexrsql);
			
			
			//$sql = "SELECT mrk.student_id from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."syndicate as syn WHERE mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term->id."' AND mrk.status = '1' AND syn.id = mrk.syndicate_id group by mrk.student_id ORDER BY syn.name asc, mrk.student_id asc";
			
			$sql = "SELECT mrk.student_id FROM dscsc_marking AS mrk, dscsc_impression_marking AS impr, dscsc_syndicate AS syn 
			WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND impr.term_id = '".$term->id."' AND mrk.status = '1' 
			AND impr.status = '1' AND syn.id = mrk.syndicate_id AND syn.id = impr.syndicate_id GROUP BY mrk.student_id 
			ORDER BY syn.name ASC, mrk.student_id asc";
			
			
		
			$studentArrForArray = $dbObj->selectDataObj($sql);
			
			foreach($studentArrForArray as $student){
			
					$total_exr_weight_new = 0;
					$array_count_mark = '';
					$exr_total_weight = '';
					
					foreach($totalExerciseArforCount as $exercise){
						
						$mark_sql = "select * from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise->id."' AND student_id = '".$student->student_id."' AND term_id = '".$term->id."' AND course_id = '".$course_id."' AND status = '1' AND ci_status = '1' order by id desc";
						
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
							$sql = "select * from ".DB_PREFIX."impression_marking WHERE student_id = '".$student->student_id."' AND term_id = '".$term->id."' AND course_id = '".$course_id."' AND status = '1'";
							$stuImprMarkArry = $dbObj->selectDataObj($sql);
							$stuImprMark = $stuImprMarkArry[0];
							
							$stu_ds_impression_mark = $stuImprMark->ds_impr_marking;
							$converted_ds_impr_mark = ($stu_ds_impression_mark*$ds_impr_mark)/100;
						}else if(($cur_user_group_id == 5) && ($forwarded == 'true')){
							//Find term impression mark of Student
							$sql = "select * from ".DB_PREFIX."impression_marking WHERE student_id = '".$student->student_id."' AND term_id = '".$term->id."' AND course_id = '".$course_id."'";
							$stuImprMarkArry = $dbObj->selectDataObj($sql);
							$stuImprMark = $stuImprMarkArry[0];
							$stu_ds_impression_mark = $stuImprMark->ds_impr_marking;
							$converted_ds_impr_mark = ($stu_ds_impression_mark*$ds_impr_mark)/100;

						}
						
						
						
					}
					$total_number = $total_exr_weight_new+$converted_ds_impr_mark;
					
					$NewnumberArray[] = $total_number;
					$terwisestudenttotalmrk[$student->student_id] = $terwisestudenttotalmrk[$student->student_id] + $total_number;
			
			}
			rsort($NewnumberArray);
			
			
			if(!empty($NewnumberArray)){
				$i = 0;
				$numberArray = '';
				foreach($NewnumberArray as $item){
					$numberArray[$i]->total = $item;
					$numberArray[$i]->position = $i+1;
					$i++; 
				}
			}
			$termWiseArray[$term->id] = $numberArray;
			
		}
		//print_r($termWiseArray);exit;
		
		/*NEW TEST Case for Array End */

?>
<link rel="shortcut icon" href="images/favicon.ico">
<title><?php echo YARDSTICK.'Print Course Results'; ?></title>
<body onLoad="javascript:this.print();" style="width:1000px; margin-left:auto; margin-right:auto; font-size:10px;">
	<table cellpadding="0" cellspacing="0" border="0" align="center" width="100%">
		<tr align="center">
			<td height="30"><strong><?php echo CONFIDENTIAL;?></strong></td>
		</tr>
		<tr align="center">
			<td height="30">
			<strong>
				<?php 
				$crsName = getNameById('course', $course_id);
				echo strtoupper($crsName->name);
				?>
			</strong>
			</td>
		</tr>
		<tr align="center">
			<td height="30">
			<strong>
				<?php 
				$pTrmName = getNameById('term', $term_id);
				echo strtoupper(PROG_RES_SHT.$pTrmName->name);
				?>
			</strong>
			</td>
		</tr>
		<tr align="center">
			<td height="30">
			<strong>
				<?php 
				$wngName = getNameById('wing', $cur_user_wing_id);
				echo strtoupper($wngName->name);
				?>
			</strong>
			</td>
		</tr>
	</table>
	<table cellpadding="0" cellspacing="0" border="1" width="100%">
		<tr class="head">
			<td align="center" rowspan="3">
				<strong><?php echo SER_NO; ?></strong>
			</td>
			<td align="center" rowspan="3">
				<strong><?php echo CN; ?></strong>
			</td>
			<td align="center" rowspan="3">
				<strong><?php echo RANK; ?></strong>
			</td>
			<td align="center"rowspan="3">
				<strong><?php echo STUDENT_NAME; ?></strong>
			</td>
			<?php foreach($termArr as $term){ ?>
			<td align="center" colspan="5">
				<strong><?php echo $term->name; ?></strong>
			</td>
			<?php }
			if(empty($term_id)){
			?>
			<td align="center" rowspan="2">
				<strong><?php echo TERM_TOT_WT; ?></strong>
			</td>
			<td align="center" rowspan="3">
				<strong><?php echo TERM_TOT_PERCENT; ?></strong>
			</td>
			<td align="center" rowspan="2" colspan="2">
				<strong><?php echo SI_IMPRES_MARK; ?></strong>
			</td>
			<td align="center" rowspan="2">
				<strong><?php echo TOTAL_WT; ?></strong>
			</td>
			<td align="center" rowspan="3">
				<strong><?php echo TOTAL_PERCENT; ?></strong>
			</td>
			<td align="center" rowspan="3">
				<strong><?php echo GRADE; ?></strong>
			</td>
			<td align="center" rowspan="3">
				<strong><?php echo POSITION; ?></strong>
			</td>
			<?php }else{ ?>
			<td align="center" rowspan="3">
				<strong><?php echo TOTAL_WT.'<hr />'.$total_term_weight; ?></strong>
			</td>
			<td align="center" rowspan="3">
				<strong><?php echo TOTAL_PERCENT; ?></strong>
			</td>
			<td align="center" rowspan="3">
				<strong><?php echo GRADE; ?></strong>
			</td>
			<td align="center" rowspan="3">
				<strong><?php echo POSITION; ?></strong>
			</td>
			<?php }//else ?>
		</tr>
		<tr class="head">
			<?php foreach($termArr as $term){ ?>
			<td align="center" rowspan="2">
				<strong><?php echo SYNDICATE; ?></strong>
			</td>
			<td align="center" rowspan="2">
				<strong><?php echo PERCENT; ?></strong>
			</td>
			<td align="center">
				<strong><?php echo WT; ?></strong>
			</td>
			<td align="center" rowspan="2">
				<strong><?php echo GRADE; ?></strong>
			</td>
			<td align="center" rowspan="2">
				<strong><?php echo POSITION; ?></strong>
			</td>
			<?php } ?>
		</tr>
		<tr class="head">
			<?php
			$total_term_weight = 0;
			foreach($termArr as $term){
				$sql = "select exr.weight from ".DB_PREFIX."exercise as exr,  ".DB_PREFIX."exercise_to_term as ett WHERE exr.course_id = '".$course_id."' AND ett.exercise_id = exr.id AND ett.term_id = '".$term->id."'";
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
			<td align="center">
				<strong><?php echo $term_weight; ?></strong>
			</td>
			<?php } 
			if(empty($term_id)){
			?>
			<td align="center">
				<strong><?php echo $total_term_weight; ?></strong>
			</td>
			<td align="center">
				<strong><?php echo PERCENT; ?></strong>
			</td>
			<td align="center">
				<strong><?php echo WT.'<br />'.$si_impr_mark; ?></strong>
			</td>
			<td align="center">
				<strong><?php echo $total_weight; ?></strong>
			</td>
			<?php }//if ?>
		</tr>
	<?php 
	
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
			$sql = "select * from ".DB_PREFIX."student WHERE id = '".$student->student_id."' AND course_id = '".$course_id."'";
			$studentInfoArr = $dbObj->selectDataObj($sql);
			$studentInfo = $studentInfoArr[0];
		?>
		<tr <?php echo $class; ?>>
			<td align="center">
				<?php echo $sl; ?>
			</td>
			<td align="center">
				<?php echo $studentInfo->student_id; ?>
			</td>
			<td>
				<?php 	$rank = getNameById('rank', $studentInfo->rank_id);
						echo $rank->short_name; ?>
			<td>			
				<?php echo $studentInfo->official_name; ?>
			</td>
			<?php
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
				
				$sql = "select exr.id, exr.name,exr.join_course, exr.weight, exr.air_weight, exr.navy_weight, exr.mark from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where ett.term_id = ".$term->id." AND ett.exercise_id = exr.id order by exr.name asc";
				
				$totalExerciseArr = $dbObj->selectDataObj($sql);
				
				$total_exr_weight = 0;
				foreach($totalExerciseArr as $exercise){
					//Find student's marks of this term
					$sql = "select * from ".DB_PREFIX."marking WHERE term_id = '".$term->id."' AND course_id = '".$course_id."' AND student_id = '".$student->student_id."' AND exercise_id = '".$exercise->id."' AND status = '1' order by id desc";
					
					$markArr = $dbObj->selectDataObj($sql);
					$mark = $markArr[0];
					$exr_total_weight = $mark->ds_marking+$mark->si_mod_marking+$mark->ci_mod_marking;
					
					//Find Exercise Info
					$sql = "select * from ".DB_PREFIX."exercise WHERE id = '".$exercise->id."'";
					$exrArr = $dbObj->selectDataObj($sql);
					$exercise = $exrArr[0];
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
					
					$from_ds_weight = ($mark->ds_marking*$exercise->weight)/100;
					//$converted_exr_weight = $mark->ds_student_weight+$mark->si_student_weight+$mark->ci_student_weight;
					$converted_exr_weight = $from_ds_weight+$mark->si_student_weight+$mark->ci_student_weight;
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
			
			
			//New Array Create For Position Calculation Start
			$allStudentNewWeightArraySql = "select * from ".DB_PREFIX."impression_marking WHERE course_id = '".$course_id."' AND term_id = '".$term->id."' AND status = '1'";
			$stdTermArrAll = $dbObj->selectDataObj($allStudentNewWeightArraySql);
			$numberArray = '';
			if(!empty($stdTermArrAll)){
				foreach($stdTermArrAll as $item){
					//print_r($item);exit;
					$numberArray[]= $total_exr_weight + $item->ds_impr_marking;
				}//foreach
			}
			arsort($numberArray);
			$numberArrayForTerm = '';
			if(!empty($numberArray)){
				$i = 0;
				foreach($numberArray as $key => $val){
					$numberArrayForTerm[$i]->total = $val;
					$numberArrayForTerm[$i]->position = $i+1;
					$i++; 
				}//foreach
			}
			
			//Find total weight of this term of Examinations
			//$sql = "select exr.* from ".DB_PREFIX."exercise as exr,  ".DB_PREFIX."exercise_to_term as ett WHERE exr.course_id = '".$course_id."' AND ett.exercise_id = exr.id AND ett.term_id = '".$term->id."'";
			$sql = "select exr.id, exr.name,exr.join_course, exr.weight, exr.air_weight, exr.navy_weight, exr.mark from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where ett.term_id = ".$term->id." AND ett.exercise_id = exr.id order by exr.name asc";
			
			
			$TexerciseArr = $dbObj->selectDataObj($sql);
			$exercise_weight = 0;
				foreach($TexerciseArr as $Texercise){
					$joinExercise = $Texercise->join_course;
					if($joinExercise == 1 && $cur_user_wing_id == 2){
						$Texercise->weight = $Texercise->air_weight;
					}else if($joinExercise == 1 && $cur_user_wing_id == 3){
						$Texercise->weight = $Texercise->navy_weight;
					}else if($joinExercise == 1 && $cur_user_wing_id == 1){
						$Texercise->weight = $Texercise->weight;
					}else{
						$Texercise->weight = $Texercise->weight;
					}
					$exercise_weight += $Texercise->weight;
				}
				
			$term_weight = $exercise_weight+$ds_impress_mark;
			
			$term_percent = ($std_term_total_weight*100)/$term_weight;
			
			$term_total_weight += $std_term_total_weight;
			$total_term_weight += $term_weight;
			$term_total_percent_mark = (100*$term_total_weight)/$total_term_weight;
			$total_weight = $total_term_weight + $si_impr_mark;
			
			
			?>
			<td align="center">
				<?php echo $syndicate_name; ?>
			</td>
			<td align="right">
				<?php echo view_number_two($term_percent); ?>
			</td>
			<td align="right">
				<?php echo view_number_two($std_term_total_weight); ?>
			</td>
			<td  style="padding-left:20px;">
				<?php echo findGrade($term_percent); ?>
			</td>
			<td align="center">
				<?php echo findPosition($termWiseArray[$term->id], $std_term_total_weight); ?>
			</td>
<?php }//foreach -- termArr
		if(empty($term_id)){
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
				<?php echo view_number_two($term_total_weight); ?>
			</td>
			<td align="right">
				<?php echo view_number_two($term_total_percent_mark); ?> 
			</td>
			<td align="right">
				<?php echo view_number_two($SIimprMark); ?> 
			</td>
			<td align="right">
				<?php echo view_number_two($converted_si_impr_mark); ?> 
			</td>
			<td align="right">
				<?php echo view_number_two($student_total_weight); ?> 
			</td>
			<td align="right">
				<?php echo view_number_two($total_percent_mark); ?>
			</td>
			<td style="padding-left:20px;">
				<?php echo findGrade($total_percent_mark); ?>
			</td>
			<td align="center">
				<?php 
				
				echo findPosition($numberArrayForTotalTerm, $term_total_weight); ?>
			</td>
<?php }else{ ?>
			<td align="center">
				<?php echo view_number_two($term_total_weight); ?>
			</td>
			<td align="center">
				<?php echo view_number_two($term_total_percent_mark);?>
			</td>
			<td align="center">
				<?php echo findGrade($term_total_percent_mark); ?>
			</td>
			<td align="center">
				<?php echo findPosition($numberArrayForTotalTerm, $term_total_weight);?>
			</td>
<?php }//else ?>
		</tr>
		<?php	
				$rownum++;
			}//foreach
		}else{		//if empty Student Array ?>
		<tr>
			<td colspan="9" height="30"><?php echo EMPTY_DATA; ?></td>
		</tr>
		<?php } ?>
	</table>
	<table cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr height="30">
			<td>
				<?php echo GEN_BY.$cur_user_rank.' '.$cur_user_off_nam.ON. date('d M Y').AT.date('h:m:i a'); ?>
			</td>
		</tr>
		<tr align="center">
			<td height="30"><strong><?php echo CONFIDENTIAL;?></strong></td>
		</tr>
	</table>
</body>
