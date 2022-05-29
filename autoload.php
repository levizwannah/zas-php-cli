<?php
    /*
    | Do all necessary autoloading here.
    | Vendor autoloading should be done here also.
    */
    require(__DIR__ . "/zas-auto-loader.class.php");

    $autoloader = new ZasAutoLoader();
    $autoloader->autoLoad();
    
?>