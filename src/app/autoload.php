<?php
/**
    Unique autoload code to include.
    
    @license    GPL
    @author    Thierry Graff
    @history    2019-02-18 05:39:28+01:00 : Creation
**/

// autoloads for vendor code
$rootdir = dirname(dirname(__DIR__));
require_once implode(DS, [$rootdir, 'vendor', 'tig12', 'tiglib', 'autoload.php']);


// autoload for tigsite code
// TODO refactor tigsite to have namespaced code and change the autoload
$DIR_SRC = dirname(__DIR__);

require_once 'jthAutoload_nonamespace.php';

jthAutoload_nonamespace::init([
    $DIR_SRC . '/lib',
    $DIR_SRC . '/commands',
]);

spl_autoload_register(['jthAutoload_nonamespace', 'autoload']);
