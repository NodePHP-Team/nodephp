<?php

	namespace NodePHP\Core;

	/**
	 * Works across the engine, and across multiple scripts.
	*/

	class VariableManager
	{
		private $variableMap = [];
		private function __construct()
		{

		}
		public function mapVariable($from){
			$this->variableMap[$from] = true;
			return $this;
		}
		public function hotSwap($jsCode)
		{
			foreach($this->variableMap as $find => $replace)
			{
				$jsCode = str_replace($find , '$' . $find , $jsCode);
			}
			return $jsCode;
		}
		public static function Get()
		{
			if(!isset($GLOBALS['NodePHP.VariableManager']))
			{
				$GLOBALS['NodePHP.VariableManager'] = new VariableManager();
			}
			return $GLOBALS['NodePHP.VariableManager'];
		}
	}

?>