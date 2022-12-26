<?php
/*
| Do all necessary autoloading here.
| Vendor autoloading should be done here also.
*/


require(__DIR__ . "/zas-auto-loader.class.php");

/*------------------------------------------------
|    GLOBAL VARIABLES
|--------------------------------------------------
*/

$zasConfig = $zasConfig ?? json_decode(file_get_contents(__DIR__ . "/zas-config.json"));

$autoloader = new ZasAutoLoader();
$autoloader->autoLoad();
    
?>