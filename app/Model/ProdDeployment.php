<?php
App::uses('AppModel', 'Model');
App::import('Vendor', 'Git');

class ProdDeployment extends AppModel {

  public $useTable = 'deployments';
	public $actsAs = array('Prosty');
	
	public $validate = array(
		'project_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Project id must be numeric',
			)				
		)
	);
		
	/*******************
	* beforeSave: deploy project, clone folders and run git
	*******************/			
	function beforeSave(){
	
		$validates = $this->validates();

		// set project id	
		$project_id = $this->data["ProdDeployment"]["project_id"];
		
		// get deployments
		$Deployment = $this->Project->Deployment->find('first', array(
			'conditions' => array('project_id' => $project_id, 'server' => 'Caesar'),
			'fields' => array('hash', 'last_commit_msg'),
			'order' => array('id DESC'),
			'recursive' => -1
		));			
				
		// Set server			
		$this->data["ProdDeployment"]["hash"] = $Deployment["Deployment"]["hash"];
		$this->data["ProdDeployment"]["last_commit_msg"] = $Deployment["Deployment"]["last_commit_msg"];
		$this->data["ProdDeployment"]["server"] = "Brutus";
		
		// remove empty values
		$this->data["ProdDeployment"] = array_filter($this->data["ProdDeployment"], 'strlen');
		
		// validation failed: remove invalid fields from array		
		$this->logCakeValidationErrors($validates);			

		// all validations passed			
		if( $validates ){		
								
			// curl to force git pull			
			$this->curl_wrapper(array(
				"url" => "http://deployment.konscript.com",
				"data" => array(
					"project_alias" => $this->getProjectAlias($project_id)
				)
			));

			// get production URL
			$Project = $this->Project->find('first', array(
				'conditions' => array('Project.id' => $project_id),
				'fields' => 'prod_url',
				'recursive' => -1
			));						
						
			// Clear cache on Brutus			
			// TODO: Install Varnish on Brutus and enable this again!
			/*
			$this->curl_wrapper(array(
				"url" => $Project["Project"]["prod_url"],
				"request_method" => "BAN"
			));
			*/			
		}
									
		// Set error status
		$this->data["ProdDeployment"]["status"] = count( $this->getErrors() ) === 0 ? true : false;
						
		return true;
	}
	
	/*******************
	* afterSave: log errors
	*******************/	
	function afterSave(){
	
		// Deployment successful: Add to NewRelic
		if(count($this->getErrors()) === 0){		
			$project_id = $this->data["ProdDeployment"]["project_id"];
			$this->newrelic_hook($project_id);
		}
		
		// save errors to log
		$this->saveErrorLogs();


	}
	
/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Project' => array(
			'className' => 'Project',
			'foreignKey' => 'project_id',
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'created_by',
		),
		'ModifiedBy' => array(
			'className' => 'User',
			'foreignKey' => 'modified_by',
		)	
	);
	
	public $hasMany = array(
		'DeploymentError' => array(
			'className' => 'DeploymentError',
			'foreignKey' => 'deployment_id',
			'dependent' => false,
		)		
	);			
	
	/***************************
	* add deployment to NewRelic		
	***************************/			
	function newrelic_hook($project_id){
	
		// get values
		$project_alias = $this->getProjectAlias($project_id);		
		$projects = $this->Project->Deployment->find('first', array(
			'conditions' => array('Deployment.project_id' => $project_id)
		));

		// make curl request
		$this->curl_wrapper(array(
			"url" => "https://rpm.newrelic.com/deployments.xml",
			"headers" => array('x-api-key: '. Configure::read('NewRelic.rest')),
			"data" => array(
				'app_name' => $project_alias,
				'user' => $_SESSION["Auth"]["User"]["username"],
				'description' => $projects["Deployment"]["last_commit_msg"]
			)
		));			
	}	 		
}
