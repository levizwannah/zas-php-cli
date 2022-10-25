<?php
/*
|-------------------------------------------------------------
| Comments with #.# are required by `zas` for code insertion.
|-------------------------------------------------------------
*/

namespace Zas;

use Exception;

#uns#


/**
 * Returns a config value.
 * Use `Config::configName('key')` to get a config value.
 * You can also pass multiple values as argument, returning
 * an array of key-value pairs.
 * Use `Config::configName()` to get the whole config array.
 * For example, Config::database('hostName') returns 'localhost_db';
 * `Config::database()` returns the whole config object;
 * `Config::database('key1', 'key2', ...)` returns and array or `['key1' => 'key1 value', ...]`;
 * **Note**: Any PHP data type could be returned based on the value of the 
 * config key. If the value is an array, then expect an array, etc.
 * 
 * 
 */
class Config   {

    /**
     * Caches configs if its loaded 2 times
     * @var array
     */
    public static $cache = [];

    /**
     * The number of times that a config was called
     * @var array<string,int>
     */
    public static $callCount = [];

    /**
     * The maximum time we should load a config before caching it.
     */
    const MAX_CACHE_MISS = 2;

    public $zasConfig;
    public $rootDir;

    public static $configObj;

    // use traits
		use FileUtilTrait;
		#ut#



    public function __construct(){
      $this->zasConfig = $this->getZasConfig();
      $root = $this->zasConfig->directories->root;

      $parentDir = preg_split("/$root/", __DIR__, 2);
      $this->rootDir = $parentDir[0].DIRECTORY_SEPARATOR."$root";
    }

    /**
     * @param mixed $name
     * @param mixed $arguments
     * 
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
      if(!isset(static::$cache[$name])){
        if(!isset(static::$configObj)){
          static::$configObj = new Config();
        }
        
        $zas = static::$configObj->zasConfig;
        $tmpName = $zas->path->configs . DIRECTORY_SEPARATOR . static::$configObj->toZasName($name, $zas->fileNameSeparator) . "." . $zas->extensions->configs;
        $fullPath = static::$configObj->getFullPath($tmpName);

        file_exists($fullPath) or throw new Exception("$name Configuration does not exist. Please create it");

        $config = require($fullPath);
  
        static::$callCount[$name] = isset(static::$callCount[$name]) ? static::$callCount[$name] + 1 : 1;
        if(static::$callCount[$name] >= Config::MAX_CACHE_MISS) static::$cache[$name] = $config;
      
      }
      else{
        $config = static::$cache[$name];
      }

      if(empty($arguments)) return $config;

      if(count($arguments) == 1){
        $config[$arguments[0]] || throw new Exception("Config for '$name' does not have a key named '{$arguments[0]}'");

        return $config[$arguments[0]];
      }

      $data = [];

      foreach($arguments as $key){
        $data[$key] = $config[$key] or throw new Exception("Config for '$name' does not have a key named '$key'");
      }

      return $data;
    }
}

?>