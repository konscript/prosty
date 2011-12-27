<?php
App::uses('AppController', 'Controller');

class DeploymentsController extends AppController {
   public $helpers = array('Time', 'Util');

	public function index() {
		$this->Deployment->recursive = 0;
		$this->Deployment->order = array('Deployment.id' => 'desc');				
		$this->set('deployments', $this->paginate());
	}

	public function view($id = null) {
		$this->Deployment->id = $id;
		if (!$this->Deployment->exists()) {
			throw new NotFoundException(__('Invalid deployment'));
		}
		$this->set('deployment', $this->Deployment->read(null, $id));
	}

}
