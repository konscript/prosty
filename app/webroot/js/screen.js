var fileArray = {};

$(document).ready(function() {

		var obj = {};
		var task_id = "parent";
		var task_id2 = "parent2";		

		obj[task_id] = new Array("value 1");
		obj[task_id2] = new Array("value 2");


		obj[task_id].push("value 3");

		
		console.log(obj);

	/**
	 * when the Ignore or Commit button has been submittet, the check files will be added to the corresponding json array, and DOM element. If all files have been resolved (either ignored or commited), a Re-deploy button will appear
	 **/
	$('.resolveDialog .addFiles').click(function(){
	
		var task_id = $(this).attr('id');

		// match checked checkboxes and hide them
		var checked = $('.resolveDialog input:checkbox:checked');
		$(checked).parent('p.file').hide();
		
		// iterate chosen files
		$.each(checked, function(key, file) { 
			var filename = $(file).data("filename");

			// create array, if it does not exists
			if(fileArray[task_id] == undefined){
				fileArray[task_id] = new Array(filename);
				console.log("array created");
			}else{
				// add to json			
				fileArray[task_id].push(filename);
			}
		
			// add to DOM
			$('<p/>', {
				class: 'file',
				text: filename
			}).appendTo('div#'+task_id).hide().fadeIn('slow');		
		});
	
		// uncheck
		$(checked).attr('checked', false);
	
		// display "re-deploy" button, when all files are resolved
		var numberOfUnresolvedFiles = $('#unresolvedFiles .file:visible').size();
		if(numberOfUnresolvedFiles == 0){
			$('.resolveDialog input:submit').hide();
			$('.resolveDialog input#redeploy').css('visibility', 'visible').fadeIn();		
		}
		console.log(fileArray);
		return false;
	});	
		
	$('.resolveDialog #redeploy').click(function(){	
		var formAction = $('.resolveDialog').attr('action');
    $.post(formAction, {'files': fileArray}, function(response) {
        $("#debugger").html(response); 
    });	
		return false;    
	});

});
