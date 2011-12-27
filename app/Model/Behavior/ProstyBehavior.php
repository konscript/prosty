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
			"request" => "",
			"response" => "",			
			"calling_function" => "",
			"return_code" => 1,
			"type" => null
		);
		
		// convert boolean false to 0
		if(isset($options["type"]) && $options["type"] == "bool" && $options["return_code"] == null){
			$options["return_code"] = 0;
		}

		// remove empty 		
		$options = array_filter($options, 'strlen');
		
		// merge with default values		
		$options = array_merge($default_options, $options);

		// skip if no errors occured
		if(
			( !$options["type"] && $options["return_code"] == 0 ) ||
			( $options["type"] == "bool" && $options["return_code"] == true ) ||
			( $options["type"] == "curl" && ( $options["return_code"] === 200 || $options["return_code"] === 201 ) )
		){
			return;
		}		
		
		// decode reponse if json 
		if( $this->is_json($Model, $options["response"]) ){		
			$json = json_decode($options["response"]);
			
			// only decode json if it is a container for multiple errors (received from external website through curl)					
			$error = $json[0];
			if(is_array($error) && array_key_exists("return_code", $error)){
				$options["response"] = $json;
			}			
		}	
	
		// multiple errors
		if(isset($options["response"][0]) && is_array($options["response"][0]) && array_key_exists("return_code", $options["response"][0])){
			// iterate errors
			foreach($options["response"] as $options){
			
				$options = array_merge($default_options, $options);
				$this->logError($Model, array(
					"request" => $options->request,
					"response" => $options->response,
					"calling_function" => $options->calling_function,
					"return_code" => $options->return_code,
					"type" => $options->type
				));
			}			
						
		// add single error
		}else{
			$this->errors[] = array(
				'request'=> $options["request"],
				'response'=> $options["response"],
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
				"request" => "git " . $git_command,
				"response" => $git_response[2],
				"calling_function" => $git_command,
				"return_code" => $git_response[0]
		));			
  }				
  
  function is_json($Model, $str){
		$json = json_decode($str);
		
		return ($json != null && json_last_error() === JSON_ERROR_NONE) ? true : false;

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
			"request" => json_encode($options),
			"response" => $response,			
			"calling_function" => __function__,
			"return_code" => $info["http_code"],
			"type" => "curl"
		));				
	}		

	/***************************
	* after save for both types of deployment
	***************************/	
	function saveErrorLogs($Model){
						
		// log Prosty errors
		foreach($this->getErrors($Model) as $error){		
			// set values
			$Model->data["DeploymentError"]["deployment_id"] = $Model->data[$Model->name]["id"];			
			$Model->data["DeploymentError"]["request"] = $error["request"];			
			$Model->data["DeploymentError"]["response"] = $error["response"];
			$Model->data["DeploymentError"]["return_code"] = $error["return_code"];
			$Model->data["DeploymentError"]["calling_function"] = $error["calling_function"];

			// save errors
			$Model->DeploymentError->create();
			$Model->DeploymentError->save($Model->data);		
		}
	}
	
	/***************************
	* beforesave for both types of deployment
	***************************/		
	function logCakeValidationErrors($Model){
		if( !$Model->validates() ) {
														
			foreach($Model->invalidFields() as $errorName => $errors){					
				$this->logError($Model, array(
					"request" => $Model->data[$Model->name][$errorName],
					"response" => json_encode($errors),
					"calling_function" => $errorName,
					"return_code" => 0,
					"type" => "bool"
				));			
						
				unset($Model->data[$Model->name][$errorName]);
			}
		}
	}			
}
