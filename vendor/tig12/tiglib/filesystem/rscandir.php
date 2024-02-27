<?php 
/**
    Similar to php function scandir(), with differences :
    - doesn't return '.' and '..'
    - returns absolute paths
    - can be recursive
    - options (cf $params) can be used to filter the results
    @param $dir string, required
        absolute path of the directory to scan
    @param $params  associative array, can contain :
        - 'include' : string or array of simplified regular expressions
            default = array('*')
        - 'exclude' : string or array of simplified regular expressions
            default = []
            "simplified regular expressions" means regex as understood by PHP fnmatch() function
            If include is present and not exclude, returns only the included files
            If exclude is present and not include, returns everything but the excluded files
            If both exclude and include are present, returns the files that are included and not excluded
        - 'recursive' : boolean : indicates if subdirs should also be scanned
            default = true
        - 'return-files' : boolean : indicates if regular files should be returned
            default = true
        - 'return-dirs' : boolean : indicates if directories should be returned
            default = true
        - 'cut-path' : string Part of the absolute path to remove
            default = null
            Must not end by "/"
            ex : if $params['cut-path'] = '/home/me/a/long/path'
            will return strings like 'dir1/file1.txt' instead of '/home/me/a/long/path/dir1/file1.txt'
    @return An array containing the absolute (or relative if $params['cut-path'] is used) paths of files and/or dirs
    
    @todo convert $dir to $params['dir']
    @todo handle param check in rscandir
    
    @licence    GPL
    @copyright  jetheme.org
    @history    2010-10-02T20:13:10+02:00, Thierry Graff : Creation 
    @history    2013-05-19 23:47:54+02:00, Thierry Graff : add cut-path parameter
**/

namespace tiglib\filesystem;


class rscandir {
    
    /**
        execute is only used to treat $params['cut-path']
        The job is done by {@link rscandir2()} 
    **/
    public static function execute($dir, $params=[]){
        // default values for parameters, and convert to arrays if necessary
        if(!isset($params['include'])){
            $params['include'] = array('*');
        }
        if(is_string($params['include'])){
            $params['include'] = array($params['include']);
        }
        //
        if(!isset($params['exclude'])){
            $params['exclude'] = [];
        }
        if(is_string($params['exclude'])){
            $params['exclude'] = array($params['exclude']);
        }
        //
        if(!isset($params['recursive'])){
            $params['recursive'] = true;
        }
        //
        if(!isset($params['return-files'])){
            $params['return-files'] = true;
        }
        //
        if(!isset($params['return-dirs'])){
            $params['return-dirs'] = true;
        }
        // do the job
        $dirs = self::rscandir2($dir, $params);
        // cut-path and return
        if(isset($params['cut-path'])){
            $res = [];
            foreach($dirs as $dir){
                $res[] = str_replace($params['cut-path'] . DS, '', $dir);
            }
            return $res;
        }
        return $dirs;
    }
    
    
    /** 
        recursive auxiliary of execute()
    **/
    private static function rscandir2($dir, $params=[]){
        //
        $res = [];
        $entries = scandir($dir);
        foreach($entries as $entry){
            if($entry === '.' || $entry === '..'){
                continue;
            }
            //
            $current = "$dir/$entry";
            $basename = basename($current);
            //
            $keep = false;
            $excluded = false;
            for($i=0; $i < count($params['include']); $i++){
                //if(fnmatch($params['include'][$i], $basename)){
                if(fnmatch($params['include'][$i], $current)){
                    $keep = true;
                    break;
                }
            }
            for($i=0; $i < count($params['exclude']); $i++){
                //if(fnmatch($params['exclude'][$i], $basename)){
                if(fnmatch($params['exclude'][$i], $current)){
                    $keep = false;
                    $excluded = true;
                    break;
                }
            }
            //
            if(is_file($current)){
                if($keep && $params['return-files']){
                    $res[] = $current;
                }
                continue;
            }
            else if(is_dir($current)){
                if($keep && $params['return-dirs']){
                    $res[] = $current;
                }
                if($params['recursive'] && !$excluded){
                    foreach(self::rscandir2($current, $params) as $entry2){ // recursive here
                        $res[]=$entry2;
                    }
                }
            }
        }
        return $res;
    }
    
    
}// end class
