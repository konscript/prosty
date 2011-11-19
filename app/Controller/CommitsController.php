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
			$payload = json_decode($_REQUEST['payload']);
		}else{
			$this->Session->setFlash(__('No payload was received'));
			$this->redirect(array('action' => 'index'));
		}					
				
		$number_of_commits = count($payload->commits);

		// get project_id via project_alias
		$projects = $this->Commit->Project->find('first', array(
			'conditions' => array('project_alias' => $payload->repository->name),
			'recursive' => -1,
			'fields' => array('id')
		));		
		
		// get user_id via user's email
		$user = $this->Commit->CreatedBy->UserEmail->find('first', array(
			'conditions' => array('email' => $payload->commits[$number_of_commits-1]->author->email),
			'recursive' => -1,
			'fields' => array('user_id')
		));		

		// data for DB
		$this->request->data["Commit"]["project_id"] = $projects["Project"]["id"];						
		$this->request->data["Commit"]["hash"] = $payload->after;			
		$this->request->data["Commit"]["last_commit_msg"] = $payload->commits[$number_of_commits-1]->message;			
		$this->request->data["Commit"]["number_of_commits"] = $number_of_commits;			
		$this->request->data["Commit"]["ip_addr"] = $_SERVER["REMOTE_ADDR"];		
		$this->request->data["Commit"]["created_by"] = $user["UserEmail"]["user_id"];			
		$this->request->data["Commit"]["modified_by"] = $user["UserEmail"]["user_id"];	
		
		// data for validation
		$this->request->data["Commit"]["branch"] = $payload->ref;
		$this->request->data["Commit"]["account"] = $payload->repository->url;
		$this->request->data["Commit"]["project_alias"] = $payload->repository->name;						
			
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
