<?php
App::uses('AppController', 'Controller');
/**
 * Commits Controller
 *
 * @property Commit $Commit
 */
class CommitsController extends AppController {


/**
 * index method
 *
 * @return void
 */
 var $scaffold;
 /*
	public function index() {
		$this->Commit->recursive = 0;
		$this->set('commits', $this->paginate());
	}
*/
/**
 * view method
 *
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		$this->Commit->id = $id;
		if (!$this->Commit->exists()) {
			throw new NotFoundException(__('Invalid commit'));
		}
		$this->set('commit', $this->Commit->read(null, $id));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
			
		$this->Commit->create();
		if ($this->Commit->save($this->request->data)) {
			$this->Session->setFlash(__('The commit has been saved'));
			$this->redirect(array('action' => 'index'));
		} else {
			$this->Session->setFlash(__('The commit could not be saved. Please, try again.'));
			debug($this->Commit->invalidFields()); 
		}
	
	}

/**
 * edit method
 *
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		$this->Commit->id = $id;
		if (!$this->Commit->exists()) {
			throw new NotFoundException(__('Invalid commit'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Commit->save($this->request->data)) {
				$this->Session->setFlash(__('The commit has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The commit could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->Commit->read(null, $id);
		}
		$projects = $this->Commit->Project->find('list');
		$this->set(compact('projects'));
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
		$this->Commit->id = $id;
		if (!$this->Commit->exists()) {
			throw new NotFoundException(__('Invalid commit'));
		}
		if ($this->Commit->delete()) {
			$this->Session->setFlash(__('Commit deleted'));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Commit was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}
