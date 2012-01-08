<?php

App::uses('AppController', 'Controller');
/**
 * DevDeployments Controller
 *
 * @property DevDeployment $DevDeployment
 */
class DevDeploymentsController extends AppController {

	// public access to add commit (GitHub needs access)
	function beforeFilter(){
		parent::beforeFilter(); 
		$this->Auth->allow('add');		
	}
	
	var $permissions = array(
		'resolveUnstagedFilesInit' => '*',
		'resolveConflictingFilesInit' => '*'		
	);
	
	// resolve repo by merging files according to --ours or --theirs
	function resolveConflictingFilesInit($project_alias){
		$project_path = $this->DevDeployment->getProjectPath($project_alias);	
		$this->DevDeployment->resolveConflictingFiles($project_path);
		$this->autoRender = false;
	}
	
	// resolve repo by adding/commit or gitingore files
	function resolveUnstagedFilesInit($project_alias){
		$project_path = $this->DevDeployment->getProjectPath($project_alias);
		
		// open repo
		$repo = $this->DevDeployment->openRepo($project_path);
		
		// add/commit/gitignore unstaged files
		if($repo){
			$this->DevDeployment->resolveUnstagedFiles($repo, $project_path);
		}
		
		// get email of current user
		$user = $this->DevDeployment->User->findById($this->Auth->user('id'));
		$email = $user["UserEmail"][0]['email'];
	
		// redeploy: imitate GitHub deploy hook
		if($this->DevDeployment->getErrorCount() == 0 ){		
			// get current commit hash
			$commit_hash = $repo->run('rev-parse HEAD');
		
			$payload = new stdClass;
			$payload->after = $commit_hash;
			$payload->repository->name = $project_alias;		
			$payload->repository->url = "https://github.com/konscript/".$project_alias."/commit/".$commit_hash;
			$payload->pusher->email = $email;		
			$payload->commits[0]->message = 'Redeployment';
			$payload->ref = 'refs/heads/master';

			$this->request->data["DevDeployment"]["payload"] = $payload;

			// User not logged in: identify with email address
			$this->DevDeployment->Behaviors->detach('WhoDidIt');		

			$this->DevDeployment->create();		
			
			// save deployment
			$save_deployment = $this->DevDeployment->save($this->request->data, array('validate' => false));
		}
		
		// errors occured
		if($this->DevDeployment->getErrorCount() > 0){
			debug($this->DevDeployment->getErrors());
			
		// success: push merged content		
		}else{
			$this->DevDeployment->executeAndLogGit($repo, 'push konscript master');
			echo "success";
		}
		$this->autoRender = false;
	}
	
	/**
	 * add method
	 *
	 * @return void
	 */
	public function add() {
		
		// localhost testing: use dummy payload
		if($_SERVER["REMOTE_ADDR"] == "127.0.0.1" && !isset($_REQUEST['payload'])){
			$_REQUEST['payload'] = file_get_contents(APP."Vendor/payload");
		}

		// receive the json payload string
		if(isset($_REQUEST['payload'])){
			$this->request->data["payload"] = json_decode($_REQUEST['payload']);
		}else{
			$this->Session->setFlash(__('No payload was received'));
			$this->redirect(array('action' => 'index'));
		}							
														
		// User not logged in: identify with email address
	  $this->DevDeployment->Behaviors->detach('WhoDidIt');
	    
		$this->DevDeployment->create();		
		if ($this->DevDeployment->save($this->request->data, array('validate' => false))) {
			echo "success";
		} else {
			echo "error";
		}
		exit();
		//$this->autoRender = false;	
	}

}
