<?php
App::uses('AppModel', 'Model');
App::import('Vendor', 'Git');
/**
 * Commit Model
 *
 * @property Project $Project
 * @property Deployment $Deployment
 */
class Commit extends AppModel {

	public $displayField = 'hash';
	public $actsAs = array('Prosty');	
	
function beforeValidate(){
	$payload = $this->data["Commit"]["payload"];

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
	$this->data["Commit"]["project_id"] = $projects["Project"]["id"];						
	$this->data["Commit"]["hash"] = $payload->after;			
	$this->data["Commit"]["last_commit_msg"] = $payload->commits[$number_of_commits-1]->message;			
	$this->data["Commit"]["number_of_commits"] = $number_of_commits;			
	$this->data["Commit"]["ip_addr"] = $_SERVER["REMOTE_ADDR"];		
	$this->data["Commit"]["created_by"] = $user["UserEmail"]["user_id"];			
	$this->data["Commit"]["modified_by"] = $user["UserEmail"]["user_id"];	
	
	// data for validation
	$this->data["Commit"]["branch"] = $payload->ref;
	$this->data["Commit"]["account"] = $payload->repository->url;
	$this->data["Commit"]["project_alias"] = $payload->repository->name;		
	
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
		'number_of_commits' => array(
			'numeric' => array(
				'rule' => array('numeric'),
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
	);
	
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
	

	/*******************
	* beforeSave: successfully passed validators
	*******************/
	function beforeSave(){	
	
		// all validations passed
		if($this->validates()){
		
			$project_alias = $this->data["Commit"]["project_alias"];			
			$repo = Git::open($this->getWebRoot().$project_alias."/dev");			
			$this->GitPull($repo);																	
						
			// set status - errors might have occured during git operation
			$this->data["Commit"]["status"] = count($this->getErrors()) == 0 ? true : false;		
		
		// validation error	occured
		}else{		
		
			// remove invalid fields from array
			foreach($this->invalidFields() as $errorName => $error){		
				unset($this->data["Commit"][$errorName]);
			}
					
			// set error status
			$this->data["Commit"]["status"]	= false;		
		}
		
		// continue to save the record
		return true;		
	}
	
	/*******************
	* afterSave: log errors
	*******************/	
	function afterSave(){
				
		// log Prosty errors
		foreach($this->getErrors() as $error){		
			// set values
			$this->data["CommitError"]["commit_id"] = $this->data["Commit"]["id"];			
			$this->data["CommitError"]["message"] = $error["message"];
			$this->data["CommitError"]["calling_function"] = $error["calling_function"];
		
			// save errors
			$this->CommitError->create();
			$this->CommitError->save($this->data);		
		}				
		
		// log cake validation errors
		foreach($this->invalidFields() as $errorName => $error){		
			// set values
			$this->data["CommitError"]["commit_id"] = $this->data["Commit"]["id"];			
			$this->data["CommitError"]["message"] = $error[0];
			$this->data["CommitError"]["calling_function"] = $errorName;
		
			// save errors
			$this->CommitError->create();
			$this->CommitError->save($this->data);		
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
		'CommitError' => array(
			'className' => 'CommitError',
			'foreignKey' => 'commit_id',
			'dependent' => false,
		)		
	);

}
