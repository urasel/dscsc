function initMenu() {
	$('#menu ul').hide();
	$('#menu li.active_menu ul').show();
	//$('#menu ul:first').show().addClass('expanded');
	$('#menu li a').click(
		function() {
			var checkElement = $(this).next();
			if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
				return false;
			}
			if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
				$('#menu ul:visible').slideUp('normal').removeClass('active_menu');
				checkElement.slideDown('normal').addClass('active_menu');
				return false;
			}
		}
	);
} 
$(document).ready(function() {initMenu();});