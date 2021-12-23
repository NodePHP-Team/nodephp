<?php

	namespace NodePHP\Core;

	class Tokenizer
	{
		/**
		 * @param {String} $jsCode - The code to tokenize
		 */
		private $rootScope;

		public function getRootScope()
		{
			return $this->rootScope;
		}	
		public function containsExpressionalBrackets($jsCode)
		{
			$stringOpened = false;
			$surroundingQuote = "";

			for($i = 0;$i < strlen($jsCode);$i++)
			{
				$char = $jsCode[$i];

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

					break;
					case '{':
						if(!$stringOpened)
						{
							return true;
						}
					break;
				}
			}
			return false;
		}
		public function containsExpressionalEndings($jsCode)
		{
			$stringOpened = false;
			$surroundingQuote = "";

			for($i = 0;$i < strlen($jsCode);$i++)
			{
				$char = $jsCode[$i];

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

					break;
					case ';':
						if(!$stringOpened)
						{
							return true;
						}
					break;
				}
			}
			return false;
		}
		public function __construct($jsCode)
		{
			$this->rootScope = new \NodePHP\Core\Tokenizer\Scope();
			$this->rootScope->setSize(0 , substr_count($jsCode , "\n"));

			if(!$this->containsExpressionalBrackets($jsCode)){
				if(!$this->containsExpressionalEndings($jsCode))
				{
					//more then likely, a single expression #DealWithItLater
				}else{
					$expressions = explode(";" , trim($jsCode));
					array_pop($expressions);

					foreach($expressions as $expression)
					{
						$exp = new Tokenizer\ExpressionScope();
						$exp->setValue($expression);

						$this->rootScope->addChild($exp);
					}
				}
			}else{

				$current = $this->rootScope;
				$stringOpened = false;
				$surroundingQuote = '';
				for($i = 0;$i < strlen($jsCode);$i++)
				{
					$char = $jsCode[$i];
					
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
							$current->addChar($char);
						break;
						case '{':
							if($stringOpened){
								$current->addChar($char);
							}else{
								$curr = $current;

								if($current->parent && stristr(get_class($current->parent) , 'class')){
									$current = $current->parent;

									if($current)
									{
										if(!$current->parent)
										{
											$current = $this->rootScope;
										}else{
											$current = $current->parent;
										}
									}else{
										$current = $this->rootScope;
									}
								}else{
									$current = new Tokenizer\Scope();
									$current->setStartIndex($i - $curr->startIndex);
									$curr->addChild($current);
									$current->addChar($char);
								}
							}
						break;
						case '}':
							if($stringOpened){
								$current->addChar($char);
							}else{
								$current->addChar($char);
								$current->setEndIndex($i - ($current->parent ? $current->parent : $this->rootScope)->endIndex);
								$scp = $current->detect($this->rootScope);

								if($scp == $current)
								{
									//do nothing.
									$current = $current->parent ?? $this->rootScope;
								}else{
									//replace scope with typed.
									$current->parent->replaceChild($scp , $current);
									$current = $current->parent;
								}
							}
							
						break;
						default:
							$current->addChar($char);
					}
				}
			}
		}
	}

?>