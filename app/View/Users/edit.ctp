<div class="users form">
<?php echo $this->Form->create('User');?>
	<fieldset>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('username');
		echo $this->Form->input('password', array('value'=>""));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
