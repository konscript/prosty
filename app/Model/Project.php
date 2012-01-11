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
    return !is_dir($path); //return false if it exists      
  }       	    

	/***************************		 
	* beforeValidate
	***************************/
	function beforeValidate(){
	
		// remove Github validation
		if(isset($this->data["Project"]["skipGithub"]) && $this->data["Project"]["skipGithub"] == true){
			unset($this->validate["project_alias"]["github"]);
		}
	}

  /**
   * beforeSave: executes after valdiations and before save
   *************************************************/
	function beforeSave($model){
			      							
		// create new project
		if(!isset($this->data["Project"]["id"])) {
			
			// set variables
			$project_alias = $this->data["Project"]['project_alias'];
			$project_path = $this->getProjectPath($project_alias);

			// create project root - 02770: leading zero is required; 2 is the sticky bit (set guid); 770 is rwx,rwx,---
			mkdir($project_path, 02770, true);

			// git init						
			$repo = Git::create($project_path); 		
			
			// add remote
			$this->executeAndLogGit($repo, 'remote add konscript '. $this->getGitRemote($project_alias));

			// set Konscript as the default remote
			$this->executeAndLogGit($repo, 'config branch.master.remote konscript');
						
			// set master as the default branch to pull from	
			$this->executeAndLogGit($repo, 'config branch.master.merge refs/heads/master');
					
			// wordpress: download and extract latest version
			if(isset($this->data["Project"]["installWordpress"]) && $this->data["Project"]["installWordpress"] == true){
				$this->setup_wp_and_kontemplate($project_alias);

				// push to GitHub
				$this->executeAndLogGit($repo, 'add -A');
				$this->executeAndLogGit($repo, 'commit -m "Automatic initial commit from Caesar"');
				$this->executeAndLogGit($repo, 'push konscript master');				
			}else{
				// add empty commit to create master branch
				$this->executeAndLogGit($repo, 'commit --allow-empty -m "empty commit"');						
			}
										 						
			// add vhost for Nginx
			$vhost_filename = "/etc/nginx/sites-available/".$project_alias;
			$vhost_content = file_get_contents("/etc/nginx/sites-available/template");
		  $vhost_content = str_replace("PROJECTALIAS", $project_alias, $vhost_content);		
			$this->writeToFile($vhost_filename, $vhost_content);
	
			// activate vhost for nginx
			$nginx_symlink = "/etc/nginx/sites-enabled/".$project_alias;
			if(is_link($nginx_symlink)){	unlink($nginx_symlink);		}
			symlink($vhost_filename, $nginx_symlink);		
			
			// create database
			$this->query("CREATE DATABASE IF NOT EXISTS ".$this->data["Project"]["project_alias"]."");						
		}
	
		// success
		if($this->getErrorCount() == 0){
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
		'Deployment' => array(
			'className' => 'Deployment',
			'foreignKey' => 'project_id',
			'order' => array('Deployment.id DESC'),
			'dependent' => false,
		)				
	);
		
	/***************************
	* download and untar Wordpress and Kontemplate
	***************************/			
	function setup_wp_and_kontemplate($project_alias) {

		$project_path = $this->getProjectPath($project_alias);
		
		// download and untar wordpress				
		$stale_date = time() - 3600 * 7; // max a week old
		$this->downloadAndUntar(APP."tmp/downloads/latest.tar.gz", "http://wordpress.org/latest.tar.gz", $project_path, $stale_date);
		
		// decode json and get date for last commit
		// determine date of latest commit 
		$commits_json = json_decode(file_get_contents("https://api.github.com/repos/konscript/dlvs-uk/commits"));	
		$last_commit = strtotime($commits_json[0]->commit->author->date);		

		// download and untar kontemplate				
		$this->downloadAndUntar(APP."tmp/downloads/kontemplate.tar.gz", "http://github.com/konscript/kontemplate-wp/tarball/master", $project_path, $last_commit);						
	}
	
	/***************************
	* Download and untar a file
	***************************/				
	function downloadAndUntar($local_path, $external_path, $project_path, $stale_date){
	
		// remove file if too old
		if(is_file($local_path) && filemtime($local_path) < $stale_date ){
			unlink($local_path);
		}
		
		// download file if it does not exist
		if(!is_file($local_path)){
			$data = file_get_contents($external_path);
			file_put_contents($local_path, $data);
		}

		// untar file
		$untar_cmd = "tar -C $project_path -zxf $local_path --strip 1";
		exec($untar_cmd . ' 2>&1', $output, $return_code);
		
		// error logging
	  $this->logError(array(
			"message" => "Command: ".$untar_cmd." Response: " . $output,
			"calling_function" => __function__,
			"return_code" => $return_code
		));
	}
		
	/***************************
	* Create and download zipped project
	***************************/		
	function downloadZip($project_id, $type = "files"){	

		// set filename depending on type
		$project_alias = $this->getProjectAlias($project_id);
		$file = APP . "tmp/downloads/" . $project_alias;
		$file .= ( $type == "sql" ) ? ".sql" : ".tar";
						

						
		// delete old files
		$stale_date = time() - 3600 * 0.5; // max half an hour old
		if( is_file($file) && filemtime($file) < $stale_date ){
			unlink($file);
		}

		// create files
		if(!is_file($file)){
			
			// create mysqldump
			if($type == "sql") {
				$dbname = $project_alias;					
				$cmd = "mysqldump -u ".Configure::read('mysql.username')." -p".Configure::read('mysql.password')." $dbname > $file";
			
			// create tar archive with web files
			}else{
				$project_path = pathinfo($this->getProjectPath($project_alias));
				$cmd = "cd " . $project_path["dirname"] . " && tar --create --file=$file ".$project_path["basename"];
			}
			
			exec($cmd . " 2>&1", $output, $return_code);
			
			// TODO: don't start download until file has been created
			
			// error logging
			$this->logError(array(
				"message" => "Command: ".$cmd." Response: " . $output,
				"calling_function" => __function__,
				"return_code" => $return_code
			));
		}			
		
		// return fileinfo
		return pathinfo($file);
	}
}
