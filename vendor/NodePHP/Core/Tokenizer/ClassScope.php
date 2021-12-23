<?php

	namespace NodePHP\Core\Tokenizer;

	class ClassScope extends Scope
	{
		function __construct()
		{

		}
		function build()
		{
			

			//BUILD TREE.
			$level = 0;
			$tokenized = '';
			$stringOpened = false;
			$surroundingQuote = '';
			for($i = 0;$i < strlen($this->value());$i++)
			{
				$char = $this->value()[$i];

				switch($char)
				{
					case '"':
					case "'":
					case '`':
						if(!$stringOpened)
						{
							$stringOpened = true;
							$surroundingQuote = $char;
						}else{
							if($surroundingQuote == $char)
							{
								//close.
								$stringOpened = false;
								$surroundingQuote = '';
							}else{

							}
						}
						$tokenized .= $char;
					break;
					case '{':
						if($stringOpened)
						{
							$tokenized .= '{';
						}else{
							$level++;
							$tokenized .= '{' . $level;
						}
					break;
					case '}':
						if($stringOpened){
							$tokenized .= '}';
						}else{
							$tokenized .= '}' . $level;
							$level--;
						}
					break;
					default:
						$tokenized .= $char;
					break;
				}
			}
			$exploded = explode("}2" , $tokenized);
			$methods = array_pop($exploded);

			foreach($exploded as $method)
			{
				if($method[0] === '{')
				{
					$method = substr($method , 2 , strlen($method));
				}
				$methodRaw = $this->removeTokens($method) . '}';

				$method = new MethodScope();
				$method->isClassMethod = true;
				$head = substr($methodRaw , 0 , strpos($methodRaw , '{'));
				$method->type = (stristr($head , '=>')) ? 'es6' : 'standard';

				switch($method->type)
				{
					case 'standard':
						$method->methodName = trim(substr($head , 0 , strpos($head , '(')));
					break;
					case 'es6':
						$method->methodName = trim(substr($head , 0 , strpos($head , '=')));
					break;
				}
				$rawArgs = explode("," , str_replace(' ' , '' , substr($head , strpos($head , '(') + 1, strrpos($head , ')') - (strpos($head , '(') + 1))));

				foreach($rawArgs as $arg)
				{
					$default = null;

					if(stristr($arg , '='))
					{
						$default = explode("=" , $arg)[1];
						$arg = explode("=" , $arg)[0];
					}else{

					}
					$arg = new Argument($arg);
					$arg->default = $default;
					$method->addArgument($arg);
				}

				//parse method body, allowing another child scope to come through and parse the child value.

				$body = "";

				switch($method->type)
				{
					case 'standard':

						$body = substr($methodRaw , strpos($methodRaw , '{') + 1 , strlen($methodRaw));
						//remove last }

						$body = substr($body , 0 , strrpos($body , '}'));

					break;
					case 'es6':

					break;
				}

				if(stristr($head , ' async '))
				{
					$method->isAsync = true;
				}

				for($i = 0;$i < strlen($body);$i++)
				{
					$method->addChar($body[$i]);
				}
				$method->build();
				$this->addChild($method);
			}
		}
		function toPHP()
		{
			$out = 'class ' . $this->className . ' extends ' . ($this->extends ?? "JavascriptObject") . '{';

			foreach($this->children as $child)
			{
				if(!stristr(get_class($child) , 'method'))
				{
					continue;
				}
				if($child->methodName == 'constructor')
				{
					$child->methodName = '__construct';
					$out .= "\n\t" . $child->toPHP();
				}else{
					$out .= "\n\t" . $child->toPHP();
				}
			}

			$out .= "\n}";

			return $out;
		}
		function removeTokens($str)
		{
			for($i = 0;$i < 20;$i++)
			{
				$str = str_replace('{' . $i , '{' , $str);
				$str = str_replace('}' . $i , '}' , $str);
			}
			return $str;
		}
	}

?>