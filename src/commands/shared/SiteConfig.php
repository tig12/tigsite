<?php
/**
    Site configuration management.
    Site configuration comes from sites/<my site>/config.yml
    
    @license    GPL
    @history    2019-02-18 12:01:41+01:00, Thierry Graff : Creation
**/

namespace tigsite\commands\shared;

use tiglib\filesystem\rscandir;

class SiteConfig {

    /**
        Computes a site configuration : checks the syntax, fills default values and returns a correct config. 
        @param      $config Configuration contained in config.yml of a site, in an array
        @return     An array containing the configuration with default values filled
        @throws     Exception if a required directive is missing or invalid.
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
    
    /**
        Computes the absolute paths of the files specified by a given site config.
        @param      $siteConfig     Associative array
                        Configuration contained in config.yml of a site.
        @param      $command        Associative array
                        Command (= content of a yaml command file of a site)
                        May contain a key 'exclude'.
        @return     Regular array of absolute paths.
        @pre        $siteConfig is valid and contains the required entries.
                    No validity check is done here.
    **/
    public static function computeFiles($siteConfig, $command) {
        $excludes = [];
        foreach($siteConfig['exclude'] as $exclude){
            $excludes[] = $siteConfig['location'] . DS . $exclude;
        }
        foreach($command['exclude'] as $exclude){
            $excludes[] = $siteConfig['location'] . DS . $exclude;
        }
        $rscandirParams = [
            'include'       => '*.html',
            'exclude'       => $excludes,
            'return-dirs'   => false,
        ];
        return rscandir::execute($siteConfig['location'], $rscandirParams);
    }
    
}// end class

