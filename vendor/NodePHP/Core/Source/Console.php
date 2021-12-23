<?php

	namespace Javascript;

	class Console
	{
		public function __construct()
		{

		}
		public function log()
		{
			foreach(func_get_args() as $arg)
			{
				echo '<pre>';
				var_dump($arg);
				echo '</pre>';
			}
		}
		public function warn()
		{
			foreach(func_get_args() as $arg)
			{
				echo '<pre class="warn">';
				var_dump($arg);
				echo '</pre>';
			}
		}
		public function error()
		{
			foreach(func_get_args() as $arg)
			{
				echo '<pre class="error">';
				var_dump($arg);
				echo '</pre>';
			}
		}
	}

	\NodePHP\Environment::Get()->addJavascriptInterface("console" , new Console());
?>