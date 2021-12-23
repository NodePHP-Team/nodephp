<?php

	namespace NodePHP\Core\Tokenizer;

	class ExpressionScope extends Scope
	{
		function toPHP($ignoreColon = false)
		{
			
			$code = str_replace("super." , "parent::" , $this->value());
			$code = str_replace("super(" , "parent::__construct(" , $this->value());
			$code = str_replace("." , "->" , $code);
			$code = str_replace(["let " , "const " , "var "] , '$' , $code);
			$code = str_replace("debugger" , "die();" , $code);
			$code = str_replace("this" , '$this' , $code);
			$code = str_replace(["\n" , "\r"] , '' , $code);

			if(stristr($code , '=')){
				$code = $this->fixVariables($code);
			}
			$code = \NodePHP\Core\VariableManager::Get()->hotSwap($code);

			return $code . ($ignoreColon ? '' : ";");
		}
		function fixVariables($code)
		{
			if(stristr($code , '=')){
				$rightHandAssignment = explode("=" , $code)[1];

				$assignmentType = '';

				//work out if its a variable from parent scope, a function call or something else.
				//iterate a lazylist first.

				switch(true)
				{
					case stristr($rightHandAssignment , '()'):
					case stristr($rightHandAssignment , '.call('):
						$assignmentType = 'function';
					break;
					case stristr($rightHandAssignment , 'new '):
						$assignmentType = 'class';
					break;
					default: 
						$assignmentType = 'variable';
				}


				switch($assignmentType)
				{
					case 'function':

					break;
				}

			}
			return $code;
		}
	}

?>