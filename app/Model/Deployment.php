<?php
App::uses('AppModel', 'Model');
App::import('Vendor', 'Git');
App::import('Vendor', 'Prosty');

/**
 * Deployment Model
 *
 * @property Project $Project
 */
class Deployment extends AppModel {

 	function getProsty(){
 		if(!$this->Prosty){
 			$this->Prosty = new Prosty();
		}	
		return $this->Prosty;
 	}

	public $validate = array(
		'project_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Project id must be numeric',
			),
			'currentVersion' => array(
				'rule' => array('validateCurrentVersion'),
				'message' => 'The current version could not be found'
			),			
			'nextVersion' => array(
				'rule' => array('validateNextVersion'),
				'message' => 'The next version already exists'
			),						
		)
	);


	function getVersionPaths($project_id){
		$Prosty = $this->getProsty();	
		
		// get project_alias to be used in path
		$project = $this->Project->findById($project_id);
		$project_alias = $project["Project"]["project_alias"];		
		$path = $Prosty->web_root.$project_alias.'/prod/';
		
		// set current version (current need not be the highest version)
		$current_version = $project["Project"]["current_version"];				
		$highest_version = $Prosty->get_latest_prod_version($path);				
		
		// set next version
		$next_version = $highest_version + 1;
		
		$versions = array(
			'current' => $path.$current_version,
			'next' => $path.$next_version,			
			'symlink' => $path.'current',				
		);
		
		return $versions;
	}

	// verify that the current version exists
	function validateCurrentVersion($check){
		$versions = $this->getVersionPaths($check["project_id"]);
				
		// success if dir exists		
		return is_dir($versions["current"]) ? true : false; 
	}

	// verify that the version "to-be" does not exist
	function validateNextVersion($check){	
		$versions = $this->getVersionPaths($check["project_id"]);		
		
		// error if dir exists			
		return is_dir($versions["next"]) ? false : true; 
	}
	
	function beforeSave(){
	
		$Prosty = $this->getProsty();
		$project_id = $this->data["Deployment"]["project_id"];
		$versions = $this->getVersionPaths($project_id);
			
		if($this->validates()){	
						
			// create new version
			if(isset($this->data["Deployment"]["create_next_version"]) && $this->data["Deployment"]["create_next_version"] == true){
			
				// clone current version
				$Prosty->recursive_copy($versions["current"], $versions["next"]);         
				   												
				// git pull
				$git_response = Git::git_callback('pull konscript master', $versions['next'], true);			
				
				// set deployed version
				$this->data["Deployment"]["deployed_version"] = basename($versions["next"]);				
		
			// update current version
			}else{
				// git pull
				$git_response = Git::git_callback('pull konscript master', $versions['current'], true);			
				
				// set deployed version
				$this->data["Deployment"]["deployed_version"] = basename($versions["current"]);								
			}
	
			// log possible errors for git action
			$Prosty->checkGitPull($git_response);  		
	
			// clear cache for current project (only if cache is enabled!)			
			$project = $this->Project->findById($project_id);
			if($project["Project"]["use_cache"] === 1){
				$Prosty->clearCache($project_id);
			}


			// set error status - errors might have occured during git
			$this->data["Deployment"]["status"] = count($Prosty->errors) == 0 ? true : false;		
		}else{
			// remove invalid fields from array
			foreach($this->invalidFields() as $errorName => $error){		
				unset($this->data["Deployment"][$errorName]);
			}		
		
			// set error status
			$this->data["Deployment"]["status"]	= false;			
		}				
		
		return true;
	}


	
	/*******************
	* afterSave: log errors
	*******************/	
	function afterSave(){
	
		$Prosty = $this->getProsty();
		$project_id = $this->data["Deployment"]["project_id"];
		$versions = $this->getVersionPaths($project_id);		
				
		// errors occured during deployment
		if($this->data["Deployment"]["status"]	== false){

			// log Prosty errors
			foreach($Prosty->errors as $error){		
				// set values
				$this->data["DeploymentError"]["deployment_id"] = $this->data["Deployment"]["id"];			
				$this->data["DeploymentError"]["message"] = $error["message"];
				$this->data["DeploymentError"]["calling_function"] = $error["calling_function"];
	
				// save errors
				$this->DeploymentError->create();
				$this->DeploymentError->save($this->data);		
			}				
	
			// log cake validation errors
			foreach($this->invalidFields() as $errorName => $error){		
				// set values
				$this->data["DeploymentError"]["deployment_id"] = $this->data["Deployment"]["id"];			
				$this->data["DeploymentError"]["message"] = $error[0];
				$this->data["DeploymentError"]["calling_function"] = $errorName;
	
				// save errors
				$this->DeploymentError->create();
				$this->DeploymentError->save($this->data);		
			}		
			
		// success: no errors occured during deployment
		}else{
		
			// update version in db	and symlink (done through relational)
			$this->Project->id = $project_id;	
			$this->Project->saveField('current_version', basename($versions["next"])); // basename mean the last part of the path (only the version-number)				
				
		}		
	}
	
/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Project' => array(
			'className' => 'Project',
			'foreignKey' => 'project_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
	
	public $hasMany = array(
		'DeploymentError' => array(
			'className' => 'DeploymentError',
			'foreignKey' => 'deployment_id',
			'dependent' => false,
		)		
	);		
}
