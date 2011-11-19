<?php
App::uses('AppController', 'Controller');
/**
 * DeploymentErrors Controller
 *
 * @property DeploymentError $DeploymentError
 */
class DeploymentErrorsController extends AppController {


/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->DeploymentError->recursive = 0;
		$this->set('deploymentErrors', $this->paginate());
	}

/**
 * view method
 *
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		$this->DeploymentError->id = $id;
		if (!$this->DeploymentError->exists()) {
			throw new NotFoundException(__('Invalid deployment error'));
		}
		$this->set('deploymentError', $this->DeploymentError->read(null, $id));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->DeploymentError->create();
			if ($this->DeploymentError->save($this->request->data)) {
				$this->Session->setFlash(__('The deployment error has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The deployment error could not be saved. Please, try again.'));
			}
		}
		$deployments = $this->DeploymentError->Deployment->find('list');
		$this->set(compact('deployments'));
	}

/**
 * edit method
 *
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		$this->DeploymentError->id = $id;
		if (!$this->DeploymentError->exists()) {
			throw new NotFoundException(__('Invalid deployment error'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->DeploymentError->save($this->request->data)) {
				$this->Session->setFlash(__('The deployment error has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The deployment error could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->DeploymentError->read(null, $id);
		}
		$deployments = $this->DeploymentError->Deployment->find('list');
		$this->set(compact('deployments'));
	}

/**
 * delete method
 *
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->DeploymentError->id = $id;
		if (!$this->DeploymentError->exists()) {
			throw new NotFoundException(__('Invalid deployment error'));
		}
		if ($this->DeploymentError->delete()) {
			$this->Session->setFlash(__('Deployment error deleted'));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Deployment error was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}
