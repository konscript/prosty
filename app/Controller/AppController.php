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
      $this->Auth->loginRedirect = array('controller' => 'deployments', 'action' => 'index');
  }
  
	function isAuthorized(){ 
     
		// allow executives access to everything
		if($this->Auth->user('role_id') == '1'){
			return true; 
		}			 
		
		// roles
		$roles = array(
			1 => 'executive',
			2 => 'agent'
		);			
				
		// default permissions - everyone has access to view, index and add
		$permissions_default = array(
			'view' => '*',
			'index' => '*',
			'add' => '*'
		);     
				
		// override default permissions
		$permissions = array_merge($permissions_default, $this->permissions);		
		
		// get current role_alias eg. agent
		$role_alias = $roles[$this->Auth->user('role_id')];
		
		// convert string to array

		
		if(isset($permissions[$this->action])){
			$current_permission = $permissions[$this->action];
			$allowedRoles = is_string($current_permission) ? array($current_permission) : $current_permission;
		}else{
			$allowedRoles = array();
		}
		
		// give access, if role_alias has permissions to th current action. eg. agent (role_alias) can delete (action) users		
		if(	in_array($role_alias, $allowedRoles) || in_array('*', $allowedRoles) ){
			return true; 
		} 
			
		// access denied
		return false;
	}
}
