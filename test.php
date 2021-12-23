<?php

	require_once('vendor/NodePHP/Javascript.php');

	$js = \NodePHP\Javascript::getRuntime();

	$js->evaluate(file_get_contents("test.js"));

?>
<style>
	pre{
		background: #ccc;border-radius: 3px;padding: 10px;
	}
	pre.warn{
		background: beige;
	}
	pre.error{
		background: pink;
		border: 1px solid #A11818;
	}
</style>