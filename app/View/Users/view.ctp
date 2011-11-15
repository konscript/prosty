<div class="users view">
<h2><?php  echo __('User');?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($user['User']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Username'); ?></dt>
		<dd>
			<?php echo h($user['User']['username']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Password'); ?></dt>
		<dd>
			<?php echo h($user['User']['password']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Role'); ?></dt>
		<dd>
			<?php echo $this->Html->link($user['Role']['title'], array('controller' => 'roles', 'action' => 'view', $user['Role']['id'])); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit User'), array('action' => 'edit', $user['User']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete User'), array('action' => 'delete', $user['User']['id']), null, __('Are you sure you want to delete # %s?', $user['User']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Roles'), array('controller' => 'roles', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Role'), array('controller' => 'roles', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Commits'), array('controller' => 'commits', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Commit'), array('controller' => 'commits', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List User Emails'), array('controller' => 'user_emails', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User Email'), array('controller' => 'user_emails', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Commits');?></h3>
	<?php if (!empty($user['Commit'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Project Id'); ?></th>
		<th><?php echo __('Hash'); ?></th>
		<th><?php echo __('Last Commit Msg'); ?></th>
		<th><?php echo __('Number Of Commits'); ?></th>
		<th><?php echo __('Ip Addr'); ?></th>
		<th><?php echo __('Status'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th><?php echo __('Created By'); ?></th>
		<th><?php echo __('Modified By'); ?></th>
		<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($user['Commit'] as $commit): ?>
		<tr>
			<td><?php echo $commit['id'];?></td>
			<td><?php echo $commit['project_id'];?></td>
			<td><?php echo $commit['hash'];?></td>
			<td><?php echo $commit['last_commit_msg'];?></td>
			<td><?php echo $commit['number_of_commits'];?></td>
			<td><?php echo $commit['ip_addr'];?></td>
			<td><?php echo $commit['status'];?></td>
			<td><?php echo $commit['created'];?></td>
			<td><?php echo $commit['modified'];?></td>
			<td><?php echo $commit['created_by'];?></td>
			<td><?php echo $commit['modified_by'];?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'commits', 'action' => 'view', $commit['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'commits', 'action' => 'edit', $commit['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'commits', 'action' => 'delete', $commit['id']), null, __('Are you sure you want to delete # %s?', $commit['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Commit'), array('controller' => 'commits', 'action' => 'add'));?> </li>
		</ul>
	</div>
</div>
<div class="related">
	<h3><?php echo __('Related User Emails');?></h3>
	<?php if (!empty($user['UserEmail'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('User Id'); ?></th>
		<th><?php echo __('Email'); ?></th>
		<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($user['UserEmail'] as $userEmail): ?>
		<tr>
			<td><?php echo $userEmail['id'];?></td>
			<td><?php echo $userEmail['user_id'];?></td>
			<td><?php echo $userEmail['email'];?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'user_emails', 'action' => 'view', $userEmail['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'user_emails', 'action' => 'edit', $userEmail['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'user_emails', 'action' => 'delete', $userEmail['id']), null, __('Are you sure you want to delete # %s?', $userEmail['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New User Email'), array('controller' => 'user_emails', 'action' => 'add'));?> </li>
		</ul>
	</div>
</div>
