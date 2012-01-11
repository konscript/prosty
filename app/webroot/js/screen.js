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

	/**
	 * when the Ignore or Commit button has been submittet, the checked files will be added to the corresponding json array, and DOM element. If all files have been resolved (either ignored or commited), a Re-deploy button will appear
	 **/
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
	
	function updateDeployButton()	{
		// display "re-deploy" button, when all files are resolved
		var numberOfUnresolvedFiles = $('#unresolvedFiles .file:visible').size();
		if(numberOfUnresolvedFiles == 0){
			$('.resolveDialog input:submit').hide();
			$('.resolveDialog input#redeploy').css('visibility', 'visible').fadeIn();		
		}	
	}
		
	// click on deploy button
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

});
