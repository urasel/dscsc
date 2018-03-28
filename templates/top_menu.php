<?php 
$usr = $user->getUser();
$cur_user_group_id = $usr[0]->group_id;
?>
<div id="topBar">
	<!--Top Menu Started-->
	<div id="topmenu">
		<ul class="menu">
			<li class="menu_item">
				<a href="index.php" id="home_icon">
					<?php
						if(!empty($usr)){
							echo HOME.' | '.LOG_OUT;
						}//if
					 ?>
				</a>
			</li>
		</ul>
	</div>	
	<?php 
		if(!empty($usr) && ($cur_user_group_id < 4)){
	?>
			<div id="search_module">
				<form id="search" class="search" name="search" method="post" action="search_result.php" onsubmit="return validateToken()">
					<table width="100%" cellpadding="0" cellspacing="0" border="0">
						<tr>							
							<td align="right" style="padding-top:7px;">
								<input name="token" id="token" type="text" class="topsearch" align="right" alt="" size="25" value="<?php if(!empty($token)){ echo $token; }else{ echo EMPTY_TOKEN; } ?>" onblur="if(this.value=='')this.value=this.defaultValue;" onfocus="if(this.value==this.defaultValue)this.value='';" />
							</td>					
							<td align="left" style="padding-top:7px;">
								<input type="submit" name="Submit" id="button" class="topsearch_panel" value="SEARCH" />			
							</td>
						</tr>		
					</table>
				</form>
			</div>
	<!--Top Menu End-->
	<?php 
		}
	?>

</div>