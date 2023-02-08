<?php
/*
|-------------------------------------------------------------
| This setup file includes the parent setup file. The parent
| setup file could be the master.setup.php or another setup
| file.
|-------------------------------------------------------------
*/
(
    isset($loaded)
    &&
    isset($loaded[dirname(__DIR__)])
)
or
require(dirname(__DIR__). "/[SN].php");

$loaded[__DIR__] = 1;

// additional code ...

?>