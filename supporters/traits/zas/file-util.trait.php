<?php
    # Comments with #...# are required by `zas` for code insertion. Do not remove nor modify them!

    namespace Zas;

    #uns#


    trait FileUtilTrait {
        #ut#

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
            
                if(Character::isUpper($char)){

                    $char = Character::lower($char);
                    if($pastChar) $newStr .= $separator;

                }
            
                $newStr .= $char;
                $pastChar = Character::isLetter($char);
            }

            return $newStr;
        }

        /**
         * Returns the full path of a given path.
         * @param string $path
         * @param string $rootDirectory should be added if the 
         * class doesn't have the rootDir property present.
         * @return string
         */
        public function getFullPath(string $path, string $rootDirectory = ""){
            if(file_exists($path)) return $path;
            
            if(empty($rootDirectory)) $rootDirectory = $this->rootDir;

            $fullPath = $rootDirectory . $path;

            #if file does not exist using the project zas-config
            #then get local zas config and use it to test the project
            if(!file_exists($fullPath)){
                # Cli::log("$fullPath doesn't exists");

                $zasConf = json_decode(
                    file_get_contents(__DIR__ ."/../../../zas-config.json")
                );

                $root = $zasConf->directories->root;

                $parentDir = preg_split("/$root/", __DIR__, 2);
                $rootDir = $parentDir[0].DIRECTORY_SEPARATOR."$root";

                if(file_exists("$rootDir$path") && !is_dir("$rootDir$path")){
                    # Cli::log("$rootDir$path exists");
                    return "$rootDir$path";
                }
            }

            return $fullPath;
        }


        /**
         * Get's the Main ZAS config file
         * @return object
         */
        public function getZasConfig(){
            global $zasConfig;
            if($zasConfig) return $zasConfig;

            $file = __DIR__. "/../../../../../../zas-config.json";
            if(!file_exists($file)) $file = __DIR__ ."/../../../zas-config.json";

            return json_decode(file_get_contents($file));
        }

    }

?>