<?php

App::uses('AppController', 'Controller');
class DevDeploymentsController extends AppController {

	/***************************		 
	* Give public access to add commit (GitHub needs access)
	***************************/		 
	function beforeFilter(){
		parent::beforeFilter(); 
		$this->Auth->allow('add');		
	}
	
	/***************************		 
	 * add method
	***************************/		 
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

/*
			$payload = new stdClass;
			$payload->after = $commit_hash;
			$payload->repository->name = $project_alias;		
			$payload->repository->url = "https://github.com/konscript/".$project_alias."/commit/".$commit_hash;
			$payload->pusher->email = $email;		
			$payload->commits[0]->message = 'Redeployment';
			$payload->ref = 'refs/heads/master';

			$this->request->data["ResolveDeployment"]["payload"] = $payload;
*/			
