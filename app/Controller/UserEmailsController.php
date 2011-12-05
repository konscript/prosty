<?php
App::uses('AppController', 'Controller');
/**
 * UserEmails Controller
 *
 * @property UserEmail $UserEmail
 */
class UserEmailsController extends AppController {


/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->UserEmail->recursive = 0;
		$this->set('userEmails', $this->paginate());
	}

/**
 * view method
 *
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		$this->UserEmail->id = $id;
		if (!$this->UserEmail->exists()) {
			throw new NotFoundException(__('Invalid user email'));
		}
		$this->set('userEmail', $this->UserEmail->read(null, $id));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->UserEmail->create();
			if ($this->UserEmail->save($this->request->data)) {
				$this->Session->setFlash(__('The user email has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user email could not be saved. Please, try again.'));
			}
		}
		$users = $this->UserEmail->User->find('list');
		$this->set(compact('users'));
	}

/**
 * edit method
 *
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		$this->UserEmail->id = $id;
		if (!$this->UserEmail->exists()) {
			throw new NotFoundException(__('Invalid user email'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->UserEmail->save($this->request->data)) {
				$this->Session->setFlash(__('The user email has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user email could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->UserEmail->read(null, $id);
		}
		$users = $this->UserEmail->User->find('list');
		$this->set(compact('users'));
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
		$this->UserEmail->id = $id;
		if (!$this->UserEmail->exists()) {
			throw new NotFoundException(__('Invalid user email'));
		}
		if ($this->UserEmail->delete()) {
			$this->Session->setFlash(__('User email deleted'));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('User email was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}
