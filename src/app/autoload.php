<?php
/**
    Unique autoload code to include.
    
    @license    GPL
    @history    2024-02-27 17:38:41+02:00, Thierry Graff : Creation (copy and adapt g5 code)
**/

// autoloads for vendor code
$rootdir = dirname(dirname(__DIR__));
require_once implode(DS, [$rootdir, 'vendor', 'tig12', 'tiglib', 'autoload.php']);

/** 
    Autoload for tigsite namespace
**/
spl_autoload_register(
    function ($full_classname){
        $namespace = 'tigsite';
        if(strpos($full_classname, $namespace) !== 0){
            return; // not managed by this autoload
        }
        $root_dir = dirname(__DIR__); // root dir for this namespace
        $classname = str_replace($namespace . '\\', '', $full_classname);
        $classname = str_replace('\\', DS, $classname);
        $filename = $root_dir . DS . $classname . '.php';
        $ok = include_once($filename);
        if(!$ok){
            throw new \Exception("AUTOLOAD FAILS for class $full_classname");
        }
    }
);

/* 
// autoload for tigsite code
// TODO refactor tigsite to have namespaced code and change the autoload
$DIR_SRC = dirname(__DIR__);

require_once 'jthAutoload_nonamespace.php';

jthAutoload_nonamespace::init([
    $DIR_SRC . '/commands',
]);

spl_autoload_register(['jthAutoload_nonamespace', 'autoload']);
*/
