<?php
/******************************************************************************

@license    GPL
@author    Thierry Graff
@history    2019-02-18 05:39:28+01:00 : Creation
********************************************************************************/

$DIR_SRC = dirname(__DIR__);

require_once 'jthAutoload_nonamespace.php';

jthAutoload_nonamespace::init([
    $DIR_SRC . '/lib',
    $DIR_SRC . '/commands',
]);

spl_autoload_register(['jthAutoload_nonamespace', 'autoload']);
