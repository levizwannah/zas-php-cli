<?php

    /**
     *  Autoloads classes, interfaces, and traits. It also automatically include the vendor autoloader if the folder is present.
     *  **requires that the zas-config.json be configured properly.**
     */
    class ZasAutoLoader{
        const ZC_TRAIT = "trait";
        const ZC_CLASS = "class";
        const ZC_ACLASS = "abstractClass";
        const ZC_CONST = "constantsClass";
        /**
         * Path Object from the zas-config
         */
        public $path;

        /**
         * Extensions object from the zas-config
         */
        public $extensions;

        /**
         * Names convention regex from the zas-config
         */
        public $convRegex;

        /**
         * The file name separator in the zas-config
         * @var string
         */
        public $fileNameSeparator;

        # load the zas-config
        public function __construct()
        {
            #Use the Zas configuration to set the extensions and path
            $zasConfig = file_get_contents(__DIR__. "/zas-config.json");
            $config = json_decode($zasConfig);
            $this->path = $config->path;
            $this->extensions = $config->extensions;
            $this->convRegex = $config->nameConventionsRegex;
            $this->fileNameSeparator = $config->fileNameSeparator;
        }

        private function getPath($name, $extension, $path){
            #names contain their namespaces attached to them.
           
            $name = str_replace("\\", "/", $name);
            $name = $this->toZasName($name, $this->fileNameSeparator);
            $fullPath = __DIR__."/$path/$name.$extension";
            
            return $fullPath;
        }

        /**
         * @param string $classname The name of the class to load
         */
        public function loadClass(string $className){

            $classPath = $this->path->class;
            $extension = $this->extensions->class;
            $path = $this->getPath($className, $extension, $classPath);

            if(!file_exists($path)) {
                return false;
            }

            require($path);
            return true;
        }

        /**
         * @param string $interfaceName The name of the interface to load
         */
        public function loadInterface(string $interfaceName){
            #interface names contain their namespaces attached to them.
            $iPath = $this->path->interface;
            $extension = $this->extensions->interface;
            $path = $this->getPath($interfaceName, $extension, $iPath);

            if(!file_exists($path)) {
                return false;
            }

            require($path);
            return true;
        }

        /**
         * @param string $traitName The name of the trait to load
         */
        public function loadTrait(string $traitName){
            #traitNames come with their namespaces attached
            $tPath = $this->path->trait;
            $ext = $this->extensions->trait;
            $path = $this->getPath($traitName, $ext, $tPath);

            if(!file_exists($path)){
                return false;
            }

            require($path);
            return true;
        }

        /**
         * @param string $abstractClassName The name of the abstract class to load
         */
        public function loadAbstractClass(string $abstractClassName){
            #abstractClassNames come with their namespaces attached
            $aPath = $this->path->abstractClass;
            $ext = $this->extensions->abstractClass;
            $path = $this->getPath($abstractClassName, $ext, $aPath);

            if(!file_exists($path)){
                return false;
            }

            require($path);
            return true;
        }
        
        /**
         * @param string $constantsName Loads The name of the class constants to load.
         */
        
        public function loadConstantsClass(string $constantsClassName){
            #constantsClassNames come with their namespaces attached
            $aPath = $this->path->constantsClass;
            $ext = $this->extensions->constantsClass;

            $path = $this->getPath($constantsClassName, $ext, $aPath);
          
            if(!file_exists($path)){
                return false;
            }

            require($path);
            return true;
        }
        
        /**
         * Gets the actual name being loaded
         * @param mixed $qualifiedName
         * 
         * @return array<string,string> ["actualName" => "actualName", "beginsAt" => $i];
         */
        private function actualName($qualifiedName){
            $an = "";
            $i = strlen($qualifiedName);

            for($i = $i - 1; $i >= 0; $i--){
                if($qualifiedName[$i] == "/" || $qualifiedName[$i] == "\\") break;
                $an = $qualifiedName[$i] . $an;
            }

            return ["actualName" => $an, "beginsAt" => $i];
        }

        /**
         * @param string $name Name to load. This will include the namespace plus specific name conventions.
         * For example, name Human, load will determine the loader to call using `Type` (Type)?Human(Type)?: Human would result in loading Human class. AbstractHuman for Abstract class, HumanInterface for interface, HumanTrait for trait.
         * 
         */
        public function load($name){
            #check the name to know whether we are loading a class, interface, trait, or abstract class.
            #name include namespace to it.
            echo "loading: $name\n";

            $actualName = $this->actualName($name);
            $beginsAt = $actualName["beginsAt"];
            $actualName = $actualName["actualName"];
            $namespace = substr($name, 0, $beginsAt + 1);

            echo "ActualName: $actualName\n";
            echo "Namespace: $namespace\n";

            #are we actually loading something?
            if(empty($actualName)) return;

            foreach((array)$this->convRegex as $type => $regex){
               if(!preg_match("/$regex/", $actualName)) continue;

                $nextRegex = preg_replace("/\\\w{1}/", "", $regex);
                $nextRegex = preg_replace("/\W/", "", $nextRegex);
                $actualName = preg_replace("/$nextRegex/", "", $actualName);

                $name = "$namespace$actualName";
                echo "Qualified name: $name\n";

               switch($type){
                   case "abstractClass": {
                       return $this->loadAbstractClass($name);
                   }
                   case "trait": {
                       return $this->loadTrait($name);
                   }
                   case "interface": {
                       return $this->loadInterface($name);
                   }
                   case "constantsClass": {
                       return $this->loadConstantsClass($name);
                   }
                   default: {
                        return $this->loadClass($name);
                   }
               }
            }
        }

        /**
         * replace load in the autoload function of this class
         * this function's name to see the load time.
         * @param mixed $name
         * 
         * @return [type]
         */
        public function loadAndShowTime($name){
            $start = microtime(true);
            $this->load($name);
            $end = microtime(true);
            echo "\n(Loading $name took " . $end - $start . " ms)\n";
        }

        /**
         * registers the vendor autoloader if it exists and registers our autoloading function
         */
        public function autoLoad(){
            #setting vendor autoloading
            if(is_dir(__DIR__."/vendor")){
                require (__DIR__."/vendor/autoload.php");
            }   

            spl_autoload_register([$this, "load"]);
            # echo "Autoloading...\n";
        }

        /**
         * Converts a camel case name into a ZAS qualified name.
         * for example, SomeNamespace/someFile will return some-namespace/some-file.
         * However, the file separator is specified in the zasconfig.json.
         * 
         * takes O(n) time.
         * @param string $name
         * 
         * @return string
         */
        public function toZasName(string $name, string $separator = "-"): string{
            $name = trim($name);
            
            $newStr = "";
            $pastChar = false;
            
            for($i = 0; $i < strlen($name); $i++){
            
                $char = $name[$i];
            
                if($this->isUpper($char)){

                    $char = $this->lower($char);
                    if($pastChar) $newStr .= $separator;

                }
            
                $newStr .= $char;
                $pastChar = $this->isLetter($char);
            }

            return $newStr;
        }

        /**
         * Checks if a letter is an upper case letter
         * @param string $char character
         * 
         * @return bool
         */                 
        public function isUpper($char){
            $c = ord($char);
            if($c >= 65 && $c <= 90) return true;

            return false;
        }

        /**
         * converts a letter to an upper case letter
         * @param string $char character
         * 
         * @return string
         */
        public function upper($char){
            if(!static::isLower($char)) return $char;

            $c = ord($char);
            return chr($c - ord('a') + ord('A'));
        }

        /**
         * Checks if a character is lower case
         * @param string $char character
         * 
         * @return bool
         */
        public function isLower($char){
            $c = ord($char);
            if($c >= 97 && $c <= 122) return true;

            return false;
        }

        /**
         * converts a letter to lower case
         * @param string $char character
         * 
         * @return string
         */
        public function lower($char){
            if(!static::isUpper($char)) return $char;

            $c = ord($char);
            return chr($c + ord('a') - ord('A'));
        }


        /**
         * Returns true if the character is an ASCII letter
         * @param string $char character
         * 
         * @return bool
         */
        public function isLetter($char){
            return static::isUpper($char) || static::isLower($char);
        }

    }

?>