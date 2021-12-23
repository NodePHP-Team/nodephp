<?php

	namespace NodePHP\Core\Tokenizer;

	class ConditionalScope extends Scope{
		public $type = '';
		public $condition = '';

		public function build()
		{
			
		}
		public function toPHP()
		{
			$out = "\t" . $this->type;
			switch(true)
			{
				case $this->type == 'else if':
				case $this->type == 'if':
					$out .= '(' . str_replace(';' , '' , $this->condition->toPHP()) . '){' . "\n";
					foreach($this->children as $child){
						$out .= "\t" . $child->toPHP(true);
					}
					$out .= "\n";
				break;
				case $this->type == 'else':
				default:
					$out .= "else{\n";

					foreach($this->children as $child)
					{
						$out .= $child->toPHP(true);
					}
					$out .= "\n";
				break;
			}
			return $out;
		}
	}


?>