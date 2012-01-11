<div class="deployments view">
	<dl>
		<dt><?php echo __('Project'); ?></dt>
		<dd>
			<?php echo $this->Html->link($deployment['Project']['title'], array('controller' => 'projects', 'action' => 'view', $deployment['Project']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Deployed by'); ?></dt>
		<dd>
			<?php echo h($deployment['Deployment']['created_by']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Deployed at'); ?></dt>
		<dd>
			<?php echo h($deployment['Deployment']['created']); ?>
			&nbsp;
		</dd>
	</dl>
</div>



<?php if(isset($unstagedFiles)): ?>
	<?php echo $this->Form->create(null, array(
		'url' => '/resolve_deployments/add/' . $deployment['Project']['id'] .'/unstaged', 
		'id'=>'resolveUnstagedFiles', 
		'class' => 'resolveDialog'
	)); ?>
	
		<div id="unresolvedFiles">
			<?php if(isset($unstagedFiles["new"])): ?>
				<?php echo $this->Util->resolveFilesView("new", "New files", $unstagedFiles["new"]); ?>
			<?php endif; ?>
		
			<?php if(isset($unstagedFiles["changed"])): ?>
				<?php echo $this->Util->resolveFilesView("changed", "Changed files", $unstagedFiles["changed"]); ?>		
			<?php endif; ?>
		</div>

		<div id="resolvedFiles">
			<?php echo $this->Util->resolveFilesView("ignoreFiles", "Files to be ignored"); ?>		
			<?php echo $this->Util->resolveFilesView("commitFiles", "Files to be commited"); ?>		
		</div>
	
		<input type="submit" value="Ignore" id="ignoreFiles" class="addFiles">
		<input type="submit" value="Commit" id="commitFiles" class="addFiles">
		<input type="submit" value="Re-deploy" id="redeploy">
		<?php echo $this->Html->image('loading.gif', array('alt' => 'Loading deployment', 'class' => 'loading'))?>		
		<div class="clear"></div>		

	<?php echo $this->Form->end(); ?>
<?php endif; ?>						

<?php if(isset($conflictingFiles)): ?>
	<?php echo $this->Form->create(null, array(
		'url' => '/resolve_deployments/add/' . $deployment['Project']['id'] .'/unmerged', 
		'id' => 'resolveConflictingFiles', 
		'class' => 'resolveDialog'
	)); ?>	
	
		<div id="unresolvedFiles">
			<?php echo $this->Util->resolveFilesView("unmergedFiles", "Unmerged files", $conflictingFiles); ?>
		</div>

		<div id="resolvedFiles">
			<?php echo $this->Util->resolveFilesView("theirFiles", "Use GitHub's version"); ?>						
			<?php echo $this->Util->resolveFilesView("ourFiles", "Use Caesar's (local) version"); ?>			
		</div>
	
		<input type="submit" value="Use Github version" id="theirFiles" class="addFiles">
		<input type="submit" value="Use local version" id="ourFiles" class="addFiles">
		<input type="submit" value="Re-deploy" id="redeploy">
		<?php echo $this->Html->image('loading.gif', array('alt' => 'Loading deployment', 'class' => 'loading'))?>		

		<div class="clear"></div>		
	<?php echo $this->Form->end(); ?>
<?php endif; ?>							
	
	
	
<div id="debugger">	

</div>
	
	

<div class="deploymentErrors">
	<h3><?php echo __('Errors during deployment');?></h3>
	<?php if (!empty($deployment['DeploymentError'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Request'); ?></th>
		<th><?php echo __('Response'); ?></th>		
		<th><?php echo __('Exit status'); ?></th>			
	</tr>
	<?php
		$i = 0;
		foreach ($deployment['DeploymentError'] as $deploymentError): ?>
		<tr>
			<td><pre><?php print_r($deploymentError['request']);?></pre></td>
			<td><pre><?php print_r($deploymentError['response']);?></pre></td>			
			<td><?php echo $deploymentError['return_code'];?></td>			
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>
</div>
