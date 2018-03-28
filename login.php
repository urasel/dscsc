<?php
require_once("includes/header.php");
$title = 'Login Panel';

// for info for counter
//$page1 = 'dcsc login Page';
//include ('counter.php');
//addinfo($page1);

//if login values are submitted
if(isset($_POST['login_submitted'])){

	$username = $_POST['username'];
	$password = $_POST['password'];
	$password = md5($password);
	$sec_key = $_POST['sec_key'];
	$user = new User();
	
	//Check validity against submitted values
	if($user->validUser($username, $password, $sec_key)){
		header("Location:index.php");
		exit;
	}
	
}else{//If already loggedIn and again try to browse login.php page	
	$usr = $user->getUser();
	if(!empty($usr)){
		header("Location:index.php");
		exit;
	}
}//if

//Call templates
require_once("includes/templates.php");
require_once("templates/top_menu.php");

?>
<div id="right_column_extend">
	<!--The above portion expands the login page with the monitor's height-->
	<table width="100%" cellspacing="0" cellpadding="0" border="0" class="module_header">
		<tr>
			<td height="70">&nbsp;
								
			</td>			
		</tr>
	</table>
	<form action="login.php" method="post" name="user_login" id="user_login_form" style="margin:0px; padding:0px;">
		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="module_content">
			<tr>
				<td align="center">
					<table cellpadding="0" cellspacing="0" border="0" id="login_panel">
						<tr>
							<td width="366" height="87" colspan="2"></td>							
						</tr>
						<tr>
							<td height="30" width="93" align="right">
								Username:
							</td>
							<td width="" align="left">
								&nbsp;&nbsp;&nbsp;<input name="username" id="username" type="text" class="inputbox" alt="Username" size="33" />
							</td>
						</tr>
						<tr>
							<td height="30" width="93" align="right">
								Password:
							</td>
							<td width="" align="left">
								&nbsp;&nbsp;&nbsp;<input type="password" id="password" name="password" class="inputbox" size="33" alt="Password" />
							</td>
						</tr>
						<tr>
							<td height="30" width="93" align="right">
								Security Key:
							</td>
							<td width="" align="left">
								&nbsp;&nbsp;&nbsp;<input type="password" id="sec_key" name="sec_key" class="inputbox" size="33" alt="Security Key" />
							</td>
						</tr>
						<tr>
							<td colspan="2" id="btn_submit" align="right">
								<div>
									<input type="submit" name="Submit" class="button" value=""  />
								</div>													
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>		
		<input type="hidden" name="login_submitted" value="1" />
	</form>
	<!--The above portion expands the login page below with the monitor's height-->
	<table width="100%" cellspacing="0" cellpadding="0" border="0" class="module_header">
		<tr>
			<td height="70">&nbsp;
			</td>			
		</tr>
	</table>
</div>
			
<?php
require_once("includes/footer.php");
?>
