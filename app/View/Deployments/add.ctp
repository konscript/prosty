<div class="deployments form">
<?php echo $this->Form->create('Deployment');?>
	<fieldset>
		<legend><?php echo __('Add Deployment'); ?></legend>
	<?php
		echo $this->Form->input('project_id');
		echo $this->Form->input('create_next_version', array('label' => 'Create new version', 'type'=>'checkbox'));		
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Deployments'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Projects'), array('controller' => 'projects', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Project'), array('controller' => 'projects', 'action' => 'add')); ?> </li>
	</ul>
</div>
