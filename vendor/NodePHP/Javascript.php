<?php
  
  namespace NodePHP;

  class Javascript
  {
    const INSTALl_DIR = "vendor/NodePHP/"; //change if you intend to use outside the vendor directory.
    const USE_CACHE = false;

    private $interfaces = [];

    public static function getRuntime()
    {
        if(!isset($GLOBALS['NodePHPRuntime']))
        {
            $GLOBALS['NodePHPRuntime'] = new Javascript();
        }
        return $GLOBALS['NodePHPRuntime'];
    }
    private function __construct()
    {
        require_once(self::INSTALl_DIR . 'Core/Tokenizer/Scope.php');
        require_once(self::INSTALl_DIR . "Core/Tokenizer/ConditionalScope.php");
        require_once(self::INSTALl_DIR . "Core/Tokenizer/ExpressionScope.php");
        require_once(self::INSTALl_DIR . "Core/Tokenizer/MethodScope.php");
        require_once(self::INSTALl_DIR . "Core/Tokenizer/ClassScope.php");
        require_once(self::INSTALl_DIR . 'Core/Tokenizer.php');
        require_once(self::INSTALl_DIR . 'Core/VariableManager.php');
        require_once(self::INSTALl_DIR . 'Core/ClassManager.php');
        require_once(self::INSTALl_DIR . 'Core/Environment.php');

        Environment::Get();

        $plugins = glob(self::INSTALl_DIR . "/Core/Source/*.php");

        if(count($plugins) < 1)
        {
            echo 'No Plugins loaded';
        }
        foreach($plugins as $source)
        {
            // echo 'Loaded Plugin: ' . basename($source , '.php') . '<br>';
            require_once($source);
        }

        $this->classManager = \NodePHP\Core\ClassManager::Get();
        $this->varManager = \NodePHP\Core\VariableManager::Get();
    }

    //localName -> console -> console->log becomes $console->log
    
    public function hotSwap($jsCode)
    {
        foreach(\NodePHP\Environment::Get() as $varName => $className){
            $jsCode = str_replace($varName . '->' , '$' . $varName . '->' , $jsCode);
        }
        return str_replace('$$' , '$' , $jsCode);
    }
    public function evaluate($jsCode , $filename = 'inline:')
    {
       $sha1 = sha1($jsCode);
       @mkdir(self::INSTALl_DIR . '/cache/bin' , 0755 , true);

       if(file_exists(self::INSTALl_DIR . "/cache/bin/{$sha1}.cache.php"))
       {
            require_once(self::INSTALl_DIR . "/cache/bin/{$sha1}.cache.php");

            if(self::USE_CACHE === false)
            {
               unlink(self::INSTALl_DIR . "/cache/bin/{$sha1}.cache.php");
            }

            nodephp_runtime(Environment::Get());
       }else{

           $this->tokenizer = new \NodePHP\Core\Tokenizer($jsCode);

           $out = '<' . '?php' . PHP_EOL;

           $out .= "\tfunction nodephp_runtime(\$environment){\n\t";

           foreach(\NodePHP\Environment::Get() as $varName => $className)
           {
                $out .= "\t\t\${$varName} = \$environment->{$varName};\n";
           }

           $out .= $this->hotSwap($this->toPHP());

           $out .= "\t}\n";

           $out .= PHP_EOL . '?>';

           file_put_contents(self::INSTALl_DIR . "/cache/bin/{$sha1}.cache.php", $this->hotSwap($out));

           $this->evaluate($jsCode , $filename);
        }

       
    }
    private function toPHP()
    {
      return $this->tokenizer->getRootScope()->toPHP();
    }
    public function dump()
    {
        return $this->tokenizer->getRootScope()->dump();
    }
  }

  

?>
