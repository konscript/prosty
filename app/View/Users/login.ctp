<div id="login">
	<?php echo $this->Session->flash('auth'); ?>
	<?php echo $this->Form->create('User');?>
    
    <?php echo $this->Form->input('username', array('type' => 'text', 'placeholder' => 'you@example.com')); ?>
	<?php echo $this->Form->input('password', array('type' => 'password', 'placeholder' => 'password')); ?>
	<p class="submit">
		<input type="submit" value="Login" class="green submit button" />
	</p>
	<p class="forgot-password">
		<span><?php echo __('or'); ?> <a href="#"><?php echo __('lost your password?'); ?></a></span>
	</p>
</div>
