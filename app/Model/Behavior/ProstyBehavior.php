<?php

class ProstyBehavior extends ModelBehavior {

	public $web_root = "/srv/www/";	
	public $errors = array();	
	
	function getErrors($Model){
		return $this->errors;
	}
	
	function getErrorCount($Model){
		return count($this->errors);
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
	
		// get calling function from backtrace
		$debug = debug_backtrace();				 
		$calling_function = $debug[1]["function"];		

		$default_options = array(
			"request" => null,
			"response" => null,			
			"calling_function" => $calling_function,
			"return_code" => 1,
			"type" => null,
			"suppressErrors" => false
		);
		
		// convert boolean false to 0
		if(isset($options["type"]) && $options["type"] == "bool" && $options["return_code"] == null){
			$options["return_code"] = 0;
		}

		// remove empty options
		$options = array_filter($options, 'strlen');
		
		// merge with default values		
		$options = array_merge($default_options, $options);	

		// todo remove
		debug($options);

		// skip if no errors occured
		if(
			( !$options["type"] && $options["return_code"] == 0 ) ||
			( $options["type"] == "bool" && $options["return_code"] == true ) ||
			( $options["type"] == "curl" && ( $options["return_code"] === 200 || $options["return_code"] === 201 ) ) ||
			( $options["type"] == "successOnEmptyResponse" && empty($options["response"])  ) ||
			( $options["suppressErrors"] === true )
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
  function executeAndLogGit($Model, $repo, $git_command, $options = array()){
  
  	$default_options = array(
  		'skipOnError' => true,
  		'suppressErrors' => false
  	);  	  	
		$options = array_merge($default_options, $options);  	

  	// skip further steps, if an error occured
		if($this->getErrorCount($Model) > 0 && $options["skipOnError"] === true){		return false;		}
		
		// execute command and get respose + return_code
		$git_response = $repo->git_run_with_validation($git_command);			
		$response = $git_response[2] . " " .$git_response[1];
		$return_code = $git_response[0];
		
		// log possible errors		
		$this->logError($Model, array(
				"request" => "git " . $git_command,
				"response" => $response,
				"return_code" => $git_response[0],
				"suppressErrors" => $options["suppressErrors"]
		));		
		
		return true;
  }
  
	/***************************
	* determine if a string is json or not
	***************************/	  
  function is_json($Model, $str){
		$json = json_decode($str);
		return ($json != null && json_last_error() === JSON_ERROR_NONE) ? true : false;
  }
  
  
	/***************************
	* write content to file
	***************************/			
	function writeToFile($Model, $filename, $content, $flags = 0){
		$response = file_put_contents($filename, $content, $flags);
		
		// file_put_contents will return 
		$return_code = is_int($response) && $response > 0 ? TRUE : FALSE;		
	
		$this->logError($Model, array(
			"request" => "filename: $filename, content: $content, flags: $flags",
			"response" => "$filename could not be updated",
			"calling_function" => __function__,
			"return_code" => $return_code,
			"type" => "bool"
		));
	}  
	
	/***************************	 
	* attempt to open git repo - if it fails (if git is not init) log the error
	***************************/	 	
	function openRepo($Model, $project_path){
		try{
			$repo = Git::open($project_path);
			
		// catch errors opening repo	
		}catch(Exception $e){
			$repo = false;
			$this->logError($Model, array(
				"request" => "Git::open(".$project_path.")",
				"response" => $e->getMessage()
			));
		}
		return $repo;
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
		if($this->getErrorCount($Model) > 0 && $options["skipOnError"] == true){			
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
			"return_code" => $info["http_code"],
			"type" => "curl"
		));				
	}
	
	/***************************	 
	* when examining a merge conflict, we need a list of the files in conflict
	****************************/	 	
	function getConflictingFiles($Model, $project_path){
		$cmd = "cd ".escapeshellarg($project_path)." && git ls-files --unmerged | cut -f2 | uniq";
		exec($cmd, $files);			
		return $files;
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
	function logCakeValidationErrors($Model, $validates){
		if( !$validates ) {
														
			foreach($Model->invalidFields() as $errorName => $errors){					
			
				$request = empty($Model->data[$Model->name][$errorName]) ? "Input field '".$errorName."' is empty" : $Model->data[$Model->name][$errorName];
			
				$this->logError($Model, array(
					"request" => $request,
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
