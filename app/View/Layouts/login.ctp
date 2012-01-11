<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo $title_for_layout?></title>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
	<?php echo $this->Html->script('https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js'); ?>
	<?php echo $this->Html->script('https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js'); ?>
	<?php echo $this->Html->script('screen'); ?>
	<?php echo $this->Html->css('main'); ?>		
	<?php echo $scripts_for_layout ?>

</head>

<body>
	<div id="body-wrapper">
		<div id="header-before"></div>
		<div id="header-wrapper">
			<div id="header">
				<h1 id="logo">Konscript</h1>
				<nav id="main-navigation">
				</nav>
			</div>
		</div>
		<div id="header-after"></div>
		
		<div id="content-before"></div>
		<div id="content-wrapper">
			<div id="content">
				<?php echo $this->Session->flash(); ?>
				<?php echo $this->Session->flash('auth'); ?>
				<?php echo $content_for_layout ?>
			</div>
		</div>
		<div id="content-after"></div>
		
		<div id="footer-wrapper">
			<div id="footer">
				<?php //echo $this->element('sql_dump'); ?>
			</div>
		</div>
	</div>
</body>
</html>
