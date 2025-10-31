<?php
/**
    Performs parameter checks common to several commands.

    @license    GPL - conforms to file LICENCE located in the root directory of current repository.
    @copyright  Thierry Graff
    @history    2025-10-22 13:56:56+02:00, Thierry Graff : Creation
**/

namespace tigsite\commands\shared;

class CheckParams {
    
    // ******************************************************
    /**
        @param  $params Parameters received by the function execute() of a command
    **/
    public static function check(&$params){
        if(!isset($params['site'])){
            throw new \Exception("MISSING PARAMETER: \$params['site']");
        }
        if(!isset($params['command'])){
            throw new \Exception("MISSING PARAMETER: \$params['command']");
        }
    }
    
}// end class
