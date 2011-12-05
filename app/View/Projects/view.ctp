<div class="projects view">
<h2><?php  echo __('Project');?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($project['Project']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Project Alias'); ?></dt>
		<dd>
			<?php echo h($project['Project']['project_alias']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Title'); ?></dt>
		<dd>
			<?php echo h($project['Project']['title']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Primary Domain'); ?></dt>
		<dd>
			<?php echo h($project['Project']['primary_domain']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Additional Domains'); ?></dt>
		<dd>
			<?php echo h($project['Project']['additional_domains']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Dev Domain'); ?></dt>
		<dd>
			<?php echo h($project['Project']['dev_domain']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Use Cache'); ?></dt>
		<dd>
			<?php echo h($project['Project']['use_cache']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Current Version'); ?></dt>
		<dd>
			<?php echo h($project['Project']['current_version']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Screenshot'); ?></dt>
		<dd>
			<?php echo h($project['Project']['screenshot']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Exclude'); ?></dt>
		<dd>
			<?php echo h($project['Project']['exclude']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Errors'); ?></dt>
		<dd>
			<?php echo h($project['Project']['errors']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Reboot Needed Date'); ?></dt>
		<dd>
			<?php echo h($project['Project']['reboot_needed_date']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($project['Project']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($project['Project']['modified']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created By'); ?></dt>
		<dd>
			<?php echo h($project['Project']['created_by']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified By'); ?></dt>
		<dd>
			<?php echo h($project['Project']['modified_by']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Project'), array('action' => 'edit', $project['Project']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Project'), array('action' => 'delete', $project['Project']['id']), null, __('Are you sure you want to delete # %s?', $project['Project']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Projects'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Project'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Commits'), array('controller' => 'commits', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Commit'), array('controller' => 'commits', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Commits');?></h3>
	<?php if (!empty($project['Commit'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Project Id'); ?></th>
		<th><?php echo __('Commit Hash'); ?></th>
		<th><?php echo __('Last Commit Msg'); ?></th>
		<th><?php echo __('Number Of Commits'); ?></th>
		<th><?php echo __('Ip Addr'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th><?php echo __('Created By'); ?></th>
		<th><?php echo __('Modified By'); ?></th>
		<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($project['Commit'] as $commit): ?>
		<tr>
			<td><?php echo $commit['id'];?></td>
			<td><?php echo $commit['project_id'];?></td>
			<td><?php echo $commit['hash'];?></td>
			<td><?php echo $commit['last_commit_msg'];?></td>
			<td><?php echo $commit['number_of_commits'];?></td>
			<td><?php echo $commit['ip_addr'];?></td>
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
