<?php
/**
    Generate js keyboard navigation for a serie of html pages sorted alphabetically.
    Quick hack not documented.
    
    @license    GPL - conforms to file LICENCE located in the root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-02-03 22:46:33+01:00, Thierry Graff : Creation
**/

namespace tigsite\commands;

use tigsite\commands\shared\SiteConfig;
use tigsite\commands\shared\PageConfig;
use tigsite\commands\shared\CheckParams;
use tigsite\commands\shared\ExpandVariables;
use tiglib\patterns\command\Command;
use tiglib\filesystem\rscandir;
use tiglib\strings\slugify;

class prevnext implements Command {
    
    /** Default values for $params['command'] passed to execute() **/
    const DEFAULT_PARAMS = [
    ];
    
//    const POSSIBLE_ACTIONS = ['save', 'print-toc', 'print-full', 'list-files'];
    
    /** Cleaned version of parameter $params passed to execute() **/
    public static array $params = [];
        
    /** 
        @param  $params Associative array that must contain the following keys :
            - 'site' (required) :
                Associative array corresponding to global site configuration
                Ex: contents of config/example/commands/replace-footer.yml
                See format in docs/
            - 'command' (required) :
                Associative array with the following keys :
            @throws \Exception in case of bad parameter
    **/
    public static function execute($params=[]){
        $report = '';
        $dir = $params['site']['site-root'];
        $files = array_map('basename', glob($dir . DS . '*.html'));
        $count = count($files);
        $p = '#<script>\s*let prev = "(.*?)";\s*let next = "(.*?)";\s*</script>#s';
        for($i=0; $i < $count; $i++){
            $file = $files[$i];
            $path = $dir . DS . $file;
            $contents = file_get_contents($path);
            preg_match($p, $contents, $m);
            if(count($m) == 0){
                continue;
            }
            $prev = $files[$i - 1] ?? $files[$count - 1];
            $next = $files[$i + 1] ?? $files[0];
            $newContents = str_replace([$m[1], $m[2]], [$prev, $next], $contents);
            echo "Processing $file\n";
            file_put_contents($path, $newContents);
        }
        echo "Done\n";
    }
    
}// end class
