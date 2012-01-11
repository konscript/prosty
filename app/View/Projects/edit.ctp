<div class="projects form">
<?php echo $this->Form->create('Project');?>
	<fieldset>
		<legend><?php echo __('Edit Project'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('title');
		echo $this->Form->input('prod_url');		
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
