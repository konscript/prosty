<?php
App::uses('AppModel', 'Model');
App::import('Vendor', 'Git');

class ResolveDeployment extends AppModel {

  public $useTable = 'deployments';
	public $actsAs = array('Prosty');
	
	public $validate = array(
		'project_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Project id must be numeric',
			)				
		)
	);
		
	/*******************
	* beforeSave: deploy project, clone folders and run git
	*******************/			
	function beforeSave(){
	
		$validates = $this->validates();
		
		// validation failed: log and remove invalid fields from array				
		$this->logCakeValidationErrors($validates);				
			
		if($validates){		
		
			// set variables
			$type = $this->data["ResolveDeployment"]["type"];			
			$project_id = $this->data["ResolveDeployment"]["project_id"];
			$project_alias = $this->getProjectAlias($project_id);
			$project_path = $this->getProjectPath($project_alias);
				
			// open reponse
			$repo = $this->openRepo($project_path);					
			
			if($repo){
				if($type == "unstaged"){
					$result = $this->resolveUnstagedFiles($repo, $project_path);
				}elseif($type == "unmerged"){
					$result = $this->resolveConflictingFiles($repo, $project_path);				
				}
				
				// set commit hash
				$hash = $repo->run('rev-parse HEAD');	 					
				
				// set db values
				$this->data["ResolveDeployment"]["last_commit_msg"] = $result["commit_message"];
				$this->data["ResolveDeployment"]["hash"] = $hash;
				$this->data["ResolveDeployment"]["server"] = "Caesar";
				$this->data["ResolveDeployment"]["files"] = json_encode($result["files"]);
				
				// pull changes from GitHub
				$gitPull = $this->executeAndLogGit($repo, 'pull konscript master');
			
				// gitpull was attempted but merge errors occured: revert git pull
				if($gitPull === true && $this->getErrorCount() > 0){
					$this->logConflictingFiles($project_path);
					$this->executeAndLogGit($repo, 'reset --hard ORIG_HEAD', array('skipOnError' => false));
					echo "failure";
				}else{
					$this->executeAndLogGit($repo, 'push konscript master');
					echo "Success";
				}				
			}
		}
		
		// set error status 
		$this->data["ResolveDeployment"]["status"] = $this->getErrorCount() == 0 ? true : false;				
		
		echo "errors";
		debug($this->getErrors());
						
		return true;
	}
	
	/*******************
	* afterSave: log errors
	*******************/	
	function afterSave(){
		$this->saveErrorLogs();
	}
	
	
	/*******************
	* HELPER: get files in string format or as array
	*******************/		
	function getFilesByAction($action, $options = array()){
		
		$default_options = array(
			'state' => "both", 
			'format' => "string",
			'delimiter' => " "
		);
		
		// merge with default values		
		$options = array_merge($default_options, $options);			
		
		$files = $_REQUEST["files"][$action];
				
		// Possible values for (file)state: ignoreFiles, commitFiles. Empty will show both
		if($options["state"] == "both"){
			$files = array_values($files);
			$files = count($files) == 2 ? array_merge($files["0"], $files["1"]) : $files["0"];			
		}else{
			if(isset($files[$options["state"]])){
				$files = $files[$options["state"]];
			}else{
				$files = array();
			}
		}		
		
		// set format
		if($options["format"] == "array"){
			$output = $files;
		}else{
			$output = trim(implode($options["delimiter"], $files));
		}
		
		return $output;
	}
		
	
	/***************************
	* add/commit or gitignore files
	****************************/	 	
	function resolveUnstagedFiles($repo, $project_path){
	
	 	// ACTION: ignore files	
	 	if(isset($_REQUEST["files"]["ignoreFiles"]) && is_array($_REQUEST["files"]["ignoreFiles"])){
	 		 		
	 		// get changed files which are to be ignored
	 		$ignoredChangedFiles = $this->getFilesByAction("ignoreFiles", array('state' => 'changed'));

			// remove files from index but keep in working repository
			if($ignoredChangedFiles != ""){
				$this->executeAndLogGit($repo, 'rm --cached ' . $ignoredChangedFiles, array('suppressErrors' => true));
			}
					
			// add files to .gitignore
	 		$ignoredFilesForGitIgnore = $this->getFilesByAction("ignoreFiles", array('delimiter' => '\n'));
	 			 				
	 		$gitignore_file = $project_path."/.gitignore";
	 		$gitignore_content = "\n# Added: " . date("d-m-y", time()) ." \n". $ignoredFilesForGitIgnore;
			$this->writeToFile($gitignore_file, $gitignore_content, FILE_APPEND);
			
			// set commit message
			$commit_message = 'Added '.$ignoredFiles.' to gitignore';
			
			// commit gitignore changes to git
			$this->executeAndLogGit($repo, 'add .gitignore');
			$this->executeAndLogGit($repo, 'commit -m "'.$commit_message.'"');
	 	}
	 	
	 	// ACTION: Commit files
	 	if(isset($_REQUEST["files"]["commitFiles"]) && is_array($_REQUEST["files"]["commitFiles"])){

	 		// get commitFiles as string
	 		$commitedFilesStr = $this->getFilesByAction("commitFiles");	 		
	 		
			// set commit message
			$commit_message = 'Added '.$commitedFilesStr;
	 	
			// add and commit
			if($commitedFilesStr != ""){
				$this->executeAndLogGit($repo, 'add ' . $commitedFilesStr);
				$this->executeAndLogGit($repo, 'commit -m "'.$commit_message.'"');
			}
	 	}
	 	
		return array(
			'commit_message' => $commit_message,
			'files' => $_REQUEST["files"]
		);
	}

	/***************************		 
	* fix merge conflicts with respect to --theirs or --ours
	****************************/	 	
	function resolveConflictingFiles($repo, $project_path){
		
		// get files which are to be ignored (and therefore not to be checked-out)
		$ignoreFiles = $this->getIgnoreFiles($repo);

		// pull from GitHub
		$this->executeAndLogGit($repo, 'pull konscript master', array('suppressErrors' => true));
	
		// ACTION: their files (GitHub))
	 	if(isset($_REQUEST["files"]["theirFiles"]) && is_array($_REQUEST["files"]["theirFiles"])){
	 	
	 		// only checkout files, which are not ignored
			$this->checkoutFiles("theirs", $_REQUEST["files"]["theirFiles"], $ignoreFiles, $repo);			
		
			// add "their" ignored files to index
			$theirIgnoredFiles = array_intersect($_REQUEST["files"]["theirFiles"], $ignoreFiles);			
	 		$theirIgnoredFilesStr = implode(" ", $theirIgnoredFiles);				
	 		if( count($theirIgnoredFiles) > 0){
				$this->executeAndLogGit($repo, 'add ' . $theirIgnoredFilesStr);			
			}
		}
	
		// ACTION: our files (Caesar)
	 	if(isset($_REQUEST["files"]["ourFiles"]) && is_array($_REQUEST["files"]["ourFiles"])){	
	 	
	 		// only checkout files, which are not ignored
			$this->checkoutFiles("ours", $_REQUEST["files"]["ourFiles"], $ignoreFiles, $repo);			
			
			// remove "our" ignored files from index
			$ourIgnoredFiles = array_intersect($_REQUEST["files"]["ourFiles"], $ignoreFiles);			
	 		$ourIgnoredFilesStr = implode(" ", $ourIgnoredFiles);			
	 		if( count($ourIgnoredFiles) > 0){	 		
				$this->executeAndLogGit($repo, 'rm --cached ' . $ourIgnoredFilesStr);				
			}
		}
			
		// set commit message
		$commit_message = 'automatic merge resolving, using '.count($_REQUEST["files"]["theirFiles"]).' of their files, '.count($_REQUEST["files"]["ourFiles"]).' of ours';
		
		// revert, if there are still conflicting files after merge attempt
		$conflictingFiles = $this->getConflictingFiles($project_path);
		if(count($conflictingFiles) > 0){		
			$this->executeAndLogGit($repo, 'reset --hard ORIG_HEAD', array('skipOnError' => false));		
		}		
		
		$this->executeAndLogGit($repo, 'commit -m "'.$commit_message.'"');		
		$this->executeAndLogGit($repo, 'push konscript master');
		
		return array(
			'commit_message' => $commit_message,
			'files' => $_REQUEST["files"]
		);
	}
	
	/***************************	 	
	* HELPER: checkout files according to the merge strategy (ours, theirs), and add to index
	***************************/	 			
 	function checkoutFiles($mergeStrategy, $files, $ignoreFiles, $repo){
 	
 		// only checkout files, which are not ignored
		$files = array_diff($files, $ignoreFiles);	 					 				
 		$filesStr = implode(" ", $files);		
 		if( count($files) > 0){	 		
			$this->executeAndLogGit($repo, 'checkout --'.$mergeStrategy.' ' . $filesStr);
			$this->executeAndLogGit($repo, 'add ' . $filesStr);
		}	 	
 	
 	}	
	
	/***************************	 	
	* HELPER: get the list of file which was to be ignored
	***************************/	 		
	function getIgnoreFiles($repo){
		// current commit hash
		$hash = $repo->run('rev-parse HEAD');
	
		// get ignored files in current deployment (commit hash)
		$deployment = $this->find("first", array(
			'order' => array('ResolveDeployment.id DESC'),
			'conditions' => array(
				'ResolveDeployment.hash' => trim($hash),
				'ResolveDeployment.files LIKE' => '%ignore%'				
			),
			'fields' => array('files')
		));

		$files = json_decode($deployment["ResolveDeployment"]["files"]);		
		
		// files->ignoreFiles can be a multidimensional array. We want to strip the dimensions, and get all ignored files in a flat structure.
		if(isset($files->ignoreFiles)){
			$ignoreFiles = array_values($files->ignoreFiles);
			$ignoreFiles = count($ignoreFiles) == 2 ? array_merge($ignoreFiles["0"], $ignoreFiles["1"]) : $ignoreFiles["0"];
		}else{
			$ignoreFiles = array();
		}

		return $ignoreFiles;
	}	
	
	/***************************	 	
	* log the list of files which cannot be merged
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
	* belongsTo associations
	***************************/	
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
	
	/***************************		 
	* hasMany associations
	***************************/	
	public $hasMany = array(
		'DeploymentError' => array(
			'className' => 'DeploymentError',
			'foreignKey' => 'deployment_id',
			'dependent' => false,
		)		
	);			

}
