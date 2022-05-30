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
         * @param string $fileName
         * 
         * @return string
         */
        public function toZasName(string $fileName, string $separator = "-"): string{
            $fileName = trim($fileName);
            $length = strlen($fileName);
            
            $newStr = "";
            $firstCharMet = false;

            for($i = 0; $i < $length; $i++){

                if($firstCharMet && preg_match("/[A-Z]/", $fileName[$i])){
                    $newStr .= $separator . strtolower($fileName[$i]);
                    continue;
                }

                if(!$firstCharMet){
                    $firstCharMet = preg_match("/[a-zA-Z]/", $fileName[$i]) == true;
                }

                if($firstCharMet){
                    $firstCharMet = !preg_match("/[\/\\\]/", $fileName[$i]);
                }

                $newStr .= strtolower($fileName[$i]);
            }

            return $newStr;
        }

        /**
         * Returns the full path of a given path.
         * @param string $path
         * @param string $rootDirectory should be added if the 
         * class doesn't have the roodDir property present.
         * @return string
         */
        public function getFullPath(string $path, string $rootDirectory = ""){
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

                $parentDir = preg_split("/$root/", __DIR__);
                $rootDir = $parentDir[0].DIRECTORY_SEPARATOR."$root";

                if(file_exists("$rootDir$path") && !is_dir("$rootDir$path")){
                    # Cli::log("$rootDir$path exists");
                    return "$rootDir$path";
                }
            }

            return $fullPath;
        }

    }

?>