<?php
App::uses('AppController', 'Controller');
/**
 * Deployments Controller
 *
 * @property Deployment $Deployment
 */
class DeploymentsController extends AppController {


/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->Deployment->recursive = 0;
		$this->set('deployments', $this->paginate());
	}

/**
 * view method
 *
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		$this->Deployment->id = $id;
		if (!$this->Deployment->exists()) {
			throw new NotFoundException(__('Invalid deployment'));
		}
		$this->set('deployment', $this->Deployment->read(null, $id));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Deployment->create();
			if ($this->Deployment->save($this->request->data)) {
				$this->Session->setFlash(__('The deployment has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The deployment could not be saved. Please, try again.'));
			}
		}
		$commits = $this->Deployment->Commit->find('list');
		$this->set(compact('commits'));
	}

/**
 * edit method
 *
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		$this->Deployment->id = $id;
		if (!$this->Deployment->exists()) {
			throw new NotFoundException(__('Invalid deployment'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Deployment->save($this->request->data)) {
				$this->Session->setFlash(__('The deployment has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The deployment could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->Deployment->read(null, $id);
		}
		$commits = $this->Deployment->Commit->find('list');
		$this->set(compact('commits'));
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
		$this->Deployment->id = $id;
		if (!$this->Deployment->exists()) {
			throw new NotFoundException(__('Invalid deployment'));
		}
		if ($this->Deployment->delete()) {
			$this->Session->setFlash(__('Deployment deleted'));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Deployment was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}
