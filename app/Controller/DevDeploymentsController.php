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

	/**
	 * add method
	 *
	 * @return void
	 */
	public function add() {
		
		// localhost testing: use dummy payload
		if($_SERVER["REMOTE_ADDR"] == "127.0.0.1"){
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
