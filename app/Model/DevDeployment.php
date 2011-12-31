<?php
App::uses('AppModel', 'Model');
App::import('Vendor', 'Git');

class DevDeployment extends AppModel {

  public $useTable = 'deployments';
	public $displayField = 'hash';
	public $actsAs = array('Prosty');	
	
	function beforeValidate(){
		$payload = $this->data["DevDeployment"]["payload"];

		$number_of_commits = count($payload->commits);

		// get project_id via project_alias
		$projects = $this->Project->find('first', array(
			'conditions' => array('project_alias' => $payload->repository->name),
			'recursive' => -1,
			'fields' => array('id')
		));		
	
		// get user_id via user's email
		$user = $this->CreatedBy->UserEmail->find('first', array(
			'conditions' => array('email' => $payload->pusher->email),
			'recursive' => -1,
			'fields' => array('user_id')
		));		

		// data for DB
		$this->data["DevDeployment"]["project_id"] = $projects["Project"]["id"];						
		$this->data["DevDeployment"]["hash"] = $payload->after;
		$this->data["DevDeployment"]["last_commit_msg"] = $payload->commits[$number_of_commits-1]->message;				
		$this->data["DevDeployment"]["created_by"] = $user["UserEmail"]["user_id"];
		$this->data["DevDeployment"]["modified_by"] = $user["UserEmail"]["user_id"];
	
		// data for validation			
		$this->data["DevDeployment"]["ip_addr"] = $_SERVER["REMOTE_ADDR"];			
		$this->data["DevDeployment"]["branch"] = $payload->ref;
		$this->data["DevDeployment"]["account"] = $payload->repository->url;
		$this->data["DevDeployment"]["project_alias"] = $payload->repository->name;		
	
		return true;
	}	 	
 	
	/*******************
	* Validations
	*******************/
	public $validate = array(
		'project_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
		'hash' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
		'last_commit_msg' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
		'ip_addr' => array(
			'ip'=> array(
		    	'rule' => array('ip'),
			    'message' => 'Please supply a valid IP address.'
		    ),
			'hostname'=> array(
		    	'rule' => array('validateHostname'),
			    'message' => 'The specified hostname is invalid!'
		    ),		    
		),
		'created_by' => array(
			'rule' => array('numeric'),
			'message' => 'Email not recognized'
		),
		'modified_by' => array(
			'rule' => array('numeric'),
			'message' => 'Email not recognized'
		),		
		// udelukkende validering - indsættes ikke i db
		'branch' => array(
			'rule' => array('validateBranch'),
		    'message' => 'Invalid branch'
		),
		'account' => array(
			'rule' => array('validateGithubAccount'),
		    'message' => 'Invalid Github account'
		),						
		'project_alias' => array(
			'rule' => array('validateProjectAlias'),
		    'message' => 'Invalid project alias'
		),		
		'project_alias' => array(
			'rule' => array('validateProjectPath'),
		    'message' => 'The path to project does not exist'
		),		
	);	
	
	/*******************
	* beforeSave: successfully passed validators
	*******************/
	function beforeSave(){	
	
		$validates = $this->validates();
	
		// validation failed: remove invalid fields from array				
		$this->logCakeValidationErrors($validates);				
		
		// all validations passed
		if($validates){
			
			// get values
			$project_alias = $this->data["DevDeployment"]["project_alias"];
			
			try{
				// open repo
				$repo = Git::open($this->getProjectPath($project_alias));	
				
				// execute git commands	
				$this->executeAndLogGit($repo, 'branch tmp');
				$this->executeAndLogGit($repo, 'checkout tmp');
				$this->executeAndLogGit($repo, 'pull konscript master', true);			
				$this->executeAndLogGit($repo, 'checkout master -f');	
				$this->executeAndLogGit($repo, 'merge tmp', true);
				$this->executeAndLogGit($repo, 'branch tmp -D'); // delete branch				
			
			// catch errors opening repo	
			}catch(Exception $e){						
				$this->logError(array(
					"request" => "Git::open(".$this->getProjectPath($project_alias).")",
					"response" => $e->getMessage(),			
					"calling_function" => __function__
				));				
			}
				
			// clear cache on Caesar
			$this->curl_wrapper(array(
				"url" => $project_alias . '.konscript.net',
				"request_method" => "BAN"
			));																				
		}		
			
		// set error status 
		$this->data["DevDeployment"]["status"] = count($this->getErrors()) == 0 ? true : false;		
		
		// set servername
		$this->data["DevDeployment"]["server"] = "Caesar";				
		
		// continue to save the record
		return true;		
	}
	
	/*******************
	* afterSave: log errors
	*******************/	
	function afterSave(){
		$this->saveErrorLogs();
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
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'created_by',
		),
		'ModifiedBy' => array(
			'className' => 'User',
			'foreignKey' => 'modified_by',
		)		
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'DeploymentError' => array(
			'className' => 'DeploymentError',
			'foreignKey' => 'deployment_id',
			'dependent' => false,
		)		
	);	

	/**
	 * Utility functions
	 *******************************************************************************************/

	// payload must originate from Github.com
	function validateHostname($check){
    	$host = gethostbyaddr($check["ip_addr"]);    	    	
        return ($check["ip_addr"] == "127.0.0.1" || substr($host, -10) == "github.com") ? true : false;
	}
	
	// branch must be master
	function validateBranch($check){
        $valid_branches = array('refs/heads/master');
        return in_array($check["branch"], $valid_branches) ? true : false;
	}
	
	// payload must originate from Konscript's Github account
	function validateGithubAccount($check){
        return strpos($check["account"], "github.com/konscript") ? true : false;
	}	

	// project must exist	
	function validateProjectAlias($check){
		// find number of projects with the given project_alias
		$projects = $this->Project->find('count', array(
			'conditions' => array('project_alias' => $check["project_alias"])
		));
				
    return $projects == 1 ? true : false;
	}
	
	// path must exist
	function validateProjectPath($check){
		$project_alias = $check["project_alias"];
		$path = $this->getProjectPath($project_alias);
    return is_dir($path);
	}	         			
}
