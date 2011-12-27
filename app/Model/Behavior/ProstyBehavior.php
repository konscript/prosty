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
		$options = array_merge($default_options, $options);

		// skip if no errors occured
		if(
			( !$options["type"] && $options["return_code"] == 0 ) ||
			( $options["type"] == "bool" && $options["return_code"] === true ) ||
			( $options["type"] == "curl" && ( $options["return_code"] === 200 || $options["return_code"] === 201 ) )
		){
			return;
		}
		
		// decode reponse if json 
		if( $this->is_json($Model, $options["response"]) ){
			$options["response"] = json_decode($options["response"]);
		}
	
		// add multiple errors - response field, can contain multiple errors
		if(is_array($options["response"])){
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
				"request" => $git_command,
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
	* after save for Deployment and Commit
	***************************/	
	function saveErrorLogs($Model){
		$modelName = $Model->name;
		$modelName_lowercase = strtolower($Model->name);
		$errorModel = $Model->name."Error";
		$errorModel = $Model->name."Error";		
					
		// log Prosty errors
		foreach($this->getErrors($Model) as $error){		
			// set values
			$Model->data[$errorModel][$modelName_lowercase . "_id"] = $Model->data[$modelName]["id"];			
			$Model->data[$errorModel]["request"] = $error["request"];			
			$Model->data[$errorModel]["response"] = $error["response"];
			$Model->data[$errorModel]["return_code"] = $error["return_code"];
			$Model->data[$errorModel]["calling_function"] = $error["calling_function"];

			// save errors
			$Model->$errorModel->create();
			$Model->$errorModel->save($Model->data);		
		}				

		// log cake validation errors
		foreach($Model->invalidFields() as $errorName => $error){		
			// set values
			$Model->data[$errorModel][$modelName_lowercase . "_id"] = $this->data[$modelName]["id"];			
			$Model->data[$errorModel]["request"] = ""; // TODO: get original value
			$Model->data[$errorModel]["response"] = $error[0];
			$Model->data[$errorModel]["calling_function"] = $errorName;

			// save errors
			$Model->$errorModel->create();
			$Model->$errorModel->save($this->data);		
		}					
	}
}
