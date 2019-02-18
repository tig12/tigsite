<?php
/******************************************************************************

Used for commands that need to deal with strings that depend on the currently processed html file.
A special syntax permits to replace strings located between {{ and }} by strings computed by this class

Supported strings :
- {{path-to-root}} : relative html path between file currently processed and root of the site.

@license    GPL
@history    2019-02-18 16:15:29+01:00 : Creation
********************************************************************************/

class expandVariables{
    
    // ******************************************************
    /**
        @param  $subject    ThePiece of html that may contain strings to expanded
        @param  $params    Depend on the strings that need to be expanded
                               See particular functions to have the list of required parameters.
    **/
    public static function expand($subject, $params){
        preg_match_all('/\{\{(.*?)\}\}/sm', $subject, $matches);
        $translate = [];
        for($i=0; $i < count($matches[0]); $i++){
            switch($matches[1][$i]){
            	case 'path-to-root' : 
            	    if(isset($translate['{{path-to-root}}'])){
            	        continue;
            	    }
            	    $translate['{{path-to-root}}'] = self::path_to_root($params);
            	break;
            }
        }
        return strtr($subject, $translate);
    }
    
    
    // ******************************************************
    /**
        Returns the replacement string for {{path-to-root}} variable
        @param  $params Associative array with the following keys :
            - 'root-dir'
            - 'current-file'
    **/
    private static function path_to_root($params){
        if(!isset($params['root-dir'])){
            throw new Exception("Missing \$params['root-dir']");
        }
        if(!isset($params['current-file'])){
            throw new Exception("Missing \$params['current-file']");
        }
        if(strpos($params['root-dir'], $params['current-file']) != 0){
            throw new Exception("Incoherence between \$params['root-dir'] and \$params['current-file']");
        }
        $relative = str_replace($params['root-dir'], '', $params['current-file']);
        $parts = explode('/', $relative);
        $n = count($parts) - 2; // -2 because -1 for first / and -1 for last part of the path
        return str_repeat('../', $n);
    }
    
}// end class
