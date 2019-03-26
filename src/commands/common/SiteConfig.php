<?php
/******************************************************************************
    Site configuration management
    
    @license    GPL
    @history    2019-02-18 12:01:41+01:00 Thierry Graff, Creation
********************************************************************************/

class SiteConfig{

    // ******************************************************
    /**
        Computes a site configuration : checks the syntax, fills default values and returns a correct config. 
        @param  $config Configuration contained in config.yml of a site, in an array
        @return  An array containing the configuration with default values filled
        @throws  Exception if a required directive is missing or invalid.
    **/
    public static function compute($config){
        if(!isset($config['location'])){
            throw new Exception("Missing \$config['site']['location']");
        }
        if(!isset($config['exclude'])){
            $config['exclude'] = [];
        }
        return $config;
    }
    
}// end class

