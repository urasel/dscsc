<?php
require_once("includes/header.php");

//check for loggedin
$usr = $user->getUser();
if(empty($usr)){
	header("Location:login.php");
	exit;
}
$title = 'Term Management';
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

switch($action){
	case 'view':	
	default:
		
		$sql = "select * from ".DB_PREFIX."term where wing_id = '".$cur_user_wing_id."' order by course_id, order_id asc";
		$termList = $dbObj->selectDataObj($sql);
		
		$action = 'view';
		$msg = $_REQUEST['msg'];
		
		//Pagination 
		if(!empty($termList)){
			$total_rows = sizeof($termList);
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
			$sql = "select * from ".DB_PREFIX."term WHERE id='".$id."'";	
			$termList = $dbObj->selectDataObj($sql);
			$term = $termList[0];
			
			//Make Sure wing admin of different wing cannot change a term by force from URL
			if($term->wing_id != $cur_user_wing_id){
				header("Location: dashboard.php");
				exit;
			}
			
			$name = $term->name;
			$term_type = $term->term_type;
			$order_id = $term->order_id;
			$course_id = $term->course_id;
			$ini_start_date = $term->start_date;
			$exploded_start_date = explode('-', $ini_start_date);
			$start_date = $exploded_start_date[2].'-'.$exploded_start_date[1].'-'.$exploded_start_date[0];
			$ini_end_date = $term->end_date;
			$exploded_end_date = explode('-', $ini_end_date);
			$end_date = $exploded_end_date[2].'-'.$exploded_end_date[1].'-'.$exploded_end_date[0];
			$weeks = $term->weeks;
		}else{
			$id = '';
			$name = '';
			$term_type = '';
			$order_id = '';
			$course_id = '';
			$start_date = '';
			$end_date = '';
			$weeks = '';
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
		
		$action = 'insert';
		break;
		
	case 'save':	
		$id = $_POST['id'];
		$course_id = $_POST['course_id'];
		$name = $_POST['name'];
		$term_type = ($_POST['term_type'] == 'on' ? 1:0);
		$order_id = $_POST['order_id'];
		$ini_start_date = $_POST['start_date'];
		$exploded_start_date = explode('-', $ini_start_date);
		$start_date = $exploded_start_date[2].'-'.$exploded_start_date[1].'-'.$exploded_start_date[0];
		$ini_end_date = $_POST['end_date'];
		$exploded_end_date = explode('-', $ini_end_date);
		$end_date = $exploded_end_date[2].'-'.$exploded_end_date[1].'-'.$exploded_end_date[0];
		$weeks = $_POST['weeks'];
		$status = '0';
		$created_date_time = $last_updated_time = date('Y-m-d H:i:s');
		
		//Check for not inserting any blank entry
		if($course_id == "" || $name == "" || $start_date == "" || $end_date == "" || $weeks == "" || $order_id == ""){
			$msg = PARAM_MISSING;
			if(empty($id)){
				$url = 'term.php?action=create&msg='.$msg;
			}else{
				$url = 'term.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
			}
			redirect($url);
		}
		
		//check repeation of Term Name
		if(empty($id)){
			$sql = "select name from ".DB_PREFIX."term WHERE name = '".$name."' AND course_id = '".$course_id."' limit 1";
			$termList = $dbObj->selectDataObj($sql);		
			
			if(!empty($termList)){
				$msg = $name.ALREADY_EXISTS;
				$url = 'term.php?action=create&msg='.$msg;
				redirect($url);
			}			
		}else if(!empty($id)){
			$sql = "select name from ".DB_PREFIX."term WHERE id!='".$id."' AND name = '".$name."' AND course_id = '".$course_id."' limit 1";
			$termList = $dbObj->selectDataObj($sql);		
			
			if(!empty($termList)){
				$msg = $name.ALREADY_EXISTS;
				$url = 'term.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
				redirect($url);
			}
		}
		
		//check repeation of Order ID in same Term
		if(empty($id)){
			$sql = "select order_id from ".DB_PREFIX."term WHERE order_id = '".$order_id."' AND course_id = '".$course_id."' limit 1";
			$termList = $dbObj->selectDataObj($sql);		
			
			if(!empty($termList)){
				$msg = ORDER_ID.' '.$order_id.ALREADY_EXISTS_IN;
				$url = 'term.php?action=create&msg='.$msg;
				redirect($url);
			}			
		}else if(!empty($id)){
			$sql = "select order_id from ".DB_PREFIX."term WHERE id!='".$id."' AND order_id = '".$order_id."' AND course_id = '".$course_id."' limit 1";
			$termList = $dbObj->selectDataObj($sql);		
			
			if(!empty($termList)){
				$msg = ORDER_ID.' '.$order_id.ALREADY_EXISTS_IN;
				$url = 'term.php?action=update&page='.$page.'&id='.$id.'&msg='.$msg;
				redirect($url);
			}
		}
		
		//save into data base
		if(!empty($id)){
			$fields = array('name' => $name,
						'term_type' => $term_type,
						'order_id' => $order_id,
						'wing_id' => $cur_user_wing_id,
						'course_id' => $course_id,
						'start_date' => $start_date,
						'end_date' => $end_date,
						'weeks' => $weeks,
						'updated_by' => $cur_user_id,
						'updated_datetime' => $last_updated_time
						);
						
			$where = "id = '".$id."'";
			$update_status = $dbObj->updateTableData("term", $fields, $where);	
			
			if(!$update_status){
				$msg = $name.COULD_NOT_BE_UPDATED;		
				$action = 'insert';
			}else{
				$msg = $name.HAS_BEEN_UPDATED;
				$url = 'term.php?action=view&page='.$page.'&msg='.$msg;
				redirect($url);
			}
		}else{
			$fields = array('name' => $name,
						'term_type' => $term_type,
						'order_id' => $order_id,
						'wing_id' => $cur_user_wing_id,
						'course_id' => $course_id,
						'start_date' => $start_date,
						'end_date' => $end_date,
						'weeks' => $weeks,
						'status' => '1',
						'created_by' => $cur_user_id,
						'created_datetime' => $created_date_time,
						'updated_by' => $cur_user_id,
						'updated_datetime' => $last_updated_time
						);
			
			$inserted = $dbObj->insertTableData("term", $fields);	
			if(!$inserted){
				$msg = $name.COULD_NOT_BE_CREATED;	
				$action = 'insert';
			}else{
				$msg = $name.CREATED_SUCCESSFULLY;
				$url = 'term.php?action=view&msg='.$msg;
				redirect($url);
			}
		}
		break;

	case 'delete':	
		$id = $_REQUEST['id'];
		
		$sql = "select * from ".DB_PREFIX."term WHERE id='".$id."'";	
		$termList = $dbObj->selectDataObj($sql);
		$term = $termList[0];
		
		//Make Sure wing admin of different wing cannot change a term by force from URL
		if($term->wing_id != $cur_user_wing_id){
			header("Location: dashboard.php");
			exit;
		}
			
		$name = $term->name;
		$where = "id='".$id."'";	
		$success = $dbObj->deleteTableData("term", $where);	
		
		if(!$success){
			$msg = $name.COULD_NOT_BE_DELETED;
		}else{
			$msg = $name.HAS_BEEN_DELETED;
		}
		
		$url = 'term.php?action=view&page='.$page.'&msg='.$msg;
		redirect($url);
		break;
	
	case 'status':	
		$id = $_REQUEST['id'];
		$sql = "select * from ".DB_PREFIX."term where id = '".$id."'";
		$termArr = $dbObj->selectDataObj($sql);
		$termId = $termArr[0];
		
		//Make Sure wing admin of different wing cannot change a term by force from URL
		if($termId->wing_id != $cur_user_wing_id){
			header("Location: dashboard.php");
			exit;
		}
		
		$name = $termId->name;
		$termStatus = $termId->status;
		
		if($termStatus == '0'){	
			$fields = array('status' => '1');
			$stat_msg = 'Disabled';
		}else{
			//At one time there can not be two Term Enabled
			$sql = "select * from ".DB_PREFIX."term  where course_id = '".$termId->course_id."' AND status = '0'";
			$isEnableArr = $dbObj->selectDataObj($sql);
			
			if(!empty($isEnableArr)){
				$msg = 'Please, Disable the Active Term first!';
				$url = 'term.php?action=view&page='.$page.'&msg='.$msg;
				redirect($url);
			}
			
			$fields = array('status' => '0');
			$stat_msg = 'Enabled';
		}
		
		$where = "id='".$id."'";	
		$success = $dbObj->updateTableData("term", $fields, $where);	
		
		if(!$success){
			$msg = $name.COULD_NOT_BE_UPDATED;
		}else{
			$msg = $name.HAS_BEEN.' '.$stat_msg.SUCCESSFULLY;
		}
		
		$url = 'term.php?action=view&page='.$page.'&msg='.$msg;
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
				<h1><?php echo TERM_MANAGEMENT; ?></h1>
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
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="module_content">
				<tr>
					<td>
						<table width="100%" cellpadding="0" cellspacing="0" border="0" class="datagrid">
								<tr class="footer">
									<td colspan="4" style=" background:#EEEEEE;">
										<b><a href="term.php?action=create"><?php echo CREATE; ?></a></b>
									</td>
								</tr>				
							<tr class="head">
								<td height="30" width="15%">
									<strong><?php echo COURSE; ?></strong>
								</td>
								<td height="30" width="15%">
									<strong><?php echo TERM; ?></strong>
								</td>
								<td height="30" width="5%" align="center">
									<strong><?php echo ORDER_ID; ?></strong>
								</td>
								<td height="30" width="40%" align="center">
									<strong><?php echo DURATION; ?></strong>
								</td>
								<td height="30" width="10%" align="center">
									<strong><?php echo NUMBER_OF_WEEKS; ?></strong>
								</td>
								<td height="30" width="10%">
									<strong><?php echo IS_JOIN_TERM; ?></strong>
								</td>
								<td height="30" width="10%">
									<strong><?php echo STATUS; ?></strong>
								</td>
								<td height="30" width="5%">
									<strong><?php echo ACTION; ?></strong>
								</td>
							</tr>
							<?php			
							if(!empty($termList)){	
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
							?>
									<tr <?php echo $class; ?> <?php if($termList[$rownum]->status == '0'){echo 'style="color:#FF3333;"';} ?>>
										<td width="15%">
											<?php
												if(!empty($termList[$rownum]->course_id)){
													$course = getNameById('course', $termList[$rownum]->course_id);
													echo $course->name;
												}else{
													echo NOT_SELECTED;
												}
											?> 
										</td>
										<td width="15%">
											<?php echo $termList[$rownum]->name; ?> 
										</td>
										<td width="15%" align="center">
											<?php echo $termList[$rownum]->order_id; ?> 
										</td>
										<td width="45%" align="center">
											<?php 
											if(!empty($termList[$rownum]->start_date) && !empty($termList[$rownum]->end_date)){
												echo dateConvertion($termList[$rownum]->start_date).' - '.dateConvertion($termList[$rownum]->end_date);
											}else{
												echo NOT_SELECTED;
											}?> 
										</td>
										<td width="10%" align="center">
											<?php 
											if(!empty($termList[$rownum]->weeks)){
												echo $termList[$rownum]->weeks;
											}else{
												echo NOT_SELECTED;
											}?> 
										</td>
										<td width="10%">
											<?php echo ($termList[$rownum]->term_type == 1 ? 'Yes': 'No') ?> 
										</td>
										<td width="10%">
											<?php 
												if($termList[$rownum]->status == '0'){
													echo ACTIVE;
												}else{
													echo INACTIVE;
												}
											?> 
										</td>
										<td width="5%">								
											<a href="term.php?action=update&page=<?php echo $page; ?>&id=<?php echo $termList[$rownum]->id; ?>"><?php echo UPDATE; ?></a>
											<a href="term.php?action=delete&page=<?php echo $page; ?>&id=<?php echo $termList[$rownum]->id; ?>" onclick="return confirm('Are you sure you want to delete?');"><?php echo DELETE; ?></a>
											<a href="term.php?action=status&page=<?php echo $page; ?>&id=<?php echo $termList[$rownum]->id; ?>" onclick="return confirm('Are you sure you want to <?php if($termList[$rownum]->status == '0'){echo INACTIVATE; } else {echo ACTIVATE;}?> this Term?');">
											<?php
												if($termList[$rownum]->status == '0'){
													echo DISABLE;
												}else{
													echo ENABLE;
												}
											?>
											</a>
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
										<?php echo pagination($total_rows,$limit,$page,''); ?>
									</td>
								</tr>
								<?php } ?>
								<tr class="footer">
									<td colspan="7">
										<b><a href="term.php?action=create"><?php echo CREATE; ?></a></b>
									</td>
								</tr>				
						</table>
					</td>
				</tr>
			</table>
				
	<?php 
		}elseif($action=="insert"){ 
	?>
				<form action="term.php" method="post" name="term" id="term" onsubmit="return validateTerm();" onmousemove="return days_between('start_date','end_date')">
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
								<?php echo COURSE; ?>:
							</td>
							<td width="80%">
								<?php echo $courseList_opt; ?>
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo TERM_NAME; ?>:
							</td>
							<td width="80%">
								<input name="name" id="name" type="text" class="inputbox" alt="Group Name" size="27" value="<?php echo $name; ?>" />
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo ORDER_ID; ?>:
							</td>
							<td width="80%">
								<input name="order_id" id="order_id" type="text" class="inputbox" alt="Order Id" size="27" value="<?php echo $order_id; ?>" onkeyup="return isNUM('order_id')" maxlength="1" />
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo INITIAL_DATE; ?>:
							</td>
							<td width="80%">
								<input type="text" id="start_date" name="start_date" readonly="readonly" value="<?php echo $start_date; ?>" />
									<img id="f_rangeStart_trigger" src="date/src/css/img/calendar.gif" title="Pick a Date" />
									<img id="f_clearRangeStart" src="date/src/css/img/no.png" title="Clear Date" onClick="return makeEmpty('start_date')" height="16" width="16"  />
									<script type="text/javascript">
									  RANGE_CAL_1 = new Calendar({
											  inputField: "start_date",
											  dateFormat: "%d-%m-%Y",
											  trigger: "f_rangeStart_trigger",
											  bottomBar: true,
											  onSelect: function() {
													  var date = Calendar.intToDate(this.selection.get());
													  this.hide();
											  }
									  });
									</script>
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo TERMINATION_DATE; ?>:
							</td>
							<td width="80%">
								<input type="text" id="end_date" name="end_date" readonly="readonly" value="<?php echo $end_date; ?>" />
									<img id="f_rangeStart_triggerr" src="date/src/css/img/calendar.gif" title="Pick a Date" />
									<img id="f_clearRangeStart" src="date/src/css/img/no.png" title="Clear Date" onClick="return makeEmpty('end_date')" height="16" width="16"  />
									<script type="text/javascript">
									  RANGE_CAL_1 = new Calendar({
											  inputField: "end_date",
											  dateFormat: "%d-%m-%Y",
											  trigger: "f_rangeStart_triggerr",
											  bottomBar: true,
											  onSelect: function() {
													  var date = Calendar.intToDate(this.selection.get());
													  this.hide();
											  }
									  });
									</script>
								<span class="required_field">*</span>
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo NUMBER_OF_WEEKS; ?>:
							</td>
							<td width="80%">
								<input name="weeks" id="weeks" type="text" class="inputbox" alt="Number of Weeks" size="20" readonly="readonly" value="<?php echo $weeks; ?>" />
							</td>
						</tr>
						
						<tr>
							<td colspan="2">&nbsp;
							</td>
						</tr>
						<tr>
							<td height="30" width="20%">
								<?php echo IS_JOIN_TERM; ?>:
							</td>
							<td width="80%">
								<?php 
								
								if($term_type == 1){
									$term_type = ' checked="checked"';
								}
								echo '<input type="checkbox" name="term_type" '.$term_type .' />'
								?>
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
					<input type="hidden" name="page" id="page" value="<?php echo $page; ?>" />
				</form>
			
	<?php }?>
</div>
			
<?php
require_once("includes/footer.php");
?>