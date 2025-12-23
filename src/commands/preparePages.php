<?php
/**
    Creates a set of pages ready to be written.

    @license    GPL - conforms to file LICENCE located in the root directory of current repository.
    @copyright  Thierry Graff
    @history    2025-12-18 00:02:04+01:00, Thierry Graff : Creation
**/

namespace tigsite\commands;

use tigsite\commands\shared\SiteConfig;
use tigsite\commands\shared\ExpandVariables;
use tigsite\commands\shared\CheckParams;
use tiglib\patterns\command\Command;

class preparePages implements Command {
    
    /* 
    const DEFAULT_PARAMS = [
        'action'            => 'save',
    ];
    */
    
    /** Cleaned version of parameter $params passed to execute() **/
    public static array $params = [];
    
    /**
        Structured array containing the rubrics hierarchy.
        linux:
            - system
    **/
    public static array $rubrics = [];
    
    
    /** 
        @param  $params Associative array that MUST contain the following keys :
            - 'site' (required) :
                associative array ; see format in docs/
            - 'command' (required) :
                - 'page-template' (required)
                    Path to the html file used to create new pages.
                    ex: docs/x-template/page.html
                - 'pages' (required)
                Relative url (from root directory) of pages to be created.
                Array of strings
        @throws Exception in case of bad parameter
        
        @todo Add parameters "config-file" and "command-file" (only useful for messages in parameter checking)
    **/
    public static function execute($params=[]){
//echo "\n"; print_r($params); echo "\n"; exit;
        //
        // check parameters
        //
        CheckParams::check($params);
        $params['site'] = SiteConfig::compute($params['site']);
        if(!isset($params['command']['pages'])){
            throw new \InvalidArgumentException("MISSING REQUIRED PARAMETER pages'");
        }
        // global vars
        self::$params = $params;
        
        self::computeRubrics();
        
        foreach($params['command']['pages'] as $page){
            echo "$page\n";
        }
    }
    
    /**
        @param  $
    **/
    public static function computeRubrics() {
        foreach($params['command']['pages'] as $page){
            echo "$page\n";
        }
//        echo "\n"; print_r(self::$params); echo "\n";
exit;
    }
    
}// end class