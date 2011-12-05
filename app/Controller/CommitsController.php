<?php
App::uses('AppController', 'Controller');
/**
 * Commits Controller
 *
 * @property Commit $Commit
 */
class CommitsController extends AppController {


	// public access to add commit (GitHub needs access)
	function beforeFilter(){
		parent::beforeFilter(); 
		$this->Auth->allow('add');
	}	

	public function index() {		
		$this->Commit->recursive = 0;
		$this->Commit->order = array('Commit.id' => 'desc');		
		$this->set('commits', $this->paginate());
	}


/**
 * view method
 *
 * @param string $id
 * @return void
 */
var $scaffold;

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
	    $this->Commit->Behaviors->detach('WhoDidIt');
	    
		$this->Commit->create();		
		if ($this->Commit->save($this->request->data, array('validate' => false))) {
			echo "success";
		} else {
			echo "error";
		}
		exit();
		//$this->autoRender = false;	
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
