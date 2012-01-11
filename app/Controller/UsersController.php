<?php
App::uses('AppController', 'Controller');
/**
 * Users Controller
 *
 * @property User $User
 */
class UsersController extends AppController {

	var $components = array('ResetPassword');		

// action specific permissions
    public $permissions = array(
    	'delete' => array('editor'),
    );     

	// allow action login for everybody
	function beforeFilter(){
		parent::beforeFilter(); 
		$this->Auth->allow('login');
		$this->Auth->allow('resetPassword');		
		$this->Auth->allow('logout');		
	}

	// basic auth
	function login() {
		$this->layout = 'login';
		
		if ($this->request->is('post')) {
		    if ($this->Auth->login()) {
		        $this->redirect($this->Auth->redirect());
		    } else {
		        $this->Session->setFlash('Your username or password was incorrect.');
		    }
		}
	}

	function logout() {
		$this->redirect($this->Auth->logout());
	}


/**
 * index method
 *
 * @return void
 */
	public function index() {

		$this->User->recursive = 0;
		$this->set('users', $this->paginate());
	}

/**
 * view method
 *
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		$this->set('user', $this->User->read(null, $id));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->User->create();
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('The user has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
			}
		}
		$roles = $this->User->Role->find('list');
		$this->set(compact('roles'));
	}

/**
 * edit method
 *
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('The user has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->User->read(null, $id);
		}
		$roles = $this->User->Role->find('list');
		$this->set(compact('roles'));
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
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->User->delete()) {
			$this->Session->setFlash(__('User deleted'));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('User was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
	
/**
 * resetPassword method
 *
 * Generates a new password and sends it to the user
 * 
 * @return void
 */	

	function resetPassword() {
			
		// Only do something if data is passed in the form
		if(!empty($this->request->data)) {
			
			// Get id and role_id for user 
			$userData = $this->User->find('first', array(
				'conditions' => array('User.username' => $this->request->data['User']['username']),
				'recursive' => 0,
				'fields' => array('User.id', 'User.role_id')
			));
			
			// username not found
			if(empty($userData)){
				$this->Session->setFlash('Den angivne email eksisterer ikke i systemet.');
				return;
			}
						
			// Generate new password (hash and cleartext)
			$password = $this->ResetPassword->generateRandomPassword();
			
			// Set user id to update record
			$this->User->id = $userData['User']['id'];			
			
			// Set password
			$this->request->data['User']['password'] = $password[1];							
			
			// save new password
			if($this->User->save($this->request->data)) {			
				
				// mail was successfully send
				
				if(mail($this->data['User']['username'], 'New password for Prosty', 'Your new password is: ' . $password[1])) {
					$this->Session->setFlash('Dit nye kodeord blev sendt til din mail!');
					debug($password);
				}else{
						$this->Session->setFlash('Email was not sent.');
				}
				
			}else{				
				$this->Session->setFlash('Der skete en fejl. Prøv venligst igen.');
			}			

		}
	}	
}
