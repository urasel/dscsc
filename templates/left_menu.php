<?php
$curr_uri = $_SERVER['PHP_SELF'];
$curr_uri_arr = explode("/", $curr_uri);
$leng = sizeof($curr_uri_arr);
$curr_page = $curr_uri_arr[($leng-1)];

//Find if the logged in DS is active/inactive
if($usr[0]->group_id == '5'){
	$sql = "select dtc.course_id, dtc.syndicate_id from ".DB_PREFIX."ds_to_course as dtc, ".DB_PREFIX."course as crs WHERE dtc.wing_id = '".$usr[0]->wing_id."' AND dtc.ds_id = '".$usr[0]->id."' AND crs.status = '0' AND crs.id = dtc.course_id limit 1";
	$active_ds = $dbObj->selectDataObj($sql);
}//if

if($usr[0]->group_id){
	$sql = "select stc.course_id from ".DB_PREFIX."si_to_course as stc,  ".DB_PREFIX."course as crs where stc.si_id = ".$usr[0]->id." AND stc.wing_id = '".$usr[0]->wing_id."' AND crs.status = '0' AND crs.id = stc.course_id ORDER BY crs.name";
	$active_si = $dbObj->selectDataObj($sql);
}//if
?>
<div id="leftMenu">
	<ul id="menu">
		<h3><?php echo MAIN_MENU; ?></h3>
		<?php if(!empty($usr) && $usr[0]->group_id == '1'){ ?>
		<li <?php if($curr_page == 'user_group.php') echo 'class="active_menu"';?>>
			<a href="user_group.php" class="user_group" rel="user_group"><?php echo USER_GROUP_MANAGEMENT; ?></a>
		</li>
		<?php } 
		if(!empty($usr) && ($usr[0]->group_id >= '1' && $usr[0]->group_id <= '3')){
		?>
		<li <?php if($curr_page == 'user.php') echo 'class="active_menu"';?>>
			<a href="user.php" class="user" rel="user"><?php echo USER_MANAGEMENT; ?></a>
		</li>
		<?php }
			if(!empty($usr) && $usr[0]->group_id == '1'){ 
		?>
		<li <?php if($curr_page == 'wing.php') echo 'class="active_menu"';?>>
			<a href="wing.php" class="wing" rel="wing"><?php echo WING_MANAGEMENT; ?></a>
		</li>
		<?php }
		if(!empty($usr) && $usr[0]->group_id == '2'){ 
		?>
		<li <?php if($curr_page == 'course.php' || $curr_page == 'assign_si.php' || $curr_page == 'assign_ds.php' || $curr_page == 'assigned_ds.php') echo 'class="active_menu parent"'; else echo 'class="parent"'; ?>>
        	<a href="course.php" class="course" rel="course"><?php echo COURSE_MANAGEMENT; ?></a>
			<ul>
				<li>
					<a href="course.php" class="course" rel="course"><?php echo COURSE_INFO; ?></a>
				</li>		
				<li>
					<a href="assign_si.php" class="assign_si" rel="assign_si"><?php echo ASSIGN_SI; ?></a>
				</li>
				<li>
					<a href="assign_ds.php" class="assign_ds" rel="assign_ds"><?php echo ASSIGN_DS; ?></a>
				</li>
				<li>
					<a href="assigned_ds.php" class="assigned_ds" rel="assigned_ds"><?php echo ASSIGNED_DS; ?></a>
				</li>	
			</ul>
		</li >
		<li <?php if($curr_page == 'term.php') echo 'class="active_menu"';?>>
			<a href="term.php" class="term" rel="term"><?php echo TERM_MANAGEMENT; ?></a>
		</li>
		<li <?php if($curr_page == 'marknweight.php' || $curr_page == 'assign_si_impr_mark.php' || $curr_page == 'assign_ds_impr_mark.php' || $curr_page == 'assigned_marknweight.php') echo 'class="active_menu parent"'; else echo 'class="parent"'; ?>>
        	<a href="marknweight.php" class="marknweight" rel="marknweight"><?php echo MARK_WEIGHT_MANAGEMENT; ?></a>
			<ul>
				<li>
					<a href="marknweight.php" class="marknweight" rel="marknweight"><?php echo MARK_WEIGHT_INFO; ?></a>
				</li>		
				<li>
					<a href="assign_si_impr_mark.php" class="assign_si_impr_mark" rel="assign_si_impr_mark"><?php echo SI_IMPR_MARK; ?></a>
				</li>
				<li>
					<a href="assign_ds_impr_mark.php" class="assign_ds_impr_mark" rel="assign_ds_impr_mark"><?php echo DS_IMPR_MARK; ?></a>
				</li>
				<li>
					<a href="assigned_marknweight.php" class="assigned_marknweight" rel="assigned_marknweight"><?php echo ASSIGNED_MARKS; ?></a>
				</li>
			</ul>
		</li >
		<li <?php if($curr_page == 'unlock_si_req.php') echo 'class="active_menu"';?>>
			<a href="unlock_si_req.php" class="unlock_si_req" rel="unlock_si_req"><?php echo UNLOCK_SI_REQ; ?></a>
		</li>
		<li <?php if($curr_page == 'del_exercise.php' || $curr_page == 'del_ds_impr.php' || $curr_page == 'del_si_impr.php') echo 'class="active_menu parent"'; else echo 'class="parent"'; ?>>
        	<a href="del_exercise.php" class="del_exercise" rel="del_exercise"><?php echo DELETE_MARKS; ?></a>
			<ul>
				<li>
					<a href="del_exercise.php" class="del_exercise" rel="del_exercise"><?php echo DELETE_EXERCISE_MARK; ?></a>
				</li>		
				<li>
					<a href="del_ds_impr.php" class="del_ds_impr" rel="del_ds_impr"><?php echo DELETE_DS_IMPR_MARK; ?></a>
				</li>
				<li>
					<a href="del_si_impr.php" class="del_si_impr" rel="del_si_impr"><?php echo DELETE_SI_IMPR_MARK; ?></a>
				</li>
			</ul>
		</li >
		<?php } 
		if(!empty($usr) && $usr[0]->group_id == '3'){ 
		?>
		<li <?php if($curr_page == 'rank.php') echo 'class="active_menu"';?>>
			<a href="rank.php" class="rank" rel="rank"><?php echo RANK_MANAGEMENT; ?></a>
		</li>
		<li <?php if($curr_page == 'appointment.php') echo 'class="active_menu"';?>>
			<a href="appointment.php" class="appointment" rel="appointment"><?php echo APPOINTMENT_MANAGEMENT; ?></a>
		</li>
		<li <?php if($curr_page == 'exercise_type.php') echo 'class="active_menu"';?>>
			<a href="exercise_type.php" class="exercise_type" rel="exercise_type"><?php echo EXERCISE_TYPE_MANAGEMENT; ?></a>
		</li>
		<li <?php if($curr_page == 'exercise.php' || $curr_page == 'relate_exr_to_trm.php' || $curr_page == 'assigned_exercise.php') echo 'class="active_menu parent"'; else echo 'class="parent"'; ?>>
        	<a href="exercise.php" class="exercise" rel="exercise"><?php echo EXERCISE_MANAGEMENT; ?></a>
			<ul>
				<li>
					<a href="exercise.php" class="exercise" rel="exercise"><?php echo EXERCISE_INFO; ?></a>
				</li>		
				<li>
					<a href="relate_exr_to_trm.php" class="relate_exr_to_trm" rel="relate_exr_to_trm"><?php echo RELATE_EXERCISE_WITH_TERM; ?></a>
				</li>
				<li>
					<a href="assigned_exercise.php" class="assigned_exercise" rel="assigned_exercise"><?php echo ASSIGNED_EXERCISE; ?></a>
				</li>	
			</ul>
		</li >
		<li <?php if($curr_page == 'syndicate.php' || $curr_page == 'relate_syn_to_crs.php') echo 'class="active_menu parent"'; else echo 'class="parent"'; ?>>
        	<a href="syndicate.php" class="syndicate" rel="syndicate"><?php echo SYNDICATE_MANAGEMENT; ?></a>
			<ul>
				<li>
					<a href="syndicate.php" class="syndicate" rel="syndicate"><?php echo SYNDICATE_INFO; ?></a>
				</li>		
				<li>
					<a href="relate_syn_to_crs.php" class="relate_syn_to_crs" rel="relate_syn_to_crs"><?php echo RELATE_SYNDICATE_WITH_COURSE; ?></a>
				</li>	
			</ul>
		</li >
		<li <?php if($curr_page == 'student.php' || $curr_page == 'student_assign.php' || $curr_page == 'assigned_student.php') echo 'class="active_menu parent"'; else echo 'class="parent"'; ?>>
        	<a href="student.php" class="student" rel="student"><?php echo STUDENT_MANAGEMENT; ?></a>
			<ul>
				<li>
					<a href="student.php" class="student" rel="student"><?php echo STUDENT_INFO; ?></a>
				</li>		
				<li>
					<a href="student_assign.php" class="student_assign" rel="student_assign"><?php echo STUDENT_ASSIGNMENT; ?></a>
				</li>
				<li>
					<a href="assigned_student.php" class="assigned_student" rel="assigned_student"><?php echo ASSIGNED_STUDENT; ?></a>
				</li>	
			</ul>
		</li >
		
		<?php } 
		if(!empty($usr) && ($usr[0]->group_id == '4') && !empty($active_si)){ ?>

		<li <?php if($curr_page == 'si_marking.php') echo 'class="active_menu"';?>>
			<a href="si_marking.php" class="si_marking" rel="si_marking"><?php echo SI_MOD_MARK; ?></a>
		</li>
		<li <?php if($curr_page == 'ci_marking.php') echo 'class="active_menu"';?>>
			<a href="ci_marking.php" class="ci_marking" rel="ci_marking"><?php echo CI_MOD_MARK; ?></a>
		</li>
		<li <?php if($curr_page == 'lock_term.php') echo 'class="active_menu"';?>>
			<a href="lock_term.php" class="lock_term" rel="lock_term"><?php echo LOCK_TERM; ?></a>
		</li>
		<li <?php if($curr_page == 'si_impr_marking.php') echo 'class="active_menu"';?>>
			<a href="si_impr_marking.php" class="si_impr_marking" rel="si_impr_marking"><?php echo ASSIGN_MOD_IMPR_MARK; ?></a>
		</li>
		<li <?php if($curr_page == 'unlock_ds_req.php') echo 'class="active_menu"';?>>
			<a href="unlock_ds_req.php" class="unlock_ds_req" rel="unlock_ds_req"><?php echo UNLOCK_REQUEST_LOCK; ?></a>
		</li>
		<?php } 
		if(!empty($usr) && ($usr[0]->group_id == '5') && !empty($active_ds)){ ?>

		<li <?php if($curr_page == 'ds_marking.php') echo 'class="active_menu"';?>>
			<a href="ds_marking.php" class="ds_marking" rel="ds_marking"><?php echo ASSIGN_MARK; ?></a>
		</li>
		<li <?php if($curr_page == 'ds_impr_marking.php') echo 'class="active_menu"';?>>
			<a href="ds_impr_marking.php" class="ds_impr_marking" rel="ds_impr_marking"><?php echo ASSIGN_IMPR_MARK; ?></a>
		</li>
		<?php 
		} 
		if(!empty($usr) && (($usr[0]->group_id == '4' && !empty($active_si)) || ($usr[0]->group_id == '5' && !empty($active_ds)))){ ?>
		<li <?php if($curr_page == 'exam_rep.php' || $curr_page == 'term_rep.php' || $curr_page == 'course_rep.php' || $curr_page == 'progress_rep.php') echo 'class="active_menu parent"'; else echo 'class="parent"'; ?>>
        	<a href="exam_rep.php" class="student" rel="student"><?php echo REPORTS; ?></a>
			<ul>
				<li>
					<a href="exam_rep.php" class="exam_rep" rel="exam_rep"><?php echo EXAM_REPORTS; ?></a>
				</li>
				<li>
					<a href="term_rep.php" class="term_rep" rel="term_rep"><?php echo TERM_RESULTS; ?></a>
				</li>
				<?php if($cur_user_group_id == '4'){ ?>
				<li>
					<a href="course_rep.php" class="course_rep" rel="course_rep"><?php echo COURSE_RESULTS; ?></a>
				</li>
				<?php } ?>
				<li>
					<a href="progress_rep.php" class="progress_rep" rel="progress_rep"><?php echo PERFORMANCE_ANALYSIS; ?></a>
				</li>
			</ul>
		</li >
		<?php } ?>
		<li <?php if($curr_page == 'change_pass.php') echo 'class="active_menu"';?>>
			<a href="change_pass.php" class="change_pass" rel="change_pass"><?php echo CHANGE_PASS; ?></a>
		</li>
		<li <?php if($curr_page == 'user_manual.php' || $curr_page == 'about.php') echo 'class="active_menu parent"'; else echo 'class="parent"'; ?>>
        	<a href="user_manual.php" class="user_manual" rel="user_manual"><?php echo HELP; ?></a>
			<ul>
				<li>
					<a target="_blank" href="<?php echo user_manual($cur_user_group_id); ?>" class="user_manual" rel="user_manual"><?php echo USER_MANUAL; ?></a>
				</li>
				<li>
					<a href="#" class="about" rel="about" onclick="block_about()"><?php echo ABOUT_YARDSTICK; ?></a>
				</li>
			</ul>
		</li>
	</ul>
</div>