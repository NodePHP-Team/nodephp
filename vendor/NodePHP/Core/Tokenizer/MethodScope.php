<?php

	namespace NodePHP\Core\Tokenizer;

	class MethodScope extends Scope
	{
		public $isClassMethod = false;
		public $methodName = '';
		public $arguments = [];
		public $type = 'standard'; //Either standard,es6 or es6-lite
		public $methodBody = '';
		public $isAsync = false;


		public function addArgument(Argument $arg)
		{
			$this->arguments[] = $arg;
			return $this;
		}
		//carry on parsing a scope tree.
		public function build()
		{
			$tokenizer = new \NodePHP\Core\Tokenizer($this->value());
			$this->addChild($tokenizer->getRootScope());
		}
		public function toPHP()
		{
			if($this->type == 'standard'){
				$out = 'public function ' . str_replace('$' , 'NODEPHP_DOLLAR' , $this->methodName) . '(';

				foreach($this->arguments as $idx => $arg){
					if($arg->name == '')
					{
						continue;
					}
					if($idx > 0)
					{
						$out .= ' , ';
					}
					$out .= '$' . $arg->name;

					if(!is_null($arg->default))
					{
						$out .= ' = ' . str_replace('`' , '"' , $arg->default);
					}
				}

				$out .= '){' . PHP_EOL . "\t\t";

				foreach($this->children as $idx => $child){
					if($idx > 0)
					{
						$out .= "\n\t\t";
					}
					$out .= $child->toPHP();
				}

				return $out .= "\n\t}";
			}else{
				
			}
		}
	}

	class Argument
	{
		public $name = '' , $dataType = '' , $default = '';

		function __construct($argument)
		{
			$this->name = $argument;
		}
	}
?>