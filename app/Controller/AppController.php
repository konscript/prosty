<?php
class AppController extends Controller {
  public $components = array('Auth', 'RequestHandler', 'Session', 'AutoLogin', 'Cookie');
  public $permissions = array();     
  public $helpers = array('Html', 'Form', 'Session');

  function beforeFilter() {    

  
  	// init isAuthorize() - action specific check
	$this->Auth->authorize = array('Controller');
  
      //Configure AuthComponent
      $this->Auth->loginAction = array('controller' => 'users', 'action' => 'login');
      $this->Auth->logoutRedirect = array('controller' => 'users', 'action' => 'login');
      $this->Auth->loginRedirect = array('controller' => 'users', 'action' => 'index');
  }
  
	function isAuthorized(){ 
     
		// allow executives access to everything
		if($this->Auth->user('role_id') == '1'){
			return true; 
		}			 
	
		// default permissions
		$permissions_default = array(
			'view' => '*',
			'index' => '*',
			'add' => '*'
		);     
		
		// override default perms
		$permissions = array_merge($permissions_default, $this->permissions);
		
		// check permission for action 
		if(!empty($permissions[$this->action])){
        
			// roles
			$roles = array(
				1 => 'executive',
				2 => 'agent'
			);
			$role_alias = $roles[$this->Auth->user('role_id')];

			// access for specific role
			if(in_array($role_alias, $permissions[$this->action])) return true; 
		} 
		return false; 
         
	}
}
