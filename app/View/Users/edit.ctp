<div class="users form">
<?php echo $this->Form->create('User');?>
	<fieldset>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('username');
		echo $this->Form->input('password', array('value'=>""));
		echo $this->Form->input('UserEmail.0.id');		
		echo $this->Form->input('UserEmail.0.email', array('class' => 'email'));		
		
		$numberOfUserEmail = count($this->request->data["UserEmail"]);
		for ( $counter = 1; $counter < $numberOfUserEmail; $counter += 1) {
			echo $this->Form->input('UserEmail.' . $counter . '.id');
			echo $this->Form->input('UserEmail.' . $counter . '.email', array('class' => 'email', 'after' => '<a href="#" class="removeEmail">Remove</a>'));
		}
		
	?>
	<a href="#" class="addEmail">Add new email</a>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
