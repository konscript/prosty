<?php
class ProstyBehavior extends ModelBehavior {

	public $web_root = "/srv/www/";	
	public $errors = array();
	
	function getServiceRoot(){
		return APP."Vendor/";	
	}
	
	function getErrors($Model){
		return $this->errors;
	}
	
	function getWebRoot($Model){
		return $this->web_root;
	}	
	/***************************
	* create new project
	***************************/	
	function createNewProject($Model, $data){

		// set variables
		$project_alias = $data['project_alias'];
		$full_path_to_project = $this->web_root.$project_alias;     

		// create project root - 02770: leading zero is required; 2 is the sticky bit (set guid); 770 is rwx,rwx,---
		mkdir($full_path_to_project, 02770);

		// create dev
		$full_path_to_dev = $full_path_to_project."/dev";
		mkdir($full_path_to_dev, 02770);
		$repo = Git::create($full_path_to_dev); //git init        
		$repo->run('remote add konscript '. $this->getGitRemote($Model, $project_alias)); 	// add remote
		$repo->run('config branch.master.remote konscript');				// set Konscript as the default remote
		$repo->run('config branch.master.merge refs/heads/master');			// set master as the default branch to pull from	
		$repo->run('commit --allow-empty -m "empty commit"');			// add empty commit to create master branch			

		
		// wordpress: download and extract latest version
		if(isset($data["wordpress"]) && $data["wordpress"] == true){
			$this->wp_get_latest($Model, $project_alias);
		}
					
				 						
		// add vhost for Nginx
		$vhost_nginx_filename = "/etc/nginx/sites-available/".$project_alias;
		$vhost_nginx_content = $this->vhost_nginx($Model, $project_alias);
		$this->writeToFile($Model, $vhost_nginx_filename, $vhost_nginx_content);
	
		// activate vhost for nginx
		$nginx_symlink = "/etc/nginx/sites-enabled/".$project_alias;
		if(is_link($nginx_symlink)){	unlink($nginx_symlink);		}
		symlink($vhost_nginx_filename, $nginx_symlink);		
	}
			
	function getProjectPath(){
		// get project_alias to be used in path
		$project = $Model->Project->findById($project_id);
		$project_alias = $project["Project"]["project_alias"];						
		return $this->web_root.$project_alias;
	}
	
	/***************************
	* general functions
	* git functions
	*
	*
	***************************/	
	
	// add error to array
	function logError($Model, $message, $calling_function){        	
		$this->errors[] = array(
			'message'=>$message,
			'calling_function'=>$calling_function
		);	
	}  		
	
		// log git commands if they return error 
    function validateGitResponse($Model, $git_response){
        if($git_response[0]>0){
        	$error = $git_response[1]."\n";
        	$error .= $git_response[2];        	
	        $this->logError($Model, $error, __function__);     
        }
    }    		
    
    // pull from GitHub - check for errors during and revert if anything fails
	function GitPull($Model, $repo){	
	
		// create tmp branch
		echo "Create branch";
		$create_branch = $repo->git_run_with_validation('branch tmp');
		debug($create_branch);
		$this->validateGitResponse($Model, $create_branch);

		// checkout tmp branch
		echo "Checkout tmp";
		$checkout_branch = $repo->git_run_with_validation('checkout tmp');
		debug($checkout_branch);
		$this->validateGitResponse($Model, $checkout_branch);


		// attempt pull
		if(count($this->errors) == 0){
			echo "Git pull";
			$pull = $repo->git_run_with_validation('pull konscript master');
			debug($pull);
			$this->validateGitResponse($Model, $pull);			
		}

		// checkout master
		echo "Checkout master";
		$checkout_master = $repo->git_run_with_validation('checkout master -f');
		debug($checkout_master);
		$this->validateGitResponse($Model, $checkout_master);	

		// merge master with tmp if no previous errors
		if(count($this->errors) == 0){
			echo "merge with tmp";	
			$merge = $repo->git_run_with_validation('merge tmp');	
			debug($merge);	
			$this->validateGitResponse($Model, $merge);		
		}

		// delete tmp
		echo "Delete branch";
		$delete_branch = $repo->git_run_with_validation('branch tmp -D');
		debug($delete_branch);	
		$this->validateGitResponse($Model, $delete_branch);	
		
		debug($this->errors);
	}	
    
 	
	// return git remote
	function getGitRemote($Model, $project_alias){
		return 'git@github.com:konscript/'.$project_alias;
	}			

	// update screenshot
	function update_screenshot($Model, $hostname, $project_alias){
			
		if($hostname){
			echo $project_alias;	
			set_time_limit(360);
			$command = $this->getServiceRoot()."wkhtmltoimage --height 1024 $hostname ".$this->getServiceRoot()."img/screenshots/".$project_alias.".jpg";
			exec($command, $status_msg, $status_code);
			return array($status_msg, $status_code);
		}else{
			return false;
		}
	}
	

	/***************************
	* files, folders and paths
	*
	*
	*
	***************************/				

  
	// download and extract latest version of wordpress to prod and dev	
	function wp_get_latest($Model, $project_alias) {

		$command = $this->getServiceRoot()."bash/download_wordpress.sh ".$project_alias;
		exec("$command 2>&1", $output, $return_code);		
		
		// something went wrong!
		if($return_code != 0){
		
			$msg = "command: ".$command."<br>";
			$msg .= "return code: ".$return_code."<br>";
								
			$msg .= "<pre>";
			$msg .= print_r( $output, true );
			$msg .= "</pre>";
			echo $msg;
			return 1;
	
		// wordpress installation went smooth
		}else{	
			return 0;	
		}	
	}

	/**
	 * Create and download zipped project
	 */
	function downloadZip($Model, $project_alias, $branch){	
	
		// download production version
		if($branch=="prod"){	
			// get target from symlink
			$path = readlink($this->web_root.$project_alias.'/prod/current');		
			$dbname = $project_alias.'-prod';
	
		// download development version		
		}else{
			$path = $this->web_root.$project_alias."/dev";
			$dbname = $project_alias.'-dev';
		}	

		if(is_dir($path)){
			// create files
			$command = $this->getServiceRoot()."bash/download_project.sh $project_alias $path $dbname";
			exec("$command  2>&1", $output, $return_code);	
			$pathToTar = "/temp/".$project_alias.".tar";

			if(in_array("success", $output) && $return_code == 0 && is_file($this->getServiceRoot().$pathToTar)){	
				header("Location: $pathToTar");
			}else{
				echo "return code: ".$return_code."<br>";
				echo "command: ".$command."<br>";		
				dump($output);
			}
		
		}else{
			echo"Path does not exist";
		}
	}	

	/**
	 * zip file/folder
	 * usage: Zip('/folder/to/compress/', './compressed.zip');
	 */
	function Zip($Model, $source, $destination)
	{
		if (extension_loaded('zip') === true)
		{
		    if (file_exists($source) === true)
		    {

		            $zip = new ZipArchive();

		            if ($zip->open($destination, ZIPARCHIVE::CREATE) === true)
		            {
		                    $source = realpath($source);

		                    if (is_dir($source) === true)
		                    {
		                            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

		                            foreach ($files as $file)
		                            {
		                                    $file = realpath($file);

		                                    if (is_dir($file) === true)
		                                    {
		                                            $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
		                                    }

		                                    else if (is_file($file) === true)
		                                    {
		                                            $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
		                                    }
		                            }

		                    }

		                    else if (is_file($source) === true)
		                    {
		                            $zip->addFromString(basename($source), file_get_contents($source));
		                    }
		            }

		            return $zip->close();
		    }
		}

		return false;
	}
	
	/***************************
	* nginx and virtual host functions
	*
	*
	*
	***************************/
 
	/**
	 * purge entire nginx cache for the current project
	 ***/	  
	function clearCache($Model, $project_id){    	
		$path_to_nginx_cache ="/var/cache/nginx/cached/".$project_id;
		$status = 1;		
		
		if(isset($project_id) && is_dir($path_to_nginx_cache)){
			$chdir = is_dir($path_to_nginx_cache) ? chdir($path_to_nginx_cache) : false;

			if($chdir && getcwd()==$path_to_nginx_cache && trim(shell_exec("pwd"))==$path_to_nginx_cache){
				exec("find $path_to_nginx_cache -type f -exec rm -f {} \;", $output, $status);
				$status = 0;
			}
		}
		
		// an error occured
		if($status == 1){
		
			// build error message
			$msg = "Cache could not be cleared in: $path_to_nginx_cache";
			$msg .= " ( PHP: ".getcwd();
			$msg .= " Shell: ".shell_exec("pwd").")";
			$this->logError($Model, $msg, __function__);
		}
	}		
	
	// write values to vhost
	function writeToFile($Model, $filename, $content){
		$res = false;
		$res = file_put_contents($filename, $content);	
	
		if($res === false){
		      $this->logError($Model, "$filename could not be updated", __function__);
		}
	}				

	// create nginx vhost
	function vhost_nginx($Model, $project_alias){
		return '
		server { 
			server_name		' . $project_alias . '.konscript.com;
			root					/srv/www/' . $project_alias . '/;

			include includes/locations_dev.conf;
			include includes/wordpress.conf;
		}';
	}		

}
