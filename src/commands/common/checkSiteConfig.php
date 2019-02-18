<?php
/******************************************************************************
    Checks the correctness of a site configuration file
    
    @license    GPL
    @history    2019-02-18 12:01:41+01:00 Thierry Graff, Creation
********************************************************************************/

class checkSiteConfig{

    // ******************************************************
    /**
        @param  $config Configuration contained in config.yml of a site, in an array
        @return  An array containing the configuration with default values filled
        @throws  Exception if a directive is missing or invalid
    **/
    public static function check($config){
        if(!isset($config['location'])){
            throw new Exception("Missing \$config['site']['location']");
        }
        if(!isset($config['exclude'])){
            $config['exclude'] = [];
        }
        return $config;
    }
    
}// end class

