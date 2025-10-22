<?php
/**
    Page configuration management.
    Page configuration is a piece of yaml included in a html comment
    that must contain a 'tigsite' (self::MARKER) directive.
    
    Example of inclusion of page configuration :
    In the html page, between <header> and </header>
    <!-- 
    tigsite:
        sidebar-right: astronomy/sidebar-astronomy.html
    -->
    
    @license    GPL
    @history    2019-03-26 12:17:29+01:00, Thierry Graff : Creation
**/

namespace tigsite\commands\shared;

class PageConfig {

    /** String used to identify the page configuration in a html comment **/
    const MARKER = 'tigsite';
    
    /**
        Computes a page configuration : extracts the yaml from the page, checks the syntax, fills default values and returns a correct config.
        If the page does not contain configuration, returns an empty array.
        @param  $page Absolute path to a html file that may contain a configuration
        @return  An array containing the configuration with default values filled
        @throws Exception if a required directive is missing or invalid.
    **/
    public static function compute($page){
        $content = file_get_contents($page);
        $p = '#<!--\s*?\n(\s*' . self::MARKER . '\:.*?)-->#sm';
        preg_match($p, $content, $m);
        if(count($m) == 0){
            return []; // no page configuration found.
        }
        // eliminate useless white spaces at the beginning of lines
        // that may exist if the yaml embedded in the page is tabulated
        $pos = strpos($m[1], self::MARKER);
        $lines = explode(PHP_EOL, $m[1]);
        $config = '';
        foreach($lines as $line){
            if(trim($line) == ''){
                continue;
            }
            $config .= substr($line, $pos) . PHP_EOL;
        }

        $yaml = yaml_parse($config);
        return $yaml[self::MARKER];
    }
    
} // end class
