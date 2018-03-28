<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta name="description" content="Faculty,Student,Bangladesh,Institute,Management,Prospective Student,Current Student, Alumni,Admin,Static Page, Dynamic Page">
	<meta name="Keywords" content="Defence Service Command &nbsp; Staff College (DSCSC) Assessment Software">
	<link rel="stylesheet" href="css/template.css" type="text/css" />
	<link rel="stylesheet" href="css/style.css" type="text/css" />
	<link rel="shortcut icon" href="images/favicon.ico">
	<script type="text/javascript" language="javascript" src="JS/jquery-1.2.1.min.js"></script>
	<script type="text/javascript" language="javascript" src="JS/menu-collapsed.js"></script>
	<script type="text/javascript" language="javascript" src="JS/menu.js"></script>	
	<script type="text/javascript" src="JS/process.js"></script>
	<!-- Calendar: Start -->
	<link type="text/css" rel="stylesheet" href="date/src/css/jscal2.css" />
	<link id="skin-steel" title="Steel" type="text/css" rel="alternate stylesheet" href="date/src/css/steel/steel.css" />
	<script src="date/src/js/jscal2.js"></script>
	<script src="date/src/js/lang/en.js"></script>
	<!-- Calendar: End -->	
	<!-- FancyBox: Start -->
	<script>
		!window.jQuery && document.write('<script src="jquery-1.4.3.min.js"><\/script>');
	</script>
	<script type="text/javascript" src="fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
	<script type="text/javascript" src="fancybox/jquery.fancybox-1.3.4.pack.js"></script>
	<link rel="stylesheet" type="text/css" href="fancybox/jquery.fancybox-1.3.4.css" media="screen" />
	
	<script type="text/javascript">
		$(document).ready(function() {
			$("a#example4").fancybox({
				'opacity'		: true,
				'overlayShow'	: false,
				'transitionIn'	: 'elastic',
				'transitionOut'	: 'none'
			});

			$("a[rel=example_group]").fancybox({
				'transitionIn'		: 'none',
				'transitionOut'		: 'none',
				'titlePosition' 	: 'over',
				'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
					return '<span id="fancybox-title-over">Image ' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + '</span>';
				}
			});
			
			$('#toggle_fullscreen').click(function(){
			  // if already full screen; exit
			  // else go fullscreen
			  if (
				document.fullscreenElement ||
				document.webkitFullscreenElement ||
				document.mozFullScreenElement ||
				document.msFullscreenElement
			  ) {
				if (document.exitFullscreen) {
				  document.exitFullscreen();
				} else if (document.mozCancelFullScreen) {
				  document.mozCancelFullScreen();
				} else if (document.webkitExitFullscreen) {
				  document.webkitExitFullscreen();
				} else if (document.msExitFullscreen) {
				  document.msExitFullscreen();
				}
			  } else {
				element = $('#term_reports').get(0);
				if (element.requestFullscreen) {
				  element.requestFullscreen();
				} else if (element.mozRequestFullScreen) {
				  element.mozRequestFullScreen();
				} else if (element.webkitRequestFullscreen) {
				  element.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
				} else if (element.msRequestFullscreen) {
				  element.msRequestFullscreen();
				}
			  }
			});
		});
	</script>
	<!-- FancyBox: End -->
	<!-- Pagination::Start-->
	<link href="css/pagination.css" rel="stylesheet" type="text/css" />
	<link href="css/grey.css" rel="stylesheet" type="text/css" />
	<!-- Pagination::End-->
	<?php
		$base = $_SERVER['PHP_SELF'];
		$baseArr = explode("/",$base);
		$baseArrSize = sizeof($baseArr);
		$baseArrSize = $baseArrSize-1;
		$basefile = $baseArr[$baseArrSize];
	?>
	<title><?php echo YARDSTICK.$title; ?></title>
</head>

<body id="wrapper_container" <?php echo $str; ?> >
	<div id="main_body_wrapper">
		<div id="loaderBlock">&nbsp;</div>
		<div id="loader_about" onclick="close_about()">
			<table id="about_table" align="center" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td align="left" height="90" id="about_header" colspan="2">
						<img src="images/banner.png" alt="DSCSC :: About" height="95" />
						<img src="images/label.png" alt="DSCSC :: About" height="95" />
					</td>
				</tr>
				<tr>
				  <td style="padding:15px;" align="justify" colspan="2" height="50">
						<h4 id="abouth3">About Yardstick</h3>
						The overall objective of the Assessment Software is to 
						control the overall Assessment System – planning, scheduling,
						faculty management, student management, syndicate management, 
						relates faculty and student with syndicate. This software also
						deals with student assessment from DS, SI and CI. Through this 
						Assessment Software a student will be evaluated in a transparent
						way and optimum level of judgment will be ensured.
					</td>
				</tr>
				<tr>
					<td width="62%" height="30">
					  <span style="font-size:9px; text-shadow:2px; font:Helvetica; padding-bottom:13px;">Copyright &copy;2012 <a href="http://www.dscsc.mil.bd" target="_blank" id="about_dscsc">Defence Service Command & Staff College</a> </span>
					 </td>
					 <td>
						<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="185" height="15" title="Grameen Solution Limited">
							  <param name="movie" value="images/flashing.swf" />
							  <param name="quality" value="high" />
							  <embed src="images/flashing.swf" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="185" height="15"></embed>
						</object>
					</td>
				</tr>
			</table>
		</div>
		<div id="wrapper">