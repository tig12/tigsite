<?php
/********************************************************************************
    CLI (command line interface) to use tigsite
    
    usage : php run-gauquelin5.php
            and follow the instructions
    
    @license    GPL
    @copyright  Thierry Graff
    @history    2019-02-02 02:40:27+01:00, Thierry Graff : creation
********************************************************************************/

$USAGE = <<<USAGE


usage : 
    php {$argv[0]} <site> <action>
Examples :
    php {$argv[0]} tig12 makenav maths/maths.html # Generate code to update site navigation in page maths/maths.html
    <action> : voir src/php/commands/ 

USAGE;

// check arguments
if(count($argv) != 3){
    die($USAGE);
}

// check serie
$serie = $argv[1];
if(!in_array($serie, $series)){
    echo "!!! INVALID SERIE !!! : '$serie' - possible choices : '$series_str'\n";
    exit;
}

// check action
$action = $argv[2];
if(!in_array($action, $series_actions[$serie])){
    echo "!!! INVALID ACTION FOR SERIE $serie !!! : - possible choices : '" . implode("' or '", $series_actions[$serie]) . "'\n";
    exit;
}


//
// run
//
define('DS', DIRECTORY_SEPARATOR);

require_once __DIR__ . DS . 'src' . DS . 'init' . DS . 'init.php';

use gauquelin5\Gauquelin5;
try{
    echo Gauquelin5::action($action, $serie); /// here run action ///
}
catch(Exception $e){
    echo 'Exception : ' . $e->getMessage() . "\n";
    echo $e->getFile() . ' - line ' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}

