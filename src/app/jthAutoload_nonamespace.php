<?php
// Software released under the General Public License (version 2 or later), available at
// http://www.gnu.org/copyleft/gpl.html
/******************************************************************************
    JeTheme autoload for classes that are not namespaced
    Calling code must first call :
    self::init()
    and then :
    spl_autoload_register(['jthAutoload_nonamespace', 'autoload']);
    
    The directories passed to self::init() are recursively scanned to add all classes located in subdirectories.
    
    @licence    GPL
    @copyright  jetheme.org
    @history    2009-11-23 06:19:47, Thierry Graff : Creation
    @history    2017-03-08 11:10:30+01:00, Thierry Graff : clean, rename for coherence with other autoload
********************************************************************************/
class jthAutoload_nonamespace{
    
    /** Root directories where classes are located */
    public static $dirs = [];
    
    /** array(class names => absolute paths of php files) */
    private static $array = [];
    
    
    // ******************************************************
    /**
        Builds self::$array
        @param  $dirs array of strings : root directories where classes are located
    **/
    public static function init($dirs){
        self::$dirs = $dirs;
        foreach(self::$dirs as $dir){
            self::load_dir($dir);
        }
//echo "<pre>"; print_r(self::$array); echo "</pre>"; exit;
    }
    
    
    //********************* autoload ******************************
    /**
        ze autoload method
        @param $className name of class to load
    **/
    public static function autoload($className){
        if(isset(self::$array[$className])){
            require_once self::$array[$className];
        }
        else{
            // comment the following line if several autoloads are in the spl stack
            //error_log(self::$ERROR_MSG . "class name = '$className'");
        }
    }
    
    
    // ******************************************************
    /**
        Recursively scans a directory and adds the entries to self::$array
        @todo   should have an exclude property to avoid hacking the code when specific dirs must be excluded
    **/
    private static function load_dir($dir){
        // dirty hacks to avoid loading some classes, locally handled by an other autoloader
        if(strpos($dir, 'phpgedcom/library') !== false){
            return;
        }
        if(strpos($dir, 'twig/Twig') !== false){
            return;
        }
        global $res;
        if($handler = opendir($dir)) {
            while(($sub = readdir($handler)) !== false) {
                if ($sub != "." && $sub != ".."){
                    $file = "$dir/$sub";
                    if(is_file($file)){
                        $filename = basename($file);
                        //
                        // personal filters can be added here
                        if(substr($filename, -4) != '.php'){
                            continue;
                        }
                        if(substr($filename, 0, 2) == 'z.'){
                            continue;
                        }
                        /* if($filename != ucFirst($filename)){
                            continue; // convention : class names start by upper case
                        } */
                        // end personal filters can be added here
                        //
                        $classname = substr(basename($file), 0, -4);
                        // add filename to class variable
                        self::$array[$classname] = $file;
                    }elseif(is_dir($file)){
                        //
                        // personal filters can be added here
                        if($sub == '1.test-files' || $sub == '1.doc-files'){
                            continue;
                        }
                        $dirname = basename($file);
                        if(substr($dirname, 0, 2) == 'z.'){
                            continue;
                        }
                        // end personal filters can be added here
                        //
                        self::load_dir($file); // recursive here
                    }
                }
            }   
            closedir($handler);
        }
    }
    
    
}// end class
