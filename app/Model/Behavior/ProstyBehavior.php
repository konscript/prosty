<?php
class ProstyBehavior extends ModelBehavior {

	public $web_root = "/srv/www/";
	//public $service_root = APP."Vendor";
	public $errors = array();
	
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
		
		// wordpress: download and extract latest version
		if(isset($data["wordpress"]) && $data["wordpress"] == true){
			$this->wp_get_latest($Model, $project_alias);
		}
		
		// create prod: clone dev to prod
		$full_path_to_prod = $full_path_to_project."/prod";
		mkdir($full_path_to_prod, 02770);
		$this->recursive_copy($Model, $full_path_to_dev, $full_path_to_prod."/1"); 				

		// add symlink to current
		$target = $full_path_to_prod."/1";
		$link = $full_path_to_prod."/current";
		symlink($target, $link);			
				
				 						
		// add vhost for Nginx
		$vhost_nginx_filename = "/etc/nginx/sites-available/".$project_alias;
		$vhost_nginx_content = $this->vhost_nginx($Model, $project_alias, $data["primary_domain"], $data["dev_domain"], $data["additional_domains"]);		
		$this->writeToFile($Model, $vhost_nginx_filename, $vhost_nginx_content);
		
		// activate vhost for nginx
		$nginx_symlink = "/etc/nginx/sites-enabled/".$project_alias;
		if(is_link($nginx_symlink)){	unlink($nginx_symlink);		}
		symlink($vhost_nginx_filename, $nginx_symlink);		
	}
	
	/***************************
	* update existing project - change virtual hosts and symlink
	***************************/
	function updateProject($Model, $data, $old_data){	
	
		$project_alias = $old_data["project_alias"];

		// these fields must be supplied, in order to update virtual hosts/symlinks
		$vhost_fields = array('primary_domain', 'additional_domains', 'dev_domain', 'use_cache');
		$all_fields = array_keys($data);	
		$changed_vhost_fields = array_intersect($all_fields, $vhost_fields);		

		if(count($changed_vhost_fields)>=count($vhost_fields)){
			// produce and check vhost for Nginx	
			$nginx = array();	
			$nginx[] = array($this->vhost_nginx_additional($Model, $old_data["primary_domain"], $old_data["additional_domains"]), $this->vhost_nginx_additional($Model, $data["primary_domain"], $data["additional_domains"]));		// additional			
			$nginx[] = array($this->vhost_nginx_rewrite($Model, $old_data["primary_domain"]), $this->vhost_nginx_rewrite($Model, $data["primary_domain"])	);	// rewrite
			$nginx[] = array($this->vhost_nginx_primary($Model, $old_data["primary_domain"]), $this->vhost_nginx_primary($Model, $data["primary_domain"])	); // primary
			$nginx[] = array($this->vhost_nginx_dev($Model, $old_data["dev_domain"]), $this->vhost_nginx_dev($Model, $data["dev_domain"])); // dev
			$nginx[] = array($this->vhost_nginx_cache($Model, $project_alias, $old_data["use_cache"]), $this->vhost_nginx_cache($Model, $project_alias, $data["use_cache"])); // dev	

			$vhost_nginx_filename = "/etc/nginx/sites-available/".$old_data["project_alias"];			
			$vhost_nginx_content = $this->get_vhost($Model, $vhost_nginx_filename, $nginx);
		}
		
		// on success
		if (count($this->errors) == 0){     

			// update symlink - if the version was changed
			if(in_array('current_version', $all_fields)){
				$symTarget = $this->web_root.$project_alias."/prod/".$data["current_version"];
				$symlink = $this->web_root.$project_alias."/prod/current";			
			
				unlink($symlink);
				symlink($symTarget, $symlink);
			}		

			if(count($changed_vhost_fields)>0){
				// write new virtual host settings to Nginx
				$this->writeToFile($Model, $vhost_nginx_filename, $vhost_nginx_content);
			}
		}			
		 	
	}
		
	/********************
	* Functions used exlusively by Deployment model
	********************/

	// get the folder with the highest number (newest version)
	function get_latest_prod_version($Model, $dir){
		$folders = $this->get_list_of_folders($Model, $dir);
		$versions = array();
		foreach($folders as $folder){
		    if(is_numeric($folder) == true){
		        $versions[] = $folder;
		    }
		}       
		
		// return the newest folder. If none exist return false   
		return count($versions) == 0 ? 1 : $versions[0];
	}	
	
	// recursively copy entire directory    
	function recursive_copy($Model, $src,$dst) { 

		if(is_dir($dst) || !is_dir($src)){
		    echo "recurse_copy error";
		    exit();
		}

		$dir = opendir($src); 
		mkdir($dst, 01770);
		
		while(false !== ( $file = readdir($dir)) ) { 
		    if (( $file != '.' ) && ( $file != '..' )) { 
		        if ( is_dir($src . '/' . $file) ) { 
		            $this->recursive_copy($Model, $src . '/' . $file,$dst . '/' . $file); 
		        } 
		        else { 
		            copy($src . '/' . $file,$dst . '/' . $file); 
		        } 
		    } 
		} 
		closedir($dir); 
	}   	

	// return array with paths to the project's current and next versions
	function getVersionPaths($Model, $project_id){
		
		if(!isset($this->versionPaths)){
		
			// get project_alias to be used in path
			$project = $Model->Project->findById($project_id);
			$project_alias = $project["Project"]["project_alias"];		
			$path = $this->web_root.$project_alias.'/prod/';			
		
			// set current version (current need not be the highest version)
			$current_version = $project["Project"]["current_version"];				
			$highest_version = $this->get_latest_prod_version($Model, $path);				
			
	
		
			// set next version
			$next_version = $highest_version + 1;
		
			$this->versionPaths = array(
				'current' => $path.$current_version,
				'next' => $path.$next_version,			
				'symlink' => $path.'current',				
			);					
		}
		
		return $this->versionPaths;
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
	
	// Analyze the return code from the "git pull" command
    function checkGitPull($Model, $git_response){
        if($git_response[0]>0){
	        $this->logError($Model, $git_response[1], __function__);     
        }
    }    		
 	
	// return git remote
	function getGitRemote($Model, $project_alias){
		return 'git@github.com:konscript/'.$project_alias;
	}			

	function getValidHostname($Model, $hostnames, $i=0){
		$ip = gethostbyname($hostnames[$i]);
		if ( preg_match('/^\d+/', $ip) != 0 ) {
			$address = $hostnames[$i];
			return $address;
		}elseif((count($hostnames)-1)>$i){
			$i++;
			return getValidHostname($hostnames, $i);
		}else{
			return false;
		}
	}

	// update screenshot
	function update_screenshot($Model, $hostnames, $project_alias){
		
		$hostname = getValidHostname($hostnames);
	
		if($hostname){
			echo $project_alias;	
			set_time_limit(360);
			$command = $this->service_root."wkhtmltoimage --height 1024 $hostname ".$this->service_root."img/screenshots/".$project_alias.".jpg";
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
		
		$command = $this->service_root."bash/download_wordpress.sh ".$project_alias;
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
			$command = $this->service_root."bash/download_project.sh $project_alias $path $dbname";
			exec("$command  2>&1", $output, $return_code);	
			$pathToTar = "/temp/".$project_alias.".tar";

			if(in_array("success", $output) && $return_code == 0 && is_file($this->service_root.$pathToTar)){	
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
	 * recursive remove directory
	 */
	function rrmdir($dir) { 

	   if (is_dir($dir) && !empty($dir)) { 
		 $objects = scandir($dir); 
		 foreach ($objects as $object) { 
		   if ($object != "." && $object != "..") { 
		     if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
		   } 
		 } 
		 reset($objects); 
		 rmdir($dir); 
	   } 
	 } 
	 

	/** 
	 * get a list of folders in a specific path
	 */
	function get_list_of_folders($Model, $dir){

		$folders = array();    
		if (is_dir($dir)) {    		
				
			// append trailing slash if omitted
			$lastLetter = substr($dir, -1);	    		    		    	
			$dir .= $lastLetter != "/" ? "/" : "";
			
		    $dh = opendir($dir);
		    while (($file = readdir($dh)) !== false) {
		        if(is_dir($dir . $file) == true && !is_link($dir . $file) && $file!=".." && $file!="."){

		            $folders[$file] = $file;  
		        }              
		    }
		    closedir($dh);
		}    

		
		arsort($folders, SORT_NUMERIC);  
		return $folders;
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

   /**
    * Produce content for the new virtual host for Nginx and check that all changes are reflected in the physical files
    *********/
	function get_vhost($Model, $filename, $fields){
		// load file into array
		$content = file($filename);		

		// remove unchanged
		foreach($fields as $field_id=>$field){
			if($field[0] == $field[1]){
				unset($fields[$field_id]);
			}
		}

		// read file per line
		foreach ($content as $line_num => $line) {	
			foreach($fields as $field_id=>$field){
	
				// compare lines (ignore whitespace). If they are identical, the line will be replace by the new 
				if((strcmp($this->ignoreWhiteSpace($Model, $line), $this->ignoreWhiteSpace($Model, $field[0])) == 0)){	
					$content[$line_num] = $field[1]."\n";
					$fields[$field_id][2] = true;
				}				
			}			
		}		

		// make sure that all changed field were updated
		$errors = array();
		foreach($fields as $field_id=>$field){
			if(!isset($field[2])){
				$errors[] = $field[0];
			}
		}

		if(count($errors)>0){
	        $this->logError($Model, "Following string missing in vhost: ".implode("<br>", $errors), __function__);
        }

		// array to string
		$content = implode("", $content);
		return $content;
	}   			
	
	// strip whitespace
	function ignoreWhiteSpace($Model, $str){
		return str_replace(array("\n", "\r", "\t", " "), '', $str);
	}	
	
	// write values to vhost
	function writeToFile($Model, $filename, $content){
		$res = false;
		$res = file_put_contents($filename, $content);	
		
		if($res === false){
	        $this->logError($Model, "$filename could not be updated", __function__);
		}
	}		

	// update nginx additional domains
	function vhost_nginx_additional($Model, $primary_domain, $additional_domains){
		if(empty($additional_domains)){
			return '	server_name  	*.'.$primary_domain.';';
		}else{
			return '	server_name  	*.'.$primary_domain.' '.$additional_domains.';';
		}
	}

	// update nginx primary domain
	function vhost_nginx_rewrite($Model, $primary_domain){
		return '	rewrite   ^ 	http://'.$primary_domain.'$request_uri?;';
	}

	// update nginx primary domain
	function vhost_nginx_primary($Model, $primary_domain){
		return '	server_name 		'.$primary_domain.';';
	}

	// update nginx development domain
	function vhost_nginx_dev($Model, $dev_domain){
		return '	server_name		 '.$dev_domain.';';
	}

	// update nginx cache setting
	function vhost_nginx_cache($Model, $project_alias, $use_cache){
		if($use_cache == 1){
			return '	fastcgi_cache     	'.$project_alias.';';
		}else{
			return '	#fastcgi_cache     	'.$project_alias.';';
		}
	}

	// create nginx vhost
	function vhost_nginx($Model, $project_alias, $primary_domain, $dev_domain, $additional_domains){
	$use_cache = 0;

	return '# cache path
	fastcgi_cache_path                /var/cache/nginx/cached/'.$project_alias.' levels=2:2 keys_zone='.$project_alias.':64m inactive=60m max_size=200m;

	# redirect additional domains
	server {
	'.$this->vhost_nginx_additional($Model, $primary_domain, $additional_domains).'
	'.$this->vhost_nginx_rewrite($Model, $primary_domain).'	
	}

	# primary domain
	server {

		##############
		# variables
		##############	
	'.$this->vhost_nginx_primary($Model, $primary_domain).'
		root				/srv/www/'.$project_alias.'/prod/current;
	'.$this->vhost_nginx_cache($Model, $project_alias, $use_cache).'	

		##############
		# Purge cache
		##############
		location ~ /purge(/.*) {
			fastcgi_cache_purge '.$project_alias.' $scheme$host$1$is_args$args;
		}

		##############
		# Logging Settings
		##############
		# access_log /var/log/nginx/'.$project_alias.'/access.log;
		# error_log /var/log/nginx/'.$project_alias.'/error.log;

		##############
		# includes
		##############
		include includes/locations.conf;
		include includes/wordpress.conf;
	}

	# dev
	server { 
	'.$this->vhost_nginx_dev($Model, $dev_domain).'	
		root		/srv/www/'.$project_alias.'/dev;

		include includes/locations_dev.conf;
		include includes/wordpress.conf;
	}';

	}		
	// create nginx vhost end	

}
