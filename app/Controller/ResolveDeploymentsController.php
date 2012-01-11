<?php
App::uses('AppController', 'Controller');

class ResolveDeploymentsController extends AppController {

	
	/***************************		 
	* Clean dirty working dir: add/commit files to index or gitingore
	***************************/		 
	function add($project_id, $type){

		// save deployment (error status)
		$this->request->data["ResolveDeployment"]["project_id"] = $project_id;
		$this->request->data["ResolveDeployment"]["type"] = $type;
		$this->ResolveDeployment->create();		
		$save_deployment = $this->ResolveDeployment->save($this->request->data, array('validate' => false));	
		exit();
		$this->autoRender = false;
	}

}
