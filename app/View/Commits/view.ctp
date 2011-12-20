<div class="commits view">
	<dl>

		<dt><?php echo __('Hash'); ?></dt>
		<dd>
			<?php echo h($commit['Commit']['hash']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Last Commit Msg'); ?></dt>
		<dd>
			<?php echo h($commit['Commit']['last_commit_msg']); ?>
			&nbsp;
		</dd>

	</dl>
</div>

<div class="related">
	<h3><?php echo __('Errors during commit to Caesar');?></h3>
	<?php if (!empty($commit['CommitError'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Calling Function'); ?></th>
		<th><?php echo __('Message'); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($commit['CommitError'] as $commitError): ?>
		<tr>
			<td><?php echo $commitError['calling_function'];?></td>
			<td><?php echo $commitError['message'];?></td>

		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

</div>
