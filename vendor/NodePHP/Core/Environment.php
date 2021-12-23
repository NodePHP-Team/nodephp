<?php

	namespace NodePHP;

	class Environment
	{
		private function __construct()
		{

		}
		public static function Get()
		{
			if(!isset($GLOBALS['NodePHP.Environment']))
			{
				$GLOBALS['NodePHP.Environment'] = new Environment();
			}
			return $GLOBALS['NodePHP.Environment'];
		}
		public function addJavascriptInterface($localName , $object)
		{
			$this->{$localName} = $object;
			return $this;
		}
	}


?>