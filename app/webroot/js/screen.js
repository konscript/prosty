var fileArray = {
	"ignoreFiles":{
		"new": [],
		"changed": []
	},
	"commitFiles":{
		"new": [],
		"changed": []
	},
	"theirFiles": [],
	"ourFiles": []
};

$(document).ready(function() {

	addFiles();
	deployFiles();
	toggleErrors();
	addEmail();
	removeEmail()	
});

/********************
 * Add files
 * When the Ignore or Commit button has been submittet, the checked files will be added to the corresponding json array, and DOM element. If all files have been resolved (either ignored or commited), a Re-deploy button will appear
 ********************/
 function addFiles(){
	$('.resolveDialog .addFiles').click(function(){

		// action can either be ignoreFiles or commitFiles
		var action = $(this).attr('id');

		// match checked checkboxes and hide them
		var checked = $('#unresolvedFiles .file input:checkbox:checked');
	
		// iterate chosen files
		$.each(checked, function(key, file) { 
			var filename = $(file).data("filename");									
			var file =	$(this).parent('p.file');
		
			// in mode "resolve unstaged files", a filestate is required
			if($('#resolveUnstagedFiles').length == 1){
				// fileState is either new or changed
				var fileState = $(file).parents('.new').length == 1 ? "new" : "changed";

				// add to json
				fileArray[action][fileState].push(filename);
			
			// in mode "resolve conflicting files" only an action is required
			}else{
				// add to json for 
				fileArray[action].push(filename);						
			}		
	
			// add to DOM			
			$(file).children('input').remove();
			$('div.' + action).children('.files').append(file);
		
		});	

		updateDeployButton();
		return false;
	});	
}

/********************
* display "re-deploy" button, when all files are resolved
********************/
function updateDeployButton()	{
	var numberOfUnresolvedFiles = $('#unresolvedFiles .file:visible').size();
	if(numberOfUnresolvedFiles == 0){
		$('.resolveDialog input:submit').hide();
		$('.resolveDialog input#redeploy').css('visibility', 'visible').fadeIn();		
	}	
}

/*********************
* click on deploy button
*********************/
function deployFiles(){
	$('.resolveDialog #redeploy').click(function(){

		// disable button and fadein loading
		$(this).attr('disabled', 'disabled');
		$(".loading").fadeIn();
		$("#debugger").html(""); 
		
		// set form urls
		var formActionUrl = $('.resolveDialog').attr('action');		
	
		// post selected files
		$.post(formActionUrl, {'files': fileArray}, function(response, textStatus) {
				
				// if response is json, we will redirect to the url given
				try {
						var json = $.parseJSON(response);
						window.location.href = json["url"];
					
				// unexpected error. Output it
				} catch (e) {
		      $("#debugger").html(response); 
				}    
		})
		.error(function() {
		  $('.resolveDialog #redeploy').removeAttr('disabled');    
	 		$("#debugger").append("http Error");
		  $(".loading").fadeOut();
		})
		.success(function() {
		  $(".loading").fadeOut();
		});
		return false;    
	});
}

function toggleErrors(){
	$('.showDeploymentErrors').click(function(){
		$('.deploymentErrors').toggle();
		$(this).hide();
		return false;
	});
}

/*********************
* create new email record
*********************/
function addEmail(){
	$('.addEmail').click(function(){
		var countEmails = $("input.email").length;
		console.log(countEmails);	
		
		var lastEmailField = $('input.email:last').parents('div.input');			
		
		// clone last email field
		var newEmailField = $(lastEmailField).clone();			
	
		// set label "for"-attribute
		$(newEmailField).children('label').attr('for', 'UserEmail'+countEmails+'Email');
		
		// set input "name"-attribute
		$(newEmailField).children('input').attr('name', 'data[UserEmail]['+countEmails+'][email]');
		
		// set input "id"-attribute
		$(newEmailField).children('input').attr('id', 'UserEmail'+countEmails+'Email');

		// clear value
		$(newEmailField).children('input').val('');		
			
		// insert
		$(newEmailField).insertAfter(lastEmailField).hide().fadeIn();
		
		return false;
	});
}

// delete email record
function removeEmail(){
	$('.removeEmail').live('click', function(){
		var container = $(this).parents('div.input');
		
		// get email id
		var idField = $(container).prev('input[type=hidden]');
		var email_id = $(idField).val();
		
		// delete email
		if(email_id){
			$.post('/user_emails/delete/' + email_id).success(function() { 
			
				// fadeout and remove DOM element
				$(container).fadeOut().delay(1000, function(){
					$(idField).remove();
					$(this).remove();
				});			
			
			});
		}

		return false;
	});	
}
