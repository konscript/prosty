<?php
App::uses('AppModel', 'Model');
App::import('Vendor', 'Git');
App::import('Vendor', 'Prosty');
/**
 * Commit Model
 *
 * @property Project $Project
 * @property Deployment $Deployment
 */
class Commit extends AppModel {


 	function getProsty(){
 		if(!$this->Prosty){
 			$this->Prosty = new Prosty();
		}	
		return $this->Prosty;
 	}
 	
	/*******************
	* beforeValidate: formatting of request values
	*******************/ 	
	function beforeValidate(){
	
		if($_SERVER["REMOTE_ADDR"] == "127.0.0.1"){
			$_REQUEST['payload'] = file_get_contents("/srv/www/prosty_cake/app/Vendor/payload");
		}

		// Receive the json payload string
		if(isset($_REQUEST['payload'])){
			$payload = json_decode($_REQUEST['payload']);
		}else{
			$this->Session->setFlash(__('No payload was received'));
			$this->redirect(array('action' => 'index'));
		}					
				
		$number_of_commits = count($payload->commits);

		// get project_id via project_alias
		$projects = $this->Project->find('first', array(
			'conditions' => array('project_alias' => $payload->repository->name),
			'recursive' => -1,
			'fields' => array('id')
		));		
		
		// get user_id via user's email
		$user = $this->CreatedBy->find('first', array(
			'conditions' => array('email' => $payload->commits[$number_of_commits-1]->author->email),
			'recursive' => -1,
			'fields' => array('id')
		));				

		// data for DB
		$this->data["Commit"]["project_id"] = $projects["Project"]["id"];						
		$this->data["Commit"]["commit_hash"] = $payload->after;			
		$this->data["Commit"]["last_commit_msg"] = $payload->commits[$number_of_commits-1]->message;			
		$this->data["Commit"]["number_of_commits"] = $number_of_commits;			
		$this->data["Commit"]["ip_addr"] = $_SERVER["REMOTE_ADDR"];		
		$this->data["Commit"]["created_by"] = $user["CreatedBy"]["id"];			
		$this->data["Commit"]["modified_by"] = $user["CreatedBy"]["id"];	
		
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
		'commit_hash' => array(
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
	
		$project_alias = $this->data["Commit"]["project_alias"];
	
		$Prosty = $this->getProsty();
		$git_response = Git::git_callback('pull konscript master', $Prosty->web_root.$project_alias."/dev", true);
		$Prosty->checkGitPull($git_response);   		
		
		// set status
		$this->data["Commit"]["status"] = count($Prosty->errors) == 0 ? true : false;
		
		return true;		
	}
	
	/*******************
	* afterSave: log errors
	*******************/	
	function afterSave(){
	
		$Prosty = $this->getProsty();
	
		foreach($Prosty->errors as $error){		
			// set values
			$this->data["CommitError"]["commit_id"] = $this->data["Commit"]["id"];			
			$this->data["CommitError"]["message"] = $error["message"];
			$this->data["CommitError"]["calling_function"] = $error["calling_function"];
		
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
		'CreatedBy' => array(
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
		'Deployment' => array(
			'className' => 'Deployment',
			'foreignKey' => 'commit_id',
			'dependent' => false,
		),
		'CommitError' => array(
			'className' => 'CommitError',
			'foreignKey' => 'commit_id',
			'dependent' => false,
		)		
	);

}
