<?php
/********************************************************************************
    CLI (command line interface) to use tigsite
    
    usage : php run-tigsite.php
            and follow the instructions
    
    @license    GPL
    @copyright  Thierry Graff
    @history    2019-02-02 02:40:27+01:00, Thierry Graff : creation
********************************************************************************/

define('DS', DIRECTORY_SEPARATOR);

$ROOT_DIR = __DIR__;                      

require_once $ROOT_DIR . DS . 'src' . DS . 'app' . DS . 'autoload.php';

$USAGE = <<<USAGE
Usage : 
    php {$argv[0]} <site> <action>
    <site> : must be a sub-directory of sites/ 
    <action> : must correspond to a yaml file of sites/<site>/commands/ 
Example :
    # Updates the footer of all site pages
    # Uses the command file sites/tig12.net/commands/replace-footer.yml
    php {$argv[0]} tig12.net replace-footer
    php {$argv[0]} g2 replace-sidebar
USAGE;

//
// check arguments
//
if(count($argv) != 3){
    echo "Invalid usage\n";
    die($USAGE);
}

$siteName = $argv[1];
$command = $argv[2];

$siteDir = $ROOT_DIR . DS . 'sites' . DS . $siteName;

if(!is_dir($siteDir)){
    echo "Wrong site name : directory sites/$siteName does not exist\n";
    exit;
}

$siteConfigFile = $siteDir . DS . 'config.yml';

if(!is_file($siteConfigFile)){
    echo "Missing site configuration file : file sites/$siteName/config.yml does not exist\n";
    exit;
}

$commandFile = $siteDir . DS . 'commands' . DS . $command . '.yml';

if(!is_file($commandFile)){
    echo "Missing command configuration file : file sites/$siteName/commands/$command.yml does not exist\n";
    exit;
}

//
// run
//
$config = [];
$config['site'] = yaml_parse_file($siteConfigFile);
$config['command'] = yaml_parse_file($commandFile);

if(!isset($config['command']['commandClass'])){
    echo "Missing entry 'commandClass' in $commandFile\n";
    exit;
}

$commandClass = 'tigsite\\commands\\' . $config['command']['commandClass'];
//echo "$commandClass\n"; exit;

if(!class_exists($commandClass)){
    echo "Entry 'commandClass' does not correspond to an existing command class in $commandFile\n";
    echo "Class not found : $commandClass\n";
    exit;
}

try{
    $commandClass::execute($config);
}
catch(Exception $e){
    echo 'Exception : ' . $e->getMessage() . "\n";
    echo $e->getFile() . ' - line ' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}

