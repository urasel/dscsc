<?php
require_once("includes/header.php");
//check for loggedin	

$usr = $user->getUser();
if(empty($usr)){
	echo 'Not Logged In';	
	exit;
}

$cur_user_id = $usr[0]->id;
$cur_user_group_id = $usr[0]->group_id;
$cur_user_wing_id = $usr[0]->wing_id;
$action = $_REQUEST['action'];
$msg = '';

$file = $_SERVER["HTTP_REFERER"];
$break = explode('/', $file);
$url = $break[count($break) - 1];
$explode = explode('.php', $url);
$uri = $explode[0].'.php';


switch($action){
	
	case 'check_username_availability':	
		$username = $_REQUEST['username'];
		
		if($username == ''){
			echo "You must enter a Username.";
		}else{
			$query = "select username from ".DB_PREFIX."user where username = '".$username."'";
			$userArr = $dbObj->selectDataObj($query);
			
			if(empty($userArr)){
				echo $username." is available to create.";
			}else{
				echo $username." is already in use!";
			}//if
		}//if
	break;
	
	case 'generate_sec_key':	
		$sec_key = generateSecKey();
		echo '<input name="sec_key" id="sec_key" type="text" readonly="readonly" class="inputbox" alt="Security Key" size="36" value="'.$sec_key.'" />';
	break;
	
	/*case 'check_student_name_availability':	
		$official_name = $_REQUEST['official_name'];
		
		if($official_name == ''){
			echo "You must enter an Official Name.";
		}else{
			$query = "select official_name from ".DB_PREFIX."student where official_name = '".$official_name."'";
			$userArr = $dbObj->selectDataObj($query);
			
			if(empty($userArr)){
				echo $official_name." is available to create.";
			}else{
				echo $official_name." is already in use!";
			}//if
		}//if
	break;*/
	
	case 'assign_rank_appoint':
		$wing_id = $_REQUEST['wing_id'];
		
		if($wing_id == '' || $wing_id == '0'){
			echo DATA_NOT_FOUND;
		}else{
			//Build Rank Array	
			$query = "select id, name from ".DB_PREFIX."rank where wing_id = '".$wing_id."' order by weight asc";
			$rankArr = $dbObj->selectDataObj($query);
			
			$rankId = array();
			$rankId[0] = SELECT_RANK_OPT;
			if(!empty($rankArr)){			
				foreach($rankArr as $item){
					$rankId[$item->id] = $item->name;
				}	
			}			
			$rankList_opt = formSelectElement($rankId, $rank_id, 'rank_id');
			echo $rankList_opt;
			
			echo ':*:';
			//Build Appointment Array	
			$query = "select id, name from ".DB_PREFIX."appointment where wing_id = '".$wing_id."' order by `order` asc";
			$appointArr = $dbObj->selectDataObj($query);
			
			$appointId = array();
			$appointId[0] = SELECT_APPOINTMENT_OPT;
			if(!empty($appointArr)){			
				foreach($appointArr as $item){
					$appointId[$item->id] = $item->name;
				}	
			}			
			$appointmentList_opt = formSelectElement($appointId, $appointment_id, 'appointment_id');
			echo $appointmentList_opt;
		}//else

	break;
	
	case 'progress_result_term':
		$course_id = $_REQUEST['course_id'];
		
		if($course_id != '0'){

			//Build Term Array	
			$query = "select id, name from ".DB_PREFIX."term where wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."' ORDER BY order_id";
			$termArr = $dbObj->selectDataObj($query);
			
			$termId = array();
			$termId[0] = SELECT_TERM_OPT;
			if(!empty($termArr)){			
				foreach($termArr as $item){
					$termId[$item->id] = $item->name;
				}	
			}			
			$termList_opt = formSelectElement($termId, $term_id, 'term_id', 'onchange = processFunction("si_exercise_list_for_progressive_result")');
			echo $termList_opt;
			
			echo ':*:';
			
			//Build Student Array	
			$query = "select id, full_name, student_id from ".DB_PREFIX."student where wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."' ORDER BY student_id";
			$studentArr = $dbObj->selectDataObj($query);
			
			$studentId = array();
			$studentId[0] = SELECT_STUDENT_OPT;
			if(!empty($studentArr)){			
				foreach($studentArr as $item){
					$studentId[$item->id] = $item->student_id.' &raquo; '.$item->full_name;
				}	
			}			
			$studentList_opt = formSelectElement($studentId, $student_id, 'student_id');
			echo $studentList_opt;
		}//else

	break;
	
	case 'assigned_student':
		$course_id = $_REQUEST['course_id'];
		
		if($course_id == '' || $course_id == '0'){
			//do nothing
		}else{
			//Build Term Array	
			//$query = "select id, name from ".DB_PREFIX."term where course_id = '".$course_id."' AND wing_id = '".$cur_user_wing_id."' order by name asc";
			//$query = "select id, name from ".DB_PREFIX."term where course_id = '".$course_id."' OR term_type = 1 order by name asc";
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
			echo $termList_opt;
			
			echo ':*:';

			//Build Syndicate List Array	
			$query = "select syn.id, syn.name from ".DB_PREFIX."syndicate as syn, ".DB_PREFIX."syndicate_to_course as stc where stc.course_id = '".$course_id."' AND stc.wing_id = '".$cur_user_wing_id."' AND stc.syndicate_id = syn.id OR syn.syndicate_type = 1 order by syn.name asc";
			
			$synArr = $dbObj->selectDataObj($query);
			$synId = array();
			$synId[0] = SELECT_SYNDICATE_OPT;
			if(!empty($synArr)){			
				foreach($synArr as $item){
					$synId[$item->id] = $item->name;
				}	
			}			
			$syndicateList_opt = formSelectElement($synId, $syndicate_id, 'syndicate_id');
			echo $syndicateList_opt;
		}//else

	break;

	case 'assign_syndicate':
		$course_id = $_REQUEST['course_id'];
		
		if($course_id != '0' || $course_id == ""){
			//Find in which wing the course exists
			$query = "select wing_id from ".DB_PREFIX."course WHERE id = '".$course_id."' limit 1";
			$wingArr = $dbObj->selectDataObj($query);
			$wing = $wingArr[0];
			
			//Build Syndicate Selection Array
			$query = "select id, name from ".DB_PREFIX."syndicate WHERE wing_id = '".$wing->wing_id."' or syndicate_type = 1 order by name asc";
			$synArr = $dbObj->selectDataObj($query);
	
			$synId = array();
			if(!empty($synArr)){
				foreach($synArr as $item){
					$synId[$item->id] = $item->name;
				}	
			}		
			
			//Find Existing Syndicate from syndicate_to_course Table with selected syndicate_id
			$query = "select syndicate_id from ".DB_PREFIX."syndicate_to_course WHERE course_id='".$course_id."'";
			$existingSynArray = $dbObj->selectDataObj($query);
			
			$syn_to_course_Array = array();	
			if(empty($existingSynArray)){
				//
			}else{
				$i = 0;
				foreach($existingSynArray as $syn){
					$syn_to_course_Array[$i] = $syn->syndicate_id;
					$i++;
				}
			}	
	
			//Show Syndicate of User's Wing and check whether already 
			//engaged in the selected Course or not		
			$assign_syn = '';
			if(!empty($synId)){	
				$assign_syn .= '<table width="100%" cellpadding="0" cellspacing="0" border="0" class="datagrid">
							<tr><td><strong><input type="checkbox" name="check_all" id="check_all" onClick="checkAllItem(\'syndicate_id[]\')" title="Check/Uncheck All" />Check/Uncheck All</strong></td></tr>
							<tr class="footer"><td><b>'.SYNDICATE_NAME.'</b></td></tr>';
				
				$rownum = 0;					
				foreach($synId as $id => $name){
					$check_str = '';
					if(in_array($id, $syn_to_course_Array)){
						$check_str = ' checked="checked"';
					}
					
					if(($rownum%2)==0){//even
						$class = ' class="even"';									
					}else{//odd
						$class = ' class="odd"';									
					}
				
					$assign_syn .= '<tr '.$class.'><td><input type="checkbox" name="syndicate_id[]" id="syndicate_id'.$id.'" value="'.$id.'" '.$check_str .' />&nbsp;<b>'.$name.'</b></td></tr>'; 	
					$rownum++;					
				}
			}	
	
	
			$str = '<table width="100%" cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td valign="top" width="20%">
								'.RELATE_SYNDICATE.':
							</td>
							<td width="80%">'.$assign_syn.'</td>
						</tr>
					</table>';
			echo $str;		
		}//if
	break;
	
	case 'assign_term_for_student':	
		$course_id = $_REQUEST['course_id'];
		
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
		
		//Build Term List Array	
		//$query = "select id, name from ".DB_PREFIX."term where course_id = ".$course_id." order by name asc";
		//$query = "select id, name from ".DB_PREFIX."term where course_id = ".$course_id." OR term_type =1  order by name asc";
		$query = "select id, name from ".DB_PREFIX."term where course_id = ".$course_id." order by name asc";
		$termArr = $dbObj->selectDataObj($query);
		$termId = array();
		$termId[0] = SELECT_TERM_OPT;
		if(!empty($termArr)){			
			foreach($termArr as $item){
				$termId[$item->id] = $item->name;
			}	
		}			
		$termList_opt = formSelectElement($termId, $term_id, 'term_id', 'onchange = processFunction("assign_syndicate_for_student")');
		
		echo $termList_opt.'<span class="required_field"> *</span>';
		
	break;
	
	case 'assign_term_for_exercise':	
		$course_id = $_REQUEST['course_id'];
		
		if($course_id == '0' || $course_id == ""){
			//do nothing
		}else{
			//Build Term List Array	
			//$query = "select id, name from ".DB_PREFIX."term where course_id = ".$course_id." OR term_type = 1 order by name asc";
			$query = "select id, name from ".DB_PREFIX."term where course_id = ".$course_id." order by name asc";
			$termArr = $dbObj->selectDataObj($query);
			$termId = array();
			$termId[0] = SELECT_TERM_OPT;
			if(!empty($termArr)){			
				foreach($termArr as $item){
					$termId[$item->id] = $item->name;
				}	
			}			
			$termList_opt = formSelectElement($termId, $term_id, 'term_id', 'onchange = processFunction("assign_exr_with_term")');
			
			echo $termList_opt.'<span class="required_field"> *</span>';
		}
		
	break;

	case 'assign_syndicate_for_student':	
		$course_id = $_REQUEST['course_id'];
		
		//Find in which wing the course exists
		$query = "select wing_id from ".DB_PREFIX."course WHERE id = '".$course_id."' limit 1";
		$wingArr = $dbObj->selectDataObj($query);
		$wing = $wingArr[0];
			
		//Build Syndicate List Array
		//$query = "select syn.id, syn.name from ".DB_PREFIX."syndicate as syn, ".DB_PREFIX."syndicate_to_course as stc where syn.wing_id = ".$wing->wing_id." OR syn.syndicate_type = 1 AND stc.course_id = '".$course_id."' AND stc.syndicate_id = syn.id order by syn.name asc";
		
		$query = "select syn.id, syn.name from ".DB_PREFIX."syndicate as syn, ".DB_PREFIX."syndicate_to_course as stc where syn.wing_id = ".$wing->wing_id." AND stc.course_id = '".$course_id."' AND stc.syndicate_id = syn.id order by syn.name asc";
		$synArr = $dbObj->selectDataObj($query);
		

		$synId = array();
		$synId[0] = SELECT_SYNDICATE_OPT;
		if(!empty($synArr)){			
			foreach($synArr as $item){
				$synId[$item->id] = $item->name;
			}	
		}			
		$syndicateList_opt = formSelectElement($synId, $syndicate_id, 'syndicate_id', 'onchange = processFunction("assign_student_for_syndicate")');
		
		$str = '<table width="100%" cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td height="30" width="20%">
								'.SELECT_SYNDICATE.':
							</td>
							<td width="80%">
								'.$syndicateList_opt.'
								<span class="required_field"> *</span>
							</td>
						</tr>
					</table>';
		echo $str;

	break;
	
	case 'assign_student_for_syndicate':
		$course_id = $_REQUEST['course_id'];
		$syndicate_id = $_REQUEST['syndicate_id'];
		$term_id = $_REQUEST['term_id'];
		if($syndicate_id == '0' || $syndicate_id == ""){
			//do nothing
		}else{
			//Build Student Selection Array	
			
			
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
				//print_r($courseIdArr);
				$threeServiceCourses = '';
				$query = "select id, student_id from ".DB_PREFIX."student WHERE course_id IN (".$courseIdArr.") order by student_id,wing_id asc";
			}else{
				$query = "select id, student_id from ".DB_PREFIX."student WHERE course_id = '".$course_id."' AND wing_id = '".$cur_user_wing_id."' order by student_id asc";
			}
			
			$studentArr = $dbObj->selectDataObj($query);
			
			$studentId = array();
			if(!empty($studentArr)){
				foreach($studentArr as $item){
					$studentId[$item->id] = $item->student_id;
				}	
			}		
			
			//Find Existing Student from student_to_syndicate Table with selected student_id
			if($syndicateType == 1){
				$query = "select student_id from ".DB_PREFIX."student_to_syndicate WHERE syndicate_id='".$syndicate_id."' AND  syndicatetype ='1' AND course_id ='".$course_id."' AND term_id = '".$term_id."'";
			}else{
				$query = "select student_id from ".DB_PREFIX."student_to_syndicate WHERE  syndicate_id='".$syndicate_id."' AND  syndicatetype ='0' AND course_id ='".$course_id."' AND term_id = '".$term_id."'";
			}
			$existingStudentArr = $dbObj->selectDataObj($query);
			
			$syn_to_student_Array = array();	
			if(empty($existingStudentArr)){
				//
			}else{
				$i = 0;
				foreach($existingStudentArr as $student){
					$syn_to_student_Array[$i] = $student->student_id;
					$i++;
				}
			}	
			//echo '<pre>';
			//print_r($syn_to_student_Array);
			//Show Syndicate of User's Wing and check whether already 
			//engaged in the selected Course or not		
			$assign_student = '';
			if(!empty($studentId)){	
				
				$assign_student .= '<table width="100%" cellpadding="0" cellspacing="0" border="0" class="datagrid">
										<tr><td colspan="5"><strong><input type="checkbox" name="check_all" id="check_all" onClick="checkAllItem(\'student_id[]\')" title="Check/Uncheck All" />Check/Uncheck All</strong></td></tr>
										<tr class="footer"><td><b>'.STUDENT_ID.'</b></td><td><b>'.WING.'</b></td><td><b>'.RANK.'</b></td><td><b>'.OFFICIAL_NAME.'</b></td><td><b>'.BA_NO.'</b></td></tr>';
				
				$rownum = 0;		
				
				foreach($studentId as $id => $student_id){
					//check whether this student is engaged in any syndicate in this same term
					if($syndicateType == 1){
						$query = "select * from ".DB_PREFIX."student_to_syndicate WHERE student_id='".$id."' AND syndicatetype ='1' AND course_id ='".$course_id."' AND term_id = '".$term_id."'";
					}else{
						$query = "select * from ".DB_PREFIX."student_to_syndicate WHERE student_id='".$id."' AND syndicatetype ='0' AND course_id ='".$course_id."' AND term_id = '".$term_id."'";
					}
					//$query = "select * from ".DB_PREFIX."student_to_syndicate WHERE student_id='".$id."' AND term_id = '".$term_id."'";
					$engagedStudentList = $dbObj->selectDataObj($query);
					//print_r($engagedStudentList);
					$engStudent = $engagedStudentList[0];
					//echo '<pre>';
					//echo $id;
					//print_r($engagedStudentList);
					$readonly = '';
						//echo $student_id;
						//print_r($syn_to_student_Array);
						if(!empty($engStudent) && $syndicate_id != $engStudent->syndicate_id && !in_array($id, $syn_to_student_Array)){
							$readonly = ' disabled="disabled"';
						}
					
					
					$check_str = '';
					if(in_array($id, $syn_to_student_Array)){
						$check_str = ' checked="checked"';
					}	
					
					if(($rownum%2)==0){//even
						$class = ' class="even"';									
					}else{//odd
						$class = ' class="odd"';									
					}
					
					$sql = "select * from ".DB_PREFIX."student WHERE id='".$id."'";
					$std_ary = $dbObj->selectDataObj($sql);
					$std = $std_ary[0];
					$wingName = getNameById('wing', $std->wing_id);
					$rnk = getNameById('rank', $std->rank_id);
					
					$assign_student .= '<tr '.$class.'><td><input type="checkbox" name="student_id[]" id="student_id'.$id.'" value="'.$id.'" '.$check_str .$readonly.' />&nbsp;<b>'.$student_id.'</b></td>
										<td><input type="hidden" name="wing_id['.$id.']" id="wing_id'.$wingName->id.'" value="'.$wingName->id.'" />'.$wingName->name.'</td>
										<td>'.$rnk->name.'</td>
										<td>'.$std->official_name.'</td>
										<td>'.$std->ba_no.'</td></tr>';
					$rownum++;
				}
				
			}	
	
	
			$str = '<table width="100%" cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td valign="top" width="20%">
								'.RELATE_STUDENT.':
							</td>
							<td width="80%">'.$assign_student.'</td>
						</tr>
					</table>';
			echo $str;		
		}//if
	break;
	
	case 'assign_exr_with_term':
		$course_id = $_REQUEST['course_id'];
		$term_id = $_REQUEST['term_id'];
		
		if($term_id != '0' || $term_id == ""){
			
			//Build Exercise Selection Array
			$query = "select id, exercise_id from ".DB_PREFIX."exercise WHERE course_id = '".$course_id."' or join_course = 1 order by exercise_id asc";
			$exrArr = $dbObj->selectDataObj($query);
	
			$exrId = array();
			if(!empty($exrArr)){
				foreach($exrArr as $item){
					$exrId[$item->id] = $item->exercise_id;
				}	
			}		
			
			//Find Existing Exercise from exercise_to_term Table with selected term_id
			$query = "select exercise_id from ".DB_PREFIX."exercise_to_term WHERE term_id='".$term_id."'";
			$existingExrArray = $dbObj->selectDataObj($query);
			
			$exr_to_term_Array = array();	
			if(empty($existingExrArray)){
				//do nothing
			}else{
				$i = 0;
				foreach($existingExrArray as $exr){
					$exr_to_term_Array[$i] = $exr->exercise_id;
					$i++;
				}
			}	
	
			//Show Exercise of Same Course and check whether already 
			//engaged in the selected term or not		
			$assign_exr = '';
			if(!empty($exrId)){	
				
				$assign_exr .= '<table width="100%" cellpadding="0" cellspacing="0" border="0" class="datagrid">
										<tr><td colspan="6"><strong><input type="checkbox" name="check_all" id="check_all" onClick="checkAllItem(\'exercise_id[]\')" title="Check/Uncheck All" />Check/Uncheck All</strong></td></tr>
										<tr class="footer"><td><b>'.EXERCISE_ID.'</b></td><td align="center"><b>'.EXERCISE_NAME.'</b></td><td align="center"><b>'.TYPE.'</b></td><td align="center"><b>'.ASSESSMENT_SYSTEM.'</b></td><td align="center"><b>'.WEIGHT.'</b></td></tr>';

				$rownum = 0;					
				foreach($exrId as $id => $exercise_id){
					$check_str = '';
					if(in_array($id, $exr_to_term_Array)){
						$check_str = ' checked="checked"';
					}	
					
					$sql = "select * from ".DB_PREFIX."exercise WHERE id='".$id."'";
					$exrAry = $dbObj->selectDataObj($sql);
					$exr = $exrAry[0]; 
					$type = getNameById('exercise_type', $exr->type_id);
			
					if(($rownum%2)==0){//even
										$class = ' class="even"';									
									}else{//odd
										$class = ' class="odd"';									
									}
					if($exr->marking_type == '1'){
						$marking_type = PERCENT_BASED;
					}else if($exr->marking_type == '2'){
						$marking_type = MARK_BASED;	
					}		
					$assign_exr .= '<tr '.$class.'><td><input type="checkbox" name="exercise_id[]" id="exercise_id'.$id.'" value="'.$id.'" '.$check_str .' />&nbsp;<b>'.$exercise_id.'</b></td>
													<td>'.$exr->name.'</td>
													<td align="center">'.$type->name.'</td>
													<td align="center">'.$marking_type.'</td>
													<td align="center">'.$exr->weight.'</td>
													</tr>'; 	
					$rownum++;	
				}
			}	
	
	
			$str = '<table width="100%" cellpadding="0" cellspacing="0" border="0" >
						<tr>
							<td valign="top" width="20%">
								'.RELATE_EXERCISE.':
							</td>
							<td width="80%">'.$assign_exr.'</td>
						</tr>
					</table>';
			echo $str;		
		}//if
	break;
	
	case 'assign_si':
		$course_id = $_REQUEST['course_id'];
		
		if($course_id != '0' || $course_id == ""){
			//Find in which wing the course exists
			$query = "select wing_id from ".DB_PREFIX."course WHERE id = '".$course_id."' limit 1";
			$wingArr = $dbObj->selectDataObj($query);
			$wing = $wingArr[0];
						
			//Build SI Selection Array
			$query = "select id, username from ".DB_PREFIX."user WHERE wing_id = '".$wing->wing_id."' AND group_id = '4' order by username asc";
			$siArr = $dbObj->selectDataObj($query);
			
			$siId = array();
			if(!empty($siArr)){
				foreach($siArr as $item){
					$siId[$item->id] = $item->username;
				}	
			}		
			
			//Find Existing SI from si_to_course Table with selected si_id
			$query = "select si_id from ".DB_PREFIX."si_to_course WHERE course_id='".$course_id."'";
			$existingSiArray = $dbObj->selectDataObj($query);
			
			$si_to_course_Array = array();	
			if(empty($existingSiArray)){
				//
			}else{
				$i = 0;
				foreach($existingSiArray as $si){
					$si_to_course_Array[$i] = $si->si_id;
					$i++;
				}
			}	
	
			//Show SI of User's Wing and check whether already 
			//engaged in the selected Course or not		
			$assign_si = '';
			if(!empty($siId)){	
				$assign_si .= '<table width="100%" cellpadding="0" cellspacing="0" border="0" class="datagrid">
									<tr><td colspan="4"><strong><input type="checkbox" name="check_all" id="check_all" onClick="checkAllItem(\'si_id[]\')" title="Check/Uncheck All" />Check/Uncheck All</strong></td></tr>
									<tr class="footer"><td><b>'.USERNAME.'</b></td><td><b>'.RANK.'</b></td><td><b>'.OFFICIAL_NAME.'</b></td><td><b>'.APPOINTMENT.'</b></td></tr>';
				$rownum = 0;					
				foreach($siId as $id => $username){
					$check_str = '';
					if(in_array($id, $si_to_course_Array)){
						$check_str = ' checked="checked"';
					}	
					if(($rownum%2)==0){//even
						$class = ' class="even"';									
					}else{//odd
						$class = ' class="odd"';									
					}
					
					$sql = "select * from ".DB_PREFIX."user WHERE id='".$id."'";
					$SIary = $dbObj->selectDataObj($sql);
					$si = $SIary[0];
					$rnk = getNameById('rank', $si->rank_id);
					$aptm = getNameById('appointment', $si->appointment_id);
					
					$assign_si .= '<tr '.$class.'><td><input type="checkbox" name="si_id[]" id="si_id'.$id.'" value="'.$id.'" '.$check_str .' />&nbsp;<b>'.$username.'</b></td>
										<td>'.$rnk->name.'</td>
										<td>'.$si->official_name.'</td>
										<td>'.$aptm->name.'</td>
										</tr>'; 	
					
					$rownum++;
				}
			}	
	
	
			$str = '<table width="100%" cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td valign="top" width="20%">
								'.ASSIGN_SI.':
							</td>
							<td width="80%">'.$assign_si.'</td>
						</tr>
					</table>';
			echo $str;		
		}//if
	break;
	
	case 'course_to_term_syn':
		$course_id = $_REQUEST['course_id'];
		
		if($course_id == '' || $course_id == '0'){
			//do nothing
		}else{
			//Build Term Array	
			//$query = "select id, name from ".DB_PREFIX."term where course_id = '".$course_id."' OR term_type = 1 order by name asc";
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
			echo $termList_opt.'<span class="required_field"> *</span>';	
			
			echo ':*:';

			//Build Syndicate Array	
			$query = "select syn.id, syn.name from ".DB_PREFIX."syndicate as syn, ".DB_PREFIX."syndicate_to_course as stc WHERE stc.course_id = '".$course_id."' AND syn.id = stc.syndicate_id OR syn.syndicate_type = 1 ORDER BY syn.name asc";
			$synArr = $dbObj->selectDataObj($query);
			
			$synId = array();
			$synId[0] = SELECT_SYNDICATE_OPT;
			if(!empty($synArr)){			
				foreach($synArr as $item){
					$synId[$item->id] = $item->name;
				}	
			}			
			$syndicateList_opt = formSelectElement($synId, $syndicate_id, 'syndicate_id', 'onchange = processFunction("assign_ds")');
			echo $syndicateList_opt.'<span class="required_field"> *</span>';
		}//else

	break;
	
	case 'assign_ds':
		$course_id = $_REQUEST['course_id'];
		$term_id = $_REQUEST['term_id'];
		$syndicate_id = $_REQUEST['syndicate_id'];
		
		$syndicateQuery = "select id, syndicate_type from ".DB_PREFIX."syndicate WHERE id = '".$syndicate_id."'";
		$syndicate = $dbObj->selectDataObj($syndicateQuery);
		$syndicateType = $syndicate[0]->syndicate_type;
		
		if($course_id != '0' || $course_id == ""){
			//Find in which wing the course exists
			$query = "select wing_id from ".DB_PREFIX."course WHERE id = '".$course_id."' limit 1";
			$wingArr = $dbObj->selectDataObj($query);
			$wing = $wingArr[0];
						
			//Build DS Selection Array
			if($syndicateType == 1){
				$query = "select id, username from ".DB_PREFIX."user WHERE group_id = '5' order by username asc";
			}else{
				$query = "select id, username from ".DB_PREFIX."user WHERE wing_id = '".$wing->wing_id."' AND group_id = '5' order by username asc";
			}
			
			$dsArr = $dbObj->selectDataObj($query);
			
			$dsId = array();
			if(!empty($dsArr)){
				foreach($dsArr as $item){
					$dsId[$item->id] = $item->username;
				}	
			}		
			
			//Find Existing DS from ds_to_course Table with selected ds_id
			if($syndicateType == 1){
				$selectedDsquery = "select ds_id from ".DB_PREFIX."ds_to_course WHERE syndicatetype ='1' AND term_id = '".$term_id."'";
			}else{
				$selectedDsquery = "select ds_id from ".DB_PREFIX."ds_to_course WHERE syndicatetype ='0' AND wing_id = '".$wing->wing_id."' AND term_id = '".$term_id."'";
			}
			//$selectedDsquery = "select ds_id from ".DB_PREFIX."ds_to_course WHERE syndicate_id='".$syndicate_id."' AND term_id = '".$term_id."'";
			$existingDsArray = $dbObj->selectDataObj($selectedDsquery);
			//print_r($existingDsArray);
			$ds_to_course_Array = array();	
			if(empty($existingDsArray)){
				//
			}else{
				$i = 0;
				foreach($existingDsArray as $ds){
					$ds_to_course_Array[$i] = $ds->ds_id;
					$i++;
				}
				
			}	
	
			//Show DS of User's Wing and check whether already 
			//engaged in the selected Course or not		
			$assign_ds = '';
			if(!empty($dsId)){	
				$assign_ds .= '<table width="100%" cellpadding="0" cellspacing="0" border="0" class="datagrid">
								<tr><td colspan="5"><strong><input type="checkbox" name="check_all" id="check_all" onClick="checkAllItem(\'ds_id[]\')" title="Check/Uncheck All" />Check/Uncheck All</strong></td></tr>
								<tr class="footer"><td><b>'.USERNAME.'</b></td><td><b>'.WING.'</b></td><td><b>'.RANK.'</b></td><td><b>'.OFFICIAL_NAME.'</b></td><td><b>'.APPOINTMENT.'</b></td></tr>';
				
				$rownum = 0;					
				foreach($dsId as $id => $username){
					//check whether this DS is engaged in any syndicate in this same term
					if($syndicateType == 1){
						$query = "select * from ".DB_PREFIX."ds_to_course WHERE syndicatetype ='1' AND ds_id='".$id."' AND term_id = '".$term_id."'";
					}else{
						$query = "select * from ".DB_PREFIX."ds_to_course WHERE syndicatetype ='0' AND  wing_id = '".$wing->wing_id."' AND ds_id='".$id."' AND term_id = '".$term_id."'";
					}
					$engagedDSList = $dbObj->selectDataObj($query);
					$engDs = $engagedDSList[0];
					/*
					echo '<pre>';
					echo $id;
					print_r($engDs);
					echo $syndicate_id;
					print_r($ds_to_course_Array);
					*/
					$readonly = '';
					
					if(!empty($engDs) && $syndicate_id != $engDs->syndicate_id){
						$readonly = ' disabled="disabled"';
					}
					
					$check_str = '';
					
					if(in_array($id, $ds_to_course_Array)){
						$check_str = ' checked="checked"';
					}	
					
					$sql = "select * from ".DB_PREFIX."user WHERE id='".$id."'";
					$DSary = $dbObj->selectDataObj($sql);
					$ds = $DSary[0];
					$rnk = getNameById('rank', $ds->rank_id);
					$wingName = getNameById('wing', $ds->wing_id);
					$aptm= getNameById('appointment', $ds->appointment_id);
					if(($rownum%2)==0){//even
						$class = ' class="even"';									
					}else{//odd
						$class = ' class="odd"';									
					}
					
					$assign_ds .= '<tr '.$class.'><td><input type="checkbox" name="ds_id[]" id="ds_id'.$id.'" value="'.$id.'" '. $check_str . $readonly .' />&nbsp;<b>'.$username.'</b></td>
										<td><input type="hidden" name="wing_id['.$id.']" id="wing_id'.$wingName->id.'" value="'.$wingName->id.'" />'.$wingName->name.'</td>
										<td>'.$rnk->name.'</td>
										<td>'.$ds->official_name.'</td>
										<td>'.$aptm->name.'</td>
										</tr>'; 	
					$rownum++;
				}
			}	
	
	
			$str = '<table width="100%" cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td valign="top" width="20%">
								'.ASSIGN_DS.':
							</td>
							<td width="80%">'.$assign_ds.'</td>
						</tr>
					</table>';
			echo $str;		
		}//if
	break;
	
	case 'assign_term_si_mark':	
		$course_id = $_REQUEST['course_id'];

		//Build Term List Array	
		$query = "select id, name from ".DB_PREFIX."term where course_id = ".$course_id." order by name asc";
		$termArr = $dbObj->selectDataObj($query);
		$termId = array();
		$termId[0] = SELECT_TERM_OPT;
		if(!empty($termArr)){			
			foreach($termArr as $item){
				$termId[$item->id] = $item->name;
			}	
		}			
		$termList_opt = formSelectElement($termId, $term_id, 'term_id', 'onchange = processFunction("si_impr_mark")');
		
		echo $termList_opt.'<span class="required_field"> *</span>';
		
	break;
	
	case 'si_impr_mark':	
		$course_id = $_REQUEST['course_id'];
		$sql = "select * from ".DB_PREFIX."course WHERE id='".$course_id."'";	
		$courseList = $dbObj->selectDataObj($sql);
		$course = $courseList[0];
		$si_impr_mark = $course->si_impr_mark;
		$si_impr_mark_limit = $course->si_impr_mark_limit; 
		
		$str = '<table width="100%" cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td height="30" width="20%">
								'.SI_IMPRES_MARK.':
							</td>
							<td width="80%">
								<input name="si_impr_mark" maxlength="4" id="si_impr_mark" type="text" class="inputbox" alt="SI Impression Mark" size="18" value="'. $si_impr_mark.'" onkeyup="return isNUM(\'si_impr_mark\')" />
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								'.MARK_LIMIT.':
							</td>
							<td width="80%">
								<input name="si_impr_mark_limit" maxlength="3" id="si_impr_mark_limit" type="text" class="inputbox" alt="SI Impression Mark Limit" size="18" value="'. $si_impr_mark_limit.'" onkeyup="return isNUM(\'si_impr_mark_limit\')" />
								<span class="required_field">*</span>
							</td>
						</tr>
					</table>';
		echo $str;	
		
	break;
	
	case 'assign_term_ds_mark':	
		$course_id = $_REQUEST['course_id'];

		//Build Term List Array	
		$query = "select id, name from ".DB_PREFIX."term where course_id = ".$course_id." order by name asc";
		$termArr = $dbObj->selectDataObj($query);
		$termId = array();
		$termId[0] = SELECT_TERM_OPT;
		if(!empty($termArr)){			
			foreach($termArr as $item){
				$termId[$item->id] = $item->name;
			}	
		}			
		$termList_opt = formSelectElement($termId, $term_id, 'term_id', 'onchange = processFunction("ds_impr_mark")');
		
		echo $termList_opt.'<span class="required_field"> *</span>';
		
	break;
	
	case 'ds_impr_mark':	
		$term_id = $_REQUEST['term_id'];
		$sql = "select * from ".DB_PREFIX."term WHERE id='".$term_id."'";	
		$termList = $dbObj->selectDataObj($sql);
		$term = $termList[0];
		$ds_impr_mark = $term->ds_impr_mark;
		$ds_impr_mark_limit = $term->ds_impr_mark_limit; 
		
		$str = '<table width="100%" cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td height="30" width="20%">
								'.DS_IMPRES_MARK.':
							</td>
							<td width="80%">
								<input name="ds_impr_mark" maxlength="4" id="ds_impr_mark" type="text" class="inputbox" alt="DS Impression Mark" size="18" value="'. $ds_impr_mark.'" onkeyup="return isNUM(\'ds_impr_mark\')" />
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								'.MARK_LIMIT.':
							</td>
							<td width="80%">
								<input name="ds_impr_mark_limit" maxlength="3" id="ds_impr_mark_limit" type="text" class="inputbox" alt="DS Impression Mark Limit" size="18" value="'. $ds_impr_mark_limit.'" onkeyup="return isNUM(\'ds_impr_mark_limit\')" />
								<span class="required_field">*</span>
							</td>
						</tr>
					</table>';
		echo $str;	
		
	break;
	
	case 'assign_term_for_course':
		$course_id = $_REQUEST['course_id'];
		
		if($course_id != '0' || $course_id == ""){
			//Find in which wing the course exists
			$query = "select wing_id from ".DB_PREFIX."course WHERE id = '".$course_id."' limit 1";
			$wingArr = $dbObj->selectDataObj($query);
			$wing = $wingArr[0];
			
			//Build Term Selection Array
			$query = "select id, name from ".DB_PREFIX."term WHERE wing_id = '".$wing->wing_id."' AND entry_status = '0' order by name asc";
			$termArr = $dbObj->selectDataObj($query);
	
			$termId = array();
			if(!empty($termArr)){
				foreach($termArr as $item){
					$termId[$item->id] = $item->name;
				}	
			}		
			
			//Show Term of User's Wing and check whether already 
			//engaged in the selected Course or not		
			$assign_term = '';
			if(!empty($termId)){	
				$term_count = 1;
				
				$assign_term .= '<strong><input type="checkbox" name="check_all" id="check_all" onClick="checkAllItem(\'term_id[]\')" title="Check/Uncheck All" />Check/Uncheck All</strong>
									<br />';
									
				foreach($termId as $id => $name){
					$assign_term .= '<input type="checkbox" name="term_id[]" id="term_id'.$id.'" value="'.$id.'" />&nbsp;<b>'.$name.'</b>'; 	
					
					if(($term_count%3) == 0){
						$assign_term .= '<br />';
					}
					
					$term_count++;
				}
			}	
	
	
			$str = '<table width="100%" cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td valign="top" width="20%">
								'.RELATE_TERM.':
							</td>
							<td width="80%">'.$assign_term.'</td>
						</tr>
					</table>';
			echo $str;		
		}//if
	break;
	
	case 'assign_ds_mark':
		$exercise_id = $_REQUEST['exercise_id'];
		$term_id = $_REQUEST['term_id'];
		$syndicate_id = $_REQUEST['syndicate_id'];
		$selectedSyndicateId = $_REQUEST['syndicate_id'];
		if($exercise_id != '0' || $exercise_id == ""){
			
			
			//Find exercise info
			$query = "select * from ".DB_PREFIX."exercise WHERE id = '".$exercise_id."'";
			$exrArr = $dbObj->selectDataObj($query);
			$exercise = $exrArr[0];
			
			//Find Exercise Type Name
			$type = getNameById('exercise_type', $exercise->type_id);
			
			if($exercise->marking_type == '2'){
				$assess_sys = MARK_BASED;
			}else if($exercise->marking_type == '1'){
				$assess_sys = PERCENT_BASED;
			}//if 
			
						
			$str = '<table width="100%" cellpadding="0" cellspacing="0" border="1">
						<tr>
							<td align="center" width="20%" colspan="6"><strong>'.EXERCISE_INFO.'</strong></td>
						</tr>
						<tr>
							<td align="center" width="20%">'.NAME.'</td>
							<td align="center" width="20%">'.EXERCISE_ID.'</td>
							<td align="center" width="20%">'.TYPE.'</td>
							<td align="center" width="20%">'.ASSESSMENT_SYSTEM.'</td>';
						if($exercise->marking_type == '2'){
			$str .=			'<td align="center" width="10%">'.MKS.'</td>';
						}	
			$str .= 		'<td align="center" width="10%">'.WT.'</td>
						</tr>
						<tr>
							<td align="center">'.$exercise->name.'</td>
							<td align="center">'.$exercise->exercise_id.'</td>
							<td align="center">'.$type->name.'</td>
							<td align="center">'.$assess_sys.'</td>';
						if($exercise->marking_type == '2'){
			$str .=			'<td align="center">'.$exercise->mark.'</td>';
						}	
			$str .=			'<td align="center">'.$exercise->weight.'</td>
						</tr>
					</table>';
			echo $str;	
			
			echo ':*:';
			
			//Find Info of this Exercise ID
			$query = "select * from ".DB_PREFIX."exercise WHERE id = '".$exercise_id."' ";
			$exrArr = $dbObj->selectDataObj($query);
			$exrcise = $exrArr[0];
			$total_mark = $exercise->mark;
			$weight = $exercise->weight;
			$markingType = $exercise->marking_type;
			
			//Find Currently Active Course AND Syndicate which is assigned to this DS
			$sql = "select dtc.course_id, dtc.syndicate_id, dtc.term_id from ".DB_PREFIX."ds_to_course as dtc, ".DB_PREFIX."course as crs, ".DB_PREFIX."term as trm WHERE dtc.wing_id = '".$cur_user_wing_id."' AND dtc.ds_id = '".$cur_user_id."' AND crs.status = '0' AND crs.id = dtc.course_id AND trm.status = '0' AND trm.id = dtc.term_id";
			$activeCourse = $dbObj->selectDataObj($sql);
			$course = $activeCourse[0];
			//echo '<pre>';
			//print_r($course);
			$course->syndicate_id = $syndicate_id;
			$syndicateQuery = "select id, syndicate_type from ".DB_PREFIX."syndicate WHERE id = '".$course->syndicate_id."'";
			$syndicate = $dbObj->selectDataObj($syndicateQuery);
			$syndicateType = $syndicate[0]->syndicate_type;
			
			if($course->term_id == 0 || $course->term_id == ""){
				$add_term = "";
			}else{
				$add_term = " AND stc.term_id = '".$course->term_id."' ";
			}
			
			if($course->syndicate_id == 0 || $course->syndicate_id == ""){
				$add_syndicate = "";
			}else{
				$add_syndicate = " AND stc.syndicate_id = '".$course->syndicate_id."' ";
			}
			
			
			if($syndicateType == 1){
				$courseIdArr = '';
				$courseQuery = "select session from ".DB_PREFIX."course WHERE id = '".$course->course_id."'";
				$coursesSession = $dbObj->selectDataObj($courseQuery);
				$coursesSession = $coursesSession[0]->session;
				$courseArrQuery = "select id from ".DB_PREFIX."course WHERE session = '".$coursesSession."'";
				$coursesArr = $dbObj->selectDataObj($courseArrQuery);
				foreach($coursesArr as $courseID){
					$courseIdArr[] = $courseID->id;
				}
				
				$courseIdArr = join(', ', $courseIdArr);
				/*
				$query = "select std.official_name, std.student_id, std.id, std.rank_id,wng.name wng from ".DB_PREFIX."student as std,  ".DB_PREFIX."student_to_syndicate as sts, ".DB_PREFIX."wing as wng WHERE std.course_id IN (".$courseIdArr.") AND sts.syndicate_id = '".$course->syndicate_id."' AND sts.term_id = '".$course->term_id."' AND sts.student_id = std.id ORDER BY wng.name ASC";
				*/
				$armyCourse = "select crs.id from ".DB_PREFIX."course as crs WHERE crs.wing_id = 1 AND crs.status = '0'";
				$armyCourse = $dbObj->selectDataObj($armyCourse);
				//echo '<pre>';
				//print_r($armyCourse[0]->id);
				$armyCourseIdForJoinSyn = $armyCourse[0]->id;
				$termQuery = "select trm.id, trm.name from ".DB_PREFIX."term as trm WHERE trm.wing_id = 1 AND trm.course_id = '".$armyCourseIdForJoinSyn."' AND trm.status = '0'";
				$armyTerm = $dbObj->selectDataObj($termQuery);
				//print_r($armyTerm);
				$joinActiveTerm = $armyTerm[0]->id;
				//$query = "select std.official_name, std.student_id, std.id, std.course_id, std.rank_id,wng.name wng from ".DB_PREFIX."student as std, ".DB_PREFIX."student_to_syndicate as stc, ".DB_PREFIX."wing as wng where stc.course_id IN (".$courseIdArr.") AND std.wing_id = wng.id  AND stc.student_id = std.id AND stc.term_id = '".$term_id."' ".$add_syndicate." order by wng.name asc";
				$query = "select std.official_name, std.student_id, std.id, std.course_id, std.rank_id,wng.name wng from ".DB_PREFIX."student as std, ".DB_PREFIX."student_to_syndicate as stc, ".DB_PREFIX."wing as wng where stc.course_id IN (".$courseIdArr.") AND std.wing_id = wng.id  AND stc.student_id = std.id AND stc.term_id = '".$joinActiveTerm."' ".$add_syndicate." order by stc.student_id asc";
			}else{
				
				$armyCourse = "select crs.id from ".DB_PREFIX."course as crs WHERE crs.wing_id = 1 AND crs.status = '0'";
				$armyCourse = $dbObj->selectDataObj($armyCourse);
				//echo '<pre>';
				//print_r($armyCourse[0]->id);
				$armyCourseIdForJoinSyn = $armyCourse[0]->id;
				$termQuery = "select trm.id, trm.name from ".DB_PREFIX."term as trm WHERE trm.wing_id = 1 AND trm.course_id = '".$armyCourseIdForJoinSyn."' AND trm.status = '0'";
				$armyTerm = $dbObj->selectDataObj($termQuery);
				//print_r($armyTerm);
				$joinActiveTerm = $armyTerm[0]->id;
				
				
				//$query = "select std.official_name, std.student_id, std.id, std.course_id, std.rank_id,wng.name wng from ".DB_PREFIX."student as std,  ".DB_PREFIX."student_to_syndicate as sts, ".DB_PREFIX."wing as wng WHERE std.wing_id = '".$cur_user_wing_id."' AND std.course_id = '".$course->course_id."' AND sts.syndicate_id = '".$course->syndicate_id."' AND sts.term_id = '".$course->term_id."' AND sts.student_id = std.id ORDER BY std.student_id";
				//$query = "select std.official_name, std.student_id, std.id, std.rank_id,wng.name wng from ".DB_PREFIX."student as std,  ".DB_PREFIX."student_to_syndicate as sts, ".DB_PREFIX."wing as wng WHERE std.wing_id = '".$cur_user_wing_id."' AND sts.syndicate_id = '".$course->syndicate_id."' AND sts.term_id = '".$course->term_id."' AND sts.student_id = std.id ORDER BY wng.name ASC";
				//$query = "select std.official_name, std.student_id, std.id, std.course_id, std.rank_id,wng.name wng from ".DB_PREFIX."student as std, ".DB_PREFIX."student_to_syndicate as stc, ".DB_PREFIX."wing as wng where stc.course_id = '".$course->course_id."' AND std.wing_id = wng.id  AND stc.student_id = std.id AND stc.term_id = '".$term_id."' ".$add_syndicate." order by wng.name asc";
				$query = "select std.official_name, std.student_id, std.id, std.course_id, std.rank_id,wng.name wng from ".DB_PREFIX."student as std, ".DB_PREFIX."student_to_syndicate as stc, ".DB_PREFIX."wing as wng where std.wing_id = wng.id  AND stc.student_id = std.id AND stc.term_id = '".$term_id."' ".$add_syndicate." order by stc.student_id asc";
				
			}
			
			//Find Student info  -- the student who are assigned in this same term & syndicate
			
			$studentArr = $dbObj->selectDataObj($query);
			//echo '<pre>';
			//print_r($studentArr);
			$studentId = array();
			if(!empty($studentArr)){	
				foreach($studentArr as $item){
					$studentId[$item->id] = $item->student_id;
				}//foreach
			}//if
			
			//Find Currently Active Term which is assigned to this DS
			//$sql = "select dtc.term_id from ".DB_PREFIX."ds_to_course as dtc, ".DB_PREFIX."term as trm WHERE dtc.wing_id = '".$cur_user_wing_id."' AND dtc.ds_id = '".$cur_user_id."' AND trm.status = '0' AND trm.id = dtc.term_id limit 1";
			$sql = "select trm.id as term_id from ".DB_PREFIX."term as trm WHERE trm.wing_id = '".$cur_user_wing_id."' AND trm.status = '0' limit 1";
			$activeTerm = $dbObj->selectDataObj($sql);
			$term = $activeTerm[0];
			//Find out if this exercise marks has been moderated by SI & CI (Especially locked by CI)
			$query = "select * from ".DB_PREFIX."ci_marking_lock WHERE course_id = '".$exrcise->course_id."' AND term_id = '".$term->term_id."' AND exercise_id = '".$exercise_id."' AND wing_id = '".$cur_user_wing_id."'";
			$moderatedArr = $dbObj->selectDataObj($query);
			$moderatedMsg = (!empty($moderatedArr))? MARKS_MODERATED : MARKS_NOT_MODERATED; 
			
			//This code find position of a student in an exercise
			//$sql = " SELECT distinct ds_marking as total from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise_id."' AND term_id = '".$term->term_id."' AND course_id = '".$course->course_id."' AND syndicate_id = '".$course->syndicate_id."' ORDER BY total DESC";
			if($syndicateType == 1){
				$sql = " SELECT distinct ds_marking as total from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise_id."' AND term_id = '".$term->term_id."' AND course_id IN (".$courseIdArr.") AND syndicate_id = '".$course->syndicate_id."' ORDER BY total DESC";
			}else{
				$sql = " SELECT distinct ds_marking as total from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise_id."' AND term_id = '".$term->term_id."' AND course_id = '".$course->course_id."' AND syndicate_id = '".$course->syndicate_id."' ORDER BY total DESC";
			}
			
			$numberArray = $dbObj->selectDataObj($sql);
			//echo '<pre>';
			//print_r($numberArray);
			if(!empty($numberArray)){
				$i = 0;
				foreach($numberArray as $item){
					$numberArray[$i]->position = $i+1;
					$i++; 
				}//foreach
			}//if
			//print_r($numberArray);
			$str = '<form action="ds_marking.php" method="post" name="ds_marking" id="ds_marking" >
					<table cellpadding="0" cellspacing="0" border="0" class="module_content">
						<tr>
							<td>
								<table cellpadding="0" cellspacing="0" border="0" class="datagrid" width="700">
									<tr class="head">
										<td align="center" width="10%"><strong>'.SER_NO.'</strong></td>
										<td align="center" width="10%"><strong>'.CN.'</strong></td>
										<td width="10%"><strong>'.WING.'</strong></td>
										<td width="10%"><strong>'.RANK.'</strong></td>
										<td width="20%"><strong>'.STUDENT_NAME.'</strong></td>
										<td align="center" width="20%"><strong>'.PERCENT.'</strong></td>';
									if($exercise->marking_type == '2'){		//2 means mark Based
			$str .=						'<td align="center" width="20%"><strong>'.MKS.'</strong></td>';
									}	
			$str .=						'<td align="center" width="10%"><strong>'.WT.'</strong></td>
										<td align="center" width="10%"><strong>'.GRADE.'</strong></td>
										<td align="center" width="10%"><strong>'.POSITION.'</strong></td>
									</tr>';
								if(!empty($studentArr)){
									$rownum = 0;
									$sl = 0;
									foreach($studentArr as $student){
										//echo '<pre>';
										//print_r($student);
										$sl = $rownum + 1;
										$class = (($rownum%2)==0) ? ' class="even"' : ' class="odd"';
										
									$sql = "select * from ".DB_PREFIX."rank WHERE id = '".$student->rank_id."'";
									$rankArr = $dbObj->selectDataObj($sql);
									$rank = $rankArr[0];
									$course_id = $student->course_id;
									$rankName = getNameById('rank', $rank->id);
									
						$str .= '	<tr '.$class.'>
										<td align="center">'.$sl.'</td>
										<td align="center">'.$student->student_id.'</td>
										<td>'.$student->wng.'</td>
										<td>'.$rankName->name.'</td>
										<td>'.$student->official_name.'</td>';
										
						
						$exercise_type_query = "select join_course from ".DB_PREFIX."exercise WHERE id = '".$exercise_id."'";
						$exercise_type_check = $dbObj->selectDataObj($exercise_type_query);
						$exercise_type = $exercise_type_check[0]->join_course;
				
						//Find number of each student exercise if already inserted in same term & course
						if($syndicateType == 1 || $exercise_type == 1){
							$sql = "select * from ".DB_PREFIX."marking WHERE course_id = '".$course_id."' AND exercise_id = '".$exercise_id."' AND student_id = '".$student->id."' AND term_id = '".$joinActiveTerm."' order by id desc";
						}else{
							$sql = "select * from ".DB_PREFIX."marking WHERE course_id = '".$course_id."' AND exercise_id = '".$exercise_id."' AND student_id = '".$student->id."' AND term_id = '".$term->term_id."'  order by id desc";
						}
						$number = $dbObj->selectDataObj($sql);
						
						$student_number = $number[0];
						$percent_mark = $student_number->ds_marking;
						//echo '<pre>';
						//print_r($number);
						if($markingType == '2' && empty($student_number)){		//This is to initiate the marking fields as Zero (0)
							$mark = '';
						}else{
							$mark = view_number_two(($percent_mark*$total_mark)/100);
						}
						//$exercise->marking_type == '2'
						$weighted_mark = ($percent_mark*$weight)/100;
						$gradeNumber = $student_number->ds_marking;
						
						if($exercise->marking_type == '1'){		//1 means percent Based 
						$str .= '		<td align="center">
											<input style="text-align:right; border:1px solid #000000;"  type="text" name="number_'.$student->id.'" id="number'.$sl.'" value="'.view_number_two($percent_mark).'" maxlength="6" size="6" onkeyup="return validateMarking(\'number'.$sl.'\');" '; if($student_number->status == '1' || ($syndicateType != 1 && $exercise_type == 1)){$str .= 'readonly="readonly" class="input_number"'; }	$str .= ' />
										</td>';
						}else{
						$str .= '		<td align="center">
											<input class="input_number" type="text" name="number_'.$student->id.'" id="number'.$sl.'" value="'.view_number_two($percent_mark).'" size="6" readonly="readonly" />
										</td>
										<td align="center">
											<input style="text-align:right; border:1px solid #000000;" type="text" name="mark_number'.$student->id.'" id="mark_number'.$sl.'" value="'.$mark.'" maxlength="6" size="6" onkeyup="return validateDirectMarking(\'number'.$sl.'\');" '; if($student_number->status == '1' || ($syndicateType != 1 && $exercise_type == 1)){$str .= 'readonly="readonly" class="input_number"'; }	$str .= ' />
										</td>';
						}
						$str .= '		<td align="center">
											<input class="input_number" type="text" name="weight_number'.$sl.'" id="weight_number'.$sl.'" value="'.view_number_two($weighted_mark).'" size="6" readonly="readonly" />
										</td>
										<td align="center">
											<input class="center_input" type="text" name="grade_number'.$sl.'" id="grade_number'.$sl.'" value="'.findGrade($gradeNumber).'" size="6" readonly="readonly" />
										</td>
										<td align="center" id="position">
											<input class="center_input" type="text" name="position'.$sl.'" id="position'.$sl.'" value="'.findPosition($numberArray, $percent_mark).'" size="5" readonly="readonly" />
										</td>
									</tr>';
						$str .= '<input type="hidden" name="join_course_id[]" id="join_course_id'.$num_count.'" value="'.$course_id.'" />';
						$str .= '<input type="hidden" name="marking_syndicate_id" id="marking_syndicate_id" value="'.$syndicate_id.'" />';
										$rownum++;
									}//foreach

						$str .= '	<tr class="head">
										<td colspan="10">'.$moderatedMsg;
						//$sql = "select * from ".DB_PREFIX."marking_lock WHERE exercise_id = '".$exercise_id."' AND course_id = '".$exercise->course_id."' AND term_id = '".$term->term_id."' AND syndicate_id = '".$course->syndicate_id."' ";
						//$sql = "select * from ".DB_PREFIX."marking_lock WHERE exercise_id = '".$exercise_id."' AND course_id = '".$course_id."' AND term_id = '".$term->term_id."' AND syndicate_id = '".$course->syndicate_id."' ";
						$sql = "select * from ".DB_PREFIX."marking_lock WHERE exercise_id = '".$exercise_id."' AND course_id = '".$course_id."' AND term_id = '".$term->term_id."' AND syndicate_id = '".$course->syndicate_id."' ";
						
						$submitStatus = $dbObj->selectDataObj($sql);
						$submit = $submitStatus[0];
						$locked_datetime = $submit->locked_datetime;
						$locked_date_time = explode(' ', $locked_datetime);
						$locked_date = $locked_date_time[0];
						$locked_time = $locked_date_time[1];
						
						$DSName = getNameById('user', $submit->locked_by);
						if(($submit->status == '0') || empty($submitStatus)){
							if($syndicateType == 1){
								$str .= '		<input type="submit" name="Submit" class="button" value="Save" />
											<input type="submit" name="Submit" class="button" value="Delete" />
											<input type="hidden" name="SyndicateType" value=1 />';
							}else{
								$str .= '		<input type="submit" name="Submit" class="button" value="Save" />
											<input type="submit" name="Submit" class="button" value="Delete" />
											<input type="submit" name="Submit" class="button" value="Forward" onclick="return validateDSmark('.$markingType.');" />
											<input type="hidden" name="SyndicateType" value=0 />';
							}
							
						}else if($submit->status == '1'){	
							if($syndicateType == 1){
								$str .= '<input type="hidden" name="SyndicateType" value=1 />';
							}else{
								$str .= '<input type="hidden" name="SyndicateType" value=0 />';
							}
							$str .= '		<input type="button" value="'.REQUEST_TO_UNLOCK.'" onclick="return submitRequest();">
											<span class="sentMsg">'.SENT_MESSAGE.$DSName->official_name.' on '.dateConvertion($locked_date).' at '.$locked_time.'</span>
											<div id="submitRequest">
												<div name="submit_req" id="submit_req" action="ds_marking.php" method="post">
													<textarea name="comment" id="comment" rows="5" cols="27" class="inputbox" alt="Comment"></textarea>
													<br  />
													<input type="hidden" name="action" value="save"  />
													<input type="submit" name="Submit" id="Submit"  value="Request to Unlock"  />
													<input type="button" name="btnclose" id="btnclose"  value="'.CANCEL.'"  onclick=Close("submitRequest") />											
												</div>						
											</div>';
						}else{
							$str .= '		'.REQUEST_MESSAGE.$DSName->official_name;
						}
						$str .= '		</td>
									</tr>';
							}else{//if empty StudentArr
						$str .= '<tr>
									<td height="30" colspan="9">'.EMPTY_DATA.'</td>
								</tr>';							
							}
						$str .=	'</table>
							</td>
						</tr>
					</table>';
					foreach($studentId as $id => $num_count){
						$str .= '<input type="hidden" name="student_id[]" id="student_id'.$num_count.'" value="'.$id.'" />';
						
					}
	$str	.= '	<input type="hidden" name="course_id" value="'.$course->course_id.'" />
					<input type="hidden" name="exercise_id" value="'.$exercise_id.'" />
					<input type="hidden" name="sl" id="sl" value="'.$sl.'" />
					<input type="hidden" name="mark" id="mark" value="'.$exrcise->mark.'" />
					<input type="hidden" name="weight" id="weight" value="'.$exrcise->weight.'" />
					<input type="hidden" name="marking_type" id="marking_type" value="'.$markingType.'" />
					<input type="hidden" name="action" value="save" />
					</form>';

			echo $str;		
		}//if
	break;
	
	case 'si_mod_mark_term':	
		$course_id = $_REQUEST['course_id'];

		//Build Term List Array	
		$query = "select id, name from ".DB_PREFIX."term where course_id = ".$course_id." AND status = '0' order by name asc";
		$termArr = $dbObj->selectDataObj($query);
		$termId = array();
		$termId[0] = SELECT_TERM_OPT;
		if(!empty($termArr)){			
			foreach($termArr as $item){
				$termId[$item->id] = $item->name;
			}	
		}			
		$termList_opt = formSelectElement($termId, $term_id, 'term_id', 'onchange = processFunction("si_mod_mark_exr")');
		
		echo $termList_opt.'<span class="required_field"> *</span>';
		
	break;
	
	case 'si_result_term':	
		$course_id = $_REQUEST['course_id'];

		//Build Term List Array	
		$query = "select id, name from ".DB_PREFIX."term where course_id = ".$course_id." order by name asc";
		$termArr = $dbObj->selectDataObj($query);
		$termId = array();
		$termId[0] = SELECT_TERM_OPT;
		if(!empty($termArr)){			
			foreach($termArr as $item){
				$termId[$item->id] = $item->name;
			}	
		}			
		$termList_opt = formSelectElement($termId, $term_id, 'term_id', 'onchange = processFunction("si_result_exr")');
		
		echo $termList_opt.'<span class="required_field"> *</span>';
		
		echo ':*:';
		
		//Build Syndicate List Array	
		$query = "select syn.id, syn.name from ".DB_PREFIX."syndicate as syn, ".DB_PREFIX."syndicate_to_course as stc where syn.wing_id = ".$cur_user_wing_id." or syn.syndicate_type = 1 AND stc.course_id = '".$course_id."' AND syn.id = stc.syndicate_id order by syn.name asc";
		$synArr = $dbObj->selectDataObj($query);

		$synId = array();
		$synId[0] = SELECT_SYNDICATE_OPT;
		if(!empty($synArr)){			
			foreach($synArr as $item){
				$synId[$item->id] = $item->name;
			}	
		}			
		$synList_opt = formSelectElement($synId, $syndicate_id, 'syndicate_id');
		
		echo $synList_opt;
	break;
	
	case 'si_report_term':	
		$course_id = $_REQUEST['course_id'];

		//Build Term List Array	
		$query = "select id, name from ".DB_PREFIX."term where course_id = ".$course_id." order by name asc";
		$termArr = $dbObj->selectDataObj($query);
		$termId = array();
		$termId[0] = SELECT_TERM_OPT;
		if(!empty($termArr)){			
			foreach($termArr as $item){
				$termId[$item->id] = $item->name;
			}	
		}			
		$termList_opt = formSelectElement($termId, $term_id, 'term_id');
		
		echo $termList_opt.'<span class="required_field"> *</span>';
		
		echo ':*:';
		
		//Build Syndicate List Array	
		$query = "select syn.id, syn.name from ".DB_PREFIX."syndicate as syn, ".DB_PREFIX."syndicate_to_course as stc where (syn.wing_id = ".$cur_user_wing_id." OR syn.syndicate_type = 1) AND stc.course_id = '".$course_id."' AND syn.id = stc.syndicate_id order by syn.name asc";
		$synArr = $dbObj->selectDataObj($query);

		$synId = array();
		$synId[0] = SELECT_SYNDICATE_OPT;
		if(!empty($synArr)){			
			foreach($synArr as $item){
				$synId[$item->id] = $item->name;
			}	
		}			
		$synList_opt = formSelectElement($synId, $syndicate_id, 'syndicate_id');
		
		echo $synList_opt;
		
	break;
	
	case 'si_result_exr':	
		$term_id = $_REQUEST['term_id'];

		//Build Exercise List Array	
		$sql = "select exr.id, exr.name from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where ett.term_id = ".$term_id." AND ett.exercise_id = exr.id order by exr.name asc";
		$exerciseArr = $dbObj->selectDataObj($sql);
		$exerciseId = array();
		$exerciseId[0] = SELECT_EXERCISE_OPT;
		if(!empty($exerciseArr)){			
			foreach($exerciseArr as $item){
				$exerciseId[$item->id] = $item->name;
			}	
		}			
		$exerciseList_opt = formSelectElement($exerciseId, $exercise_id, 'exercise_id');
		
		echo $exerciseList_opt.'<span class="required_field"> *</span>';
		
	break;
	
	//though, the upper case and this case are same, it is important to keep them as separate
	//as they show * (must be selcted) vary
	case 'si_exercise_list_for_progressive_result':	
		$term_id = $_REQUEST['term_id'];

		//Build Exercise List Array	
		$sql = "select exr.id, exr.name from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where ett.term_id = ".$term_id." AND ett.exercise_id = exr.id order by exr.name asc";
		$exerciseArr = $dbObj->selectDataObj($sql);
		$exerciseId = array();
		$exerciseId[0] = SELECT_EXERCISE_OPT;
		if(!empty($exerciseArr)){			
			foreach($exerciseArr as $item){
				$exerciseId[$item->id] = $item->name;
			}	
		}			
		$exerciseList_opt = formSelectElement($exerciseId, $exercise_id, 'exercise_id');
		
		echo $exerciseList_opt;
		
	break;
	
	case 'result_syndicate':	
		$course_id = $_REQUEST['course_id'];

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
		
		echo $synList_opt;
		
	break;
	
	case 'si_mod_mark_exr':	
		$term_id = $_REQUEST['term_id'];

		//Build Exercise List Array	
		$sql = "select exr.id, exr.name from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where ett.term_id = ".$term_id." AND ett.exercise_id = exr.id order by exr.name asc";
		$exerciseArr = $dbObj->selectDataObj($sql);
		$exerciseId = array();
		$exerciseId[0] = SELECT_EXERCISE_OPT;
		if(!empty($exerciseArr)){			
			foreach($exerciseArr as $item){
				$exerciseId[$item->id] = $item->name;
			}	
		}			
		$exerciseList_opt = formSelectElement($exerciseId, $exercise_id, 'exercise_id', 'onchange = processFunction("assign_si_mark")');
		
		echo $exerciseList_opt.'<span class="required_field"> *</span>';
		
	break;
	
	case 'assign_si_mark':
		$exercise_id = $_REQUEST['exercise_id'];
		$term_id = $_REQUEST['term_id'];
		$course_id = $_REQUEST['course_id'];
		
		if($term_id != '0' || $course_id != '0' || $term_id != '0'){
		
			//Find exercise info
			$query = "select * from ".DB_PREFIX."exercise WHERE id = '".$exercise_id."'";
			$exrArr = $dbObj->selectDataObj($query);
			$exercise = $exrArr[0];
			$marking_type = $exercise->marking_type;
			
			//Find Exercise Type Name
			$type = getNameById('exercise_type', $exercise->type_id);
						
			if($exercise->marking_type == '2'){
				$assess_sys = MARK_BASED;
			}else if($exercise->marking_type == '1'){
				$assess_sys = PERCENT_BASED;
			}
						
			$str = '<table width="100%" cellpadding="0" cellspacing="0" border="1">
						<tr>
							<td align="center" width="20%" colspan="6"><strong>'.EXERCISE_INFO.'</strong></td>
						</tr>
						<tr>
							<td align="center" width="20%">'.NAME.'</td>
							<td align="center" width="20%">'.EXERCISE_ID.'</td>
							<td align="center" width="20%">'.TYPE.'</td>
							<td align="center" width="20%">'.ASSESSMENT_SYSTEM.'</td>';
					if($marking_type == '2'){
			$str .=			'<td align="center" width="10%">'.MKS.'</td>';
					}
			$str .=			'<td align="center" width="10%">'.WT.'</td>
						</tr>
						<tr>
							<td align="center">'.$exercise->name.'</td>
							<td align="center">'.$exercise->exercise_id.'</td>
							<td align="center">'.$type->name.'</td>
							<td align="center">'.$assess_sys.'</td>';
					if($marking_type == '2'){
				$str .=		'<td align="center">'.$exercise->mark.'</td>';
					}
				$str .=		'<td align="center">'.$exercise->weight.'</td>
						</tr>
					</table>';
			echo $str;	
			
			echo ':*:';
			
			//Find Info of this Exercise ID
			$query = "select * from ".DB_PREFIX."exercise WHERE id = '".$exercise_id."' ";
			$exrArr = $dbObj->selectDataObj($query);
			$exrcise = $exrArr[0];
			$joinExercise = $exrcise->join_course;
			
			$weight = $exercise->weight;
			
			//Find All Student ID and number from dscdc_marking Table by this exercise ID
			//$sql = "select mrk.* from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."syndicate as syn WHERE mrk.exercise_id = '".$exercise_id."' AND mrk.term_id = '".$term_id."' AND mrk.course_id = '".$course_id."' AND mrk.status = '1' AND syn.id = mrk.syndicate_id ORDER BY syn.name ";
			
			$sql = "select mrk.* from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."syndicate as syn WHERE mrk.exercise_id = '".$exercise_id."' AND mrk.term_id = '".$term_id."' AND mrk.course_id = '".$course_id."' AND mrk.status = '1' AND syn.id = mrk.syndicate_id ORDER BY mrk.id desc ";
			
			
			$studentInfo = $dbObj->selectDataObj($sql);
			//echo '<pre>';
			//print_r($studentInfo);
			if(!empty($studentInfo)){
				//Find Student info  -- the student who are assigned in this term and exercise in dscsc_marking table
				$sql = "select DISTINCT(std.id), std.* from ".DB_PREFIX."student as std, ".DB_PREFIX."student_to_syndicate as stc, ".DB_PREFIX."syndicate as syn WHERE syn.id = stc.syndicate_id AND stc.student_id = std.id AND stc.course_id = '".$course_id."' AND stc.term_id = '".$term_id."' AND std.wing_id = '".$cur_user_wing_id."' AND std.course_id = '".$course_id."' AND (";
												foreach($studentInfo as $studentId){
													$sql .= " std.id = '".$studentId->student_id."' || ";						
												}
												$sql = rtrim($sql, "|| ");
												$sql = $sql.') ORDER BY syn.name, std.student_id;';
				//echo $sql;exit;
				$studentArr = $dbObj->selectDataObj($sql);
				
				$studentId = array();
				if(!empty($studentArr)){
					foreach($studentArr as $item){
						$studentId[$item->id] = $item->student_id;
					}//foreach	
				}//if	
			}//if
			
			//This code find position of a student in an exercise
			$sql = " SELECT distinct (ds_marking+si_mod_marking) as total from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' ORDER BY total DESC";
			$numberArray = $dbObj->selectDataObj($sql);
		 	
			if(!empty($numberArray)){
				$i = 0;
				foreach($numberArray as $item){
					$numberArray[$i]->position = $i+1;
					$i++; 
				}//foreach
			}//if
			
			//Find out if this exercise result has been forwarded by CI or not
			$sql = "select * from ".DB_PREFIX."ci_marking_lock WHERE exercise_id = '".$exercise_id."' AND course_id = '".$course_id."' AND term_id = '".$term_id."' AND status != '0'";
			$forwardedStatusArr = $dbObj->selectDataObj($sql);
			$forwardedStatus = $forwardedStatusArr[0];
			$SIName = getNameById('user', $forwardedStatus->locked_by);
			$locked_datetime = $forwardedStatus->locked_datetime;
			$locked_date_time = explode(' ', $locked_datetime);
			$locked_date = $locked_date_time[0];
			$locked_time = $locked_date_time[1];
			
			
			
			if(!empty($studentArr)){
				$str = '<form action="si_marking.php" method="post" name="si_marking" id="si_marking" onsubmit="return validateSIForm();" >
						<table cellpadding="0" cellspacing="0" border="0" class="module_content">
							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" border="0" class="datagrid" width="700">
										<tr class="head">
											<td align="center" width="10%"><strong>'.SER_NO.'</strong></td>
											<td align="center" width="10%"><strong>'.CN.'</strong></td>
											<td width="10%"><strong>'.RANK.'</strong></td>
											<td width="10%"><strong>'.STUDENT_NAME.'</strong></td>
											<td align="center" width="10%"><strong>'.SYNDICATE.'</strong></td>
											<td align="center" width="10%"><strong>'.FROM_DS.'</strong></td>
											<td align="center" width="10%"><strong>'.FROM_DS_WT.'</strong></td>
											<td align="center" width="10%"><strong>'.SI_NUMBER_PERCENT.' ('.PERCENT.')</strong></td>
											<td align="center" width="10%"><strong>'.MOD_PERCENT.'</strong></td>
											<td align="center" width="10%"><strong>'.MOD_WT.'</strong></td>
											<td align="center" width="10%"><strong>'.GRADE.'</strong></td>
											<td align="center" width="10%"><strong>'.POSITION.'</strong></td>
										</tr>';
							$rownum = 0;
							$sl = 0;
							foreach($studentArr as $student){
								$sl = $rownum + 1;
								$class = (($rownum%2)==0) ? ' class="even"' : ' class="odd"';
							
							$sql = "select * from ".DB_PREFIX."rank WHERE id = '".$student->rank_id."'";
							$rankArr = $dbObj->selectDataObj($sql);
							$rank = $rankArr[0];
							$rankName = getNameById('rank', $rank->id);
													
							//Find All Student ID and number from dscdc_marking Table by this exercise ID
							$sql = "select * from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' AND student_id = '".$student->id."' order by id desc";
							$markInfo = $dbObj->selectDataObj($sql);
							$mark = $markInfo[0];
							
							//Find official_name of each student
							$sql = "select official_name from ".DB_PREFIX."student WHERE id = '".$mark->student_id."' limit 1";
							$nameArr = $dbObj->selectDataObj($sql);
							$name = $nameArr[0];
							
							//Find Syndicate Name of the Student
							if($joinExercise == 0){
								$sql = "select syndicate_id from ".DB_PREFIX."student_to_syndicate WHERE student_id = '".$mark->student_id."' AND course_id = '".$course_id."' AND syndicatetype = 0 AND term_id = '".$term_id."' ";
							}else{
								$sql = "select syndicate_id from ".DB_PREFIX."student_to_syndicate WHERE student_id = '".$mark->student_id."' AND course_id = '".$course_id."' AND syndicatetype = 1 AND term_id = '".$term_id."' ";
							}
							
							
							$synArr = $dbObj->selectDataObj($sql);
							
							$syndicate = $synArr[0];
							$syndicateName = getNameById('syndicate', $syndicate->syndicate_id);
							//echo '<pre>';
							//print_r($syndicateName);
							$si_put_num = ($mark->si_mod_marking == '0')?'':view_number_two($mark->si_mod_marking);
							$sign = ($mark->si_sign == '+')?$mark->si_sign:'';
							$si_weighted_mark = ($weight*((($mark->ds_marking+$mark->si_mod_marking))/100));
							$gradeNumber = $mark->ds_marking + $mark->si_mod_marking;
							$mark_number = (($mark->ds_marking+$mark->si_mod_marking)*$total_mark)/100;
							$ds_weighted_mark = ($mark->ds_marking*$weight)/100; 
							
							$str .= '	<tr '.$class.'>
											<td align="center">'.$sl.'</td>
											<td align="center">'.$student->student_id.'</td>
											<td>'.$rankName->name.'</td>
											<td>'.$name->official_name.'</td>
											<td>'.$syndicateName->name.'</td>
											<td align="center">
												<input class="input_number" type="text" name="ds_number'.$sl.'" id="ds_number'.$sl.'" value="'.view_number_two($mark->ds_marking).'" " size="3" readonly="readonly" />
											</td>
											<td align="center">
												<input class="input_number" type="text" name="ds_weight_number'.$sl.'" id="ds_weight_number'.$sl.'" value="'.view_number_two($ds_weighted_mark).'" " size="3" readonly="readonly" />
											</td>
											<td align="center">
												<input style="text-align:right; border:1px solid #000000" type="text" name="number_'.$student->id.'" id="number'.$sl.'" value="'.$sign.$si_put_num.'" maxlength="5" size="3" onkeyup="return validateSiMarking(\'number'.$sl.'\');" '; if(!empty($forwardedStatus)){ $str .= ' readonly="readonly" class="input_number"'; } $str .= '/>
											</td>
											<td align="center">
												<input class="input_number" type="text" name="tot_number'.$sl.'" id="tot_number'.$sl.'" value="'.view_number_two($gradeNumber).'" size="3" readonly="readonly" />
											</td>
											<td align="center">
												<input class="input_number" type="text" name="weight_number'.$sl.'" id="weight_number'.$sl.'" value="'.view_number_two($si_weighted_mark).'" size="3" readonly="readonly" />
											</td>
											<td align="center">
												<input class="center_input" type="text" name="grade_number'.$sl.'" id="grade_number'.$sl.'" value="'.findGrade($gradeNumber).'" size="3" readonly="readonly" />
											</td class="center">
											<td align="center">
												<input class="center_input" type="text" name="position'.$sl.'" id="position'.$sl.'" value="'.findPosition($numberArray, $gradeNumber).'" size="3" readonly="readonly" />
											</td>
										</tr>';
											$rownum++;
											
										}//foreach
										
							$str .= '	<tr class="head">
											<td colspan="12">';
							if(empty($forwardedStatus)){ $str .= '<input type="submit" name="Submit" class="button" value="Save" />'; }
							else{ $str .= LOCKED_MESSAGE.$SIName->official_name.' on '.dateConvertion($locked_date).' at '.$locked_time;}
							$str .= '		</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>';
						foreach($studentId as $id => $num_count){
							$str .= '<input type="hidden" name="student_id[]" id="student_id'.$num_count.'" value="'.$id.'" />';
						}
							$str	.= '	<input type="hidden" name="course_id" value="'.$course_id.'" />
											<input type="hidden" name="exercise_id" value="'.$exercise_id.'" />
											<input type="hidden" name="sl" id="sl" value="'.$sl.'" />
											<input type="hidden" name="term_id" id="term_id" value="'.$term_id.'" />
											<input type="hidden" name="mark" id="mark" value="'.$exrcise->mark.'" />
											<input type="hidden" name="weight" id="weight" value="'.$exrcise->weight.'" />
											<input type="hidden" name="action" value="save" />
											</form>';
						
							echo $str;	
			}else{
				
				echo '<table cellpadding="0" cellspacing="0" border="0" class="module_content">
							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" border="0" class="datagrid" width="700">
										<tr class="head">
											<td><strong>'.NO_DATA_FOUND.'</strong></td>
										</tr>
									 </table>
								</td>
							</tr>
						</table>';
			}
		}//if
	break;
	
	case 'ci_mod_mark_term':	
		$course_id = $_REQUEST['course_id'];

		//Build Term List Array	
		$query = "select id, name from ".DB_PREFIX."term where course_id = ".$course_id." AND status = '0' order by name asc";
		$termArr = $dbObj->selectDataObj($query);
		$termId = array();
		$termId[0] = SELECT_TERM_OPT;
		if(!empty($termArr)){			
			foreach($termArr as $item){
				$termId[$item->id] = $item->name;
			}	
		}			
		$termList_opt = formSelectElement($termId, $term_id, 'term_id', 'onchange = processFunction("ci_mod_mark_exr")');
		
		echo $termList_opt.'<span class="required_field"> *</span>';
		
	break;
	
	case 'ci_mod_mark_exr':	
		$term_id = $_REQUEST['term_id'];

		//Build Exercise List Array	
		$sql = "select exr.id, exr.name from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where ett.term_id = ".$term_id." AND ett.exercise_id = exr.id order by exr.name asc";
		$exerciseArr = $dbObj->selectDataObj($sql);
		$exerciseId = array();
		$exerciseId[0] = SELECT_EXERCISE_OPT;
		if(!empty($exerciseArr)){			
			foreach($exerciseArr as $item){
				$exerciseId[$item->id] = $item->name;
			}	
		}			
		$exerciseList_opt = formSelectElement($exerciseId, $exercise_id, 'exercise_id', 'onchange = processFunction("assign_ci_mark")');
		
		echo $exerciseList_opt.'<span class="required_field"> *</span>';
		
	break;
	
	case 'impr_mark_term':	
		$course_id = $_REQUEST['course_id'];

		//Build Term List Array	
		$sql = "select id, name from ".DB_PREFIX."term WHERE course_id = '".$course_id."' AND wing_id = '".$cur_user_wing_id."' AND status = '0' order by name asc";
		$termArr = $dbObj->selectDataObj($sql);
		$termId = array();
		$termId[0] = SELECT_TERM_OPT;
		if(!empty($termArr)){			
			foreach($termArr as $item){
				$termId[$item->id] = $item->name;
			}	
		}			
		$termList_opt = formSelectElement($termId, $term_id, 'term_id', 'onchange = processFunction("term_impr_mark_assign")');
		
		echo $termList_opt.'<span class="required_field"> *</span>';
		
	break;
	
	case 'assign_ci_mark':
		$exercise_id = $_REQUEST['exercise_id'];
		$term_id = $_REQUEST['term_id'];
		$course_id = $_REQUEST['course_id'];
		
		if($term_id != '0' || $course_id != '0' || $term_id != '0'){
		
			//Find exercise info
			$query = "select * from ".DB_PREFIX."exercise WHERE id = '".$exercise_id."'";
			$exrArr = $dbObj->selectDataObj($query);
			$exercise = $exrArr[0];
			$marking_type = $exercise->marking_type;
			
			//Find Exercise Type Name
			$type = getNameById('exercise_type', $exercise->type_id);
			
			if($exercise->marking_type == '2'){
				$assess_sys = MARK_BASED;
			}else if($exercise->marking_type == '1'){
				$assess_sys = PERCENT_BASED;
			}
						
			$str = '<table width="100%" cellpadding="0" cellspacing="0" border="1">
						<tr>
							<td align="center" width="20%" colspan="6"><strong>'.EXERCISE_INFO.'</strong></td>
						</tr>
						<tr>
							<td align="center" width="20%">'.NAME.'</td>
							<td align="center" width="20%">'.EXERCISE_ID.'</td>
							<td align="center" width="20%">'.TYPE.'</td>
							<td align="center" width="20%">'.ASSESSMENT_SYSTEM.'</td>';
						if($marking_type == '2'){
			$str .=			'<td align="center" width="10%">'.MKS.'</td>';
						}
			$str .=			'<td align="center" width="10%">'.WT.'</td>
						</tr>
						<tr>
							<td align="center">'.$exercise->name.'</td>
							<td align="center">'.$exercise->exercise_id.'</td>
							<td align="center">'.$type->name.'</td>
							<td align="center">'.$assess_sys.'</td>';
						if($marking_type == '2'){
			$str .=			'<td align="center">'.$exercise->mark.'</td>';
						}	
			$str .=			'<td align="center">'.$exercise->weight.'</td>
						</tr>
					</table>';
			echo $str;	
			
			echo ':*:';
			
			//Find Info of this Exercise ID
			$query = "select * from ".DB_PREFIX."exercise WHERE id = '".$exercise_id."' ";
			$exrArr = $dbObj->selectDataObj($query);
			$exrcise = $exrArr[0];
			$weight = $exercise->weight;
			
			//Find All Student ID and number from dscdc_marking Table by this exercise ID
			$sql = "select DISTINCT(mrk.student_id), mrk.* from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."syndicate as syn WHERE mrk.exercise_id = '".$exercise_id."' AND mrk.term_id = '".$term_id."' AND mrk.course_id = '".$course_id."' AND mrk.status = '1' AND syn.id = mrk.syndicate_id ORDER BY syn.name ";
			$studentInfo = $dbObj->selectDataObj($sql);
			/*
			if(!empty($studentInfo)){
				//Find Student info  -- the student who are assigned in this term and exercise in dscsc_marking table
				$sql = "select DISTINCT(stc.student_id), std.*, syn.id as syndicate_id from ".DB_PREFIX."student as std, ".DB_PREFIX."student_to_syndicate as stc, ".DB_PREFIX."syndicate as syn WHERE syn.id = stc.syndicate_id AND stc.student_id = std.id AND stc.course_id = '".$course_id."' AND stc.term_id = '".$term_id."' AND std.wing_id = '".$cur_user_wing_id."' AND std.course_id = '".$course_id."' AND (";
												foreach($studentInfo as $studentId){
													$sql .= " std.id = '".$studentId->student_id."' || ";						
												}//foreach
												$sql = rtrim($sql, "|| ");
												$sql = $sql.') ORDER BY syn.name, std.student_id;';
				
				$studentArr = $dbObj->selectDataObj($sql);
				
				$studentId = array();
				if(!empty($studentArr)){
					foreach($studentArr as $item){
						$studentId[$item->id] = $item->student_id;
					}//foreach	
				}//if	
			}//if
			*/
			
			if(!empty($studentInfo)){
				//Find Student info  -- the student who are assigned in this term and exercise in dscsc_marking table
				$sql = "select DISTINCT(std.id), std.* from ".DB_PREFIX."student as std, ".DB_PREFIX."student_to_syndicate as stc, ".DB_PREFIX."syndicate as syn WHERE syn.id = stc.syndicate_id AND stc.student_id = std.id AND stc.course_id = '".$course_id."' AND stc.term_id = '".$term_id."' AND std.wing_id = '".$cur_user_wing_id."' AND std.course_id = '".$course_id."' AND (";
												foreach($studentInfo as $studentId){
													$sql .= " std.id = '".$studentId->student_id."' || ";						
												}
												$sql = rtrim($sql, "|| ");
												$sql = $sql.') ORDER BY syn.name, std.student_id;';
				//echo $sql;exit;
				$studentArr = $dbObj->selectDataObj($sql);
				
				$studentId = array();
				if(!empty($studentArr)){
					foreach($studentArr as $item){
						$studentId[$item->id] = $item->student_id;
					}//foreach	
				}//if	
			}//if
			
			//This code find position of a student in an exercise
			$sql = " SELECT distinct (ds_marking+si_mod_marking+ci_mod_marking) as total from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' ORDER BY total DESC";
			$numberArray = $dbObj->selectDataObj($sql);
			
			if(!empty($numberArray)){
				$i = 0;
				foreach($numberArray as $item){
					$numberArray[$i]->position = $i+1;
					$i++; 
				}//foreach
			}//if
			
			
			if(!empty($studentArr)){
				$str = '<form action="ci_marking.php" method="post" name="ci_marking" id="ci_marking" onsubmit="return validateCIForm();" >
						<table cellpadding="0" cellspacing="0" border="0" class="module_content">
							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" border="0" class="datagrid" width="700">
										<tr class="head">
											<td align="center" width="5%"><strong>'.SER_NO.'</strong></td>
											<td align="center" width="20%"><strong>'.CN.'</strong></td>
											<td width="10%"><strong>'.RANK.'</strong></td>
											<td width="10%"><strong>'.STUDENT_NAME.'</strong></td>
											<td align="center" width="10%"><strong>'.SYNDICATE.'</strong></td>
											<td align="center" width="10%"><strong>'.FROM_DS.'</strong></td>
											<td align="center" width="10%"><strong>'.FROM_DS_WT.'</strong></td>
											<td align="center" width="10%"><strong>'.SI_NUMBER_PERCENT.' ('.PERCENT.')</strong></td>
											<td align="center" width="10%"><strong>'.CI_NUMBER_PERCENT.' ('.PERCENT.')</strong></td>
											<td align="center" width="10%"><strong>'.MOD_PERCENT.'</strong></td>
											<td align="center" width="10%"><strong>'.MOD_WT.'</strong></td>
											<td align="center" width="10%"><strong>'.GRADE.'</strong></td>
											<td align="center" width="10%"><strong>'.POSITION.'</strong></td>
										</tr>';
										$rownum = 0;
										$sl = 0;
										foreach($studentArr as $student){
											$sl = $rownum + 1;
											$class = (($rownum%2)==0) ? ' class="even"' : ' class="odd"';
											
							//Find Student Rank
							$sql = "select * from ".DB_PREFIX."rank WHERE id = '".$student->rank_id."'";
							$rankArr = $dbObj->selectDataObj($sql);
							$rank = $rankArr[0];
							$rankName = getNameById('rank', $rank->id);
											
							//Find All Student ID and number from dscdc_marking Table by this exercise ID
							$sql = "select * from ".DB_PREFIX."marking WHERE exercise_id = '".$exercise_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' AND student_id = '".$student->id."' ORDER BY id desc";
							$markInfo = $dbObj->selectDataObj($sql);
							$mark = $markInfo[0];
							
							$si_put_num = ($mark->si_mod_marking == '0')?'':view_number_two($mark->si_mod_marking);
							$ci_put_num = ($mark->ci_mod_marking == '0')?'':view_number_two($mark->ci_mod_marking);
							$si_sign = ($mark->si_sign == '+')?$mark->si_sign:'';
							$ci_sign = ($mark->ci_sign == '+')?$mark->ci_sign:'';
							
							//Find Syndicate Name of the Student
							$sql = "select syndicate_id from ".DB_PREFIX."student_to_syndicate WHERE student_id = '".$mark->student_id."' AND term_id = '".$term_id."' AND course_id = '".$course_id."' ";
							$synArr = $dbObj->selectDataObj($sql);
							$syndicate = $synArr[0];
							$syndicateName = getNameById('syndicate', $syndicate->syndicate_id);
							
							//Find out the Total Number according to weight 
							$si_weighted_mark = ($weight*((($mark->ds_marking+$mark->si_mod_marking+$mark->ci_mod_marking))/100));
							$gradeNumber = $mark->ds_marking+$mark->si_mod_marking+$mark->ci_mod_marking;
							$ds_weighted_number = ($mark->ds_marking*$weight)/100;
							
							//Find out if this exercise result has been forwarded by CI or not
							$sql = "select * from ".DB_PREFIX."ci_marking_lock WHERE exercise_id = '".$exercise_id."' AND course_id = '".$course_id."' AND term_id = '".$term_id."' AND status != '0'";
							$forwardedStatusArr = $dbObj->selectDataObj($sql);
							$forwardedStatus = $forwardedStatusArr[0];
							$locked_datetime = $forwardedStatus->locked_datetime;
							$locked_date_time = explode(' ', $locked_datetime);
							$locked_date = $locked_date_time[0];
							$locked_time = $locked_date_time[1];
							
							//Find total Syndicate assigned in this Term
							$query = "select syn.id, syn.name from ".DB_PREFIX."syndicate as syn, ".DB_PREFIX."syndicate_to_course as stc where syn.wing_id = ".$cur_user_wing_id." AND stc.course_id = '".$course_id."' AND syn.syndicate_type = 0 AND syn.id = stc.syndicate_id order by syn.name asc";
							$synArr = $dbObj->selectDataObj($query);
							$totalSyndicate = sizeof($synArr);
							
							//Find out all syndicate result of this exercise has been forwarded
							$sql = "select * from ".DB_PREFIX."marking_lock WHERE exercise_id = '".$exercise_id."' AND course_id = '".$course_id."' AND term_id = '".$term_id."' AND status != '0'";
							$allForwardedArr = $dbObj->selectDataObj($sql);
							$AllForwarded = sizeof($allForwardedArr);
							
							$str .= '	<tr '.$class.'>
											<td align="center">'.$sl.'</td>
											<td align="center">'.$student->student_id.'</td>
											<td>'.$rankName->short_name.'</td>
											<td>'.$student->official_name.'</td>
											<td>'.$syndicateName->name.'</td>
											<td align="center">
												<input class="input_number" type="text" name="ds_number'.$sl.'" id="ds_number'.$sl.'" value="'.view_number_two($mark->ds_marking).'" " size="3" readonly="readonly" />
											</td>
											<td align="center">
												<input class="input_number" type="text" name="ds_weight_number'.$sl.'" id="ds_weight_number'.$sl.'" value="'.view_number_two($ds_weighted_number).'" " size="3" readonly="readonly" />
											</td>
											<td align="center">
												<input class="input_number" type="text" name="si_number'.$sl.'" id="si_number'.$sl.'" value="'.$si_sign.$si_put_num.'" size="3" readonly="readonly"/>
											</td>
											<td align="center">
												<input style="text-align:right; border:1px solid #000000" type="text" name="number_'.$student->id.'" id="number'.$sl.'" value="'.$ci_sign.$ci_put_num.'" size="3" onkeyup="return validateCiMarking(\'number'.$sl.'\');" '; if(!empty($forwardedStatus)){ $str .=  ' readonly = "readonly" class="input_number"'; } $str .= ' />
											</td>
											<td align="center">
												<input class="input_number" type="text" name="tot_number'.$sl.'" id="tot_number'.$sl.'" value="'.view_number_two($gradeNumber).'" size="3" readonly="readonly" />
											</td>
											<td align="center">
												<input class="input_number" type="text" name="weight_number'.$sl.'" id="weight_number'.$sl.'" value="'.view_number_two($si_weighted_mark).'" size="3" readonly="readonly" />
											</td>
											<td align="center">
												<input class="center_input" type="text" name="grade_number'.$sl.'" id="grade_number'.$sl.'" value="'.findGrade($gradeNumber).'" size="3" readonly="readonly" />
											</td>
											<td align="center">
												<input class="center_input" type="text" name="grade_number'.$sl.'" id="grade_number'.$sl.'" value="'.findPosition($numberArray, $gradeNumber).'" size="3" readonly="readonly" />
											</td>
										</tr>';
											$rownum++;
											
										}//foreach
							//Find out ths situation/Status of this exercise
							$sql = "select * from ".DB_PREFIX."ci_marking_lock WHERE exercise_id = '".$exercise_id."' AND course_id = '".$course_id."' AND term_id = '".$term_id."'";
							
							$allForwardedArr = $dbObj->selectDataObj($sql);
							$forwardedStatus = $allForwardedArr[0];

							$str .= '	<tr class="head">
											<td colspan="13">';
							$SIname = getNameById('user', $forwardedStatus->locked_by);
							if(($forwardedStatus->status == '0') || empty($forwardedStatus)){
								$str .= '		<input type="submit" name="Submit" class="button" value="Save" />';
								
								if($totalSyndicate == $AllForwarded){
									$str .= '	<input type="submit" name="Submit" class="button" value="Lock" />';
								}
							}else if($forwardedStatus->status == '1'){	
								$str .= '		<input type="button" value="'.REQUEST_TO_UNLOCK.'" onclick="return submitRequest()">
												<span class="sentMsg">'.LOCKED_MESSAGE.$SIname->official_name.' on '.dateConvertion($locked_date).' at '.$locked_time.'</span>
												<div id="submitRequest">
													<div name="submit_req" id="submit_req" action="ci_marking.php" method="post">
														<textarea name="comment" id="comment" rows="5" cols="27" class="inputbox" alt="Comment"></textarea>
														<br  />
														<input type="hidden" name="action" value="save"  />
														<input type="submit" name="Submit" id="Submit"  value="Request to Unlock"  />
														<input type="button" name="btnclose" id="btnclose"  value="'.CANCEL.'"  onclick=Close("submitRequest") />											
													</div>						
												</div>';
							}else{
								$str .= '		'.REQUEST_MESSAGE.$SIname->official_name.'';
							}
							$str .= '		</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>';
						foreach($studentId as $id => $num_count){
							$str .= '<input type="hidden" name="student_id[]" id="student_id'.$num_count.'" value="'.$id.'" />';
						}
							$str	.= '	<input type="hidden" name="course_id" value="'.$course_id.'" />
											<input type="hidden" name="exercise_id" value="'.$exercise_id.'" />
											<input type="hidden" name="sl" id="sl" value="'.$sl.'" />
											<input type="hidden" name="term_id" id="term_id" value="'.$term_id.'" />
											<input type="hidden" name="mark" id="mark" value="'.$exrcise->mark.'" />
											<input type="hidden" name="weight" id="weight" value="'.$exrcise->weight.'" />
											<input type="hidden" name="action" value="save" />
											</form>';
						
							echo $str;	
			}else{
				
				echo '<table cellpadding="0" cellspacing="0" border="0" class="module_content">
							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" border="0" class="datagrid" width="700">
										<tr class="head">
											<td><strong>'.NO_DATA_FOUND.'</strong></td>
										</tr>
									 </table>
								</td>
							</tr>
						</table>';
			}
		}//if
	break;
	
	case 'term_impr_mark_assign':
		$course_id = $_REQUEST['course_id'];
		
		if(!empty($course_id)){

		//Find Course Info - especially SI Impresion Mark
		$sql = "select * from ".DB_PREFIX."course WHERE id = '".$course_id."'";
		$courseArr = $dbObj->selectDataObj($sql);
		$course = $courseArr[0];
		
		//Find which tersm are under this course
		$sql = "select * from ".DB_PREFIX."term WHERE course_id = '".$course_id."' ORDER BY order_id asc";
		$termArr = $dbObj->selectDataObj($sql);
		$totalTerm = sizeof($termArr);
		
		
		//Find out if all terms of this course has been locked
		$sql = "select * from ".DB_PREFIX."term WHERE course_id = '".$course_id."' AND lock_status = '1'";
		$lockedTermArr = $dbObj->selectDataObj($sql);
		$lockedTotalTerm = sizeof($lockedTermArr);
		
		if($totalTerm != $lockedTotalTerm){
			$readable = ' readonly="readonly" class="input_number" ';
		}else{
			$readable = '';
		}//else 
		
		//Find if the course has been given number for once, if given we will make the studentArr from si_impression_mark table
		$sql = "select id from ".DB_PREFIX."si_impression_marking WHERE course_id = '".$course_id."' LIMIT 1";
		$markGiven = $dbObj->selectDataObj($sql);
		
		//Find all Students of this term
		if(empty($markGiven)){
			$sql = "select * from ".DB_PREFIX."student WHERE course_id = '".$course_id."' ORDER BY student_id";
		}else{
			$sql = "select s.* from ".DB_PREFIX."student as s, ".DB_PREFIX."si_impression_marking as m WHERE s.course_id = '".$course_id."' AND m.course_id = '".$course_id."' AND s.id = m.student_id ORDER BY m.total_weight desc";
		}
		$studentArr = $dbObj->selectDataObj($sql);
		$totalCount = sizeof($studentArr);
		
		if(!empty($studentArr)){
			//Find Student info  -- the student who are assigned in this term and exercise in dscsc_marking table
			$sql = "select * from ".DB_PREFIX."student WHERE wing_id = '".$cur_user_wing_id."' AND course_id = '".$course_id."' AND (";
											foreach($studentArr as $studentId){
												$sql .= " id = '".$studentId->id."' || ";						
											}
											$sql = rtrim($sql, "|| ");
											$sql = $sql.') ORDER BY student_id;';
			$studentMarkArr = $dbObj->selectDataObj($sql);
			if(!empty($studentArr)){
				$studentId = array();
				if(!empty($studentMarkArr)){
					foreach($studentMarkArr as $item){
						$studentId[$item->id] = $item->student_id;
					}//foreach	
				}//if
			}//if not empty student array
		}//if
		
		//This code find position of a student in a course	
		$sql = " SELECT distinct total_weight as total, student_id from ".DB_PREFIX."si_impression_marking WHERE course_id = '".$course_id."' ORDER BY total desc";
		$numberArrayForCourse = $dbObj->selectDataObj($sql);
		
		if(!empty($numberArrayForCourse)){
			$i = 0;
			foreach($numberArrayForCourse as $item){
				$numberArrayForCourse[$i]->position = $i+1;
				$i++; 
			}//foreach
		}//if
		
		$str = '
		<table cellpadding="0" cellspacing="0" border="0" class="module_content">
					<tr>
						<td height="20">'.MARK_LIMIT.'&nbsp;:&nbsp;</td>
						<td align="left"><strong>'.$course->si_impr_mark_limit.'%</strong></td>
				</tr>
		</table>
		
		<form action="si_impr_marking.php" method="post" name="si_impr_marking_result" id="si_impr_marking_result" onsubmit="return validateStudentImprOfSI();" >
			<table cellpadding="0" cellspacing="0" border="0" class="module_content">
				<tr>
					<td>
						<table cellpadding="0" cellspacing="0" border="0" class="datagrid">
							<tr class="head">
								<td align="center" rowspan="2" ><strong>'.SER_NO.'</strong></td>
								<td align="center" rowspan="2"><strong>'.CN.'</strong></td>
								<td rowspan="2"><strong>'.BANO.'</strong></td>
								<td rowspan="2"><strong>'.RANK.'</strong></td>
								<td rowspan="2"><strong>'.STUDENT_NAME.'</strong></td>';
								foreach($termArr as $term){
								
		$str .='				<td align="center" colspan="5"><strong>'.$term->name.'</strong></td>';
								}
		$str .='				<td align="center"><strong>'.SUB_TOTAL_WT.'</strong></td>
								<td align="center" rowspan="2"><strong>'.SUB_TOTAL_PERCENT.'</strong></td>
								<td align="center" colspan="2"><strong>'.SI_IMPRES_MARK.'</strong></td>
								<td align="center"><strong>'.TOTAL_WT.'</strong></td>
								<td align="center" rowspan="2"><strong>'.TOTAL_PERCENT.'</strong></td>
								<td align="center" rowspan="2"><strong>'.GRADE.'</strong></td>
								<td align="center" rowspan="2"><strong>'.POSITION.'</strong></td>
								<td rowspan="2"><strong>'.BANO.'</strong></td>
								<td rowspan="2"><strong>'.RANK.'</strong></td>
								<td rowspan="2"><strong>'.STUDENT_NAME.'</strong></td>
							</tr>
							<tr class="head">';
							
								$sub_total_weight = 0;
								//$sub_total_mark = 0;
								foreach($termArr as $term){
								
									//Find total exercise weight of this term --- thus get term weightage marks
									//$sql = "select exr.weight from ".DB_PREFIX."exercise as exr,  ".DB_PREFIX."exercise_to_term as ett WHERE exr.course_id = '".$course_id."' AND ett.exercise_id = exr.id AND ett.term_id = '".$term->id."'";
									$sql = "select * from ".DB_PREFIX."exercise as exr,  ".DB_PREFIX."exercise_to_term as ett WHERE ett.exercise_id = exr.id AND ett.term_id = '".$term->id."'";
									
									$totalExerciseArr = $dbObj->selectDataObj($sql);
									
									//Find DS Impression Mark of this term
									$ds_impr_mark = $term->ds_impr_mark;
								
									$exr_weight = 0;
									foreach($totalExerciseArr as $totalExercise){
										$joinExercise = $totalExercise->join_course;
										if($joinExercise == 1 && $cur_user_wing_id == 2){
											$totalExercise->weight = $totalExercise->air_weight;
										}else if($joinExercise == 1 && $cur_user_wing_id == 3){
											$totalExercise->weight = $totalExercise->navy_weight;
										}else if($joinExercise == 1 && $cur_user_wing_id == 1){
											$totalExercise->weight = $totalExercise->weight;
										}else{
											$totalExercise->weight = $totalExercise->weight;
										}
										
										$exr_weight += $totalExercise->weight;
									}//foreach -- totalExerciseArr
									$term_weight = $exr_weight+$ds_impr_mark;
									
		$str .='				<td align="center"><strong>'.SYNDICATE.'</strong></td>
								<td align="center"><strong>'.PERCENT.'</strong></td>
								<td align="center"><strong>'.WT.'<br />'.$term_weight.'</strong></td>
								<td align="center"><strong>'.GRADE.'</strong></td>
								<td align="center"><strong>'.POSITION.'</strong></td>';
									
									$sub_total_weight += $term_weight;
								}//foreach
								$total_weight = $sub_total_weight+$course->si_impr_mark;
								
		$str .='				<td align="center"><strong>'.$sub_total_weight.'</strong></td>
								<td align="center"><strong>'.PERCENT.'</strong></td>
								<td align="center"><strong>'.WT.'<br />'.$course->si_impr_mark.'</strong></td>
								<td align="center"><strong>'.$total_weight.'</strong></td>											
							</tr>';
						if(!empty($studentArr)){
							$rownum = 0;
							$sl = 0;
							foreach($studentArr as $student){
								$sl = $rownum + 1;
								$class = (($rownum%2)==0) ? ' class="even"' : ' class="odd"';
		
								//Find rank of each student
								$sql = "select * from ".DB_PREFIX."rank WHERE id = '".$student->rank_id."'";
								$rankArr = $dbObj->selectDataObj($sql);
								$rank = $rankArr[0];
								$rankName = getNameById('rank', $rank->id);
								/*
								echo '<pre>';
								print_r($student);
								echo '<pre>';exit;
								$student->ba_no;
								*/
		$str .='			<tr '.$class.'>
								<td align="center" >'.$sl.'</td>
								<td align="center" id="student_id_'.$student->student_id.'" >'.$student->student_id.'</td>
								<td>'.$student->ba_no.'</td>
								<td>'.$rankName->name.'</td>
								<td>'.$student->official_name.'</td>';
								$total_term_weight = 0;
									foreach($termArr as $term){
									//Find Syndicate Name of the Student
									$sql = "select syndicate_id from ".DB_PREFIX."student_to_syndicate WHERE student_id = '".$student->id."' AND term_id = '".$term->id."' AND course_id = '".$course_id."' ";
									$synArr = $dbObj->selectDataObj($sql);
									$syndicate = $synArr[0];
									$syndicateName = getNameById('syndicate', $syndicate->syndicate_id);
									
									//Find mark in every exercise for student
									$query = "select * from ".DB_PREFIX."marking WHERE course_id = '".$course_id."' AND term_id = '".$term->id."' AND student_id = '".$student->id."' AND status = '1' order by id desc";
									
									$dsMark = $dbObj->selectDataObj($query);
											
									$student_term_weight = 0;
										foreach($dsMark as $mark){
										
											//Find Exercise Info
											$query = "select * from ".DB_PREFIX."exercise WHERE id = '".$mark->exercise_id."'";
											$exrInfo = $dbObj->selectDataObj($query);
											$exr = $exrInfo[0];
											
												$exercise = $exrInfo[0];
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
											
											//$converted_student_weight = $mark->ds_student_weight+$mark->si_student_weight+$mark->ci_student_weight;
											$from_ds_weight = ($mark->ds_marking*$exercise->weight)/100;
											$converted_student_weight = $from_ds_weight+$mark->si_student_weight+$mark->ci_student_weight;
											$student_term_weight += $converted_student_weight;
										}//foerach
										
										//Find Impression marking of this student of this term
										$sql = "select * from ".DB_PREFIX."impression_marking WHERE course_id = '".$course_id."' AND term_id = '".$term->id."' AND student_id = '".$student->id."' AND status = '1'";
										$dsImprMarkArr = $dbObj->selectDataObj($sql);
										$dsImprmark = $dsImprMarkArr[0];
										
										//Find DS Impression Mark of this term
										
										$from_ds_weight = ($mark->ds_marking*$exercise->weight)/100;
										
										$ds_impr_mark = $term->ds_impr_mark;
										$converted_ds_impr_mark = $dsImprmark->ds_impr_weight;
										$sub_total_term_weight = $converted_ds_impr_mark+$student_term_weight;
										if($sub_total_term_weight > 0){
											$find_zero = 1;
										}else{
											$find_zero = 0;
										}//else
										$first_impr_valid = $sub_total_term_weight+$find_zero;
										$second_impr_valid = $sub_total_term_weight+$totalTerm;
										if($first_impr_valid != $second_impr_valid){
											$condit = "readonly='readonly'";
										}else{
											$condit = "";
										}
										//Find total exercise weight of this term --- thus get term weightage marks
										//$sql = "select exr.weight from ".DB_PREFIX."exercise as exr,  ".DB_PREFIX."exercise_to_term as ett WHERE exr.course_id = '".$course_id."' AND ett.exercise_id = exr.id AND ett.term_id = '".$term->id."'";
										
										$sql = "select * from ".DB_PREFIX."exercise as exr,  ".DB_PREFIX."exercise_to_term as ett WHERE ett.exercise_id = exr.id AND ett.term_id = '".$term->id."'";
										
										$totalExerciseArr = $dbObj->selectDataObj($sql);
										
										$exr_weight = 0;
											foreach($totalExerciseArr as $totalExercise){
												$joinExercise = $totalExercise->join_course;
												if($joinExercise == 1 && $cur_user_wing_id == 2){
													$totalExercise->weight = $totalExercise->air_weight;
												}else if($joinExercise == 1 && $cur_user_wing_id == 3){
													$totalExercise->weight = $totalExercise->navy_weight;
												}else if($joinExercise == 1 && $cur_user_wing_id == 1){
													$totalExercise->weight = $totalExercise->weight;
												}else{
													$totalExercise->weight = $totalExercise->weight;
												}
												
												$exr_weight += $totalExercise->weight;
											}//foreach -- totalExerciseArr
											$term_weight = $exr_weight+$ds_impr_mark;
										
											$term_percent_number = ($sub_total_term_weight*100)/$term_weight;
								//This code find position of a student in this Term	
								$sql = " SELECT distinct (sum(mrk.ds_student_weight+mrk.si_student_weight+mrk.ci_student_weight)+impr.ds_impr_weight) as total from ".DB_PREFIX."marking as mrk, ".DB_PREFIX."impression_marking as impr WHERE impr.student_id = mrk.student_id AND impr.course_id = '".$course_id."' AND impr.term_id = '".$term->id."' AND mrk.course_id = '".$course_id."' AND mrk.term_id = '".$term->id."' AND mrk.status = '1' AND impr.status = '1' group by mrk.student_id ORDER BY total desc";
								
								$numberArrayForTerm = $dbObj->selectDataObj($sql);
								
								if(!empty($numberArrayForTerm)){
									$i = 0;
									foreach($numberArrayForTerm as $item){
										$numberArrayForTerm[$i]->position = $i+1;
										$i++; 
									}//foreach
								}//if
									
		$str .='				<td>'.$syndicateName->name.'</td>
								<td align="right" >'.view_number_two($term_percent_number).'</td>
								<td align="right" >'.view_number_two($sub_total_term_weight).'</td>
								<td align="center" >'.findGrade($term_percent_number).'</td>
								<td align="center" >'.findPosition($numberArrayForTerm, $sub_total_term_weight).'</td>';
										$total_term_weight += $sub_total_term_weight;
										$total_term_percent = ($total_term_weight*100)/$sub_total_weight;
									}//foreach
								
								//Find Impression marking of this student of this course Submitted by SI
								$sql = "select * from ".DB_PREFIX."si_impression_marking WHERE course_id = '".$course_id."' AND student_id = '".$student->id."'";
								$SiImprArr = $dbObj->selectDataObj($sql);
								$SiImprMark = $SiImprArr[0];
								$si_impr_mark = $SiImprMark->si_impr_mark;
								$converted_si_impr_mark = ($si_impr_mark*$course->si_impr_mark)/100;
								$student_total_weight = $total_term_weight+$converted_si_impr_mark;
								$student_total_percent = ($student_total_weight*100)/$total_weight;
								$converted_si_number = $SiImprMark->si_impr_weight;
								
								//Find out ths situation/Status of this course Impression Marks
								echo $sql = "select * from ".DB_PREFIX."si_impr_marking_lock WHERE course_id = '".$course_id."'";
								$forwardedArr = $dbObj->selectDataObj($sql);
								$forwarded = $forwardedArr[0];
								
								$SIname = getNameById('user', $forwarded->si_id);
								if($forwarded->status == '1' || $forwarded->status == '2'){
									$add_cond = 'readonly="readonly" class="input_number"';
								}else{
									$add_cond = '';
								}
								
		$str .='				<td align="right" >
									<input class="input_number" type="text" name="sub_total_number'.$sl.'" id="sub_total_number'.$sl.'" value="'.view_number_two($total_term_weight).'" size="5" readonly = "readonly" >
								</td>
								<td align="center" >
									<input class="input_number" type="text" name="sub_total_percent_number'.$sl.'" id="sub_total_percent_number'.$sl.'" value="'.view_number_two($total_term_percent).'" size="3" readonly = "readonly" >
								</td>
								<td align="center" >
									<input style="border:1px solid #000000; text-align:right;" type="text" name="number_'.$student->id.'" id="number'.$sl.'" value="'.$si_impr_mark.'" '.$readable.$add_cond.' size="3" onkeyup="return validateSiImprMarking(\'number'.$sl.'\');" maxlength="5" title="C/N '.$student->student_id.' - '.$rankName->name.' '.$student->official_name.'"  />
								</td>
								<td align="center" >
									<input class="input_number" type="text" name="converted_si_number'.$student->id.'" id="converted_si_number'.$sl.'" value="'.view_number_two($converted_si_number).'" size="4" readonly="readonly" />
								</td>
								<td align="center" >
									<input class="input_number" type="text" name="total_number'.$student->id.'" id="total_number'.$sl.'" value="'.view_number_two($student_total_weight).'" size="5" readonly="readonly" />
								</td>
								<td align="center">
									<input class="input_number" type="text" name="total_percent_number'.$student->id.'" id="total_percent_number'.$sl.'" value="'.view_number_two($student_total_percent).'" size="3" readonly="readonly" />
								</td>
								<td align="center">
									<input class="center_input" type="text" name="grade_number'.$student->id.'" id="grade_number'.$sl.'" value="'.findGrade($student_total_percent).'" size="3" readonly="readonly" />
								</td>
								<td align="center">
									<input class="center_input" type="text" name="position_number'.$student->id.'" id="position_number'.$sl.'" value="'.findPosition($numberArrayForCourse, $student_total_weight).'" size="3" readonly="readonly" />
								</td>
								<td>'.$student->ba_no.'</td>
								<td>'.$rankName->name.'</td>
								<td>'.$student->official_name.'</td>
							</tr>';
								$rownum++; //		
							}//foreach
							
		$str .= '	<tr class="head">
						<td colspan="'.(($totalTerm*6)+12).'">';
		if($forwarded->status == '0' || empty($forwarded)){
		$str .= '		<input type="submit" name="Submit" class="button" value="Save" />
					<input type="submit" name="Submit" class="button" value="Lock" />';
		}else if($forwarded->status == '1'){	
			$str .= '		<a href=javascript:submitRequest()><input type="button" value="'.REQUEST_TO_UNLOCK.'"></a>
							<span class="sentMsg">'.COURSE_LOCKED_MSG.$SIname->official_name.'</span>
							<div id="submitRequest">
								<div name="submit_req" id="submit_req" action="si_impr_marking.php" method="post">
									<textarea name="comment" id="comment" rows="5" cols="27" class="inputbox" alt="Comment"></textarea>
									<br  />
									<input type="hidden" name="action" value="save"  />
									<input type="submit" name="Submit" id="Submit"  value="Request to Unlock"  />
									<input type="button" name="btnclose" id="btnclose"  value="'.CANCEL.'"  onclick=Close("submitRequest") />											
								</div>						
							</div>';
		}else if($forwarded->status == '2'){
			$str .= '		'.REQUEST_MESSAGE.$SIname->official_name.'';
		}
		$str .= '		</td>
					</tr>
				</table>
			</td>
		</tr>';
	}else{ 		// if empty student array
		$str .= '		<tr>
							<td colspan="9" height="30">'.EMPTY_DATA.'</td>
						</tr>';
					}
		$str .=			'</table>
					</td>
				</tr>
			</table>';
			if(!empty($studentArr)){
				foreach($studentId as $id => $num_count){
		$str .='	<input type="hidden" name="student_id[]" id="student_id'.$num_count.'" value="'.$id.'" />';
				}//foreach
			}//if
		$str .='<input type="hidden" name="sl" id="sl" value="'.$sl.'" />
				<input type="hidden" name="course_id" id="course_id" value="'.$course_id.'" />
				<input type="hidden" name="term_id" id="term_id" value="'.$term_id.'" />
				<input type="hidden" name="si_impr_mark" id="si_impr_mark" value="'.$course->si_impr_mark.'" />
				<input type="hidden" name="si_impr_mark_limit" id="si_impr_mark_limit" value="'.$course->si_impr_mark_limit.'" />
				<input type="hidden" name="t_mark" id="t_mark" value="'.$total_weight.'" />
				<input type="hidden" name="total_count" id="total_count" value="'.$totalCount.'" />
				<input type="hidden" name="action" value="save" />
			</form>';
						
		echo $str;	
			
		}//if
	break;
	
	case 'assigned_exercise':	
		$course_id = $_REQUEST['course_id'];
		
		//Build Term List Array	
		//$query = "select id, name from ".DB_PREFIX."term where course_id = ".$course_id." OR term_type = 1 order by name asc";
		$query = "select id, name from ".DB_PREFIX."term where course_id = ".$course_id." order by name asc";
		$termArr = $dbObj->selectDataObj($query);
		$termId = array();
		$termId[0] = SELECT_TERM_OPT;
		if(!empty($termArr)){			
			foreach($termArr as $item){
				$termId[$item->id] = $item->name;
			}	
		}			
		$termList_opt = formSelectElement($termId, $term_id, 'term_id');
		
		echo $termList_opt;
		
	break;
	
	case 'syndicate_wise_exercise':	
		$syndicate_id = $_REQUEST['syndicate_id'];
		$term_id = $_REQUEST['term_id'];
		
		$syndicateQuery = "select id, syndicate_type from ".DB_PREFIX."syndicate WHERE id = '".$syndicate_id."'";
		$syndicate = $dbObj->selectDataObj($syndicateQuery);
		$syndicateType = $syndicate[0]->syndicate_type;
		//Build Term List Array	
		if($syndicateType == 1){
			$sql = "select exr.id, exr.name from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett,".DB_PREFIX."ds_to_course as dtc where dtc.syndicate_id = ".$syndicate_id." AND exr.id = ett.exercise_id AND dtc.term_id = ett.term_id AND exr.id = ett.exercise_id AND dtc.ds_id = ".$cur_user_id." AND exr.join_course = 1 order by exr.name asc";
		}else{
			//$sql = "select exr.id, exr.name from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett,".DB_PREFIX."ds_to_course as dtc where ett.term_id = ".$term_id." AND dtc.syndicate_id = ".$syndicate_id." AND exr.id = ett.exercise_id AND dtc.term_id = ett.term_id AND exr.id = ett.exercise_id AND dtc.ds_id = ".$cur_user_id." AND exr.join_course <> 1  order by exr.name asc";
			$sql = "select exr.id, exr.name from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett,".DB_PREFIX."ds_to_course as dtc where ett.term_id = ".$term_id." AND dtc.syndicate_id = ".$syndicate_id." AND exr.id = ett.exercise_id AND dtc.term_id = ett.term_id AND exr.id = ett.exercise_id order by exr.name asc";
		}
		$exerciseArr = $dbObj->selectDataObj($sql);
		
		if(empty($exerciseArr)){
			$sql = "select exr.id, exr.name from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett,".DB_PREFIX."ds_to_course as dtc where dtc.syndicate_id = ".$syndicate_id." AND ett.term_id = ".$term_id." AND exr.id = ett.exercise_id AND dtc.term_id = ett.term_id AND exr.id = ett.exercise_id AND dtc.ds_id = ".$cur_user_id." AND exr.join_course = 1 order by exr.name asc";
			$exerciseArr = $dbObj->selectDataObj($sql);
			
		}
		$exerciseId = array();
		$exerciseId[0] = SELECT_EXERCISE_OPT;
		if(!empty($exerciseArr)){			
			foreach($exerciseArr as $item){
				$exerciseId[$item->id] = $item->name;
			}	
		}	
		
		$exerciseList_opt = formSelectElement($exerciseId, $exercise_id, 'exercise_id','onchange = processFunction("assign_ds_mark")');
		
		echo $exerciseList_opt;
		
	break;
	
	
	case 'progressive_term_list':	
		$course_id = $_REQUEST['course_id'];

		//Build Term List Array	
		$query = "select id, name from ".DB_PREFIX."term where course_id = ".$course_id." AND lock_status = '1' order by name asc";
		$termArr = $dbObj->selectDataObj($query);
		$termId = array();
		$termId[0] = SELECT_TERM_OPT;
		if(!empty($termArr)){			
			foreach($termArr as $item){
				$termId[$item->id] = $item->name;
			}	
		}			
		$termList_opt = formSelectElement($termId, $term_id, 'term_id');
		
		echo $termList_opt;
		
	break;
	
	case 'exercise_by_type':	
		$term_id = $_REQUEST['term_id'];
		$exercise_type_id = $_REQUEST['exercise_type_id'];

		//Build Exercise List Array	of a specific type
		if($exercise_type_id != '0'){
			$sql = "select exr.id, exr.name from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where ett.term_id = ".$term_id." AND ett.exercise_id = exr.id AND exr.type_id = '".$exercise_type_id."' order by exr.name asc";
		}else{
			$sql = "select exr.id, exr.name from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where ett.term_id = ".$term_id." AND ett.exercise_id = exr.id order by exr.name asc";
		}
		$exerciseArr = $dbObj->selectDataObj($sql);
		$exerciseId = array();
		$exerciseId[0] = SELECT_EXERCISE_OPT;
		if(!empty($exerciseArr)){			
			foreach($exerciseArr as $item){
				$exerciseId[$item->id] = $item->name;
			}	
		}			
		$exerciseList_opt = formSelectElement($exerciseId, $exercise_id, 'exercise_id');
		
		echo $exerciseList_opt;
		
	break;
	
	case 'assign_term_for_delete':	
		$course_id = $_REQUEST['course_id'];
		//Build Term Array	
		$query = "select id, name from ".DB_PREFIX."term where wing_id = ".$cur_user_wing_id." AND course_id = '".$course_id."' AND status = '0' ORDER BY order_id asc";
		$termArr = $dbObj->selectDataObj($query);

		$termId = array();
		$termId[0] = SELECT_TERM_OPT;
		if(!empty($termArr)){			
			foreach($termArr as $item){
				$termId[$item->id] = $item->name;
			}	
		}
		
		if($uri == 'del_ds_impr.php'){
			$termList_opt = formSelectElement($termId, $term_id, 'term_id', ' onchange = processFunction("proceed_to_delete")');
		}else if($uri == 'del_exercise.php'){
			$termList_opt = formSelectElement($termId, $term_id, 'term_id', ' onchange = processFunction("assign_exercise_for_delete")');
		}
		
		echo $termList_opt.'<span class="required_field"> *</span>';
		
	break;
	
	case 'assign_exercise_for_delete':	
		$course_id = $_REQUEST['course_id'];
		$term_id = $_REQUEST['term_id'];

		//Build Exercise List Array	
		$query = "select exr.id, exr.name from ".DB_PREFIX."exercise as exr, ".DB_PREFIX."exercise_to_term as ett where exr.wing_id = ".$cur_user_wing_id." AND exr.course_id = '".$course_id."' AND ett.term_id = '".$term_id."' AND exr.id = ett.exercise_id order by exr.name asc";
		$exrArr = $dbObj->selectDataObj($query);

		$exrId = array();
		$exrId[0] = SELECT_EXERCISE_OPT;
		if(!empty($exrArr)){			
			foreach($exrArr as $item){
				$exrId[$item->id] = $item->name;
			}	
		}
		
		
		$exerciseList_opt = formSelectElement($exrId, $exercise_id, 'exercise_id', ' onchange = processFunction("proceed_to_delete")');
				
		echo $exerciseList_opt.'<span class="required_field"> *</span>';
		
	break;
	
	case 'proceed_to_delete':
		$course_id = $_REQUEST['course_id'];
		$term_id = $_REQUEST['term_id'];	
		$exercise_id = $_REQUEST['exercise_id'];	
		
		
		if($uri == 'del_si_impr.php'){
		
			//Be confirmed if all exercise has been deleted for deleting course result
			$query = "select * from ".DB_PREFIX."marking where course_id = ".$course_id."";
			$existing = $dbObj->selectDataObj($query);
			
			//Be confirmed if all Impression marks has been deleted for deleting course result
			$query = "select * from ".DB_PREFIX."impression_marking where course_id = ".$course_id."";
			$existing = $dbObj->selectDataObj($query);
			
			//if there is any data
			$query = "select * from ".DB_PREFIX."si_impr_marking_lock where course_id = '".$course_id."'";
			$isEmpty = $dbObj->selectDataObj($query);
			
		}else if($uri == 'del_ds_impr.php'){
			
			//Be confirmed if all exercise has been deleted for deleting term result
			$query = "select * from ".DB_PREFIX."marking where term_id = ".$term_id."";
			$existing = $dbObj->selectDataObj($query);
			
			//if there is any data
			$query = "select * from ".DB_PREFIX."impression_marking where course_id = '".$course_id."' AND term_id = ".$term_id."";
			$isEmpty = $dbObj->selectDataObj($query);
			
		}else if($uri == 'del_exercise.php'){
		
			//if there is any data
			$query = "select * from ".DB_PREFIX."marking where course_id = '".$course_id."' AND term_id = ".$term_id." AND exercise_id = '".$exercise_id."'";
			$isEmpty = $dbObj->selectDataObj($query);
		
		}//else if
		
		if((!empty($existing)) || (!empty($imprExisting))){
			$str = '<div class="no_delete">All Exercise marks of this term has not been deleted yet</div>';
		}else{
			if(!empty($isEmpty)){
				$str = '<input class="delete_button" type="submit" name="delete" id="delete" onclick="return deleteConfirmMsg();" value="Delete Marks" \>';
			}else{
				$str = '<div class="no_delete">There is no data to Delete</div>';
			}
		}
		echo $str;
		
	break;
	
	
	default:
		echo DATA_NOT_FOUND;
	break;
}//switch

