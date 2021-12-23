<?php

	namespace NodePHP\Core;

	class ClassManager
	{
		private $registeredClasses = [];
		private $scope; //optional.



		private function __construct()
		{

		}
		public function registerClass($className , $prototype)
		{
			$template = new ClassTemplate($className , $prototype);
			$this->registeredClasses[] = $template;
			return $this;
		}
		public function bindScope(\NodePHP\Core\Tokenizer\Scope $scope)
		{
			$this->scope = $scope;
			return $this;
		}
		public static function Get()
		{
			if(!@$GLOBALS['NodePHP.ClassManager'])
			{
				$GLOBALS['NodePHP.ClassManager'] = new ClassManager();
			}
			return $GLOBALS['NodePHP.ClassManager'];
		}
	}


	class ClassTemplate
	{
		private $className = '';
		private $prototype = null;
		private $locationalReferences = [];

		function __construct($className , $prototype)
		{
			$this->className = $className;
			$this->prototype = $prototype;
		}
		/**
		 * You cannot instantiate objects in PHP like you can javascript.
		 * this is a wrapper for it. when doing a check against a "new" instantiation
		 * the system can hot swap so it runs as it would JS side.
		 */
		function addLocationalReference($data)
		{
			$path = explode("." , $data); //THREE.Loaders.GLTFLoader (e.g) (Not a valid reference, just a random one.)


		}
	}

?>