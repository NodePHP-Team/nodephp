<?php

	namespace NodePHP\Core\Tokenizer;

	class Scope
	{
		private $startLine = 0;
		private $endLine = 0;

		public $startIndex = 0;
		public $endIndex = 0;

		public $parent = null;
		public $children = [];
		private $nodeValue = '';

		public function __construct()
		{

		}
		public function eachParent(\Closure $callback)
		{
			$current = $this;

			while(($current = $current->parent) !== null)
			{
				$callback->call($this , $current);
			}
		}
		public function replaceChild(Scope $newScope , Scope $oldScope)
		{
			foreach($this->children as $idx => $child)
			{
				if($child == $oldScope)
				{
					$this->children[$idx] = $newScope;
					break;
				}
			}
			return $this;
		}
		public function removeChildAt($index)
		{
			unset($this->children[$index]);

			$this->children = array_values($this->children);
			return $this;
		}
		public function removeChild(Scope $child)
		{
			foreach($this->children as $idx => $childScope)
			{
				if($childScope == $child)
				{
					$this->removeChildAt($idx);

					break;
				}
			}
			return $this;
		}
		public function detect($root)
		{
			$beforeThis = $this->startIndex;
			$code = '';
			for($i = $beforeThis;$i > -1;$i--)
			{
				if($root->nodeValue[$i] === ';')
				{
					//was an expression before
					break;
				}
				if($root->nodeValue[$i] == PHP_EOL)
				{
					break;
				}
				if($root->nodeValue[$i] == '}')
				{
					break;
				}
				$code .= $root->nodeValue[$i];
			}
			$js = strrev($code);

			$meta = explode(" " , $js);

			if(@$meta[2] === 'exten')
			{
				return $this;
			}
			if(strlen($js) - 1 !== strpos($js, '{'))
			{
				return $this;
			}

			if(stristr($js , 'class'))
			{
				$this->type = 'class';
				
				if(stristr($js , 'extends'))
				{
					$data = explode(" " , trim($js));

					foreach($data as $key => $value)
					{
						$val = $data[$key + 1] ?? false;
						$ukey = $value;
						if($ukey == 'class')
						{
							$this->className = str_replace(['{' , ' ' , "\n" , "\r"] , '' , $val);
						}
						if($ukey == 'extends')
						{
							$this->extends = $val;
						}
					}
				}else{
					$data = explode(" " , trim($js));

					foreach($data as $key => $value)
					{
						$val = $data[$key + 1] ?? false;
						$ukey = $value;
						if($ukey == 'class')
						{
							$this->className = str_replace(['{' , ' ' , "\n" , "\r"] , '' , $val);
						}
					} 
					$this->extends = 'JavascriptObject';
				}

			}else if(stristr($js , 'function'))
			{
				$this->type = 'function';
				$this->isMemberOfClass = false;
			}else if(stristr($js , 'else if') || stristr($js , 'if') || stristr($js , 'else')){
				$this->type = 'conditional';
				$this->conditionType = false;

				switch(true)
				{
					case stristr($js , 'else if'):
						$this->conditionType = 'else if';
						$this->conditionStatement = str_replace(')' , '' , trim(substr($js , strpos($js , 'if') + 3 , (strlen($js) - strpos($js , 'if') + 2) - strrpos($js , '{') - 1)));
					break;
					case stristr($js , 'if'):
						$this->conditionType = 'if';
						$this->conditionStatement = str_replace(')' , '' , trim(substr($js , strpos($js , 'if') + 3 , (strlen($js) - strpos($js , 'if') + 2) - strrpos($js , '{') - 1)));
					break;
					case stristr($js , 'else'):
						$this->conditionType = 'else';
					break;
				}
			}
			return $this->getCorrectScopeDefinition();
		}
		public function value()
		{
			return $this->nodeValue;
		}
		//just for expressions.
		public function setValue($value)
		{
			if(!stristr(get_class($this) , 'Expression'))
			{
				return $this;
			}
			$this->nodeValue = $value;
			return $this;
		}
		public function getCorrectScopeDefinition()
		{
			$type = $this->type ?? false;

			if(!$type)
			{
				return $this;
			}
			switch($type)
			{
				case 'class':
					$data = new ClassScope();

					foreach($this as $prop => $value)
					{
						$data->{$prop} = $value;
					}
					$data->build();
					return $data;
				break;
				case 'conditional':
					if(!$this->conditionType)
					{
						return $this;
					}
					$data = new ConditionalScope();
					foreach($this as $prop => $value)
					{
						$data->{$prop} = $value;
					}
					$data->type = $this->conditionType;
					$data->condition = new ExpressionScope();
					$data->condition->setValue(@$this->conditionStatement);

					$child = new ExpressionScope();
					$child->setValue(substr($data->value() , 1 , strlen($data->value()) - 1));
					$data->addChild($child);
					$data->build();
					return $data;
				break;
				default:
					return $this;
			}
		}
		public function toPHP()
		{
			if(@$this->rootScope && $this === $this->rootScope || $this->parent && $this->parent === @$this->rootScope){
				$php = '';

				foreach($this->children as $child)
				{
					$php .= "\n" . $child->toPHP();
				}

				return $php;
			}else{
				$php = '';
				
				foreach($this->children as $child)
				{
					$php .= "\n" . $child->toPHP();
				}

				return str_replace('elseelse' , 'else', $php);
			}
		}
		/**
		 * 
		 */
		public function dump($scopeName = 'rootScope'){
			$out = '';
			$out .= '<div class="scope"><div class="title">' . $scopeName . '</div><pre>';

				$out .= '[JS]: ' .	PHP_EOL;

				$out .= $this->nodeValue;

				foreach($this->children as $child)
				{
					$out .= str_replace($child->nodeValue , $child->dump($scopeName . ' > ' . spl_object_id($child)) , $out);
				}

			$out .= '</pre></div>';
			return $out;
		}
		/**
		 * @description - This adds a character to the Scope. It 
		 * is needed to build the Tree on the Javascript Document.
		 */
		public function addChar($char)
		{
			$this->nodeValue .= $char;
			if($this->parent){
				if($this->parent == $this)
				{

				}else{
					$this->parent->addChar($char);
				}
			}
		}
		/**
		 * @param {Scope} $scope - The scope to add to this scope.
		 * @return Scope (Parent)
		 */
		public function addChild(Scope $scope)
		{
			$this->children[] = $scope;
			$scope->parent = $this;
			return $this;
		}
		public function setRange($start = 0 , $end = 0)
		{
			$this->startIndex = $start;
			$this->endIndex = $end;
			return $this;
		}
		public function setStartIndex($start = 0)
		{
			$this->startIndex = $start;
			return $this;
		}
		public function setEndIndex($end = 0)
		{
			$this->endIndex = $end;
			return $this;
		}
		/**
		 * @param {int} $start - The start of the scope.
		 * @param {int} $end - The end of the scope.
		 * @return Scope (Parent)
		 */
		public function setSize($start = 0 , $end = 0)
		{
			$this->startLine = $start;
			$this->endLine = $end;
			return $this;
		}


		public function __debugInfo()
		{
			$out = [];

			foreach($this as $key => $value)
			{
				if($key == 'parent')
				{
					continue;
				}

				$out[$key] = $value;
			}
			return $out;
		}
	}

?>