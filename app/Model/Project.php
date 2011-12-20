<?php
App::uses('AppModel', 'Model');
App::import('Vendor', 'Git');

class Project extends AppModel {
 
	public $actsAs = array('Prosty', 'Containable');
	public $validate = array(
		'project_alias' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Cannot be empty',
				'on' => 'create', // Limit validation to 'create' operations				
			),		
			'unique' => array(
				'rule' => 'isUnique',
				'message' => 'The name must be unique',
				'on' => 'create', // Limit validation to 'create' operations				
			),
			'folder' => array(
				'rule' => array('validateProjectAliasFolder'),
				'message' => 'Folder already exists',
				'on' => 'create', // Limit validation to 'create' operations				
			),
			'github' => array(
				'rule' => array('checkGithub'),
				'message' => 'GitHub account doesn\'t exists or repository is not empty',
				'on' => 'create', // Limit validation to 'create' operations
			)	
		),
		'title' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
		'screenshot' => array(
			'boolean' => array(
				'rule' => array('boolean'),
			),
		),
		'errors' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
		'created_by' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
		'modified_by' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
	);

    /**
     * Validation: check if a Github account has been created
     *************************************************/
	function checkGithub($check) {
        return Git::git_check_repository('ls-remote -h '.$this->getGitRemote($check["project_alias"]).'.git' );        
	}
	
	/**
	 * Check if a folder of the name "$project_alias" exists
	 * Validation: will fail if the folder exists
	 ***************************************/
    function validateProjectAliasFolder($check) {		      					
			$project_alias = $check["project_alias"];
			$path = $this->getProjectPath($project_alias);	
      return !file_exists($path); //return false if it exists      
    }       	    
           		
    /**
     * beforeSave: executes after valdiations and before save
     *************************************************/
	function beforeSave($model){
			      							
		// create new project
		if(!isset($this->data["Project"]["id"])) {
			$this->createNewProject($this->data["Project"]);
			
			// create database
			$this->query("CREATE DATABASE IF NOT EXISTS ".$this->data["Project"]["project_alias"]."");			
			
		// update project
		} else{	
					
		
		}
	
		// success
		if(count($this->getErrors()) == 0){
			return true;
			
		// error
		}else{
			debug($this->getErrors());
			return false;
		}					
	}
	
	// delete virtual hosts before deleting project (not deleting folder nor db)
	function beforeDelete(){
		$project = $this->findById($this->id);
		$project_alias = $project["Project"]["project_alias"];
		unlink("/etc/nginx/sites-available/".$project_alias);
		unlink("/etc/nginx/sites-enabled/".$project_alias);					
		return true;
	}

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'Commit' => array(
			'className' => 'Commit',
			'foreignKey' => 'project_id',
		),
		'Deployment' => array(
			'className' => 'Deployment',
			'foreignKey' => 'project_id',
			'dependent' => false,
		)				
	);

}
