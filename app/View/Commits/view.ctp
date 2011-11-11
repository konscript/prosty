<div class="commits view">
<h2><?php  echo __('Commit');?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($commit['Commit']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Project'); ?></dt>
		<dd>
			<?php echo $this->Html->link($commit['Project']['title'], array('controller' => 'projects', 'action' => 'view', $commit['Project']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Commit Hash'); ?></dt>
		<dd>
			<?php echo h($commit['Commit']['commit_hash']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Last Commit Msg'); ?></dt>
		<dd>
			<?php echo h($commit['Commit']['last_commit_msg']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Number Of Commits'); ?></dt>
		<dd>
			<?php echo h($commit['Commit']['number_of_commits']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Ip Addr'); ?></dt>
		<dd>
			<?php echo h($commit['Commit']['ip_addr']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($commit['Commit']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($commit['Commit']['modified']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created By'); ?></dt>
		<dd>
			<?php echo h($commit['Commit']['created_by']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified By'); ?></dt>
		<dd>
			<?php echo h($commit['Commit']['modified_by']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Commit'), array('action' => 'edit', $commit['Commit']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Commit'), array('action' => 'delete', $commit['Commit']['id']), null, __('Are you sure you want to delete # %s?', $commit['Commit']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Commits'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Commit'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Projects'), array('controller' => 'projects', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Project'), array('controller' => 'projects', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Deployments'), array('controller' => 'deployments', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Deployment'), array('controller' => 'deployments', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Deployments');?></h3>
	<?php if (!empty($commit['Deployment'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Commit Id'); ?></th>
		<th><?php echo __('Status'); ?></th>
		<th><?php echo __('Created By'); ?></th>
		<th><?php echo __('Modified By'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($commit['Deployment'] as $deployment): ?>
		<tr>
			<td><?php echo $deployment['id'];?></td>
			<td><?php echo $deployment['commit_id'];?></td>
			<td><?php echo $deployment['status'];?></td>
			<td><?php echo $deployment['created_by'];?></td>
			<td><?php echo $deployment['modified_by'];?></td>
			<td><?php echo $deployment['created'];?></td>
			<td><?php echo $deployment['modified'];?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'deployments', 'action' => 'view', $deployment['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'deployments', 'action' => 'edit', $deployment['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'deployments', 'action' => 'delete', $deployment['id']), null, __('Are you sure you want to delete # %s?', $deployment['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Deployment'), array('controller' => 'deployments', 'action' => 'add'));?> </li>
		</ul>
	</div>
</div>
