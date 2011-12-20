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
	
	function getProjectPath($Model, $project_alias){
		return $this->web_root.$project_alias."/web";
	}
	
	function getProjectAlias($Model, $project_id){
		// get project_alias from project_id
		$project = $Model->Project->findById($project_id);
		$project_alias = $project["Project"]["project_alias"];						
		return $project_alias;
	}
	
	function getGitRemote($Model, $project_alias){
		return 'git@github.com:konscript/'.$project_alias;
	}				
		
	/***************************
	* create new project
	***************************/	
	function createNewProject($Model, $data){

		// set variables
		$project_alias = $data['project_alias'];
		$project_path = $this->getProjectPath($Model, $project_alias);

		// create project root - 02770: leading zero is required; 2 is the sticky bit (set guid); 770 is rwx,rwx,---
		
		mkdir($project_path, 02770, true);

		// git init
		$repo = Git::create($project_path); 		
		// add remote
		$repo->run('remote add konscript '. $this->getGitRemote($Model, $project_alias));		
		// set Konscript as the default remote
		$repo->run('config branch.master.remote konscript');		
		// set master as the default branch to pull from	
		$repo->run('config branch.master.merge refs/heads/master');		
		// add empty commit to create master branch
		$repo->run('commit --allow-empty -m "empty commit"');
		
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
	
	/***************************
	* write values to vhost
	***************************/			
	function writeToFile($Model, $filename, $content){
		$res = false;
		$res = file_put_contents($filename, $content);	
	
		if($res === false){
		      $this->logError($Model, "$filename could not be updated", __function__);
		}
	}				

	/***************************
	* create nginx vhost
	***************************/			
	function vhost_nginx($Model, $project_alias){
		return '
		server { 
			server_name		' . $project_alias . '.konscript.com;
			root					/srv/www/' . $project_alias . '/;

			include includes/locations_dev.conf;
			include includes/wordpress.conf;
		}';
	}			
	
	/***************************
	* add error to array
	***************************/		
	function logError($Model, $message, $calling_function){        	
		$this->errors[] = array(
			'message'=>$message,
			'calling_function'=>$calling_function
		);	
	}  		
	
	/***************************
	* log git commands if they return errors
	***************************/			
  function validateGitResponse($Model, $git_response, $function_name){
      if($git_response[0]>0){
      	// $error = $git_response[1]."\n";
      	$error = $git_response[2];        	
        $this->logError($Model, $error, $function_name);
      }
  }    		
    
	/***************************
	* pull from GitHub - check for errors during and revert if anything fails
	***************************/		  
	function GitPull($Model, $repo){	
	
		// create tmp branch
		// echo "Create branch";
		$create_branch = $repo->git_run_with_validation('branch tmp');
		// debug($create_branch);
		$this->validateGitResponse($Model, $create_branch, "create branch");

		// checkout tmp branch
		// echo "Checkout tmp";
		$checkout_branch = $repo->git_run_with_validation('checkout tmp');
		// debug($checkout_branch);
		$this->validateGitResponse($Model, $checkout_branch, "checkout branch");


		// attempt pull
		if(count($this->errors) == 0){
			// echo "Git pull";
			$pull = $repo->git_run_with_validation('pull konscript master');
			// debug($pull);
			$this->validateGitResponse($Model, $pull, "git pull");			
		}

		// checkout master
		// echo "Checkout master";
		$checkout_master = $repo->git_run_with_validation('checkout master -f');
		// debug($checkout_master);
		$this->validateGitResponse($Model, $checkout_master, "checkout master");	

		// merge master with tmp if no previous errors
		if(count($this->errors) == 0){
			// echo "merge with tmp";	
			$merge = $repo->git_run_with_validation('merge tmp');	
			// debug($merge);	
			$this->validateGitResponse($Model, $merge, "merge with tmp");
		}

		// delete tmp
		// echo "Delete branch";
		$delete_branch = $repo->git_run_with_validation('branch tmp -D');
		// debug($delete_branch);	
		$this->validateGitResponse($Model, $delete_branch, "delete branch");	
		
		debug($this->errors);
	}	
	
	
	/***************************
	* Call Brutus to make deployment
	***************************/			
	function deployment_hook($Model, $project_id){
		$project_alias = $this->getProjectAlias($Model, $project_id);
		
		// if no errors were encountered
		if(count($this->errors) == 0){
			
			// set variables			
			$url = "http://deployment.konscript.com";
			$key = Configure::read('Deployment.key');
			$keys = $this->split_key($key);
			$iv = $this->random_iv();

			// generate signature from key and AES encrypted message
			$encrypted_project_alias = openssl_encrypt($project_alias, "AES-256-CBC", $key, false, $iv);
			$signature = sha1($keys[0].$encrypted_project_alias.$keys[1]);

			$data = array(
				"iv" => $iv,
				"signature" => $signature,
				"encrypted_project_alias" => $encrypted_project_alias
			);			
					
			$curl = $this->curl_helper($url, $data);

			// log errors			
			$errors_php = json_decode($curl["response"]);						
			if(is_array($errors_php)){
				foreach($errors_php as $error){
		      $this->logError($Model, $error->message, $error->calling_function);
				}
			}else{
	      $this->logError($Model, "Unknown error", __function__);			
			}
			
			// log: connection problem
			if($curl["http_code"] != 200){
	      $this->logError($Model, "Problem connecting to $url - http code: ".$curl["http_code"], __function__);
			}			
			
		}		
	}	
	
	/***************************
	* add deployment to NewRelic		
	***************************/			
	function newrelic_hook($Model, $project_id){
		$project_alias = $this->getProjectAlias($Model, $project_id);
	
		$project_id = $Model->data["Deployment"]["project_id"];
	
		$all = $Model->Project->Commit->find('first', array(
			'conditions' => array('Commit.project_id' => $project_id)
		));
		
		$username = $_SESSION["Auth"]["User"]["username"];
		$commit = $all["Commit"]["last_commit_msg"];
				
		if(count($this->errors) == 0){
	
			$url = "https://rpm.newrelic.com/deployments.xml";	
			$data = array(
				'app_name' => $project_alias,
				'user' => $username,
				'description' => $commit
			);				
			$headers = array(
				'x-api-key: d1fbd044db12e57daf5af391289571049d66659e01c88d7'
			);
			$this->curl_helper($url, $data, $headers);
		}		
	}	     
	
	// makes a random alpha numeric 16 char string 
	function random_iv($length = 16){
		$aZ09 = array_merge(range('A', 'Z'), range('a', 'z'),range(0, 9)); 
		$out =''; 
		for($c=0;$c < $length;$c++) { 
		   $out .= $aZ09[mt_rand(0,count($aZ09)-1)]; 
		} 
		return $out; 
	}
	
	// split key into two
	function split_key($key){
		$half = (int) ( (strlen($key) / 2) );
		$left = substr($key, 0, $half);
		$right = substr($key, $half);
		return array($left, $right);
	}	
	
	function curl_helper($url, $data, $headers = null){
	
		$ch = curl_init();				
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);		// return instead of echo result
		curl_setopt($ch, CURLOPT_POST, true);						// post instead of get
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);		// post data
		if(isset($headers)){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);	// custom headers	
		}
		$reponse = curl_exec($ch);
		$info = curl_getinfo($ch);
		
		curl_close($ch);	
		return array(
			"response" => $reponse, 
			"http_code" => $info["http_code"]
		);
		// debug($output);
		// debug($info);	
	
	}

	/***************************
	* update screenshot
	***************************/			
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
	* download and extract latest version of wordpress
	***************************/			
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

	/***************************
	* Create and download zipped project
	***************************/		
	function downloadZip($Model, $project_alias, $branch){	
	
		$path = $this->getProjectPath($Model, $project_alias);
		$dbname = $project_alias;
	
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

	/***************************
	 * zip file/folder
	 * usage: Zip('/folder/to/compress/', './compressed.zip');
	***************************/		
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
}
