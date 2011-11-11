<?php
App::uses('AppController', 'Controller');
App::import('Vendor', 'Prosty');
/**
 * Projects Controller
 *
 * @property Project $Project
 */
class ProjectsController extends AppController {


/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->Project->recursive = 0;
		$this->set('projects', $this->paginate());
	}

/**
 * view method
 *
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		$this->Project->id = $id;
		if (!$this->Project->exists()) {
			throw new NotFoundException(__('Invalid project'));
		}
		$this->set('project', $this->Project->read(null, $id));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Project->create();
			
			// set default dev_domain
			$this->request->data["Project"]["dev_domain"] = $this->request->data["Project"]["project_alias"] . '.konscript.com';			
			$this->request->data["Project"]["additional_domains"] = "";						
											
			
			if ($this->Project->save($this->request->data)) {
				$this->Session->setFlash(__('The project has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The project could not be saved. Please, try again.'));
			}
		}
	}

/**
 * edit method
 *
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		$this->Project->id = $id;
		if (!$this->Project->exists()) {
			throw new NotFoundException(__('Invalid project'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Project->save($this->request->data)) {
				$this->Session->setFlash(__('The project has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The project could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->Project->read(null, $id);					
		}
		
		// get available versions
		$Prosty = new Prosty();	
		
		// get project alias from db	    
		$projects = $this->Project->find('first', array(
		    'conditions' => array('id' => $id), //array of conditions
			'fields' => array('project_alias'),
			'recursive' => -1
		));
	    $project_alias = $projects["Project"]["project_alias"];		
							
		$available_versions = $Prosty->get_list_of_folders($Prosty->web_root.$project_alias."/prod");
		//debug($available_versions);
		$this->set(compact('available_versions'));		
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
		$this->Project->id = $id;
		if (!$this->Project->exists()) {
			throw new NotFoundException(__('Invalid project'));
		}
		if ($this->Project->delete()) {
			$this->Session->setFlash(__('Project deleted'));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Project was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}
