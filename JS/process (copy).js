function browserVerification(){
	var xmlhttp;
	if (window.XMLHttpRequest){
	  // code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	}else if (window.ActiveXObject){
	  // code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}else{
	  alert("Your browser does not support XMLHTTP!");
	  //xmlhttp = 0;
	}
	return xmlhttp;	
}//function

function processFunction(actiontype)
{	
	var xmlhttp;
	var fileToProcess;
	
	xmlhttp = browserVerification();	
	
	switch(actiontype){
			
		case 'check_username_availability':
			var username = document.getElementById("username").value;
			fileToProcess = "ajax.php?action=check_username_availability&username="+username;	
		break;
		
		case 'generate_sec_key':
			fileToProcess = "ajax.php?action=generate_sec_key";	
		break;
		
		/*case 'check_student_name_availability':
			var official_name = document.getElementById("official_name").value;
			fileToProcess = "ajax.php?action=check_student_name_availability&official_name="+official_name;	
		break;*/
		
		case 'assign_rank_appoint':
			var wing_id = document.getElementById("wing_id").value;
			fileToProcess = "ajax.php?action=assign_rank_appoint&wing_id="+wing_id;	
		break;
		
		case 'assign_syndicate':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=assign_syndicate&course_id="+course_id;	
		break;
		
		case 'assign_term_for_student':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=assign_term_for_student&course_id="+course_id;	
		break;
		
		case 'assign_term_for_exercise':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=assign_term_for_exercise&course_id="+course_id;	
		break;
		
		case 'assign_exr_with_term':
			var course_id = document.getElementById("course_id").value;
			var term_id = document.getElementById("term_id").value;
			fileToProcess = "ajax.php?action=assign_exr_with_term&course_id="+course_id+"&term_id="+term_id;	
		break;
		
		case 'assign_syndicate_for_student':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=assign_syndicate_for_student&course_id="+course_id;	
		break;
		
		case 'assign_student_for_syndicate':
			var course_id = document.getElementById("course_id").value;
			var syndicate_id = document.getElementById("syndicate_id").value;
			var term_id = document.getElementById("term_id").value;
			fileToProcess = "ajax.php?action=assign_student_for_syndicate&course_id="+course_id+"&syndicate_id="+syndicate_id+"&term_id="+term_id;	
		break;
		
		case 'assign_si':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=assign_si&course_id="+course_id;	
		break;
		
		case 'assign_ds':
			var course_id = document.getElementById("course_id").value;
			var term_id = document.getElementById("term_id").value;
			var syndicate_id = document.getElementById("syndicate_id").value;
			fileToProcess = "ajax.php?action=assign_ds&course_id="+course_id+"&syndicate_id="+syndicate_id+"&term_id="+term_id;	
		break;
		
		case 'course_to_term_syn':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=course_to_term_syn&course_id="+course_id;	
		break;
		
		case 'assign_term_si_mark':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=assign_term_si_mark&course_id="+course_id;	
		break;
		
		case 'si_impr_mark':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=si_impr_mark&course_id="+course_id;	
		break;
		
		case 'assign_term_ds_mark':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=assign_term_ds_mark&course_id="+course_id;	
		break;
		
		case 'ds_impr_mark':
			var term_id = document.getElementById("term_id").value;
			fileToProcess = "ajax.php?action=ds_impr_mark&term_id="+term_id;	
		break;
		
		case 'assign_term_for_course':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=assign_term_for_course&course_id="+course_id;	
		break;
		
		case 'exr_info':
			var exercise_id = document.getElementById("exercise_id").value;
			fileToProcess = "ajax.php?action=exr_info&exercise_id="+exercise_id;
		break;
		
		case 'assign_ds_mark':
			var exercise_id = document.getElementById("exercise_id").value;
			fileToProcess = "ajax.php?action=assign_ds_mark&exercise_id="+exercise_id;
		break;
		
		case 'si_mod_mark_term':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=si_mod_mark_term&course_id="+course_id;
		break;
		
		case 'ci_mod_mark_term':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=ci_mod_mark_term&course_id="+course_id;
		break;
		
		case 'si_mod_mark_exr':
			var term_id = document.getElementById("term_id").value;
			fileToProcess = "ajax.php?action=si_mod_mark_exr&term_id="+term_id;
		break;
		
		case 'ci_mod_mark_exr':
			var term_id = document.getElementById("term_id").value;
			fileToProcess = "ajax.php?action=ci_mod_mark_exr&term_id="+term_id;
		break;
		
		case 'assign_si_mark':
			var exercise_id = document.getElementById("exercise_id").value;
			var term_id = document.getElementById("term_id").value;
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=assign_si_mark&exercise_id="+exercise_id+"&term_id="+term_id+"&course_id="+course_id;
		break;
		
		case 'assign_ci_mark':
			var exercise_id = document.getElementById("exercise_id").value;
			var term_id = document.getElementById("term_id").value;
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=assign_ci_mark&exercise_id="+exercise_id+"&term_id="+term_id+"&course_id="+course_id;
		break;
		
		case 'impr_mark_term':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=impr_mark_term&course_id="+course_id;
		break;
		
		case 'term_impr_mark_assign':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=term_impr_mark_assign&course_id="+course_id;
		break;
		
		case 'si_result_term':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=si_result_term&course_id="+course_id;
		break;
		
		case 'si_report_term':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=si_report_term&course_id="+course_id;
		break;
		
		case 'si_result_exr':
			var term_id = document.getElementById("term_id").value;
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=si_result_exr&term_id="+term_id+"&course_id="+course_id;
		break;
		
		case 'si_exercise_list_for_progressive_result':
			var term_id = document.getElementById("term_id").value;
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=si_exercise_list_for_progressive_result&term_id="+term_id+"&course_id="+course_id;
		break;
		
		case 'assigned_student':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=assigned_student&course_id="+course_id;
		break;
		
		case 'assigned_exercise':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=assigned_exercise&course_id="+course_id;
		break;
		
		case 'progressive_term_list':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=progressive_term_list&course_id="+course_id;
		break;
		
		case 'progress_result_term':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=progress_result_term&course_id="+course_id;
		break;
		
		case 'exercise_by_type':
			var term_id = document.getElementById("term_id").value;
			var exercise_type_id = document.getElementById("exercise_type_id").value;
			fileToProcess = "ajax.php?action=exercise_by_type&term_id="+term_id+"&exercise_type_id="+exercise_type_id;
		break;
		
		case 'assign_term_for_delete':
			var course_id = document.getElementById("course_id").value;
			fileToProcess = "ajax.php?action=assign_term_for_delete&course_id="+course_id;
		break;
		
		case 'assign_exercise_for_delete':
			var course_id = document.getElementById("course_id").value;
			var term_id = document.getElementById("term_id").value;
			fileToProcess = "ajax.php?action=assign_exercise_for_delete&term_id="+term_id+"&course_id="+course_id;
		break;
		
		case 'proceed_to_delete':
			var course_id = document.getElementById("course_id").value;
			if(document.getElementById("term_id")){
				var term_id = document.getElementById("term_id").value;
			}
			if(document.getElementById("exercise_id")){
				var exercise_id = document.getElementById("exercise_id").value;
			}
			fileToProcess = "ajax.php?action=proceed_to_delete&term_id="+term_id+"&course_id="+course_id+"&exercise_id="+exercise_id;
		break;
		
		case 'default':
				return false;
		break;
		//For format to procedure : End
	}//switch
	
	//alert(resArr[0]);
	xmlhttp.onreadystatechange=function(){

		var action = actiontype;
		
		if(action == 'check_username_availability'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("username_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("username_display").style.display = "block";	
				document.getElementById("username_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'generate_sec_key'){
			if(xmlhttp.readyState!=4){
				document.getElementById("sec_key_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("sec_key_display").style.display = "block";	
				document.getElementById("sec_key_display").innerHTML = xmlhttp.responseText;
			}//else if
		/*}else if(action == 'check_student_name_availability'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("studentname_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("studentname_display").style.display = "block";	
				document.getElementById("studentname_display").innerHTML = xmlhttp.responseText;
			}//else if*/
		}else if(action == 'progress_result_term'){
			if(xmlhttp.readyState!=4){
				document.getElementById("term_display").style.display = "none";
				document.getElementById("student_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				
				var response = xmlhttp.responseText;
				found = response.search(":*:");
				if(found == "-1"){ //no match found			
				}else{ //match found
					var resArr = response.split(":*:");
				}
				
				document.getElementById("term_display").style.display = "block";			
				document.getElementById("term_display").innerHTML = resArr[0];							

				document.getElementById("student_display").style.display = "block";			
				document.getElementById("student_display").innerHTML = resArr[1];							
			}//else if	
		}else if(action == 'assigned_student'){
			if(xmlhttp.readyState!=4){
				document.getElementById("term_display").style.display = "none";
				document.getElementById("syndicate_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				
				var response = xmlhttp.responseText;
				found = response.search(":*:");
				if(found == "-1"){ //no match found			
				}else{ //match found
					var resArr = response.split(":*:");
				}
				
				document.getElementById("term_display").style.display = "block";			
				document.getElementById("term_display").innerHTML = resArr[0];							

				document.getElementById("syndicate_display").style.display = "block";			
				document.getElementById("syndicate_display").innerHTML = resArr[1];							
			}//else if
		}else if(action == 'assign_rank_appoint'){
			if(xmlhttp.readyState!=4){
				document.getElementById("rank_display").style.display = "none";
				document.getElementById("appointment_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				
				var response = xmlhttp.responseText;
				found = response.search(":*:");
				if(found == "-1"){ //no match found			
				}else{ //match found
					var resArr = response.split(":*:");
				}
				
				document.getElementById("rank_display").style.display = "block";			
				document.getElementById("rank_display").innerHTML = resArr[0];							

				document.getElementById("appointment_display").style.display = "block";			
				document.getElementById("appointment_display").innerHTML = resArr[1];							
			}//else if
		}else if(action == 'assign_syndicate'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("syndicate_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("syndicate_display").style.display = "block";	
				document.getElementById("syndicate_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'assign_term_for_student'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("term_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("term_display").style.display = "block";	
				document.getElementById("term_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'assign_term_for_exercise'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("term_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("term_display").style.display = "block";	
				document.getElementById("term_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'assign_exr_with_term'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("exercise_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("exercise_display").style.display = "block";	
				document.getElementById("exercise_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'assigned_exercise'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("term_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("term_display").style.display = "block";	
				document.getElementById("term_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'progressive_term_list'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("term_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("term_display").style.display = "block";	
				document.getElementById("term_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'assign_syndicate_for_student'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("syndicate_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("syndicate_display").style.display = "block";	
				document.getElementById("syndicate_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'si_result_term'){
			if(xmlhttp.readyState!=4){
				document.getElementById("term_display").style.display = "block";
				document.getElementById("syndicate_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				
				var response = xmlhttp.responseText;
				found = response.search(":*:");
				if(found == "-1"){ //no match found			
				}else{ //match found
					var resArr = response.split(":*:");
				}
				
				document.getElementById("term_display").style.display = "block";			
				document.getElementById("term_display").innerHTML = resArr[0];							

				document.getElementById("syndicate_display").style.display = "block";			
				document.getElementById("syndicate_display").innerHTML = resArr[1];		
			}//else if
		}else if(action == 'si_report_term'){
			if(xmlhttp.readyState!=4){
				document.getElementById("term_display").style.display = "none";
				document.getElementById("syndicate_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				
				var response = xmlhttp.responseText;
				found = response.search(":*:");
				if(found == "-1"){ //no match found			
				}else{ //match found
					var resArr = response.split(":*:");
				}
				
				document.getElementById("term_display").style.display = "block";			
				document.getElementById("term_display").innerHTML = resArr[0];							

				document.getElementById("syndicate_display").style.display = "block";			
				document.getElementById("syndicate_display").innerHTML = resArr[1];		
			}//else if
		}else if(action == 'si_result_exr'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("exr_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("exr_display").style.display = "block";	
				document.getElementById("exr_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'si_exercise_list_for_progressive_result'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("exr_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("exr_display").style.display = "block";	
				document.getElementById("exr_display").innerHTML = xmlhttp.responseText;
			}//else if	
		}else if(action == 'assign_student_for_syndicate'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("student_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("student_display").style.display = "block";	
				document.getElementById("student_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'assign_si'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("si_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("si_display").style.display = "block";	
				document.getElementById("si_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'assign_ds'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("ds_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("ds_display").style.display = "block";	
				document.getElementById("ds_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'assign_term_si_mark'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("term_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("term_display").style.display = "block";	
				document.getElementById("term_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'si_impr_mark'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("si_impr_mark_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("si_impr_mark_display").style.display = "block";	
				document.getElementById("si_impr_mark_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'assign_term_ds_mark'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("term_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("term_display").style.display = "block";	
				document.getElementById("term_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'ds_impr_mark'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("ds_impr_mark_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("ds_impr_mark_display").style.display = "block";	
				document.getElementById("ds_impr_mark_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'assign_term_for_course'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("term_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("term_display").style.display = "block";	
				document.getElementById("term_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'si_mod_mark_term'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("term_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("term_display").style.display = "block";	
				document.getElementById("term_display").innerHTML = xmlhttp.responseText;
				if(document.getElementById("system_message")){
					document.getElementById("system_message").style.display = "none";
				}//if
			}//else if
		}else if(action == 'ci_mod_mark_term'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("term_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("term_display").style.display = "block";	
				document.getElementById("term_display").innerHTML = xmlhttp.responseText;
				if(document.getElementById("system_message")){
					document.getElementById("system_message").style.display = "none";
				}//if
			}//else if
		}else if(action == 'si_mod_mark_exr'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("exercise_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("exercise_display").style.display = "block";	
				document.getElementById("exercise_display").innerHTML = xmlhttp.responseText;
				if(document.getElementById("system_message")){
					document.getElementById("system_message").style.display = "none";
				}//if
			}//else if
		}else if(action == 'ci_mod_mark_exr'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("exercise_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("exercise_display").style.display = "block";	
				document.getElementById("exercise_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'impr_mark_term'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("term_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("term_display").style.display = "block";	
				document.getElementById("term_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'term_impr_mark_assign'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("si_term_marking_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("si_term_marking_display").style.display = "block";
				document.getElementById("si_impres_mark").style.display = "block";
				document.getElementById("si_term_marking_display").innerHTML = xmlhttp.responseText;
			}//else if	
		}else if(action == 'exr_info'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("exr_info").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("exr_info").style.display = "block";	
				document.getElementById("exr_info").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'exercise_by_type'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainer").style.display = "block";
				document.getElementById("exr_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("loaderContainer").style.display = "none";
				document.getElementById("exr_display").style.display = "block";	
				document.getElementById("exr_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'assign_term_for_delete'){
			if(xmlhttp.readyState!=4){
				document.getElementById("delete_term_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("delete_term_display").style.display = "block";	
				document.getElementById("delete_term_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'assign_exercise_for_delete'){
			if(xmlhttp.readyState!=4){
				document.getElementById("delete_exercise_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("delete_exercise_display").style.display = "block";	
				document.getElementById("delete_exercise_display").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'proceed_to_delete'){
			if(xmlhttp.readyState!=4){
				document.getElementById("is_deleteable").style.display = "none";
			}else if(xmlhttp.readyState==4){
				document.getElementById("is_deleteable").style.display = "block";	
				document.getElementById("is_deleteable").innerHTML = xmlhttp.responseText;
			}//else if
		}else if(action == 'assign_ds_mark'){
			if(xmlhttp.readyState!=4){
				document.getElementById("exr_info").style.display = "block";
				document.getElementById("ds_marking_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				
				var response = xmlhttp.responseText;
				found = response.search(":*:");
				if(found == "-1"){ //no match found			
				}else{ //match found
					var resArr = response.split(":*:");
				}
				
				document.getElementById("exr_info").style.display = "block";			
				document.getElementById("exr_info").innerHTML = resArr[0];							

				document.getElementById("ds_marking_display").style.display = "block";			
				document.getElementById("ds_marking_display").innerHTML = resArr[1];
				
				//Hide the displayed system msg
				if(document.getElementById("system_message")){
					document.getElementById("system_message").style.display = "none";
				}
			}//else if
		}else if(action == 'assign_si_mark'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainerForStd").style.display = "block";
				document.getElementById("exr_info").style.display = "none";
				document.getElementById("si_marking_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				
				document.getElementById("loaderContainerForStd").style.display = "none";
				
				var response = xmlhttp.responseText;
				found = response.search(":*:");
				if(found == "-1"){ //no match found			
				}else{ //match found
					var resArr = response.split(":*:");
				}
				
				document.getElementById("exr_info").style.display = "block";			
				document.getElementById("exr_info").innerHTML = resArr[0];							

				document.getElementById("si_marking_display").style.display = "block";			
				document.getElementById("si_marking_display").innerHTML = resArr[1];		
			}//else if
		}else if(action == 'assign_ci_mark'){
			if(xmlhttp.readyState!=4){
				document.getElementById("loaderContainerForStd").style.display = "block";
				document.getElementById("exr_info").style.display = "none";
				document.getElementById("ci_marking_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				
				document.getElementById("loaderContainerForStd").style.display = "none";
				
				var response = xmlhttp.responseText;
				found = response.search(":*:");
				if(found == "-1"){ //no match found			
				}else{ //match found
					var resArr = response.split(":*:");
				}
				
				document.getElementById("exr_info").style.display = "block";			
				document.getElementById("exr_info").innerHTML = resArr[0];							

				document.getElementById("ci_marking_display").style.display = "block";			
				document.getElementById("ci_marking_display").innerHTML = resArr[1];		
			}//else if
		}else if(action == 'course_to_term_syn'){
			if(xmlhttp.readyState!=4){
				document.getElementById("term_display").style.display = "none";
				document.getElementById("syndicate_display").style.display = "none";
			}else if(xmlhttp.readyState==4){
				
				var response = xmlhttp.responseText;
				found = response.search(":*:");
				if(found == "-1"){ //no match found			
				}else{ //match found
					var resArr = response.split(":*:");
				}
				
				document.getElementById("term_display").style.display = "block";			
				document.getElementById("term_display").innerHTML = resArr[0];							

				document.getElementById("syndicate_display").style.display = "block";			
				document.getElementById("syndicate_display").innerHTML = resArr[1];							
			}//else if
		}////else if
	}
	xmlhttp.open("GET",fileToProcess,true);
	xmlhttp.send(null);
}//Function End



//---------------------------------------------------Function to Verify System---------------------------------

//show the comment box while submitting a request to unlock result
function submitRequest(){		
	document.getElementById("loaderBlock").style.display = "block";
	document.getElementById("submitRequest").style.display = "block";
}	

//This function close a div by ID
function Close(id){
	document.getElementById(id).style.display = "none";
	document.getElementById("loaderBlock").style.display = "none";
}

function days_between(start_date, end_date) {
	
	// Here are the two dates to compare
	var start_date = document.getElementById('start_date').value;
	var end_date = document.getElementById('end_date').value;
	
	// First we split the values to arrays date1[0] is the year, [1] the month and [2] the day
	start_date = start_date.split('-');
	end_date = end_date.split('-');
	
	// Now we convert the array to a Date object, which has several helpful methods
	start_date = new Date(start_date[2], start_date[1], start_date[0]);
	end_date = new Date(end_date[2], end_date[1], end_date[0]);
	
	// We use the getTime() method and get the unixtime (in milliseconds, but we want seconds, therefore we divide it through 1000)
	date1_unixtime = parseInt(start_date.getTime() / 1000);
	date2_unixtime = parseInt(end_date.getTime() / 1000);
	
	// This is the calculated difference in seconds
	var timeDifference = date2_unixtime - date1_unixtime;
	
	// in Hours
	var timeDifferenceInHours = timeDifference / 60 / 60;
	
	// in days :)
	var timeDifferenceInDays = timeDifferenceInHours  / 24;
	
	// and finaly, in weeks :)
	var timeDifferenceInWeeks = timeDifferenceInDays  / 7;
	
	//turn it into round figure
	var value = Math.ceil(timeDifferenceInWeeks);
	
	if((start_date != "Invalid Date") && (end_date != "Invalid Date")){
		document.getElementById('weeks').value = value;
	}
}//function

//Check mark number & put value of this number in respective fields like 
//number by weight, position, grade
function validateMarking(id){
	var container, num, sl, mark, weight, put_value, pos_num, i, weight_value, weight_array, continue_flag;
	container = id;
	
	continue_flag = 0;
	num = document.getElementById(container).value;
	marking_type = document.getElementById("marking_type").value;
	sl = document.getElementById("sl").value;
	if(marking_type == 2){
		mark = document.getElementById("mark").value;
	}
	weight = document.getElementById("weight").value;
	/*percent_value = (num*mark)/100;
	percent_value = percent_value.toFixed(2);*/
	put_value = (num*weight)/100;
	put_value = put_value.toFixed(2);
	
	if(isNaN(num) || num > 100 || num == ''){
		if(isNaN(num)){
			alert("Only Number Supported!");
		}else if(num > 100){
			alert("Put a value up to 100");
		}
		if(num != ''){
			continue_flag = 1;
		}
		document.getElementById(container).value = '';
		if(marking_type == 2){
			document.getElementById("mark_"+container).value = 0;
		}
		document.getElementById("weight_"+container).value = 0;
		document.getElementById("grade_"+container).value = 'F';
	}//if
	
	if(num != "" && continue_flag != 1){
		document.getElementById("grade_"+container).value = findGrade(num);
		document.getElementById("weight_"+container).value = put_value;
		if(marking_type == 2){
			document.getElementById("mark_"+container).value = percent_value;
		}
	}//if
}//function

function validateDirectMarking(id){
	var container, num, int_num, sl, mark, weight, put_value, total_put_value, ds_mark, total_mark, continue_flag, mark, highest_mark, converted_total_mark;
	
	container = id;
	continue_flag = 0;
	num = document.getElementById("mark_"+container).value;
	int_num = parseFloat(num);
	mark = parseFloat(document.getElementById("mark").value);
	percent_mark = parseFloat((int_num*100)/mark);
	percent_mark = percent_mark.toFixed(2);
	weight = parseFloat(document.getElementById("weight").value);
	weight_mark = (int_num*weight)/mark;
	weight_mark = parseFloat(weight_mark.toFixed(2));

if(isNaN(num) || num > mark || num == ''){
		if(isNaN(num)){
			alert("Only Number Supported!");
		}else if(num > mark){
			alert("The highest mark you can put here is "+mark);
		}
		if(num != ''){
			continue_flag = 1;
		}
		document.getElementById("mark_"+container).value = '';
		document.getElementById("weight_"+container).value = 0;
		document.getElementById(container).value = 0;
		document.getElementById("grade_"+container).value = 'F';
	}//if
	
	if(num != "" && continue_flag != 1){
		document.getElementById("grade_"+container).value = findGrade(percent_mark);
		document.getElementById("weight_"+container).value = weight_mark;
		document.getElementById(container).value = percent_mark;
	}
}//function

function validateSiMarking(id){
	var container, num, int_num, sl, mark, weight, put_value, total_put_value, ds_mark, total_mark, continue_flag, mark, highest_mark, converted_total_mark;
	
	userRegEx = /^[+-]{1,1}[0-9.]{0,}$/;
	container = id;
	continue_flag = 0;
	num = document.getElementById(container).value;
	if(num == "+" || num == "-" || num == "" || !num.match(userRegEx)){
		int_num = 0;
	}else{
		int_num = parseFloat(num);
	}
	ds_mark = parseFloat(document.getElementById("ds_"+container).value);
	total_mark = ds_mark+int_num;
	total_mark = total_mark.toFixed(2);
	//mark = document.getElementById("mark").value;
	pre_mark = parseFloat(document.getElementById("ds_"+container).value);
	highest_mark = 100 - ds_mark;
	lowest_mark = pre_mark*(-1);
	weight = document.getElementById("weight").value;
	total_put_value = (total_mark*weight)/100;
	total_put_value = total_put_value.toFixed(2);
	for_zero = (ds_mark*weight)/100;
	for_zero = for_zero.toFixed(2);

	
	if(num != "" && !num.match(userRegEx)){
		alert("Only Number and +/- supported!");
		document.getElementById(container).value = '';
		document.getElementById("weight_"+container).value = for_zero;
		document.getElementById("tot_"+container).value = ds_mark;
		document.getElementById("grade_"+container).value = findGrade(ds_mark);
	}
	
	if(num > highest_mark || num == '' || num < lowest_mark){
		if(num > highest_mark){
			alert("The highest mark you can put here is "+highest_mark);
		}
		if(num != ''){
			continue_flag = 1;
		}
		if(num < lowest_mark){
			alert("The lowest mark you can put here is "+lowest_mark);
		}
		document.getElementById(container).value = '';
		document.getElementById("weight_"+container).value = for_zero;
		document.getElementById("tot_"+container).value = ds_mark;
		document.getElementById("grade_"+container).value = findGrade(ds_mark);
	}//if
	
	if(num != "" && continue_flag != 1){
		document.getElementById("grade_"+container).value = findGrade(total_mark);
		document.getElementById("weight_"+container).value = total_put_value;
		document.getElementById("tot_"+container).value = total_mark;
	}
}//function

//Set action for CI Moderation Marks
function validateCiMarking(id){
	var container, num, int_num, sl, total_put_value, converted_total_mark, mark, weight, put_value, pos_num, ds_mark, total_mark, si_mark,highest_mark, continue_flag;
	//original --->>>   /^[0-9-+.]+$/
	  
	userRegEx = /^[+-]{1,1}[0-9.]{0,}$/;
	container = id;
	continue_flag = 0;
	num = document.getElementById(container).value;
	if(num == "+" || num == "-" || num == "" || !num.match(userRegEx)){
		int_num = 0;
	}else{
		int_num = parseFloat(num);
	}
	ds_mark = parseFloat(document.getElementById("ds_"+container).value);
	if(document.getElementById("si_"+container).value != ""){
		si_mark = parseFloat(document.getElementById("si_"+container).value);
	}else{
		si_mark = 0;
	}
	highest_mark = 100 - (ds_mark+si_mark);
	highest_mark = parseFloat(highest_mark.toFixed(2));
	init_total_mark = si_mark+ds_mark;
	lowest_mark = init_total_mark*(-1);
	total_mark = parseFloat(si_mark+ds_mark+int_num);
	total_mark = total_mark.toFixed(2);
	//mark = document.getElementById("mark").value;	
	//converted_total_mark = (total_mark*mark)/100;
	weight = document.getElementById("weight").value;	
	total_put_value = (total_mark*weight)/100;
	total_put_value = total_put_value.toFixed(2);
	for_zero = (init_total_mark*weight)/100;
	for_zero = for_zero.toFixed(2);
	
	if(num != "" && !num.match(userRegEx)){
		alert("Only +/- with Number supported!");
		document.getElementById(container).value = '';
		document.getElementById("weight_"+container).value = for_zero;
		document.getElementById("tot_"+container).value = init_total_mark;
		document.getElementById("grade_"+container).value = findGrade(ds_mark);
	}
	
	if(num > highest_mark || num == '' || num < lowest_mark){
		if(num > highest_mark){
			alert("The highest mark you can put here is "+highest_mark);
		}
		if(num != ''){
			continue_flag = 1;
		}
		if(num < lowest_mark){
			alert("The lowest mark you can put here is "+lowest_mark);
		}
		document.getElementById(container).value = '';
		document.getElementById("weight_"+container).value = for_zero;
		document.getElementById("tot_"+container).value = init_total_mark;
		document.getElementById("grade_"+container).value = findGrade(init_total_mark);
	}//if
	
	if(num != "" && continue_flag != 1){
		document.getElementById("grade_"+container).value = findGrade(total_mark);
		document.getElementById("weight_"+container).value = total_put_value;
		document.getElementById("tot_"+container).value = total_mark;
	}
}//function


//Check Impression Marking & put value of this number in respective fields like 
//number by weight, position, grade
function validateImpressionMarking(id){
	var container, num, sl, impr_mark, impr_mark_limit, s_total_number,total_mark, continue_flag, percent_value, weight, put_value, len_student_number, ini_length;
	
	continue_flag = 0;
	container = id;
	num = document.getElementById(container).value;
	int_num = parseFloat(num);
	sl = document.getElementById("sl").value;
	impr_mark = parseFloat(document.getElementById("impr_mark").value);
	ds_weight = (impr_mark*int_num)/100;
	ds_weight = ds_weight.toFixed(2);
	impr_mark_limit = document.getElementById("impr_mark_limit").value;
	impr_mark_limit = parseFloat(impr_mark_limit);
	impr_mark_limit = (impr_mark_limit * impr_mark)/100;
	sub_student_number =  parseFloat(document.getElementById("sub_student_"+container).value);
	impr_mark_limit = (sub_student_number * impr_mark_limit)/100;
	highest = parseFloat(sub_student_number+impr_mark_limit);
	highest = highest.toFixed(2);
	lowest = parseFloat(sub_student_number-impr_mark_limit);
	final_lowest = Math.floor(lowest);
	lowest = lowest.toFixed(2);
	exr_weight = parseFloat(document.getElementById("weight_"+container).value);
	converted_weight = (int_num*impr_mark)/100;
	len_student_number = Math.floor(sub_student_number);
	len_student_number = len_student_number.toString();
	ini_length = len_student_number.length;
	put_length = num.length;
	

	if(int_num == '' || isNaN(int_num) || int_num > highest || (ini_length == 2 && put_length == 2 && int_num < final_lowest) || (ini_length == 1 && put_length == 1 && int_num < final_lowest)){
		student_total_weight = parseFloat(exr_weight);
	}else{
		student_total_weight = parseFloat(exr_weight+converted_weight);
	}
	
	student_total_weight = student_total_weight.toFixed(2);
	total_weight = parseFloat(document.getElementById("t_mark").value);
	total_percent = (100*student_total_weight)/total_weight;
	total_percent = total_percent.toFixed(2);
	
	if(int_num == '' || isNaN(int_num) || int_num > highest || (ini_length == 2 && put_length == 2 && int_num < final_lowest) || (ini_length == 1 && put_length == 1 && int_num < final_lowest)){
		if(isNaN(int_num)){
			alert("Only Number Supported!");
		}else if(int_num > 100){
			alert("The highest value you can put here is "+highest);
		}else if(int_num > highest){
			alert("The highest value you can put here is "+highest);
		}else if(ini_length == 2 && put_length == 2 && int_num < final_lowest){
			alert("The lowest value you can put here is "+lowest);
		}else if(ini_length == 1 && put_length == 1 && int_num < final_lowest){
			alert("The lowest value you can put here is "+lowest);
		}//else
		if(num != ''){
			continue_flag = 1;
		}
		document.getElementById(container).value = '';
		document.getElementById("ds_"+container).value = 0;
		document.getElementById("grade_"+container).value = findGrade(total_percent);
		document.getElementById("total_"+container).value = student_total_weight;
		document.getElementById("total_percent_"+container).value = total_percent;	
	}//if
	
	if(num != "" && continue_flag != 1){
		document.getElementById("ds_"+container).value = ds_weight;
		document.getElementById("grade_"+container).value = findGrade(total_percent);
		document.getElementById("total_"+container).value = student_total_weight;
		document.getElementById("total_percent_"+container).value = total_percent;
	}//if
	
}//function


//Check mark number & put value of this number in respective fields like 
//number by weight, position, grade of SI Impression Mark
function validateSiImprMarking(id){
	var container, num, sl, impr_mark, s_total_number, total_mark, put_value, percent_value, mark_limit, continue_flag, length;
	container = id;
	continue_flag = 0;
	num = document.getElementById(container).value;
	int_num = parseFloat(num);
	sub_total_number = parseFloat(document.getElementById("sub_total_"+container).value);
	sub_total_percent = parseFloat(document.getElementById("sub_total_percent_"+container).value);
	si_impr_mark_limit = document.getElementById("si_impr_mark_limit").value;
	length = si_impr_mark_limit.length;
	si_impr_mark = parseFloat(document.getElementById("si_impr_mark").value);
	course_total_weight = parseFloat(document.getElementById("t_mark").value);
	ci_put_limit = parseFloat((si_impr_mark_limit*sub_total_percent)/100);
	highest = parseFloat(ci_put_limit+sub_total_percent);
	highest = parseFloat(highest.toFixed(2));
	lowest = sub_total_percent-parseFloat(ci_put_limit);
	lowest = parseFloat(lowest.toFixed(2));
	converted_int_number = parseFloat((int_num*si_impr_mark)/100);
	
	if(num == '' || isNaN(num) || num > highest || (length == 2 && num < lowest && num > 10) || (length == 1 && num < lowest && num.length == 2)){
		total_weight = (sub_total_number);
	}else{
		total_weight = (converted_int_number+sub_total_number);
	}
	
				
	total_weight = total_weight.toFixed(2);
	total_percent = (100*total_weight)/course_total_weight;
	total_percent = total_percent.toFixed(2);
	
	if(num == '' || isNaN(num) || num > highest || (length == 2 && num < lowest && num > 10) || (length == 1 && num < lowest && num.length == 2)){
		if(isNaN(num)){
			alert("Only Number Supported!");
		}else if(num > highest){
			alert("The highest value you can put here is "+highest);
		}else if(length == 2 && num < lowest && num > 10){		//10 is for inserting 100
			alert("The lowest value you can put here is "+lowest);
		}else if(length == 1 && num < lowest && num.length == 2){
			alert("The lowest value you can put here is "+lowest);
		}//else
		if(num != ''){
			continue_flag = 1;
		}
		document.getElementById(container).value = '';
		document.getElementById(container).value = '';
		document.getElementById("grade_"+container).value = findGrade(total_percent);
		document.getElementById("total_"+container).value = total_weight;
		document.getElementById("converted_si_"+container).value = total_percent;	
	}//if
	
	if(num != "" && continue_flag != 1){
		document.getElementById("grade_"+container).value = findGrade(total_percent);
		document.getElementById("total_"+container).value = total_weight;
		document.getElementById("total_percent_"+container).value = total_percent;
		document.getElementById("converted_si_"+container).value = converted_int_number;
	}//if
	
}//function

function ImprMarkMsg(){
	return confirm("Are you sure you want to Forward this result to SI?")
}

function confirmDeleteCourse(){
	var courseData = confirm("Did you take Printout of Course Data?");
	if(courseData == true){
		return confirm("Did you make CD of this Course Data?");
	}
}

//This function gives an alert for confirmation of forwarding DS result to SI
//and highlights the blank input
function validateDSmark(marking_type){
	var number, sl, number_box;
	
	sl = document.getElementById("sl").value;
	for(i=1; i<sl+1; i++){
		if(marking_type == 1){
			number = document.getElementById("number"+i).value;
			number_box = "number"+i;
		}else if(marking_type == 2){
			number = document.getElementById("mark_number"+i).value;
			number_box = "mark_number"+i;
		}

		if(document.getElementById(number_box).value == ""){
			document.getElementById(number_box).style.backgroundColor = "#FFFF00";
			return false;
		}else if(document.getElementById(number_box).value != ""){
			document.getElementById(number_box).style.backgroundColor = "#ffffff";
		}
	}//for					works has to be done here
}//function


//Check Whether a Number or Not
function isNUM(id){
	var container, num;
	container = id;
	
	num = document.getElementById(container).value;
	
	if(isNaN(num)){
		alert("Only Number Supported!");
		document.getElementById(container).value = '';
	}
}//function

//Check/uncheck All Items of a bunch of check box
function checkAllItem(toCheck) {
	
	var master, objects, name, attribute;
	name = toCheck;

	master = document.getElementById("check_all").checked;
	objects = document.getElementsByTagName("input");
	
	for(i = 1; i < objects.length; i++){
		name = objects.item(i).name;
		attribute = objects.item(i).getAttribute("disabled");
		
		if(name == toCheck && attribute != 'disabled'){
			objects.item(i).checked = master;
		}
	}//for
}//function
		
		
//Set a Field empty on click
function makeEmpty(id){
	var container;
	container = id;
	document.getElementById(container).value = '';
}

//Validate User Name Field
function validateUser(){
	var userRegEx;
	username = document.getElementById('username').value;
	userRegEx = /^[A-Za-z0-9_]+$/;
	
	if(!username.match(userRegEx)){
		return false;
	}else{
		return true;
	}
}//function

//Check for Available User Name
function getUsername(){
	var username;
	username = document.getElementById('username').value;
	
	if(username == ''){
		alert("Please, Insert Username!");
		return false;
	}else{
		if(username != "" && validateUser() != true){
			alert("Username only supports alphanumeric character, including '_'");
		}else{
			processFunction("check_username_availability");
		}//else
	}//else
}//function

//This function creates security key
function getSecKey(){
	processFunction("generate_sec_key");
}//function

//Check for Available Official Name
function getOfficeName(){
	var official_name;
	official_name = document.getElementById('official_name').value;
	
	if(official_name == ''){
		alert("Please, Insert Official Name!");
		return false;
	}else{
		processFunction("check_officename_availability");
	}
}

//Check for Available Student Name
function getStudentName(){
	var official_name;
	official_name = document.getElementById('official_name').value;
	
	if(official_name == ''){
		alert("Please, Insert Official Name!");
		return false;
	}else{
		processFunction("check_student_name_availability");
	}//else
}//function

//Display DS Result Sheet
function displayDsResult(){
	var exercise_id;
	exercise_id = document.getElementById('exercise_id').value;
	
	if(exercise_id != 0){	
		document.getElementById("ds_marking").style.display = "block";
		document.getElementById("exr_info").style.display = "block";
	}else{
		document.getElementById("ds_marking").style.display = "none";
		document.getElementById("exr_info").style.display = "none";
	}
}


//--------------------------------------------------------Validate Submit Button-----------------------------------
// User Group Fields
function validateUserGroup(){
	var name; 
	name = document.getElementById('name').value;
	
	if(name == ""){
		alert("Please, Insert User Group Name.");
		return false;
	}else{
		return true;
	}
}

// User Fields
function validateUserCreate(token){
	var group_id, wing_id, rank_id, appointment_id, ba_no, full_name, official_name, username, password, retype_password;
	group_id = document.getElementById('group_id').value;
	wing_id = document.getElementById('wing_id').value;
	rank_id = document.getElementById('rank_id').value;
	appointment_id = document.getElementById('appointment_id').value;
	ba_no = document.getElementById('ba_no').value;
	full_name = document.getElementById('full_name').value;
	official_name = document.getElementById('official_name').value;
	username = document.getElementById('username').value;
	sec_key = document.getElementById('sec_key').value;
	password = document.getElementById('password').value;
	retype_password = document.getElementById('retype_password').value;
		
	if(group_id == 0 || group_id == ""){
		alert("User Group must be selected.");
		return false;
	}else if(wing_id == 0 || wing_id == ""){
		alert("Wing must be selected.");
		return false;
	}else if(sec_key == ""){
		alert("Security Key is empty!");
		return false;
	/*}else if(appointment_id == 0 || appointment_id == ""){
		alert("Appointment must be selected.");
		return false;*/
	}else if(ba_no == ""){
		alert("Please, Insert BA/Service Number.");
		return false;
	}else if(full_name == ""){
		alert("Please, Insert Full Name.");
		return false;
	}else if(official_name == ""){
		alert("Please, Insert Official Name.");
		return false;
	}else if(username == ""){
		alert("Please, Insert Username.");
		return false;
	}else if(username != "" && validateUser()!= true){
		alert("Username only supports digit, number & '_'");
		return false;
	}else if(token == 0 && password == ""){
		alert("Please, Insert Password.");
		return false;
	}else if(token == 0 && retype_password == ""){
		alert("Password Confirmation field is empty!");
		return false;
	}else if(password != retype_password){
		alert("Password Confirmation does not match!");
		return false;
	}else{
		return true;
	}
}

//Wing Fields
function validateWing(){
	var name; 
	name = document.getElementById('name').value;
	
	if(name == ""){
		alert("Please, Insert Wing Name.");
		return false;
	}else{
		return true;
	}
}

//Show Student Fields
function showStudent(){
	var course_id; 
	course_id = document.getElementById('course_id').value;
	
	if(course_id == "" || course_id == 0){
		alert("Please, Select a Course.");
		return false;
	}else{
		return true;
	}
}

//Update User Fields
function updateUser(){
	var username, password, retype_password; 
	username = document.getElementById('username').value;
	password = document.getElementById('password').value;
	retype_password = document.getElementById('retype_password').value;
	
	if(username == ""){
		alert("Please, Insert Username.");
		return false;
	}else if(password != retype_password){
		alert("Password confirmation does not match!");
		return false;
	}else{
		return true;
	}
}

//Course Fields
function validateCourse(){
	var name, start_date, end_date; 
	name = document.getElementById('name').value;
	start_date = document.getElementById('start_date').value;
	end_date = document.getElementById('end_date').value;
	
	if(name == ""){
		alert("Please, Insert Course Name.");
		return false;
	}else if(start_date == ""){
		alert("Please, Insert Course Initial Date.");
		return false;
	}else if(end_date == ""){
		alert("Please, Insert Course Termination Date.");
		return false;
	}else{
		return true;
	}
}

//Term Fields
function validateTerm(){
	var course_id, name, start_date, end_date; 
	course_id = document.getElementById('course_id').value;
	name = document.getElementById('name').value;
	order_id = document.getElementById('order_id').value;
	start_date = document.getElementById('start_date').value;
	end_date = document.getElementById('end_date').value;
	
	if(course_id == '' || course_id == 0){
		alert("Course Must be selected.");
		return false;
	}else if(name == ""){
		alert("Please, Insert Term Name.");
		return false;
	}else if(order_id == ""){
		alert("Please, Insert Order ID.");
		return false;
	}else if(start_date == ""){
		alert("Please, Insert Term Initial Date.");
		return false;
	}else if(end_date == ""){
		alert("Please, Insert Term Termination Date.");
		return false;
	}else{
		return true;
	}
}

//Term Fields
function validateAssignTermm(){
	var course_id, start_date, end_date; 
	
	course_id = document.getElementById('course_id').value;
	start_date = document.getElementById('start_date').value;
	end_date = document.getElementById('end_date').value;
	
	if(course_id == "" || course_id == 0){
		alert("Course must be selected.");
		return false;
	}else if(start_date == ""){
		alert("Please, Insert Term Initial Date!");
		return false;
	}else if(end_date == ""){
		alert("Please, Insert Term Termination Date!");
		return fasle;
	}else if(course_id != 0 && start_date != "" && end_date != ""){
		confirm("You can not alter this information anyway further. Are you sure you want to continue?");
	}
	
	return confirm("You can not alter this information anyway further. Are you sure you want to continue?");
}

//Rank Fields
function validateRank(){
	var name, short_name, weight; 
	name = document.getElementById('name').value;
	short_name = document.getElementById('short_name').value;
	weight = document.getElementById('weight').value;
	
	if(name == ""){
		alert("Please, Insert Rank Name.");
		return false;
	}else if(short_name == ""){
		alert("Please, Insert Rank Short Name.");
		return false;
	}else if(weight == 0 || weight == ""){
		alert("Please, Insert Rank Weight.");
		return false;
	}else{
		return true;
	}
}

//Appointment Fields
function validateAppointment(){
	var name, short_name, order; 
	name = document.getElementById('name').value;
	short_name = document.getElementById('short_name').value;
	order = document.getElementById('order').value;
	
	if(name == ""){
		alert("Please, Insert Appointment Name.");
		return false;
	}else if(short_name == ""){
		alert("Please, Insert Appointment Short Name.");
		return false;
	}else if(order == 0 || order == ""){
		alert("Please, Insert Appointment Order.");
		return false;
	}else{
		return true;
	}
}

//Exercise Type Fields
function validateExerciseType(){
	var name; 
	name = document.getElementById('name').value;
	
	if(name == ""){
		alert("Please, Insert Exercise Type Name.");
		return false;
	}else{
		return true;
	}
}

//Exercise Fields
function validateExercise(){
	var course_id, type_id, exercise_id, name, marking_type; 
	course_id = document.getElementById('course_id').value;
	type_id = document.getElementById('type_id').value;
	exercise_id = document.getElementById('exercise_id').value;
	name = document.getElementById('name').value;
	marking_type = document.getElementById('marking_type').value;
	
	if(course_id == 0 || course_id == ""){
		alert("Course must be selected !");
		return false;
	}else if(type_id == 0 || type_id == ""){
		alert("Exercise Type must be selected!");
		return false;
	}else if(exercise_id == ""){
		alert("Please, Insert Exercise ID.");
		return false;
	}else if(name == ""){
		alert("Please, Insert Exercise Name.");
		return false;
	}else if(marking_type == "" || marking_type == 0){
		alert("Marking Type must be selected.");
		return false;
	}else{
		return true;
	}
}

//Assign SI Fields
function validateAssignSI(){
	var course_id; 
	course_id = document.getElementById('course_id').value;
	
	if(course_id == "" || course_id == 0){
		alert("Course must be selected.");
		return false;
	}else{
		return true;
	}
}

//Validate SI Mark Submition Form
function validateSIForm(){
	var course_id, term_id, exercise_id; 
	course_id = document.getElementById('course_id').value;
	term_id = document.getElementById('term_id').value;
	exercise_id = document.getElementById('exercise_id').value;

	if(course_id == "" || course_id == 0){
		alert("Course must be selected.");
		return false;
	}else if(term_id == 0 || term_id == ""){
		alert("Term must be selected!");
		return false;
	}else if(exercise_id == 0 || exercise_id == ""){
		alert("Exercise must be selected!");
		return false;
	}else{
		return true;
	}
}//function

//Validate CI Mark Submition Form
function validateCIForm(){
	var course_id, term_id, exercise_id; 
	course_id = document.getElementById('course_id').value;
	term_id = document.getElementById('term_id').value;
	exercise_id = document.getElementById('exercise_id').value;

	if(course_id == "" || course_id == 0){
		alert("Course must be selected.");
		return false;
	}else if(term_id == 0 || term_id == ""){
		alert("Term must be selected!");
		return false;
	}else if(exercise_id == 0 || exercise_id == ""){
		alert("Exercise must be selected!");
		return false;
	}else{
		return true;
	}
}//function

//Assign DS Fields
function validateAssignDS(){
	var course_id, term_id, syndicate_id; 
	course_id = document.getElementById('course_id').value;
	term_id = document.getElementById('term_id').value;
	syndicate_id = document.getElementById('syndicate_id').value;
	
	if(course_id == 0 || course_id == ""){
		alert("Course must be selected.");
		return false;
	}else if(term_id == 0 || term_id == ""){
		alert("Term must be selected!");
		return false;
	}else if(syndicate_id == 0 || syndicate_id == ""){
		alert("Syndicate must be selected!");
		return false;
	}else{
		return true;
	}
}

//Mark & Weight Fields
function validateMarkWeight(markingType){
	var mark, weight,markingType; 
	
	if(markingType == 2){
		mark = document.getElementById('mark').value;
	}
	weight = document.getElementById('weight').value;
	
	if(mark == ""){
		alert("Please, Insert Exercise Marks");
		return false;
	}else if(weight == ""){
		alert("Please, Insert Exercise Weight");
		return false;
	}else{
		return true;
	}
}

//Exercise to Term Fields
function validateExerciseTerm(){
	var course_id, term_id; 
	course_id = document.getElementById('course_id').value;
	term_id = document.getElementById('term_id').value;
	
	if(course_id == 0 || course_id == ""){
		alert("Course must be selected !");
		return false;
	}else if(term_id == 0 || term_id == ""){
		alert("Term must be selected!");
		return false;
	}else{
		return true;
	}
}

//SI Impression Mark
function validateSiImprMark(){
	var course_id, si_impr_mark, si_impr_mark_limit; 
	course_id = document.getElementById('course_id').value;

	if(course_id != 0){
		si_impr_mark = document.getElementById('si_impr_mark').value;
		si_impr_mark_limit = document.getElementById('si_impr_mark_limit').value;
	}else{/*do nothing*/ }
	
	if(course_id == 0 || course_id == ""){
		alert("Course must be selected !");
		return false;
	}else if(si_impr_mark == ""){
		alert("Please, Insert SI Impression Mark!");
		return false;
	}else if(si_impr_mark_limit == ""){
		alert("Please, Insert SI Impression Mark Limitation!");
		return false;
	}else{
		return true;
	}
}

//DS Impression Mark
function validateDsImprMark(){
	var course_id, term_id, ds_impr_mark, ds_impr_mark_limit; 
	course_id = document.getElementById('course_id').value;
	term_id = document.getElementById('term_id').value;
	if(course_id != 0 && term_id != 0){
		ds_impr_mark = document.getElementById('ds_impr_mark').value;
		ds_impr_mark_limit = document.getElementById('ds_impr_mark_limit').value;
	}else{/*do nothing*/ }
	
	if(course_id == 0 || course_id == ""){
		alert("Course must be selected !");
		return false;
	}else if(term_id == 0 || term_id == ""){
		alert("Term must be selected!");
		return false;
	}else if(ds_impr_mark == ""){
		alert("Please, Insert DS Impression Mark!");
		return false;
	}else if(ds_impr_mark_limit == ""){
		alert("Please, Insert DS Impression Mark Limitation!");
		return false;
	}else{
		return true;
	}
}

//Syndicate Fields
function validateSyndicate(){
	var name; 
	name = document.getElementById('name').value;
	
	if(name == ""){
		alert("Please, Insert Syndicate Name.");
		return false;
	}else{
		return true;
	}
}

//Syndicate to Course Fields
function validateSyndicateCourse(){
	var course_id; 
	course_id = document.getElementById('course_id').value;
	
	if(course_id == "" || course_id == 0){
		alert("Course must be selected!");
		return false;
	}else{
		return true;
	}
}

//Student Fields
function validateStudent(){
	var rank_id, course_id, student_id, ba_no, full_name, official_name; 
	rank_id = document.getElementById('rank_id').value;
	course_id = document.getElementById('course_id').value;
	student_id = document.getElementById('student_id').value;
	ba_no = document.getElementById('ba_no').value;
	full_name = document.getElementById('full_name').value;
	official_name = document.getElementById('official_name').value;
	
	if(rank_id == 0 || rank_id == ""){
		alert("Rank must be selected !");
		return false;
	}else if(course_id == 0 || course_id == ""){
		alert("Course must be selected !");
		return false;
	}else if(student_id == ""){
		alert("Please, Insert Student ID!");
		return false;
	}else if(ba_no == ""){
		alert("Please, Inset BA/Service Number!");
		return false;
	}else if(full_name == ""){
		alert("Please, Insert Full Name");
		return false;
	}else if(official_name == ""){
		alert("Please, Insert Official Name");
		return false;
	}else{
		return true;
	}
}

//Password Change Fields Validity
function validatePassChangeField(){
	var cur_password, new_password, retype_password; 
	cur_password = document.getElementById('cur_password').value;
	new_password = document.getElementById('new_password').value;
	retype_password = document.getElementById('retype_password').value;
	
	if(cur_password == ""){
		alert("Please, Insert your current Password!");
		return false;
	}else if(new_password == ""){
		alert("Please, Insert your new Password!");
		return false;
	}else if(retype_password == ""){
		alert("Please, Retype your new password!");
		return false;
	}else if(new_password != retype_password){
		alert("Your new password does not match");
		return false;
	}else{
		return true;
	}
}

//Validate Term Assignment
function validateAssignTerm(){
	var start_date, end_date, course_id; 
	start_date = document.getElementById('start_date').value;
	end_date = document.getElementById('end_date').value;
	course_id = document.getElementById('course_id').value;
	
	if(start_date == ""){
		alert("Please, Insert Initial Date!");
		return false;
	}else if(end_date == ""){
		alert("Please, Insert Termination Date!");
		return false;
	}else if(course_id == "" || course_id == 0){
		alert("Course must be selected!");
		return false;
	}else{
		return true;
	}
}

//Validate Term Assignment
function validateStudentAssign(){
	var course_id, term_id, syndicate_id; 
	course_id = document.getElementById('course_id').value;
	term_id = document.getElementById('term_id').value;
	if(course_id != 0 && term_id != 0){
		syndicate_id = document.getElementById('syndicate_id').value;
	}
	
	if(course_id == "" || course_id == 0){
		alert("Course must be selected!");
		return false;
	}else if(term_id == "" || term_id == 0){
		alert("Term must be selected!");
		return false;
	}else if(syndicate_id == "" || syndicate_id == 0){
		alert("Syndicate must be selected!");
		return false;
	}else{
		return true;
	}
}

//Validate Exercise Result
function validateExerciseResult(ind){
	var course_id, term_id, exercise_id, ind; 
	if(ind == 1){
		course_id = document.getElementById('course_id').value;
		term_id = document.getElementById('term_id').value;
	}
	exercise_id = document.getElementById('exercise_id').value;
	
	if(course_id == "" || course_id == 0){
		alert("Course must be selected!");
		return false;
	}else if(term_id == "" || term_id == 0){
		alert("Term must be selected!");
		return false;
	}else if(exercise_id == "" || exercise_id == 0){
		alert("Please, Select an Exercise");
		return false;
	}else{
		return true;
	}
}


//Validate view Assigned Student
function validateAssignedStudent(){
	var course_id; 
	course_id = document.getElementById('course_id').value;
	
	if(course_id == "" || course_id == 0){
		alert("Course must be selected!");
		return false;
	}else{
		return true;
	}
}

//Validate view Assigned Exercise
function validateAssignedExercise(){
	var course_id; 
	course_id = document.getElementById('course_id').value;
	
	if(course_id == "" || course_id == 0){
		alert("Course must be selected!");
		return false;
	}else{
		return true;
	}
}

//Validate view Mark & Weight Management
function validateMarkWeightView(){
	var course_id; 
	course_id = document.getElementById('course_id').value;
	
	if(course_id == "" || course_id == 0){
		alert("Course must be selected!");
		return false;
	}else{
		return true;
	}
}

//Validate Course Report Exercise
function validateCourseRep(){
	var course_id; 
	course_id = document.getElementById('course_id').value;
	
	if(course_id == "" || course_id == 0){
		alert("Course must be selected!");
		return false;
	}else{
		return true;
	}//else 
}//function

//Validate Student Progressive Result
function validateProgressResult(ind){
	
	var course_id, student_id, exercise_type_id;
	if(ind == 1){
		course_id = document.getElementById('course_id').value;
	}
	if(ind == 0){
		exercise_type_id = document.getElementById('exercise_type_id').value;
	}
	student_id = document.getElementById('student_id').value;
	
	if(ind == 0 && student_id == 0 && exercise_type_id == 0){
		alert("At least one parameter should be selected!");
		return false;
	}
	
	if(course_id == "" || course_id == 0){
		alert("Course must be selected!");
		return false;
	}else{
		return true;
	}//else 
}

//Validate Term Result
function validateTermResult(){
	var course_id, term_id;
	course_id = document.getElementById('course_id').value;
	term_id = document.getElementById('term_id').value;
	
	if(course_id == "" || course_id == 0){
		alert("Course must be selected!");
		return false;
	}else if(term_id == "" || term_id == 0){
		alert("Term must be selected!");
		return false;
	}else{
		return true;
	}//else 
}

//This function generate Grade sheet
function findGrade(total_mark){
	if(total_mark < 39.99){
		grade = 'F';
	}else if(total_mark >= 40 && total_mark < 44.99){
		grade = 'C';
	}else if(total_mark >= 45 && total_mark < 49.99){
		grade = 'B-';
	}else if(total_mark >= 50 && total_mark < 62.99){
		grade = 'B';
	}else if(total_mark >= 63 && total_mark < 65.99){
		grade = 'B+';
	}else if(total_mark >= 66 && total_mark < 74.99){
		grade = 'A';
	}else if(total_mark >= 75 && total_mark < 89.99){
		grade = 'A+';
	}else if(total_mark >= 90 && total_mark < 101){
		grade = 'D';
	}
	
	return grade;
}//function

function validateStudentImprOfDS(status){
	var total_count, ds_mark_limit, put_number, prev_number, total_count;
	
	total_count = document.getElementById('total_count').value;
	ds_mark_limit = parseInt(document.getElementById('impr_mark_limit').value);
	
	for(i=1;i < total_count;i++){
		prev_number = parseFloat(document.getElementById('sub_student_number'+i).value);
		put_number  = document.getElementById('number'+i).value;
		cn  = document.getElementById('cn_'+i).value;
		
		if(put_number == ''){
			put_number = 0;	
		}
		
		putable = parseFloat((prev_number*ds_mark_limit)/100);
		
		lowest = prev_number - putable;
		lowest = lowest.toFixed(2);
		
		highest = prev_number + putable;
		highest = highest.toFixed(2);
		
		//Change any background color in general that is yellow now
		document.getElementById('number'+i).style.backgroundColor = "#FFFFFF";
		
		if(put_number < lowest){
			alert('The lowest value you can put for C/N '+cn+' is '+lowest);
			document.getElementById('number'+i).style.backgroundColor = "#FFFF00";
			return false;
		}else if(put_number > highest){
			alert('The highest value you can put for C/N '+cn+' is '+highest);
			document.getElementById('number'+i).style.backgroundColor = "#FFFF00";
			return false;
		}
	}
	
	if(status == 1){
		return confirm("Are you sure you want to Forward this result to SI?");
	}
	
}//function 

//This function validate students mark when submiting SI Impression form
function validateStudentImprOfSI(){
	var put_number, prev_number, total_count;
	
	total_count = document.getElementById('total_count').value;
	ini_si_impr_mark = parseFloat(document.getElementById('si_impr_mark_limit').value);
	si_impr_mark = ini_si_impr_mark.toFixed(2);

	for(i=1;i < total_count;i++){
		prev_number = parseFloat(document.getElementById('sub_total_percent_number'+i).value);
		put_number  = document.getElementById('number'+i).value;
		if(put_number == ''){
			put_number = 0;	
		}
		
		putable = parseFloat((prev_number*si_impr_mark)/100);
		lowest = prev_number - putable;
		lowest = lowest.toFixed(2);
		//difference = prev_number - put_number;
		
		if(put_number < lowest){
			alert('The lowest value you can put is '+lowest);
			return false;
		}
	}
}//function 

//Give an alert before deleting anyn marks
function validateExerciseDelete(){
	return confirm("This process will delete all marks of corresponding perspective. Are you sure you want to proceed?")	
}

function block_about(){
	document.getElementById("loader_about").style.display = "block";
}

function close_about(){
	document.getElementById("loader_about").style.display = "none";
}
