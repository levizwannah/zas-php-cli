<?php
/**
* The ZAS commandline helper for api development.
*/

namespace Zas;

#uns#

class ZasHelper{

    /**
     * @var object $zasConfig Contains the configuration in the zas-config.json
     */
    protected $zasConfig;
    protected $rootDir;
    public static $configPath = __DIR__. "/../../../../../../zas-config.json";


    # traits here
    use NsUtilTrait;
    #ut#

    /**
     * Loads the configuration of Zas
     */
    public function __construct()   
    {
        # Use the Zas configuration to set the extensions and path
        $this->loadConfig();
        $root = $this->zasConfig->directories->root;

        $parentDir = preg_split("/$root/", __DIR__, 2);
        $this->rootDir = $parentDir[0].DIRECTORY_SEPARATOR."$root";
    }

    /**
     * Loads the zas configuration from the zas-config.json
     */
    protected function loadConfig(){
        if(file_exists(ZasHelper::$configPath)){
            $config = file_get_contents(ZasHelper::$configPath);
        }
        else{
            $config = file_get_contents(__DIR__. "/../../../zas-config.json");
        } 
        $this->zasConfig = json_decode($config);
    }

    /**
     * Update root path in the zas-config.json
     */
    protected function updateRootPath(){
        $curRoot = getcwd();
        $root = preg_split("/[\\".DIRECTORY_SEPARATOR."\/]/", $curRoot);
        $rIndex = array_key_last($root);
        $finalRoot = $root[$rIndex];
        $fileContent = file_get_contents(self::$configPath);
        $str = preg_replace("/[\"']root[\"']:\s*[\"']\w*[\"'],/", "\"root\": \"$finalRoot\",", $fileContent);
        
        file_put_contents(self::$configPath, $str);

        ZasHelper::log("updated root path to: $finalRoot");
    }
    

    /**
     * Prints the commands for ZAS
     * @return void
     */
    public function printHelp(){

        echo file_get_contents(__DIR__ . "/../../../cmd.txt");
        $customCommands = $this->rootDir . DIRECTORY_SEPARATOR . "cmd.txt";
        if(file_exists($customCommands)){
            echo "\n==========================================\n\n";
            echo file_get_contents($customCommands);
        }
    }
    
    #----------------------------------------------------
    # Functions for executing different commands
    #----------------------------------------------------

    /**
     * Executes the make command
     * @param int $argc
     * @param array $argv
     * 
     * @return void
     */
    protected function execMake(int $argc, array $argv){
        $maker = new ContainerMaker($this->zasConfig);
        $updater = new Updater($this->zasConfig);

        $container = strtolower($argv[2] ?? "");
        $containerName = $argv[3] ?? "";
        if(empty($containerName))
        {
            ZasHelper::log("Error::Name ERROR: No name");
            return;
        }

        $functionsToImpl = [];

        $force = false;
        $forceIndex = array_search(ZasConstant::DASH_DASH_F, $argv);
        if($forceIndex !== false) {
            $force = true;
            unset($argv[$forceIndex]);
            $argv = array_values($argv);
            $argc = count($argv);
        }
        
        switch($container){
            case ZasConstant::ZC_CLASS:
                {
                    

                    $interfaces = $traits = [];
                    $parentClass = "";

                    $isParent = $isTrait = $isInterface = false;
                    $states = [&$isParent, &$isTrait, &$isInterface];
                    
                    $setState = function(array &$states, int $index){
                        foreach($states as  $i => &$state){
                                $state = false;
                                if($i == $index) $state = true; 
                        }
                    };

                    for($i = 4; $i < $argc; $i++){
                        
                        $currentVal = $argv[$i];

                        # check for -i, -p or -t
                        switch($currentVal){
                            case ZasConstant::DASH_P:
                                {
                                    $setState($states, 0);
                                    continue 2;
                                }
                            case ZasConstant::DASH_T:
                                {
                                    $setState($states, 1);
                                    continue 2;
                                }
                            case ZasConstant::DASH_I:
                                {
                                    $setState($states, 2);
                                    continue 2;
                                }
                        }

                        # set the parent class
                        switch(true){
                            case $isParent:
                                {
                                    $parent = (object)$maker->makeSpecifiedClass($currentVal);
                                    $functionsToImpl = array_merge($functionsToImpl, $maker->getFuncToImplement($parent->filePath));
                                    $parentClass = $parent->actualName;
                                    break;
                                }
                            case $isTrait:
                                {
                                    $trait = (object)$maker->makeTrait($currentVal);
                                    $traits[] = $trait->actualName;

                                    break;
                                }
                            case $isInterface:
                                {
                                    $interface = (object) $maker->makeInterface($currentVal);
                                    $interfaces[] = $interface->actualName;
                                    $functionsToImpl = array_merge($functionsToImpl, $maker->getFuncToImplement($interface->filePath));
                                    break;
                                }
                        }
                    }

                    $createdClass =  ((object)$maker->makeClass($containerName, $parentClass,$interfaces, $traits, $force));
                    $updater->addFunc($functionsToImpl, $createdClass->filePath);

                    ZasHelper::log( "\nSuccessfully created class: ".
                        $createdClass->actualName
                    );
                    ZasHelper::log("Path: ". $createdClass->filePath);

                    break;
                }
            case ZasConstant::ZC_INFC:
                {
                    $interfaces = [];
                    $isIntList = false;
                    for($i = 4; $i < $argc; $i++){
                        
                        $currentVal = $argv[$i];

                        switch($currentVal){
                            case ZasConstant::DASH_E:
                                {
                                    $isIntList = true;
                                    continue 2;
                                }
                        }

                        switch(true){
                            case $isIntList:
                                {
                                    # make every interface seen
                                    $interface = (object)$maker->makeInterface($currentVal);
                                    $interfaces[] = $interface->actualName;
                                    break;
                                }
                        }
                    }

                    $madeInterface = ((object)$maker->makeInterface($containerName, $interfaces, $force));
                    ZasHelper::log( "\nSuccessfully created Interface: ".
                        $madeInterface->actualName
                    );
                    ZasHelper::log("Path: ". $madeInterface->filePath);

                    break;
                }
            case ZasConstant::ZC_TRAIT:
                {
                    $traits = [];
                    $isTraitList = false;
                    for($i = 4; $i < $argc; $i++){
                        
                        $currentVal = $argv[$i];

                        switch($currentVal){
                            case ZasConstant::DASH_T:
                                {
                                    $isTraitList = true;
                                    continue 2;
                                }
                        }

                        switch(true){
                            case $isTraitList:
                                {
                                    # make every trait found
                                    $trait = (object)$maker->makeTrait($currentVal);
                                    $traits[] = $trait->actualName;
                                    break;
                                }
                        }
                    }
                    $madeTrait = ((object)$maker->makeTrait($containerName, $traits, $force));
                    ZasHelper::log( "\nSuccessfully created Trait: ".
                        $madeTrait->actualName .
                        "\nPath: ". $madeTrait->filePath
                    );

                    break;
                }
            case ZasConstant::ZC_CONST:
                {
                    $parentClass = "";
                    $isParent = false;
                    for($i = 4; $i < $argc; $i++){
                        
                        $currentVal = $argv[$i];

                        switch($currentVal){
                            case ZasConstant::DASH_P:
                                {
                                    $isParent = true;
                                    continue 2;
                                }
                        }

                        if($isParent){
                            $parent = (object)$maker->makeConstClass($currentVal);
                            $parentClass = $parent->actualName;
                        }
                    }

                    $madeConst =   ((object)$maker->makeConstClass($containerName, $parentClass, $force));
                    ZasHelper::log(
                        "\nSuccessfully created Constants Class: ".
                        $madeConst->actualName
                        ."\nPath: ".$madeConst->filePath . "\nNote: Constants Class Constructor is  protected by default"
                    );

                    break;
                }
            case ZasConstant::ZC_ABCLASS:
                {
                    $interfaces = $traits = [];
                    $parentClass = "";

                    $isParent = $isTrait = $isInterface = false;
                    $states = [&$isParent, &$isTrait, &$isInterface];
                    
                    $setState = function(array &$states, int $index){
                        foreach($states as  $i => &$state){
                                $state = false;
                                if($i == $index) $state = true; 
                        }
                    };

                    for($i = 4; $i < $argc; $i++){
                        
                        $currentVal = $argv[$i];

                        # check for -i, -p or -t
                        switch($currentVal){
                            case ZasConstant::DASH_P:
                                {
                                    $setState($states, 0);
                                    continue 2;
                                }
                            case ZasConstant::DASH_T:
                                {
                                    $setState($states, 1);
                                    continue 2;
                                }
                            case ZasConstant::DASH_I:
                                {
                                    $setState($states, 2);
                                    continue 2;
                                }
                        }

                        # set the parent class
                        switch(true){
                            case $isParent:
                                {
                                    $parent = (object)$maker->makeAbstractClass($currentVal);
                                    $parentClass = $parent->actualName;
                                    break;
                                }
                            case $isTrait:
                                {
                                    $trait = (object)$maker->makeTrait($currentVal);
                                    $traits[] = $trait->actualName;

                                    break;
                                }
                            case $isInterface:
                                {
                                    $interface = (object) $maker->makeInterface($currentVal);
                                    $interfaces[] = $interface->actualName;
                                    $functionsToImpl = array_merge($functionsToImpl, $maker->getFuncToImplement($interface->filePath));
                                    break;
                                }
                        }
                    }
                    $madeAbstract = ((object)$maker->makeAbstractClass($containerName, $parentClass,$interfaces, $traits, $force));
                    $updater->addFunc($functionsToImpl, $madeAbstract->filePath);
                    ZasHelper::log(
                        "\nSuccessfully made Abstract class: ".
                        $madeAbstract->actualName .
                        "\nPath: ". $madeAbstract->filePath
                    );

                    break;
                }
            case ZasConstant::ZC_ACTOR:
                {
                    $parentDirName = "";
                    $actorTypeDir = "";
                    $isDir = false;

                    $isNothing = $isParent = $isType =  false;
                    $states = [&$isNothing, &$isParent, &$isType];

                    $setState = function(array &$states, int $index){
                        foreach($states as  $i => &$state){
                                $state = false;
                                if($i == $index) $state = true; 
                        }
                    };

                    for($i = 4; $i < $argc; $i++){
                        $currentVal = $argv[$i];

                        if($currentVal == ZasConstant::DASH_D){
                            $isDir = true;
                            $setState($states, 0);
                            continue;
                        }

                        # check for -p, -d or -in
                        switch($currentVal){
                            case ZasConstant::DASH_P:
                                {
                                    $setState($states, 1);
                                    continue 2;
                                }
                            case ZasConstant::DASH_IN:
                                {
                                    $setState($states, 2);
                                    continue 2;
                                }
                        }

                        if($isParent){
                            $parentDirName = $currentVal;
                            $setState($states, 0);
                            continue;
                        }

                        if($isType){
                            switch($currentVal){
                                case ZasConstant::WORD_FORE:
                                    {
                                        $actorTypeDir = $this->zasConfig->path->actors->foreground;
                                        $setState($states, 0);
                                        break;
                                    }
                                case ZasConstant::WORD_BACK:
                                    {
                                        $actorTypeDir = $this->zasConfig->path->actors->background;
                                        $setState($states, 0);
                                        break;
                                    }
                                default:
                                    {
                                        ZasHelper::log("ACTOR::ERROR: choose fore or back after -in: '$currentVal' given");
                                        return;
                                    }
                            }

                            $setState($states, 0);
                            continue;
                        }
                    }

                    if($actorTypeDir == ""){
                        ZasHelper::log("ERROR::ACTOR: Actor type must be 'fore' or 'back'");
                        return;
                    }

                    $containerName = $this->toZasName($containerName, $this->zasConfig->fileNameSeparator);
                    $parentDirName= $this->toZasName($parentDirName, $this->zasConfig->fileNameSeparator);

                    $maker = new FileMaker($this->zasConfig);

                    if($isDir){
                        $maker = new FolderMaker($this->zasConfig);
                        $dirName = $maker->in($actorTypeDir)->make($parentDirName.DIRECTORY_SEPARATOR.$containerName);

                        # make the setup file
                        $maker = new FileMaker($this->zasConfig);
                        $file = (object)$maker->in($dirName)->make($this->zasConfig->setupFileName, "");

                        if(!$file->exists){
                            $tmpContent = file_get_contents($this->getFullPath($this->zasConfig->templatePath->setup));

                            $setupFile = "master.setup";
                            if(!file_exists($dirName."/../$setupFile.php")){
                                $setupFile = $this->zasConfig->setupFileName;
                            }

                            $tmpContent = str_replace("[SN]", $setupFile, $tmpContent);
                            file_put_contents($file->fullPath, $tmpContent); 
                        }
                    }
                    else{
                        $file = (object) $maker->in($actorTypeDir)->make($containerName, $parentDirName);
                        
                        $tmpPath = $this->getFullPath($this->zasConfig->templatePath->actors->all);
                        
                        $tmpContent = file_get_contents($tmpPath);

                        $setupFile = "master.setup";
                        if(!file_exists(dirname($file->fullPath) . "/../$setupFile.php")){
                            $setupFile = $this->zasConfig->setupFileName;
                        }

                        $tmpContent = str_replace("[SN]", $setupFile, $tmpContent);
                        file_put_contents($file->fullPath, $tmpContent);
                    }

                    

                    ZasHelper::log("Successfully made $containerName actor ". (($isDir)? "directory":"file"). " in $actorTypeDir");

                    break;
                }
            case ZasConstant::ZC_SUPPORTER:
                {
                    $parentDirName = "";
                    $isDir = false;

                    $isNothing = $isParent =  false;
                    $states = [&$isNothing, &$isParent];

                    $setState = function(array &$states, int $index){
                        foreach($states as  $i => &$state){
                                $state = false;
                                if($i == $index) $state = true; 
                        }
                    };

                    for($i = 4; $i < $argc; $i++){
                        $currentVal = $argv[$i];

                        if($currentVal == ZasConstant::DASH_D){
                            $isDir = true;
                            $setState($states, 0);
                            continue;
                        }

                        # check for -p
                        switch($currentVal){
                            case ZasConstant::DASH_P:
                                {
                                    $setState($states, 1);
                                    continue 2;
                                }
                        }

                        if($isParent){
                            $parentDirName = $currentVal;
                            $setState($states, 0);
                            continue;
                        }

                    }
                    $containerName = $this->toZasName($containerName, $this->zasConfig->fileNameSeparator);
                    $parentDirName= $this->toZasName($parentDirName, $this->zasConfig->fileNameSeparator);

                    $maker = new FileMaker($this->zasConfig);

                    if($isDir){
                        $maker = new FolderMaker($this->zasConfig);
                        $maker->in($this->zasConfig->path->supporters)->make($parentDirName.DIRECTORY_SEPARATOR.$containerName);
                    }
                    else{
                        $file = (object) $maker->in($this->zasConfig->path->supporters)->make($containerName, $parentDirName);
                        file_put_contents($file->fullPath, ZasConstant::TXT_PHP_INIT);
                    }

                    

                    ZasHelper::log("Successfully made $containerName supporter ". (($isDir)? "directory":"file"). " in $parentDirName");

                        break;
                }
            case ZasConstant::ZC_CONFIG:
                {
                    
                    $tmpContent = file_get_contents($this->getFullPath($this->zasConfig->templatePath->configs));
                    $tmpContent = str_replace("[CN]", $containerName, $tmpContent);

                    $containerName = $this->toZasName($containerName, $this->zasConfig->fileNameSeparator);
                    $maker = new FileMaker($this->zasConfig);
                    $file = (object) $maker->in($this->zasConfig->path->configs)->make($containerName, "", $this->zasConfig->extensions->configs);

                    file_put_contents($file->fullPath, $tmpContent);

                    

                    ZasHelper::log("Successfully made $containerName config file in {$this->zasConfig->path->configs}");
                    break;
                }
            default:
                {
                    ZasHelper::log("Command incomplete:: please select the container");
                    $this->printHelp();
                }
            
        }
    }

    /**
     * Executes the run command for background files
     * @param int $argc
     * @param array $argv
     * 
     * @return void
     */
    protected function run(int $argc, array $argv){
        if(!isset($argv[2])){
            exit(
                Cli::log("You must provide a file path")
            );
        }

        $path = strtolower($argv[2]);

        $argArray = [];
        $isArg = false;

        for($i = 3; $i< $argc; $i++){
            $currentVal = $argv[$i];

            if($currentVal == ZasConstant::DASH_DASH_ARG){
                $isArg = true;
                continue;
            }

            if(!$isArg) continue;            
            $argArray[] = $currentVal;
        }
        $runner = new FileRunner($this->zasConfig);
        $runner->withArg($argArray)->runFile($path);
        
        return $this;
    }

    /**
     * Write text to the console
     * @param string|array $txt
     * 
     * @return void
     */
    public static function log($txt){
        if(is_array($txt)){
            foreach($txt as $cnt){
                ZasHelper::log($cnt);
            }
            return;
        }
        echo "$txt\n";
    }
}

?>