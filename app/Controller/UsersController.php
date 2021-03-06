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
  	'edit' => '*',
  );

	// allow action login for everybody
	function beforeFilter(){
		parent::beforeFilter(); 
		$this->Auth->allow('login');
		$this->Auth->allow('logout');		
		$this->Auth->allow('resetPassword');
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
	
		// user can only edit own user
		if($this->Auth->user('id') != $id){
			$this->Session->setFlash(__('You are only allowed to edit you own profile'));		
			$this->redirect(array('action' => 'index'));
		}

		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {		
			if ($this->User->saveAssociated($this->request->data)) {
				$this->Session->setFlash(__('The user has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->User->read(null, $id);
		}

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
		
			$username = $this->request->data['User']['username'];
			
			// Get id and role_id for user 
			$userData = $this->User->find('first', array(
				'conditions' => array('User.username' => $username),
				'fields' => array('User.id', 'User.role_id')
			));
			
			// username not found
			if(empty($userData)){
				$this->Session->setFlash('The username does not exist.');
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
			
				$headers = 'From: prosty@localhost' . "\r\n" .
				'Reply-To: sl@konscript.com' . "\r\n" .
				'X-Mailer: PHP/' . phpversion();	
				
				// mail was successfully send			
				if(mail($userData['UserEmail'][0]['email'], 'New password for Prosty', 'Hi '.$username.',\n Your new password is: ' . $password[1], $headers)) {
					$this->Session->setFlash('A new password was sent to your mail.');
				}else{
						$this->Session->setFlash('Email was not sent.');
				}
				
			}else{				
				$this->Session->setFlash('Der skete en fejl. Prøv venligst igen.');
			}			

		}
	}	
}
