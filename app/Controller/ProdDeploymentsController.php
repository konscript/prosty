<?php
App::uses('AppController', 'Controller');
/**
 * ProdDeployments Controller
 *
 * @property ProdDeployment $ProdDeployment
 */
class ProdDeploymentsController extends AppController {

/**
 * add method
 *
 * @return void
 */
	public function add($project_id = null) {
	
		// set data
		$this->request->data["ProdDeployment"]["project_id"] = $project_id;
	
		$this->ProdDeployment->create();
		if ($this->ProdDeployment->save($this->request->data, array('validate' => false))) {
			$this->Session->setFlash(__('The deployment has been saved'));
			$this->redirect(array('controller'=>'deployments', 'action' => 'index'));
		} else {
			$this->Session->setFlash(__('The deployment could not be saved. Please, try again.'));
		}
	}

}
