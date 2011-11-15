<div class="userEmails view">
<h2><?php  echo __('User Email');?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($userEmail['UserEmail']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('User'); ?></dt>
		<dd>
			<?php echo $this->Html->link($userEmail['User']['id'], array('controller' => 'users', 'action' => 'view', $userEmail['User']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Email'); ?></dt>
		<dd>
			<?php echo h($userEmail['UserEmail']['email']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit User Email'), array('action' => 'edit', $userEmail['UserEmail']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete User Email'), array('action' => 'delete', $userEmail['UserEmail']['id']), null, __('Are you sure you want to delete # %s?', $userEmail['UserEmail']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List User Emails'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User Email'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
