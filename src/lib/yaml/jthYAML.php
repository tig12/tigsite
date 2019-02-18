<?php 
// Software released under the General Public License (version 3 or later), available at
// http://www.gnu.org/copyleft/gpl.html
/********************************************************************************
    
    @license    GPL
    @copyright  jetheme.org
    @history    2012, Thierry Graff : Creation 
********************************************************************************/

class jthYAML{
    
    
    // ******************************************************
    /**
        Parses a YAML file and returns an associative array
        @param $str yaml file path or yaml string to parse
    **/
    public static function parse($str){
        // BUG (or feature ?) : dates YYYY-MM-DD are converted to unix timestamp
        $yaml = new sfYamlParser();
        if(is_file($str)){
            return $yaml->parse(file_get_contents($str));
        }
        return $yaml->parse($str);
    }// end parse
    
        
    // ******************************************************
    /**
        Converts a php array to YAML
        @param $array   php array to convert
        @param $filename If present, dumps the yaml in a file - may issue a warning if $filename is not writable
        @return if $filename = false, returns the resulting yaml string
                if $filename != false, returns the nb of bytes written or false if impossible to store
    **/
    public static function dump($array, $filename=false){
        $dumper = new sfYamlDumper();
        $yaml = $dumper->dump($array, 50);
        if($filename){
            return file_put_contents($filename, $yaml); // nb of bytes written or false
        }
        else{
            return $yaml;
        }
    }// end dump
    
    
}// end class

