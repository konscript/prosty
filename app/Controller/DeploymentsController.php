<?php
App::uses('AppController', 'Controller');

class DeploymentsController extends AppController {
   public $helpers = array('Time', 'Util');

	/***************************		 
	* index method
	***************************/		 
	public function index() {
		$this->Deployment->recursive = 0;
		$this->Deployment->order = array('Deployment.id' => 'desc');				
		$this->set('deployments', $this->paginate());
	}

	/***************************		 
	* view method
	***************************/		 
	public function view($id = null) {
		$this->Deployment->id = $id;
		if (!$this->Deployment->exists()) {
			throw new NotFoundException(__('Invalid deployment'));
		}
		
		// fetch deployment and associated errors
		$deployment = $this->Deployment->read(null, $id);
		$newest_deployment = $this->Deployment->find('first', array(
			'conditions' => array('Project.project_alias' => $deployment["Project"]["project_alias"]),
			'order' => array('Deployment.id DESC')
		));
		
		// only handle git for current deployment version - don't allow it for older deployments
		if($newest_deployment["Deployment"]["id"] == $deployment["Deployment"]["id"]){
		
			// prepare git handling
			foreach ($deployment['DeploymentError'] as $deploymentError) {
	
				// unstaged files detected
				if($deploymentError["calling_function"] == "logUnstagedFiles"){
	
					// untracked files (new files not added to index)
					if(strpos($deploymentError["request"], "ls-files") ){
						$unstagedFiles["new"] = $deploymentError["response"];
					}
		
					// uncommited files (changed files not commited)
					if(strpos($deploymentError["request"], "diff-index") ){
						$unstagedFiles["changed"] = $deploymentError["response"];					
					}		
				}			

				// merge conflict detected
				if($deploymentError["calling_function"] == "logConflictingFiles" &&
					 strpos($deploymentError["request"], "pull") && 
					 is_array($deploymentError["response"])
				 ){
					$conflictingFiles = $deploymentError["response"];
				}						
			}		
		}
		$this->set(compact('deployment', 'conflictingFiles','unstagedFiles'));
	}

}
