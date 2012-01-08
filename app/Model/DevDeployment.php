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
			$project_path = $this->getProjectPath($project_alias);
						
			$repo = $this->openRepo($project_path);
			if($repo){
				// Show untracked files (not added)
				$this->logUnstagedFiles($repo, 'ls-files --exclude-standard --others');
							
				// Show uncommited files
				$this->logUnstagedFiles($repo, 'diff-index --name-only HEAD');					
			
				// pull changes from GitHub
				$gitPull = $this->executeAndLogGit($repo, 'pull konscript master');
			
				// gitpull was attempted but merge errors occured: revert git pull
				if($gitPull === true && $this->getErrorCount() > 0){
					$this->logConflictingFiles($project_path);
					$this->executeAndLogGit($repo, 'reset --hard ORIG_HEAD', array('skipOnError' => false));
				}
			}		
			
			// clear cache on Caesar
			/*
			TODO: temp. disabled. Restore!
			$this->curl_wrapper(array(
				"url" => $project_alias . '.konscript.net',
				"request_method" => "BAN"
			));								
			*/												
		}		
			
		// set error status 
		$this->data["DevDeployment"]["status"] = $this->getErrorCount() == 0 ? true : false;		
		
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
	 
	/***************************	 
	* attempt to open git repo - if it fails (if git is not init) log the error
	***************************/	 	
	function openRepo($project_path){
		try{
			$repo = Git::open($project_path);
			
		// catch errors opening repo	
		}catch(Exception $e){
			$repo = false;
			$this->DevDeployment->logError(array(
				"request" => "Git::open(".$project_path.")",
				"response" => $e->getMessage()
			));
		}
		return $repo;
	}  	 

	/***************************	 
	* when examining a merge conflict, we need a list of the files in conflict
	****************************/	 	
	function getConflictingFiles($project_path){
		$cmd = "cd ".escapeshellarg($project_path)." && git ls-files --unmerged | cut -f2 | uniq";
		exec($cmd, $files);			
		return $files;
	}	
	
	/***************************	 	
	* log the list of files with cannot be merged
	***************************/	 	
	function logConflictingFiles($project_path){
		
		$response = $this->getConflictingFiles($project_path);
	
		$this->logError(array(
				"request" 					=> "git pull konscript master",
				"response" 					=> json_encode($response),
				"calling_function"	=> __function__
		));				
	}

	/***************************		 
	* fix merge conflicts with respect to --theirs or --ours
	****************************/	 	
	function resolveConflictingFiles($project_path){
	
		// open repo
		$repo = $this->openRepo($project_path);
		
		// pull from GitHub
		$this->executeAndLogGit($repo, 'pull konscript master', array('suppressErrors' => true));
		
		// checkout their files (GitHub))
	 	if(isset($_REQUEST["files"]["theirFiles"]) && is_array($_REQUEST["files"]["theirFiles"])){
	 		$theirFiles = implode(" ", $_REQUEST["files"]["theirFiles"]);
			$this->executeAndLogGit($repo, 'checkout --theirs ' . $theirFiles);
		}
		
		// checkout our files (Caesar)
	 	if(isset($_REQUEST["files"]["ourFiles"]) && is_array($_REQUEST["files"]["ourFiles"])){
	 		$ourFiles = implode(" ", $_REQUEST["files"]["ourFiles"]);		
			$this->executeAndLogGit($repo, 'checkout --ours ' . $ourFiles);
		}
		
		// revert, if there are still conflicting files after merge attempt
		$conflictingFiles = $this->getConflictingFiles($project_path);
		if(count($conflictingFiles) > 0){
			$this->executeAndLogGit($repo, 'reset --hard ORIG_HEAD', array('skipOnError' => false));		
		}
		
		// commit and push new files
		$this->executeAndLogGit($repo, 'add ' . $ourFiles ." ".$theirFiles);
		$this->executeAndLogGit($repo, 'commit -m "automatic merge resolving, using '.count($_REQUEST["files"]["theirFiles"]).' of their files, '.count($_REQUEST["files"]["ourFiles"]).' of ours"');
		$this->executeAndLogGit($repo, 'push konscript master');
	}
	
	/***************************
	* log any uncommited or unadded files
	***************************/				
	function logUnstagedFiles($repo, $git_command){
      
		$git_response = $repo->git_run_with_validation($git_command);
		
		// use std_error as response, unless if it is empty, then use std_out
		$response = empty($git_response[2]) ? $git_response[1] : $git_response[2];
		$return_code = $git_response[0];
		
		// on successful git command, convert response to json
		if( $return_code == 0 && !empty($response)){
			$response = explode("\n", $response);			
			
			// remove last element (is always empty)
			$last_element = count($response) - 1;
			unset($response[$last_element]);
						
			$response = json_encode($response);
		}
		
		$this->logError(array(
				"request" 					=> "git " . $git_command,
				"response" 					=> $response,
				"return_code" 			=> $return_code,
				"type"							=> "successOnEmptyResponse",
				"calling_function"	=> __function__
		));			
  }				

	/***************************
	* add/commit files	or gitignore files
	****************************/	 	
	function resolveUnstagedFiles($repo, $project_path){ 
 
	 	if(isset($_REQUEST["files"]["ignoreFiles"]) && is_array($_REQUEST["files"]["ignoreFiles"])){
	 		$ignoredFiles = implode(" ", $_REQUEST["files"]["ignoreFiles"]);
	 		
			// remove files from index but keep in working repository
			$this->executeAndLogGit($repo, 'rm --cached ' . $ignoredFiles, array('suppressErrors' => true));
			
			// add files to .gitignore
	 		$ignoredFilesForGitIgnore = implode("\n", $_REQUEST["files"]["ignoreFiles"]);			
	 		$filename = $project_path."/.gitignore";
	 		$content = "\n# Added: " . date("d-m-y", time()) ." \n". $ignoredFilesForGitIgnore;
			$this->writeToFile($filename, $content, FILE_APPEND);
			
			// commit gitignore changes to git
			$this->executeAndLogGit($repo, 'add .gitignore');
			$this->executeAndLogGit($repo, 'commit -m "Automatic gitignore update"');
	 	}
	 	
	 	if(isset($_REQUEST["files"]["commitFiles"]) && is_array($_REQUEST["files"]["commitFiles"])){

	 		// array to string
	 		$commitedFiles = implode(" ", $_REQUEST["files"]["commitFiles"]);
	 	
			// add and commit
			$this->executeAndLogGit($repo, 'add ' . $commitedFiles);
			$this->executeAndLogGit($repo, 'commit -m "Redeploy by user"');						
	 	}
	}

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
