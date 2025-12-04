<?php
/**
    Inserts existing html code in a page.

    @license    GPL - conforms to file LICENCE located in the root directory of current repository.
    @copyright  Thierry Graff
    @history    2019-02-18 12:13:18+01:00, Thierry Graff : Creation
**/

namespace tigsite\commands;

use tigsite\commands\shared\SiteConfig;
use tigsite\commands\shared\ExpandVariables;
use tigsite\commands\shared\CheckParams;
use tiglib\patterns\command\Command;

class insertHtml implements Command {
    
    /** 
        Inserts existing html code in a page.
        Restrictions : 
        - In the existing pages, the html code specified by $params['before'] or $params['after'] must exist and be unique.
        
        @param  $params Associative array that MUST contain the following keys :
            - 'site' (required) :
                associative array ; see format in docs/
            - 'command' (required) :
                associative array with the following keys :
                - 'before' or 'after' :
                    html piece of code in existing pages
                    to mark the place where the new html must be inserted.
                - 'insert-file' or 'insert-string' :
                    html content to insert
                - 'exclude' : 
                    Array of patterns 
        @throws Exception in case of bad parameter
        
        @todo Add parameters "config-file" and "command-file" (only useful for messages in parameter checking)
    **/
    public static function execute($params=[]){
        //
        // check parameters
        //
        CheckParams::check($params);
        
        $params['site'] = SiteConfig::compute($params['site']);
        
        if(!isset($params['command']['before']) && !isset($params['command']['after'])){
            throw new \InvalidArgumentException("You must specify either 'before' or 'after'");
        }
        if(isset($params['command']['before']) && isset($params['command']['after'])){
            throw new \InvalidArgumentException("You can't specify both 'before' and 'after'");
        }
        //
        if(!isset($params['command']['insert-file']) && !isset($params['command']['insert-string'])){
            throw new \InvalidArgumentException("You must specify either 'insert-file' or 'insert-string'");
        }
        if(isset($params['command']['insert-file']) && isset($params['command']['insert-string'])){
            throw new \InvalidArgumentException("You can't specify both 'insert-file' and 'insert-string'");
        }
        if(!isset($params['command']['exclude'])){
            $params['command']['exclude'] = [];
        }
        
        //
        // compute files to process
        //
        $files = SiteConfig::computeFiles(siteConfig: $params['site'], command: $params['command']);
        
        //
        // do the job
        //
        if(isset($params['command']['insert-file'])){
            $insert = file_get_contents($params['site']['site-root'] . DS . $params['command']['insert-file']);
        }
        else{
            $insert = $params['command']['insert-string'];
        }
        
        if(isset($params['command']['before'])){
            $find = $params['command']['before'];
            $replace = $insert . $params['command']['before'];
        }
        else{
            $find = $params['command']['after'];
            $replace = $params['command']['after'] . $insert;
        }
        $N = count($files);
        for($i=0; $i < $N; $i++){
            echo "processing {$files[$i]}\n";
            $old = file_get_contents($files[$i]);
            $replace2 = ExpandVariables::expand($replace, ['root-dir' => $params['site']['site-root'], 'current-file' => $files[$i]]);
            $new = str_replace($find, $replace2, $old);
            file_put_contents($files[$i], $new);
        }
    }
    
}// end class