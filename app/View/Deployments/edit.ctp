<div class="deployments form">
<?php echo $this->Form->create('Deployment');?>
	<fieldset>
		<legend><?php echo __('Edit Deployment'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('project_id');
		echo $this->Form->input('status');
		echo $this->Form->input('next_version');
		echo $this->Form->input('created_by');
		echo $this->Form->input('modified_by');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Deployment.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Deployment.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Deployments'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Projects'), array('controller' => 'projects', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Project'), array('controller' => 'projects', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Deployment Errors'), array('controller' => 'deployment_errors', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Deployment Error'), array('controller' => 'deployment_errors', 'action' => 'add')); ?> </li>
	</ul>
</div>
