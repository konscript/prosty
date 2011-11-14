<div class="commits form">
<?php echo $this->Form->create('Commit');?>
	<fieldset>
		<legend><?php echo __('Add Commit'); ?></legend>
	<?php
		echo $this->Form->input('project_id');
		echo $this->Form->input('hash');
		echo $this->Form->input('last_commit_msg');
		echo $this->Form->input('number_of_commits');
		echo $this->Form->input('ip_addr');
		echo $this->Form->input('created_by');
		echo $this->Form->input('modified_by');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Commits'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Projects'), array('controller' => 'projects', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Project'), array('controller' => 'projects', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Deployments'), array('controller' => 'deployments', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Deployment'), array('controller' => 'deployments', 'action' => 'add')); ?> </li>
	</ul>
</div>
