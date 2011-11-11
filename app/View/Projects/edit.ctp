<div class="projects form">
<?php echo $this->Form->create('Project');?>
	<fieldset>
		<legend><?php echo __('Edit Project'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('title');
		echo $this->Form->input('primary_domain');
		echo $this->Form->input('additional_domains');
		echo $this->Form->input('dev_domain');
		echo $this->Form->input('use_cache');
		echo $this->Form->input('current_version', array('options' => $available_versions));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Project.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Project.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Projects'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Commits'), array('controller' => 'commits', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Commit'), array('controller' => 'commits', 'action' => 'add')); ?> </li>
	</ul>
</div>
