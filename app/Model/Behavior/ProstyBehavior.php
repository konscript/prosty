<?php
class ProstyBehavior extends ModelBehavior {

	public $web_root = "/srv/www/";	
	public $errors = array();	
	
	function getErrors($Model){
		return $this->errors;
	}
	
	function getWebRoot($Model){
		return $this->web_root;
	}	
	
	function getProjectPath($Model, $project_alias){
		return $this->web_root.$project_alias."/web";
	}
	
	function getProjectAlias($Model, $project_id){
		// get project_alias from project_id
		
		$project = ($Model->name == "Project") ? $Model->findById($project_id) : $Model->Project->findById($project_id);
		
		$project_alias = $project["Project"]["project_alias"];						
		return $project_alias;
	}
	
	function getGitRemote($Model, $project_alias){
		return 'git@github.com:konscript/'.$project_alias;
	}								
	
	/***************************
	* add error to array
	***************************/		
	function logError($Model, $options = array()){
		
		$default_options = array(
			"message" => "",
			"calling_function" => "",
			"return_code" => 1,
			"type" => null
		);		
		$options = array_merge($default_options, $options);


		// don't log if no errors occured
		if(!$options["type"] && $options["return_code"] == 0 ||
			 $options["type"] == "bool" && $options["return_code"] === true){
			return;
		}	
		
		// curl request are always encoded as json
		if( $options["type"] == "curl" && $options["return_code"] == 200 ){
			$options["message"] = json_decode($options["message"]);
		}
	
		// add multiple errors - message field, can contain multiple errors
		if(is_array($options["message"])){
			// iterate errors
			foreach($options["message"] as $options){
			
				$options = array_merge($default_options, $options);
				$this->logError($Model, array(
					"message" => $message->message,
					"calling_function" => $message->calling_function,
					"return_code" => $message->return_code,
					"type" => $message->type
				));
			}			
						
		// add single error
		}else{
			$this->errors[] = array(
				'message'=> $options["message"],
				'calling_function'=> $options["calling_function"],
				'return_code' => $options["return_code"]
			);				
		}		
	}  		 
	
	/***************************
	* log git commands if they return errors
	***************************/			
  function executeAndLogGit($Model, $repo, $git_command, $skipOnError = false){
      
  	// skip certain git commands if an error occured
		if(count($this->errors) > 0 && $skipOnError === true){
			return;
		}		
		$git_response = $repo->git_run_with_validation($git_command);

		$this->logError($Model, array(
				"message" => $git_response[2],
				"calling_function" => $git_command,
				"return_code" => $git_response[0]
		));			
  }				
			
	/***************************
	* wrapper function for cURL requests
	***************************/	
	function curl_wrapper($Model, $options = array()){	
	
		// set default options
		$default_options = array(
			"url" => "",
			"request_method" => "POST",
			"data" => null,
			"headers" => null,
			"skipOnError" => true
		);		
		$options = array_merge($default_options, $options);
		
  	// skip curl if an error occured
		if(count($this->errors) > 0 && $options["skipOnError"] == true){			
			return;
		}		
	
		$ch = curl_init();				
		
		// set URL
		curl_setopt($ch, CURLOPT_URL, $options["url"]);
		
		// return instead of echo result
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);		
		
		// set request method
		if($options["request_method"] == "POST"){
			curl_setopt($ch, CURLOPT_POST, true);						
			curl_setopt($ch, CURLOPT_POSTFIELDS, $options["data"]);
		}else{
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $options["request_method"]);
		}
		
		// set custom headers	
		if(isset($options["headers"])){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $options["headers"]);	
		}
		
		$response = curl_exec($ch);
		$info = curl_getinfo($ch);		
		curl_close($ch);	
		
		// log curl error
    $this->logError($Model, array(
			"message" => "Request: ".json_encode($options)." Response: " . $response,
			"calling_function" => __function__,
			"return_code" => $info["http_code"],
			"type" => "curl"
		));				
	}		
}
